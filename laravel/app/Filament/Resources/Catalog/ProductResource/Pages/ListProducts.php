<?php

namespace App\Filament\Resources\Catalog\ProductResource\Pages;

use App\Filament\Resources\Catalog\ProductResource;
use App\Models\Catalog\Product;
use Illuminate\Support\Facades\DB;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use ZipArchive;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function updateProduct(){
        $zipPath = '/var/www/aromoplus1/data/www/aromoplus.com.ua/files/From1C.zip';
        $xmlFileName = 'From1C.xml';

        if (!file_exists($zipPath)) {
            Notification::make()->title('Архів не знайдено')->danger()->send();
            return;
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $xmlContent = $zip->getFromName($xmlFileName);
            $zip->close();

            if (!$xmlContent) {
                Notification::make()->title('Не вдалося прочитати XML з архіву')->danger()->send();
                return;
            }

            try {
                $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
                
                if (!$xml) {
                    throw new \Exception("Помилка структури XML");
                }

                $importCount = 0;


                //Оновлення наявності
                if (isset($xml->Products->ITEM)) {
                    foreach ($xml->Products->ITEM as $item) {
                        $attributes = $item->attributes();
                        
                        $guid = (string) $attributes['GUID'];
                        $name = (string) $attributes['Name'];
                        $art  = (string) $attributes['Art'];

                        $product = Product::firstOrCreate(
                            [
                                'one_c_sku' => $guid
                            ],
                            [
                                'is_published' => false,
                                'name' => $name,
                                'description' => $art .$name,
                                'manufacturer_sku' => $art,
                                'availability' => 'in_stock',
                                'quantity' => 0,
                                'price' => 0,
                                'main_image' => 'product-images/photo-camera-off-svgrepo-com_lj0ut13.jpg',
                            ]
                        );

                        if ($product->wasRecentlyCreated) {
                            $product->categories()->syncWithoutDetaching([17]);
                        }
                        $importCount++;
                    }
                }
                

                //Оновлення залишків
                Product::whereNotNull('one_c_sku')->update(['quantity' => 0]);
                if (isset($xml->StockBalance->ITEM)) {
                    foreach ($xml->StockBalance->ITEM as $item) {
                        $attributes = $item->attributes();
                        
                        $guid = (string) $attributes['Product'];
                        $balance = (string) $attributes['Balance'];

                        Product::where('one_c_sku', $guid)->update([
                            'quantity' => $balance,
                            
                        ]);
                    }
                }

                //Оновлення ціни
                if (isset($xml->Prices->ITEM)) {
                    foreach ($xml->Prices->ITEM as $item) {
                        $attributes = $item->attributes();
                        
                        $guid = (string) $attributes['Product'];
                        $price = (string) $attributes['Price'];
                        
                        Product::where('one_c_sku', $guid)->update(['price' => $price]);
                    }
                }


                Notification::make()
                    ->title('Обробка завершена')
                    ->body("Успішно опрацьовано {$importCount} товарів з файлу.")
                    ->success()
                    ->send();
                

            } catch (\Exception $e) {
                Notification::make()
                    ->title('Помилка парсингу')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        } else {
            Notification::make()->title('Не вдалося відкрити ZIP')->danger()->send();
        }
    }

    public function updatePopularity(){
        $dateLimit = now()->subDays(365);

        // 1. Збираємо статистику по кожному товару
        $stats = Product::all()->map(function ($product) use ($dateLimit) {
            // Рахуємо унікальні перегляди через модель/зв'язок View
            $viewsCount = $product->views()->where('created_at', '>=', $dateLimit)->count();

            // Рахуємо кількість замовлень та проданих штук
            $orderStats = DB::table('order_productitem')
                ->where('product_id', $product->id)
                ->where('created_at', '>=', $dateLimit)
                ->select(
                    DB::raw('COUNT(DISTINCT order_id) as orders_count'),
                    DB::raw('SUM(quantity) as items_count')
                )->first();

            return [
                'id'      => $product->id,
                'orders'  => $orderStats->orders_count ?? 0,
                'items'   => $orderStats->items_count ?? 0,
                'views'   => $viewsCount,
            ];
        });

        // 2. Знаходимо максимуми по всьому сайту для лінійної нормалізації
        $maxOrders = max($stats->pluck('orders')->toArray()) ?: 1;
        $maxItems  = max($stats->pluck('items')->toArray()) ?: 1;
        $maxViews  = max($stats->pluck('views')->toArray()) ?: 1;

        // 3. Рахуємо бали та оновлюємо поле popularity у базі даних
        foreach ($stats as $stat) {
            // Приводимо кожну метрику до шкали від 0 до 5
            $scoreOrders = ($stat['orders'] / $maxOrders) * 5;
            $scoreItems  = ($stat['items'] / $maxItems) * 5;
            $scoreViews  = ($stat['views'] / $maxViews) * 5;

            // Вага для B2B: 60% — замовлення, 20% — штуки, 20% — перегляди
            $popularity = ($scoreOrders * 0.6) + ($scoreItems * 0.2) + ($scoreViews * 0.2);
            
            // Округлюємо до одного знака після коми (наприклад, 4.2)
            $popularity = round($popularity, 1);

            // Оновлюємо товар у базі
            Product::where('id', $stat['id'])->update(['popularity' => $popularity]);
        }
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Оновити каталог')
                ->color('gray')
                ->icon('heroicon-o-arrow-path') // Змінив на іконку оновлення
                ->requiresConfirmation() // Додасть вікно підтвердження
                ->modalHeading('Оновлення каталогу товарів')
                ->modalDescription('Оновити наявність, кількість, ціни та прорахувати популярність товарів?')
                ->action(function(){ 
                    $this->updateProduct();
                    $this->updatePopularity();
                }),
                
            Actions\CreateAction::make(),
        ];
    }
}