<?php

namespace App\Filament\Resources\Catalog\ProductResource\Pages;

use App\Filament\Resources\Catalog\ProductResource;
use App\Models\Catalog\Product;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use ZipArchive;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Оновити каталог')
                ->color('gray')
                ->icon('heroicon-o-arrow-path') // Змінив на іконку оновлення
                ->requiresConfirmation() // Додасть вікно підтвердження
                ->modalHeading('Оновлення з 1С')
                ->modalDescription('Ви впевнені, що хочете прочитати дані з архіву From1C.zip?')
                ->action(function () {
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
                }),
                
            Actions\CreateAction::make(),
        ];
    }
}