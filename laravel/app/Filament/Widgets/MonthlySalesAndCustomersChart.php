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

        $orders = Order::where('status', '!=', 'canceled')
            ->whereYear('created_at', $currentYear)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $firstOrders = Order::where('status', '!=', 'canceled')
            ->select('customer_id', DB::raw('MIN(created_at) as first_order_date'))
            ->whereNotNull('customer_id')
            ->groupBy('customer_id');

        $newCustomersByMonth = DB::table(DB::raw("({$firstOrders->toSql()}) as first_orders"))
            ->mergeBindings($firstOrders->getQuery())
            ->whereYear('first_order_date', $currentYear)
            ->select(DB::raw('MONTH(first_order_date) as month'), DB::raw('COUNT(customer_id) as count'))
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $salesData = [];
        $newCustomersData = [];
        $returningCustomersData = [];
        $totalOrdersData = []; //Загальна кількість замовлень
        $labels = [];

        $allMonthsLabels = ['Січ', 'Лют', 'Бер', 'Квіт', 'Трав', 'Черв', 'Лип', 'Серп', 'Верес', 'Жовт', 'Лист', 'Груд'];

        for ($m = 1; $m <= $currentMonth; $m++) {
            $labels[] = $allMonthsLabels[$m - 1];

            $monthOrder = $orders->get($m);
            $salesData[] = $monthOrder ? (float) $monthOrder->total_sales : 0;

            $totalOrdersCount = $monthOrder ? (int) $monthOrder->total_orders : 0;
            $totalOrdersData[] = $totalOrdersCount;

            $newCount = $newCustomersByMonth->get($m)?->count ?? 0;
            $newCustomersData[] = $newCount;

            $returningCount = max(0, $totalOrdersCount - $newCount);
            $returningCustomersData[] = $returningCount;
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
                    'label' => 'Всього замовлень',
                    'data' => $totalOrdersData,
                    'borderColor' => '#8B5CF6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'yAxisID' => 'y1',
                    'type' => 'line',
                    'borderDash' => [5, 5],
                ],
                [
                    'label' => 'Нові клієнти',
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