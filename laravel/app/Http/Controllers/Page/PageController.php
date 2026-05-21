<?php

namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Page\Page;
use App\Models\Page\Element;
use App\Models\Page\Slider;
use App\Models\Catalog\Product;
use App\Models\Catalog\PriceList;
use App\Models\Catalog\Price;
use App\Models\Catalog\ProductImages;

class PageController extends Controller
{
    public function info($pageSlug)
    {
        $page = Page::where('slug', $pageSlug)
                    ->where('is_published', true)
                    ->where('type', 'info')
                    ->get();

        if ($page->isEmpty()) {
            abort(404);
        }

        $title = $page->first()->title;
        $title_page = $page->first()->name;
        $content = $page->first()->content;

        return view('page.info', compact('title', 
                                    'title_page',
                                    'content' 
                                ));
    }

    public function services($pageSlug)
    {
        $page = Page::where('slug', $pageSlug)
                    ->where('is_published', true)
                    ->where('type', 'services')
                    ->get();

        if ($page->isEmpty()) {
            abort(404);
        }

        $title = $page->first()->title;
        $title_page = $page->first()->name;
        $content = $page->first()->content;
        $image = $page->first()->image;
        $pages = Page::where('is_published', true)->get();

        return view('page.services', compact('title', 
                                    'title_page',
                                    'content',
                                    'image' 
                                ));
    }
}
