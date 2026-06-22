<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Catalog\Price;
use App\Models\Order\Order;
use App\Models\Order\ProductItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use App\Models\Remont\Blanck;
use App\Models\Remont\PayBlanck;
use Illuminate\Support\Facades\Http;


class AdminController extends Controller
{
    public function status()
    {
        Order::where(function($query) {
            $query->where('ttn', '')->orWhereNull('ttn');
        })->update(['ttn' => 'Не створено']);

        return response()->json([
                'status' => 'ok',
                'user' => auth()->user()->email
            ]);
    }

    public function updateTotal(Request $request, $order_id, $total){
        $order = Order::firstOrCreate(
                ['id' => $order_id]
            );

        $order->total = $total;
        $order->save();

        return response()->json(['status' => '200']);
    }

    public function change(Request $request, $order_id, $product_id, $price_list_id, $quantity, $save)
    {


        $order = Order::firstOrCreate(
                ['id' => $order_id]
            );

        $prices = Price::where('product_id', $product_id)
            ->where('price_list_id', $price_list_id)
            ->orderBy('quantity', 'asc')
            ->get();

        $product = Product::findOrFail($product_id);
        $price = $product->price;
        

        $item = ProductItem::where('order_id', $order_id)
            ->where('product_id', $product_id)
            ->first();

        if (!$item && $save) {
            $item = ProductItem::create([
                'order_id'   => $order_id,
                'product_id' => $product_id,
                'price'      => $price,
                'quantity'   => 0,
            ]);
            
        }

        $curent_quantity = $quantity;
        if ($quantity <= 0 && $save) {
            $item->delete();
            return response()->json(['message' => 'Product removed']);
        }
        


        foreach ($prices as $qprice) {
            if ($qprice->quantity <= $curent_quantity) {
                $price = $product->price;

                $price = ceil($price * $qprice->discount * 10) / 10;
                
                if ($qprice->vat_included){
                    $price = ceil($price * 1.2 * 10) / 10;
                }
            } 
        }
        

        if($save){
            $item->price = $price;
            $item->save();
            return response()->json(['status' => '200', 'price' => $price]);
        }else{
            
            return response()->json(
                [
                    'status' => '200', 
                    'price' => $price,
                    'image' => '/storage/'.$product->main_image,
                ]
            );
        }
        
    }

    public function printBlanck(Request $request, $blanck_id, $pay_id)
    {
        $blanck = Blanck::where('id', $blanck_id)->firstOrFail();
        $pay = PayBlanck::where('id', $pay_id)->firstOrFail();
        return view('admin.printBlanck', compact('blanck', 'pay'));
    }

    
}
