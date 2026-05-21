<?php

namespace App\Http\Controllers\Order;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Page\Page;
use App\Models\Page\Element;
use App\Models\Page\Slider;
use App\Models\Catalog\Product;
use App\Models\Order\Basket;
use App\Models\Order\Order;
use App\Models\Order\City;
use App\Models\Order\ProductItem;
use App\Models\Order\Warehouse;
use App\Models\Catalog\PriceList;
use App\Models\Catalog\Price;
use App\Models\Catalog\ProductImages;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;


class TrackerController extends Controller
{

    public function tracker($key){
        $order = Order::with('productItems.product')->where('key', $key)->firstOrFail();
        $title = "Замовлення №" .$order->number;
        $title_page = "Відстежити статус замовлення";

        return view('order.tracker', compact('title', 
                                'title_page',
                                'order'
                            ));
    }


}
