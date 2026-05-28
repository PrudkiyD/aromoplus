<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\Order\Order;

class PartsSalesChart extends ChartWidget
{
    // Заголовок віджета в панелі
    protected static ?string $heading = 'Продажі запчастин за категоріями';
    
    // Сортування (опціонально — щоб відображався у правильному місці)
    protected static ?int $sort = 1;

    // Оновлюємо дані в реальному часі кожні 5 хвилин (опціонально)
    protected static ?string $pollingInterval = '300s';

    protected function getData(): array
    {
        // Робимо швидкий запит через DB для підрахунку проданої кількості (quantity)
        // Враховуємо лише успішні чи оброблені замовлення (виключаємо скасовані)
        $salesByCategory = DB::table('order_productitem')
            ->join('order_order', 'order_productitem()->order_id', '=', 'order_order.id')
            ->join('product_product_category', 'order_productitem.product_id', '=', 'product_product_category.product_id')
            ->join('product_category', 'product_product_category.category_id', '=', 'product_category.id')
            // Фільтруємо замовлення, щоб не рахувати "скасовані" або "нові" (налаштуйте під себе)
            ->whereIn('order_order.status', [
                Order::STATUS_READY, 
                Order::STATUS_SHIPPED, 
                Order::STATUS_SUCCESSFUL
            ])
            ->select('product_category.name as category_name', DB::raw('SUM(order_productitem.quantity) as total_qty'))
            ->groupBy('product_category.id', 'product_category.name')
            ->orderByDesc('total_qty')
            ->limit(10) // Обмежуємо топ-10 категорій, щоб діаграма не перетворилась на "кашу"
            ->get();

        // Формуємо масиви для графіка
        $labels = $salesByCategory->pluck('category_name')->toArray();
        $data = $salesByCategory->pluck('total_qty')->toArray();

        // Палітра кольорів для секторів діаграми
        $backgroundColors = [
            '#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6',
            '#ec4899', '#6b7280', '#14b8a6', '#f43f5e', '#a855f7'
        ];

        // Зрізаємо кольори під кількість категорій, що реально повернулися
        $colorsForChart = array_slice($backgroundColors, 0, count($data));

        return [
            'datasets' => [
                [
                    'label' => 'Продано одиниць',
                    'data' => $data,
                    'backgroundColor' => $colorsForChart,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        // Повертаємо 'pie' для секторної діаграми, або 'doughnut' для діаграми з "діркою" всередині
        return 'pie'; 
    }
}