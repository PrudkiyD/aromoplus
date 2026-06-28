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

    public function imgImport(Request $request)
    {
        $request->validate([
            'file'     => 'required|image|max:5120',
            'path'     => 'required|string',
            'model'    => 'required|string',
            'col'      => 'required|string',
            'model_id' => 'nullable|integer',
            'par_id'   => 'nullable|integer',
            'par_col'  => 'nullable|string',
        ]);

        $dataPath   = trim($request->input('path'), '/');
        $modelName  = str_replace('/', '\\', $request->input('model'));
        $col        = $request->input('col');
        $modelId    = $request->input('model_id');
        $parId      = $request->input('par_id');
        $parCol     = $request->input('par_col');
        $modelClass = 'App\\Models\\' . $modelName;

        if (!class_exists($modelClass)) {
            return response()->json(['message' => "Модель {$modelName} не знайдена"], 422);
        }

        // Зберігаємо файл
        $file     = $request->file('file');
        $filename = $file->hashName();
        $destDir  = self::STORAGE_BASE . $dataPath;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $file->move($destDir, $filename);

        $dbPath = $dataPath . '/' . $filename;

        if ($modelId) {
            // Оновлюємо існуючий запис
            $instance = $modelClass::find($modelId);

            if (!$instance) {
                return response()->json(['message' => "Запис #{$modelId} не знайдений"], 404);
            }

            // Видаляємо старе зображення
            if (!empty($instance->$col)) {
                $oldFile = self::STORAGE_BASE . $instance->$col;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $instance->$col = $dbPath;
            $instance->save();

        } else {
            // Створюємо новий запис з прив'язкою до батька
            $data = [$col => $dbPath];

            if ($parId && $parCol) {
                $data[$parCol] = $parId; // напр. product_id => 42
            }

            $instance = $modelClass::create($data);
        }

        return response()->json([
            'id'      => $instance->id,
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
