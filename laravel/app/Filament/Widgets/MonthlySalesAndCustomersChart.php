<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlySalesAndCustomersChart extends ChartWidget
{
    protected static ?string $heading = 'Аналітика продажів (в розробці)';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = '1/2';

    protected function getData(): array
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // 1. Отримуємо дату найпершого замовлення для КОЖНОГО клієнта за весь час
        $firstOrdersSub = Order::where('status', '!=', 'canceled')
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('MIN(created_at) as first_order_at'))
            ->groupBy('customer_id');

        // 2. Витягуємо всі замовлення за поточний рік та за допомогою JOIN 
        // визначаємо, чи створене замовлення в той самий місяць/рік, коли клієнт зробив першу покупку.
        $ordersData = Order::where('orders.status', '!=', 'canceled')
            ->whereYear('orders.created_at', $currentYear)
            ->leftJoinSub($firstOrdersSub, 'first_orders', function ($join) {
                $join->on('orders.customer_id', '=', 'first_orders.customer_id');
            })
            ->select(
                DB::raw('MONTH(orders.created_at) as month'),
                DB::raw('SUM(orders.total) as total_sales'),
                // Замовлення вважається замовленням нового клієнта, якщо місяць і рік замовлення збігаються з його найпершим замовленням
                DB::raw('COUNT(CASE WHEN MONTH(orders.created_at) = MONTH(first_orders.first_order_at) AND YEAR(orders.created_at) = YEAR(first_orders.first_order_at) THEN 1 END) as new_customers_orders'),
                // Усі інші замовлення — це повторні замовлення від старих клієнтів
                DB::raw('COUNT(CASE WHEN MONTH(orders.created_at) != MONTH(first_orders.first_order_at) OR YEAR(orders.created_at) != YEAR(first_orders.first_order_at) OR first_orders.customer_id IS NULL THEN 1 END) as returning_orders')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $salesData = [];
        $newCustomersData = [];
        $returningCustomersData = [];
        $labels = [];

        $allMonthsLabels = ['Січ', 'Лют', 'Бер', 'Квіт', 'Трав', 'Черв', 'Лип', 'Серп', 'Верес', 'Жовт', 'Лист', 'Груд'];

        for ($m = 1; $m <= $currentMonth; $m++) {
            $labels[] = $allMonthsLabels[$m - 1];

            $monthData = $ordersData->get($m);

            $salesData[] = $monthData ? (float) $monthData->total_sales : 0;
            $newCustomersData[] = $monthData ? (int) $monthData->new_customers_orders : 0;
            $returningCustomersData[] = $monthData ? (int) $monthData->returning_orders : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Продажі (₴)',
                    'data' => $salesData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.05)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'yAxisID' => 'y',
                    'type' => 'line',
                ],
                [
                    'label' => 'Нові клієнти', // фактично: замовлення від нових клієнтів
                    'data' => $newCustomersData,
                    'backgroundColor' => '#3B82F6',
                    'borderRadius' => 6,
                    'stack' => 'customers',
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Повторні замовлення',
                    'data' => $returningCustomersData,
                    'backgroundColor' => '#F59E0B',
                    'borderRadius' => 6,
                    'stack' => 'customers',
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'boxWidth' => 8,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.1)',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Сума продажів (₴)',
                        'color' => '#9CA3AF',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Кількість замовлень',
                        'color' => '#9CA3AF',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }
}