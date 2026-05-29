<?php

namespace App\Filament\Resources\Catalog\ProductResource\Pages;

use App\Filament\Resources\Catalog\ProductResource;
use App\Models\Catalog\Product;
use App\Models\Order\ProductItem;
use App\Models\Order\Order;
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
            ->join('order_order', 'order_order.id', '=', 'product_items.order_id') // виправлення #3: join для доступу до дати замовлення
            ->where('order_order.status', \App\Models\Order\Order::STATUS_SUCCESSFUL)
            ->where('order_order.created_at', '>=', $oneYearAgo)
            ->select('product_items.product_id')
            ->selectRaw('SUM(product_items.quantity * product_items.price) as total_revenue')
            ->selectRaw('COUNT(DISTINCT DATE_FORMAT(order_order.created_at, "%Y-%m")) as months_count') // виправлення #3:
            ->selectRaw('SUM(product_items.quantity) / 365 as daily_velocity')
            ->groupBy('product_items.product_id')
            ->get()
            ->keyBy('product_id');

        if ($salesData->isEmpty()) {
            return;
        }

        // 2. ABC-аналіз за виторгом
        $sortedForAbc = $salesData->sortByDesc('total_revenue');
        $totalRevenueAll = $sortedForAbc->sum('total_revenue');

        $runningSum = 0;
        $abcMap = [];
        $xyzMap = [];
        $velocityMap = [];

        foreach ($sortedForAbc as $productId => $data) {
            $runningSum += $data->total_revenue;
            $percentage = ($runningSum / $totalRevenueAll) * 100;

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

        // 3. Оновлення товарів
        Product::query()->chunk(200, function ($products) use ($abcMap, $xyzMap, $velocityMap) {
            foreach ($products as $product) {
                $id = $product->id;

                $abc = $abcMap[$id] ?? 'C';
                $monthsWithSales = $xyzMap[$id] ?? 0;
                $velocity = $velocityMap[$id] ?? 0;

                // виправлення #1 і #2: пороги через відсотки від 12 місяців
                $totalMonths = 12;
                $ratio = $monthsWithSales / $totalMonths;

                if ($ratio >= 0.67) {       // 8+ місяців із 12 — стабільний
                    $xyz = 'X';
                } elseif ($ratio >= 0.33) { // 4–7 місяців — нерегулярний
                    $xyz = 'Y';
                } else {                    // 0–3 місяці — хаотичний
                    $xyz = 'Z';
                }

                $safetyDays = 0;
                $combination = $abc . $xyz;

                switch ($combination) {
                    case 'AX':
                        $safetyDays = 14;
                        break;
                    case 'AY':
                    case 'AZ':
                        $safetyDays = 45;
                        break;
                    case 'BX':
                    case 'BY':
                        $safetyDays = 21;
                        break;
                    case 'BZ':
                        $safetyDays = 30;
                        break;
                    case 'CX':
                    case 'CY':
                        $safetyDays = 60;
                        break;
                    case 'CZ':
                    default:
                        $safetyDays = 0;
                        break;
                }

                $safetyStock = (int) ceil($velocity * $safetyDays);

                $product->timestamps = false;
                $product->update([
                    'abc_class'    => $abc,
                    'xyz_class'    => $xyz,
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