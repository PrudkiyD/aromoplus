<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Order\ProductItem;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;

class TopProductsTable extends BaseWidget
{
    protected static ?string $heading = 'Топ-5 ходових товарів';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = '1/2'; // Займає половину екрану

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductItem::query()
                    ->join('order_order', 'order_productitem.order_id', '=', 'order_order.id')
                    ->join('product_product', 'order_productitem.product_id', '=', 'product_product.id')
                    ->where('order_order.status', Order::STATUS_SUCCESSFUL)
                    ->select(
                        'product_product.id as id', // Filament автоматично підхопить це поле як ключ запису
                        'product_product.name',
                        'product_product.internal_sku',
                        DB::raw('SUM(order_productitem.quantity) as total_qty'),
                        DB::raw('SUM(order_productitem.price * order_productitem.quantity) as total_earned')
                    )
                    ->groupBy('product_product.id', 'product_product.name', 'product_product.internal_sku')
                    ->orderByDesc('total_qty')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва товару')
                    ->limit(30),
                Tables\Columns\TextColumn::make('internal_sku')
                    ->label('Артикул'),
                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Продано (шт)'),
                Tables\Columns\TextColumn::make('total_earned')
                    ->label('Сума (₴)')
                    ->money('UAH'),
            ])
            ->paginated(false); // Вимикаємо пагінацію, бо це фіксований Топ-5
    }
}