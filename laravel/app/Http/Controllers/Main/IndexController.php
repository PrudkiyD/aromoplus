<?php

namespace App\Http\Controllers\Main;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Page\Page;
use App\Models\Page\Element;
use App\Models\Page\Slider;
use App\Models\Catalog\Product;
use App\Models\Catalog\PriceList;
use App\Models\Catalog\Price;
use App\Models\Catalog\ProductImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $page = Page::where('slug', 'home')->get();
        $title = $page->first()->title;
        $title_page = $page->first()->name;
        $pages = Page::where('is_published', true)->get();

        $slider = Slider::all();
        $about_us = Element::where('slug', 'pro-nas')->first();


        $price_list_id = $request->cookie('user_price', 1);

        $category = Category::where('slug', 'sale')->firstOrFail();
        $sale_products = $category->products()
            ->where('is_published', true)
            ->orderBy('availability', 'asc')
            ->with(['discounts' => function ($q) use ($price_list_id) {
                $q->where('price_list_id', $price_list_id);
            }])
            ->paginate(4);
        
        $category = Category::where('slug', 'rekomendovani')->firstOrFail();
        $recomend_products = $category->products()
            ->where('is_published', true)
            ->orderBy('availability', 'asc')
            ->with(['discounts' => function ($q) use ($price_list_id) {
                $q->where('price_list_id', $price_list_id);
            }])
            ->paginate(4);

        $category = Category::where('slug', 'populyarni')->firstOrFail();
        $populyarni_products = $category->products()
            ->where('is_published', true)
            ->orderBy('availability', 'asc')
            ->with(['discounts' => function ($q) use ($price_list_id) {
                $q->where('price_list_id', $price_list_id);
            }])
            ->paginate(4);

        return view('home', compact('title', 
                                    'title_page',  
                                    'slider', 
                                    'about_us',
                                    'sale_products',
                                    'recomend_products',
                                    'populyarni_products'
                                ));
    }

    public function orenda(){
        return redirect('/services/orenda');
    }

    public function remont(){
        return redirect('/services/remont');
    }
    
    public function sitemap()
    {
        $urls = [
            URL::to('/'),
            URL::to('/catalog/detali'),
            URL::to('/catalog/forcoffee'),
            URL::to('/services/orenda'),
            URL::to('/services/remont'),
        ];

        
        
        $products = Product::where('is_published', true)->get();
        foreach ($products as $product) {
            $urls[] = URL::to('/catalog/product/' . $product->id);
        }
        
        

        $content = view('sitemap', compact('urls'));

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
