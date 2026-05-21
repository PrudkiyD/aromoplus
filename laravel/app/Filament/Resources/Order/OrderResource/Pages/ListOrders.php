<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use App\Filament\Resources\Order\OrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Order\Order;
use Illuminate\Support\Facades\Http;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatuses')
            ->label('Оновити статуси НП')
            ->color('gray')
            ->icon('heroicon-o-arrow-path')
            ->requiresConfirmation()
            ->action(function () {
                $apiKey = env('NOVA_KEY');
                
                // Обираємо замовлення зі статусами ready або shipped
                // Також важливо, щоб у замовлення був заповнений номер ТТН
                $orders = Order::whereIn('status', ['ready', 'shipped'])
                    ->whereNotNull('ttn') 
                    ->where('ttn', '!=', '')
                    ->get();

                if ($orders->isEmpty()) {
                    \Filament\Notifications\Notification::make()
                        ->title('Немає замовлень для оновлення')
                        ->warning()
                        ->send();
                    return;
                }

                // Готуємо список ТТН для одного запиту (так швидше)
                $documents = $orders->map(function ($order) {
                    return ['DocumentNumber' => $order->ttn, 'Phone' => ''];
                })->toArray();

                $response = Http::post('https://api.novaposhta.ua/v2.0/json/', [
                    'apiKey' => $apiKey,
                    'modelName' => 'TrackingDocument',
                    'calledMethod' => 'getStatusDocuments',
                    'methodProperties' => [
                        'Documents' => $documents,
                    ],
                ]);

                if ($response->successful()) {
                    $results = $response->json();
                    
                    if (!empty($results['success']) && isset($results['data'])) {
                        foreach ($results['data'] as $tracking) {
                            $code = (int)$tracking['StatusCode'];
                            $currentTtn = $tracking['Number'];

                            // Визначаємо новий статус
                            $newStatus = match (true) {
                                in_array($code, [1, 2, 3])            => 'ready',
                                in_array($code, [4, 41, 5, 6, 101, 7, 8]) => 'shipped',
                                in_array($code, [9, 10, 11])          => 'successful',
                                in_array($code, [102, 103, 104, 108]) => 'canceled',
                                default                               => null,
                            };

                            // Оновлюємо замовлення в базі
                            if ($newStatus) {
                                Order::where('ttn', $currentTtn)
                                    ->whereIn('status', ['ready', 'shipped'])
                                    ->update(['status' => $newStatus]);
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Статуси оновлено успішно')
                            ->success()
                            ->send();
                    }
                } else {
                    \Filament\Notifications\Notification::make()
                        ->title('Помилка запиту до Нової Пошти')
                        ->danger()
                        ->send();
                }
            }),
            Actions\CreateAction::make(),
        ];
    }
}
