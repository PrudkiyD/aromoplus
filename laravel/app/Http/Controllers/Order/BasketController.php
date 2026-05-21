<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Catalog\Price;
use App\Models\Order\Basket;
use App\Models\Order\ProductItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

class BasketController extends Controller
{
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

    private function updateBasketTotal($basket)
    {
        $basket->total = ProductItem::where('basket_id', $basket->id)
            ->sum(\DB::raw('price * quantity'));
        $basket->save();
    }

    public function basket()
    {
        $basket = Basket::with([
            'productItems.product' => function ($query) {
                $query->select('id', 'name', 'price', 'main_image');
            }
        ])
        ->firstOrCreate(
            ['session_key' => $this->checkOrCreateBasketKey()],
            ['total' => 0]
        );

        $this->updateBasketTotal($basket);

        return response()->json($basket);
    }

    public function change(Request $request, $action, $product_id, $quantity)
    {
        $basket = Basket::firstOrCreate(
            ['session_key' => $this->checkOrCreateBasketKey()],
            ['total' => 0]
        );

        // Отримуємо ID прайс-листа
        $price_list_id = $request->cookie('user_price', 1);

        // Беремо всі ціни для продукту та прайс-листа
        $prices = Price::where('product_id', $product_id)
            ->where('price_list_id', $price_list_id)
            ->orderBy('quantity', 'asc')
            ->get();

        // Встановлюємо початкову ціну як базову ціну продукту
        $product = Product::findOrFail($product_id);
        $price = $product->price;
        

        // Знаходимо або створюємо товар у кошику
        $item = ProductItem::firstOrCreate(
            [
                'basket_id' => $basket->id,
                'product_id' => $product_id
            ],
            [
                'price' => $price,
                'quantity' => 0
            ]
        );

        // Виконуємо дію
        if ($action === 'plus') {
            $curent_quantity = $item->quantity + $quantity;
            $item->quantity += $quantity;
        
        } elseif ($action === 'minus') {
            $curent_quantity = $item->quantity - $quantity;
            $item->quantity -= $quantity;
            if ($item->quantity <= 0) {
                $item->delete();
                $this->updateBasketTotal($basket);
                return response()->json(['message' => 'Product removed']);
            }
        } elseif ($action === 'quantity') {
            $curent_quantity = $quantity;
            if ($quantity <= 0) {
                $item->delete();
                $this->updateBasketTotal($basket);
                return response()->json(['message' => 'Product removed']);
            }
            $item->quantity = max(1, $quantity);
        }

        // Вибираємо ціну з урахуванням quantity та discount
        foreach ($prices as $qprice) {
            if ($qprice->quantity <= $curent_quantity) {
                $price = $product->price;

                $price = ceil($price * $qprice->discount * 10) / 10;
                
                if ($qprice->vat_included){
                    $price = ceil($price * 1.2 * 10) / 10;
                }
            } 
        }

        // Оновлюємо ціну та зберігаємо
        $item->price = $price;
        $item->save();

        // Оновлюємо total кошика
        $this->updateBasketTotal($basket);

        return response()->json(['price' => $price, 'prices' => $prices]);
    }
}
