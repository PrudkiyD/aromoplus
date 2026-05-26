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
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month; // Отримуємо номер поточного місяця

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

        // 2. Визначаємо нових клієнтів
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

        // Масиви для результатів
        $salesData = [];
        $newCustomersData = [];
        $returningCustomersData = [];
        $labels = [];

        $allMonthsLabels = ['Січ', 'Лют', 'Бер', 'Квіт', 'Трав', 'Черв', 'Лип', 'Серп', 'Верес', 'Жовт', 'Лист', 'Груд'];

        // Цикл йде ТІЛЬКИ до поточного місяця включно ($currentMonth)
        for ($m = 1; $m <= $currentMonth; $m++) {
            $labels[] = $allMonthsLabels[$m - 1];

            $monthOrder = $orders->get($m);
            $salesData[] = $monthOrder ? (float) $monthOrder->total_sales : 0;

            $newCount = $newCustomersByMonth->get($m)?->count ?? 0;
            $newCustomersData[] = $newCount;

            $totalOrdersCount = $monthOrder ? $monthOrder->total_orders : 0;
            $returningCount = max(0, $totalOrdersCount - $newCount);
            $returningCustomersData[] = $returningCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Продажі (₴)',
                    'data' => $salesData,
                    'borderColor' => '#10B981', // Смарагдовий
                    'backgroundColor' => 'rgba(16, 185, 129, 0.05)', // Легке стильне підсвічування під лінією
                    'fill' => true,
                    'tension' => 0.4, // Згладжування лінії (робить її плавною та сучасною)
                    'borderWidth' => 3,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'yAxisID' => 'y',
                    'type' => 'line',
                ],
                [
                    'label' => 'Нові клієнти',
                    'data' => $newCustomersData,
                    'backgroundColor' => '#3B82F6', // Синій
                    'borderRadius' => 6, // Закруглені кути стовпчиків
                    'stack' => 'customers', // Об'єднуємо в один стовпчик (stack)
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Повторні замовлення',
                    'data' => $returningCustomersData,
                    'backgroundColor' => '#F59E0B', // Бурштиновий
                    'borderRadius' => 6, // Закруглені кути
                    'stack' => 'customers', // Стек з новими клієнтами
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
//test