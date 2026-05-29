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

    public function updatePopularity()
    {
        $dateLimit = now()->subDays(365);

        // -------------------------------------------------
        // 1. ЗБИРАЄМО СТАТИСТИКУ
        // -------------------------------------------------

        $stats = Product::all()->map(function ($product) use ($dateLimit) {

            // Перегляди товару за 365 днів
            $viewsCount = $product->views()
                ->where('created_at', '>=', $dateLimit)
                ->count();

            // Замовлення + кількість проданих штук
            $orderStats = DB::table('order_productitem')
                ->where('product_id', $product->id)
                ->where('created_at', '>=', $dateLimit)
                ->select(
                    DB::raw('COUNT(DISTINCT order_id) as orders_count'),
                    DB::raw('SUM(quantity) as items_count')
                )
                ->first();

            $orders = $orderStats->orders_count ?? 0;
            $items  = $orderStats->items_count ?? 0;

            // -------------------------------------------------
            // ЗМІНА №1
            // ДОДАВ КОНВЕРСІЮ
            // -------------------------------------------------

            $conversion = $viewsCount > 0
                ? $orders / $viewsCount
                : 0;

            return [
                'id'         => $product->id,
                'orders'     => $orders,
                'items'      => $items,
                'views'      => $viewsCount,
                'conversion' => $conversion,
            ];
        });

        // -------------------------------------------------
        // 2. ЗНАХОДИМО МАКСИМУМИ
        // -------------------------------------------------

        $maxOrders     = max($stats->pluck('orders')->toArray()) ?: 1;
        $maxItems      = max($stats->pluck('items')->toArray()) ?: 1;
        $maxConversion = max($stats->pluck('conversion')->toArray()) ?: 1;

        // -------------------------------------------------
        // 3. РАХУЄМО POPULARITY
        // -------------------------------------------------

        foreach ($stats as $stat) {

            // -------------------------------------------------
            // ЗМІНА №2
            // LOG НОРМАЛІЗАЦІЯ
            // -------------------------------------------------

            $scoreOrders = (
                log(1 + $stat['orders']) /
                log(1 + $maxOrders)
            ) * 5;

            $scoreItems = (
                log(1 + $stat['items']) /
                log(1 + $maxItems)
            ) * 5;

            $scoreConversion = (
                log(1 + $stat['conversion']) /
                log(1 + $maxConversion)
            ) * 5;

            // -------------------------------------------------
            // ЗМІНА №3
            // НОВІ ВАГИ
            // -------------------------------------------------

            $popularity =
                ($scoreOrders * 0.6) +
                ($scoreItems * 0.3) +
                ($scoreConversion * 0.1);

            // -------------------------------------------------
            // ЗМІНА №4
            // МІНІМАЛЬНИЙ ПОРІГ
            // -------------------------------------------------

            if ($stat['orders'] < 3) {
                $popularity = 0;
            }

            // Округлення
            $popularity = round($popularity, 1);

            // Захист від значень > 5
            $popularity = min($popularity, 5);

            // Оновлення товару
            Product::where('id', $stat['id'])
                ->update([
                    'popularity' => $popularity
                ]);
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