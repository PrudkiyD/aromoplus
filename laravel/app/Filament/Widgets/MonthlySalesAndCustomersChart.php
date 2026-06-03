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
        $ordersTable = (new Order())->getTable();

        // 1. Дата найпершого замовлення для кожного клієнта (за весь час)
        $firstOrdersSub = Order::where('status', '!=', 'canceled')
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('MIN(created_at) as first_order_at'))
            ->groupBy('customer_id');

        // 2. Рахуємо саме УНІКАЛЬНИХ клієнтів (DISTINCT)
        $analyticsData = Order::where("{$ordersTable}.status", '!=', 'canceled')
            ->whereYear("{$ordersTable}.created_at", $currentYear)
            ->leftJoinSub($firstOrdersSub, 'first_orders', function ($join) use ($ordersTable) {
                $join->on("{$ordersTable}.customer_id", '=', 'first_orders.customer_id');
            })
            ->select(
                DB::raw("MONTH({$ordersTable}.created_at) as month"),
                DB::raw("SUM({$ordersTable}.total) as total_sales"),
                // Рахуємо унікальних клієнтів, чиє перше замовлення було в цьому місяці
                DB::raw("COUNT(DISTINCT CASE WHEN MONTH({$ordersTable}.created_at) = MONTH(first_orders.first_order_at) AND YEAR({$ordersTable}.created_at) = YEAR(first_orders.first_order_at) THEN {$ordersTable}.customer_id END) as unique_new_customers"),
                // Рахуємо унікальних клієнтів, які вже купували в минулих місяцях/роках
                DB::raw("COUNT(DISTINCT CASE WHEN MONTH({$ordersTable}.created_at) != MONTH(first_orders.first_order_at) OR YEAR({$ordersTable}.created_at) != YEAR(first_orders.first_order_at) THEN {$ordersTable}.customer_id END) as unique_returning_customers")
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
            $monthData = $analyticsData->get($m);

            $salesData[] = $monthData ? (float) $monthData->total_sales : 0;
            $newCustomersData[] = $monthData ? (int) $monthData->unique_new_customers : 0;
            $returningCustomersData[] = $monthData ? (int) $monthData->unique_returning_customers : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Продажі (₴)',
                    'data' => $salesData,
                    'borderColor' => '#10B981',
                    'type' => 'line',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Нові клієнти',
                    'data' => $newCustomersData,
                    'backgroundColor' => '#3B82F6',
                    'stack' => 'customers',
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Повторні клієнти',
                    'data' => $returningCustomersData,
                    'backgroundColor' => '#F59E0B',
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