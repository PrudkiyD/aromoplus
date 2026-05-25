<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlySalesAndCustomersChart extends ChartWidget
{
    protected static ?string $heading = 'Аналітика продажів та клієнтів за поточний рік';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full'; // На всю ширину екрану

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        // 1. Отримуємо успішні замовлення за поточний рік, згруповані по місяцях
        $orders = Order::where('status', Order::STATUS_SUCCESSFUL)
            ->whereYear('created_at', $currentYear)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 2. Визначаємо нових та повторних клієнтів по місяцях
        // Новий клієнт — це той, чиє НАЙПЕРШЕ успішне замовлення було в цьому місяці
        $firstOrders = Order::where('status', Order::STATUS_SUCCESSFUL)
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

        // Формуємо масиви даних для всіх 12 місяців
        $salesData = [];
        $newCustomersData = [];
        $returningCustomersData = [];

        $monthsLabels = ['Січ', 'Лют', 'Бер', 'Квіт', 'Трав', 'Черв', 'Лип', 'Серп', 'Верес', 'Жовт', 'Лист', 'Груд'];

        for ($m = 1; $m <= 12; $m++) {
            $monthOrder = $orders->get($m);
            $salesData[] = $monthOrder ? (float) $monthOrder->total_sales : 0;

            // Кількість нових покупців у цьому місяці
            $newCount = $newCustomersByMonth->get($m)?->count ?? 0;
            $newCustomersData[] = $newCount;

            // Повторні покупки (загальна к-сть замовлень мінус нові клієнти)
            $totalOrdersCount = $monthOrder ? $monthOrder->total_orders : 0;
            $returningCount = max(0, $totalOrdersCount - $newCount);
            $returningCustomersData[] = $returningCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Продажі (₴)',
                    'data' => $salesData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'y',
                    'type' => 'line', // Робимо продажі лінією
                ],
                [
                    'label' => 'Нові клієнти (замовлення)',
                    'data' => $newCustomersData,
                    'backgroundColor' => '#3B82F6',
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Повторні замовлення',
                    'data' => $returningCustomersData,
                    'backgroundColor' => '#F59E0B',
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
            ],
            'labels' => $monthsLabels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Основний тип (комбінований)
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => 'Сума продажів (₴)'],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false], // щоб сітки не накладалися
                    'title' => ['display' => true, 'text' => 'Кількість замовлень'],
                ],
            ],
        ];
    }
}