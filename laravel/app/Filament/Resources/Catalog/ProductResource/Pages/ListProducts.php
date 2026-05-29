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

    public function updateClassSafe()
    {
        // 1. Збираємо статистику успішних замовлень за останні 365 днів
        $oneYearAgo = now()->subDays(365);
        
        $salesData = \App\Models\Order\ProductItem::query()
            ->whereHas('order', function ($query) use ($oneYearAgo) {
                $query->where('status', \App\Models\Order\Order::STATUS_SUCCESSFUL)
                    ->where('created_at', '>=', $oneYearAgo);
            })
            ->select('product_id')
            ->selectRaw('SUM(quantity * price) as total_revenue') // Для ABC
            ->selectRaw('COUNT(DISTINCT DATE_FORMAT(created_at, "%Y-%m")) as months_count') // Для XYZ
            ->selectRaw('SUM(quantity) / 365 as daily_velocity') // Середня швидкість продажів на день
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Якщо за рік замовлень взагалі не було — зупиняємо, щоб не занулити наявні дані
        if ($salesData->isEmpty()) {
            return;
        }

        // 2. Сортуємо для ABC-аналізу за спаданням виторгу
        $sortedForAbc = $salesData->sortByDesc('total_revenue');
        $totalRevenueAll = $sortedForAbc->sum('total_revenue');
        
        $runningSum = 0;
        $abcMap = [];
        $xyzMap = [];
        $velocityMap = [];

        foreach ($sortedForAbc as $productId => $data) {
            $runningSum += $data->total_revenue;
            $percentage = ($runningSum / $totalRevenueAll) * 100;

            // Розподіл ABC (80% / 15% / 5%)
            if ($percentage <= 80) {
                $abcMap[$productId] = 'A';
            } elseif ($percentage <= 95) {
                $abcMap[$productId] = 'B';
            } else {
                $abcMap[$productId] = 'C';
            }

            $xyzMap[$productId] = $data->months_count;
            $velocityMap[$productId] = $data->daily_velocity;
        }

        // 3. Порційно (chunk) оновлюємо всі товари в базі даних
        Product::query()->chunk(200, function ($products) use ($abcMap, $xyzMap, $velocityMap) {
            foreach ($products as $product) {
                $id = $product->id;

                // Якщо запчастину за рік жодного разу не купили — за замовчуванням даємо C і Z
                $abc = $abcMap[$id] ?? 'C';
                $monthsWithSales = $xyzMap[$id] ?? 0;
                $velocity = $velocityMap[$id] ?? 0;

                // Логіка XYZ (ваше правило регулярності)
                if ($monthsWithSales >= 11) {
                    $xyz = 'X'; // Щомісяця
                } elseif ($monthsWithSales >= 4) {
                    $xyz = 'Y'; // Раз на 3 місяці (квартал)
                } else {
                    $xyz = 'Z'; // Всі інші (хаос / рідкісні)
                }

                // Визначаємо кількість днів для страхового запасу (safety_stock)
                $safetyDays = 0;
                $combination = $abc . $xyz;

                switch ($combination) {
                    case 'AX':
                        $safetyDays = 14; // Стабільний ТОП (подушка 2 тижні)
                        break;
                    case 'AY':
                    case 'AZ':
                        $safetyDays = 45; // Нестабільний ТОП — як ваш носик (подушка 1.5 міс)
                        break;
                    case 'BX':
                    case 'BY':
                        $safetyDays = 21; // Середній стабільний (3 тижні)
                        break;
                    case 'BZ':
                        $safetyDays = 30; // Середній нестабільний (1 місяць)
                        break;
                    case 'CX':
                    case 'CY':
                        $safetyDays = 60; // Ходова копійчана дрібнота (беремо на 2 міс наперед)
                        break;
                    case 'CZ':
                    default:
                        $safetyDays = 0; // Рідкісні запчастини (страховий запас не потрібен)
                        break;
                }

                // Розраховуємо кінцеву кількість штук для страхового запасу
                $safetyStock = (int) ceil($velocity * $safetyDays);

                // Оновлюємо три поля в базі даних без зачіпання timestamps
                $product->timestamps = false;
                $product->update([
                    'abc_class' => $abc,
                    'xyz_class' => $xyz,
                    'safety_stock' => $safetyStock,
                ]);
            }
        });
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
                ->modalDescription('Оновити наявність, кількість, ціни та прорахувати популярність товарів, ABC\XYZ, страховий залишок?')
                ->action(function(){ 
                    $this->updateProduct();
                    $this->updatePopularity();
                    $this->updateClassSafe();
                }),
                
            Actions\CreateAction::make(),
        ];
    }
}