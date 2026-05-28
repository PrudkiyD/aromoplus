<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Page\Page;
use App\Models\Page\Element;
use App\Models\Catalog\Product;
use App\Models\Catalog\Price;
use Illuminate\Http\Request;
use App\Models\Catalog\View;

class CatalogController extends Controller
{
    public function category(Request $request, ...$slugs)
    {
        $part = explode('/', $slugs[0]);
        $slug = end($part);

        $category = Category::where('slug', $slug)->firstOrFail();
        $subcategorys = Category::where('parent_id', $category->id)->where('is_published', TRUE)->get();
        
        $title = $category->title ?? $category->name;
        $title_page = $category->name;

        

        // Поточний прайс-лист
        $price_list_id = $request->cookie('user_price', 1);

        $products = $category->products()
            ->where('is_published', true)
            ->orderBy('availability', 'asc') 
            ->orderByRaw('quantity > 0 DESC')
            ->orderBy('popularity', 'desc')
            ->orderBy('quantity', 'desc')
            
            ->with([
                'labels',
                'discounts' => function ($q) use ($price_list_id) {
                    $q->where('price_list_id', $price_list_id);
                }
            ])
            ->paginate(12);



        return view('catalog.catalog', compact(
            'title',
            'title_page',
            'products',
            'category',
            'subcategorys'
        ));
    }



    public function product(Request $request, $productId)
    {
        // Отримуємо ID прайс-листа
        $price_list_id = $request->cookie('user_price', 1);
        $product = Product::where('id', $productId)
            ->where('is_published', true)
            ->with([
                'labels',
                'images',
                'discounts' => function ($q) use ($price_list_id) {
                    $q->where('price_list_id', $price_list_id);
                },
            ])
            ->firstOrFail();


        $title = $product->name;
        $title_page = $product->name;

        // Отримуємо ID прайс-листа
        $price_list_id = $request->cookie('user_price', 1);

        // Створюємо унікальний ключ для сесії, наприклад: 'viewed_products.5'
        $sessionKey = 'viewed_products.' . $product->id;

        // Перевіряємо, чи немає цього ключа в сесії
        if (!$request->session()->has($sessionKey)) {
            
            
            // Записуємо перегляд у базу даних
            View::create([
                'product_id' => $product->id,
                'user_id'    => auth()->check() ? auth()->id() : null, // ID юзера, якщо залогінений
                'ip_address' => $request->ip(),                        // IP-адреса відвідувача
            ]);

            // Позначаємо в сесії, що цей товар у поточній сесії вже "подівилися"
            $request->session()->put($sessionKey, true);
        }
        

        return view('catalog.product', compact(
            'title',
            'title_page',
            'product',
        ));
    }

    public function search(Request $request)
    {

        $search = urldecode(trim($request->query('search', '')));
        $title = empty($search)
            ? 'Пошук товарів'
            : 'Результат пошуку: "' . e($search) . '"';
        $title_page = $title;

        // ID прайс-листа
        $price_list_id = $request->cookie('user_price', 1);

        // Формуємо запит
        $query = Product::where('is_published', true)
            ->orderBy('availability', 'asc')
            ->with(['discounts' => function ($q) use ($price_list_id) {
                $q->where('price_list_id', $price_list_id);
            }]);

        // Якщо є запит пошуку
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('search_words', 'like', "%{$search}%")
                ->orWhere('internal_sku', 'like', "%{$search}%")
                ->orWhere('manufacturer_sku', 'like', "%{$search}%");
            });
        }

        // Отримуємо товари
        $products = $query
            ->paginate(12)
            ->appends(['search' => $search]);

        return view('catalog.search', compact(
            'title',
            'title_page',
            'products',
            'search'
        ));
    }


    /*
    public function sale(Request $request){
        $categories = Category::with(['subcategories' => function ($query) {
            $query->where('is_published', true);
        }])
        ->where('is_published', true)
        ->get();
        $pages = Page::where('is_published', true)->get();
        $services = $pages->where('type', 'services');
        $infos = $pages->where('type', 'info');
        $elements = Element::all();
        $adresa = $elements->where('slug', 'adresa')->first();
        $kontakti = $elements->where('slug', 'kontakti')->first();


        $title = "Товари зі знижкою";

        $title_page = $title;

        // ID прайс-листа
        $price_list_id = $request->cookie('user_price', 1);

        // Фільтр за наявністю в прайсі
        $productIds = Price::where('price_list_id', $price_list_id)
                   ->where('discount_applied', true)
                   ->where('quantity', 1)
                   ->distinct()
                   ->pluck('product_id');

        // Запит до продуктів
        $products = Product::whereIn('id', $productIds)
            ->whereHas('images', function($query) {
                $query->where('main_image', true);
            })
            ->with([
                'images' => function($query) {
                    $query->where('main_image', true);
                },
                'prices' => function($query) use ($price_list_id) {
                    $query->where('price_list_id', $price_list_id);
                    $query->where('quantity', 1);
                }
            ])
            ->paginate(12);

        return view('catalog.sale', compact(
            'title',
            'title_page',
            'categories',
            'services',
            'infos',
            'adresa',
            'kontakti',
            'products',
        ));
    }*/
}
