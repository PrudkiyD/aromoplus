<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;

class TopCustomersTable extends BaseWidget
{
    protected static ?string $heading = 'Топ-5 покупців';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = '1/2'; // Займає другу половину екрану

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->join('user_customer', 'order_order.customer_id', '=', 'user_customer.id')
                    ->where('order_order.status', Order::STATUS_SUCCESSFUL)
                    ->select(
                        'user_customer.id as id', // Filament автоматично підхопить це поле як ключ запису
                        'user_customer.name',
                        'user_customer.phone',
                        DB::raw('COUNT(order_order.id) as orders_count'),
                        DB::raw('SUM(order_order.total) as total_spent')
                    )
                    ->groupBy('user_customer.id', 'user_customer.name', 'user_customer.phone')
                    ->orderByDesc('total_spent')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Клієнт'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон'),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Замовлень'),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Всього витратив')
                    ->money('UAH'),
            ])
            ->paginated(false); // Вимикаємо пагінацію
    }
}