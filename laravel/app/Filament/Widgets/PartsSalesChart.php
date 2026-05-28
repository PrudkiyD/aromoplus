<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\Order\Order;

class PartsSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Сума продажів за категоріями (в розробці)';
    protected int | string | array $columnSpan = '1/2';
    protected static ?int $sort = 1;
    protected function getData(): array
    {
        $salesByCategory = DB::table('order_productitem')
            ->join('order_order', 'order_productitem.order_id', '=', 'order_order.id')
            ->join('product_product_category', 'order_productitem.product_id', '=', 'product_product_category.product_id')
            ->join('product_category', 'product_product_category.category_id', '=', 'product_category.id')
            ->whereIn('order_order.status', [
                Order::STATUS_READY, 
                Order::STATUS_SHIPPED, 
                Order::STATUS_SUCCESSFUL
            ])
            ->where('product_category.is_published', true)
            ->where('order_order.created_at', '>=', now()->subDays(365))
            ->select(
                'product_category.name as category_name', 
                DB::raw('SUM(order_productitem.price * order_productitem.quantity) as total_sum')
            )
            ->groupBy('product_category.id', 'product_category.name')
            ->orderByDesc('total_sum')
            ->limit(10)
            ->get();

        $labels = $salesByCategory->pluck('category_name')->toArray();

        $data = $salesByCategory->pluck('total_sum')->map(fn ($value) => round($value, 2))->toArray();

        $backgroundColors = [
            '#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6',
            '#ec4899', '#6b7280', '#14b8a6', '#f43f5e', '#a855f7'
        ];

        $colorsForChart = array_slice($backgroundColors, 0, count($data));

        return [
            'datasets' => [
                [
                    'label' => 'Сума продажів',
                    'data' => $data,
                    'backgroundColor' => $colorsForChart,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie'; 
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}