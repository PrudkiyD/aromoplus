<?php

namespace App\Http\Controllers\Order;
use App\Models\User\User;
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
use Illuminate\Support\Facades\Http;


class OrderController extends Controller
{

    function generateUniqueOrderNumber(): string
    {
        do {
            $number = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // 6-значний номер
        } while (Order::where('number', $number)->exists());

        return $number;
    }

    public function checkOrCreateBasketKey()
    {
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey) ?? null;

        if ($userKey) {
            $basketKey = $userKey;
        } else {
            $cookieKey = 'basket_key';
            $basketKey = request()->cookie($cookieKey);

            if (!$basketKey) {
                $basketKey = Str::uuid()->toString();
                Cookie::queue(Cookie::make($cookieKey, $basketKey, 60 * 24 * 180));
            }
        }

        return $basketKey;
    }


    public function start()
    {
        $page = Page::where('slug', 'home')->get();
        $title = "Оформити замовлення";
        $title_page = "Оформити замовлення";
        $cardHiden = TRUE;

        return view('order.order-start', compact('title', 
                                    'title_page',
                                    'cardHiden',
                                    ));
    }

    public function save(Request $request)
    {
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey) ?? null;

        $price_list_id = $request->cookie('user_price', 1);

        if(!$price_list_id){
            $price_list_id = 1;
        }

        $session_key = $this->checkOrCreateBasketKey();
        $basket = Basket::where('session_key', $session_key)->first();
        $order_number = $this->generateUniqueOrderNumber();

        // Вибираємо дані з POST
        $data = $request->only([
            'first_name',
            'last_name',
            'middle_name',
            'organization',
            'phone_number',
            'delivery',
            'city',
            'department',
            'street',
            'addresses',
            'payment_type',
            'comment',
        ]);
        
        $order_key = Str::uuid()->toString();


        if (!User::where('id', $userKey)->exists()) {
            $userKey = null;
        }   

        // Створюємо замовлення
        $order = Order::create([
            'user_id'       => $userKey,
            'number'        => $order_number,
            'status'        => 'new',
            'first_name'    => $data['first_name'] ?? null,
            'last_name'     => $data['last_name'] ?? null,
            'middle_name'   => $data['middle_name'] ?? null,
            'organization'  => $data['organization'] ?? null,
            'phone_number'  => $data['phone_number'] ?? null,
            'delivery'      => $data['delivery'] ?? null,
            'city'          => $data['city'] ?? null,
            'department'    => $data['department'] ?? null,
            'street'        => $data['street'] ?? null,
            'addresses'     => $data['addresses'] ?? null,
            'payment_type'  => $data['payment_type'] ?? null,
            'comment'       => $data['comment'] ?? null,
            'key'           => $order_key,
            'price_list_id' => $price_list_id,
            'total'         => $basket->total ?? 0,
            'send'          => 0
        ]);

        // Переносимо товари з корзини в замовлення
        ProductItem::where('basket_id', $basket->id)
            ->update(['order_id' => $order->id,
                        'basket_id' => null,
                    ]);

        // Видаляємо корзину
        $basket->delete();
        session(['order_successful' => true]);

        $text = "🛒 <b>Нове замовлення #{$order->id}</b>\n";
        $text .= "👤 {$order->first_name} {$order->last_name}\n";
        $text .= "💰 Сума: {$order->total} грн\n";
        $text .= "https://aromoplus.com.ua/order/tracker/{$order->key}";

        Http::post("https://api.telegram.org/bot" . env('TG_TOKEN') . "/sendMessage", [
            'chat_id' => env('TG_ID_ADMIN'),
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);

        return redirect('order/successful/' .$order->key);
    }


    public function successful($key){
        $orderSuccess = session('order_successful'); 

        if($orderSuccess){
            session(['order_successful' => false]);

            $title = "Замовлення успішно оформлене";
            $title_page = "Замовлення успішно оформлене";
            $order = Order::with('productItems')->where('key', $key)->first();

            return view('order.successful', compact('title', 
                                    'title_page',
                                    'order'
                                ));
            
        }else{
            return redirect('order/tracker/' .$key);
        }
    }


}
