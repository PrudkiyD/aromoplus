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

    const STORAGE_BASE = '/var/www/aromoplus1/data/www/aromoplus.com.ua/storage/';
    
    public function status()
    {
        return response()->json([
                'status' => 'ok',
                'user' => auth()->user()->email
            ]);
    }

    public function imgImport(Request $request){
        $request->validate([
            'file'     => 'required|image|max:5120',
            'path'     => 'required|string',
            'model'    => 'required|string',
            'col'      => 'required|string',
            'model_id' => 'nullable|integer',
        ]);

        $dataPath  = trim($request->input('path'), '/');  // напр. "product-images"
        $modelName = $request->input('model');             // напр. "Product"
        $col       = $request->input('col');               // напр. "main_image"
        $modelId   = $request->input('model_id');

        // Зберігаємо файл
        $file     = $request->file('file');
        $filename = $file->hashName(); // унікальне ім'я типу "abc123.jpg"
        $destDir  = self::STORAGE_BASE . $dataPath;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $file->move($destDir, $filename);

        // Шлях для запису в БД: "product-images/abc123.jpg"
        $dbPath = $dataPath . '/' . $filename;

        // Оновлюємо модель якщо є model_id
        if ($modelId) {
            $modelClass = 'App\\Models\\' . $modelName;

            if (!class_exists($modelClass)) {
                return response()->json(['message' => "Модель {$modelName} не знайдена"], 422);
            }

            $instance = $modelClass::find($modelId);

            if (!$instance) {
                return response()->json(['message' => "Запис #{$modelId} не знайдений"], 404);
            }

            // Видаляємо старе зображення якщо є
            if (!empty($instance->$col)) {
                $oldFile = self::STORAGE_BASE . $instance->$col;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $instance->$col = $dbPath;
            $instance->save();
        }

        return response()->json([
            'path'    => $dbPath,
            'message' => 'Збережено успішно',
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
