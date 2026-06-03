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

    protected function getData(): array
    {
        $ordersTable = (new Order())->getTable();

        // 1. Отримуємо дату найпершого замовлення для КОЖНОГО клієнта за весь час
        $firstOrdersSub = Order::where('status', '!=', 'canceled')
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('MIN(created_at) as first_order_at'))
            ->groupBy('customer_id');

        // 2. Отримуємо ВСІ замовлення за весь період, групуючи за Роком та Місяцем
        $ordersData = Order::where("{$ordersTable}.status", '!=', 'canceled')
            ->leftJoinSub($firstOrdersSub, 'first_orders', function ($join) use ($ordersTable) {
                $join->on("{$ordersTable}.customer_id", '=', 'first_orders.customer_id');
            })
            ->select(
                DB::raw("YEAR({$ordersTable}.created_at) as year"),
                DB::raw("MONTH({$ordersTable}.created_at) as month"),
                DB::raw("SUM({$ordersTable}.total) as total_sales"),
                // Новий клієнт: якщо місяць і рік замовлення збігаються з його найпершим замовленням за весь час
                DB::raw("COUNT(DISTINCT CASE WHEN MONTH({$ordersTable}.created_at) = MONTH(first_orders.first_order_at) AND YEAR({$ordersTable}.created_at) = YEAR(first_orders.first_order_at) THEN {$ordersTable}.customer_id END) as unique_new_customers"),
                // Повторний клієнт: якщо він купує в місяці, який не є його найпершим
                DB::raw("COUNT(DISTINCT CASE WHEN MONTH({$ordersTable}.created_at) != MONTH(first_orders.first_order_at) OR YEAR({$ordersTable}.created_at) != YEAR(first_orders.first_order_at) THEN {$ordersTable}.customer_id END) as unique_returning_customers")
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $salesData = [];
        $newCustomersData = [];
        $returningCustomersData = [];
        $labels = [];

        $allMonthsLabels = ['Січ', 'Лют', 'Бер', 'Квіт', 'Трав', 'Черв', 'Лип', 'Серп', 'Верес', 'Жовт', 'Лист', 'Груд'];

        // 3. Динамічно наповнюємо масиви на основі отриманих з бази даних періодів
        foreach ($ordersData as $data) {
            // Формуємо гарну мітку для осі Х, наприклад: "Черв 2026" або "Січ 2025"
            $labels[] = $allMonthsLabels[$data->month - 1] . ' ' . $data->year;

            $salesData[] = (float) $data->total_sales;
            $newCustomersData[] = (int) $data->unique_new_customers;
            $returningCustomersData[] = (int) $data->unique_returning_customers;
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
                    'label' => 'Нові клієнти (унікальні)',
                    'data' => $newCustomersData,
                    'backgroundColor' => '#3B82F6',
                    'borderRadius' => 6,
                    'stack' => 'customers',
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Повторні клієнти (унікальні)',
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