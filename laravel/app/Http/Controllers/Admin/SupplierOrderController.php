<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Order\ProductItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SupplierOrderController extends Controller
{
    public function supplierOrder()
    {
        $dateFrom = Carbon::now()->subDays(90);

        // Продажі за останні 3 місяці
        $sales = ProductItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_sold')
            )
            ->whereHas('order', function ($q) use ($dateFrom) {
                $q->where('created_at', '>=', $dateFrom)
                  ->whereNotIn('status', [
                      'canceled'
                  ]);
            })
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        $products = Product::where('is_published', true)
            ->get()
            ->map(function ($product) use ($sales) {

                $sold90 = $sales[$product->id] ?? 0;

                // середній продаж за день
                $avgPerDay = $sold90 / 90;

                // прогноз на 90 днів
                $forecast3Months = ceil($avgPerDay * 90);

                // скільки треба замовити
                $toOrder = max(0, $forecast3Months - $product->quantity);

                $product->sold_90 = $sold90;
                $product->forecast_3_months = $forecast3Months;
                $product->to_order = $toOrder;

                return $product;
            })
            ->sortByDesc('to_order');

        return view('admin.supplierOrder', compact('products'));
    }
}