<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Catalog\Category;
use App\Models\Page\Page;
use App\Models\Page\Element;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }


    public function boot()
    {
        // Один composer для всіх даних
        View::composer(['base.base', 'home', 'catalog.search'], function ($view) {
            // Отримуємо всі опубліковані категорії
            $categories = Category::where('is_published', true)->get();

            // Формуємо дерево категорій
            $categoryTree = $this->buildCategoryTree($categories);

            // Отримуємо інформаційні сторінки
            $infos = Page::where('type', 'info')
                ->where('is_published', true)
                ->get();

            // Отримуємо сторінки послуг
            $services = Page::where('type', 'services')
                ->where('is_published', true)
                ->get();
            
            // Контактні елементи
            $elements = Element::all();
            $adresa = $elements->firstWhere('slug', 'adresa');
            $kontakti = $elements->firstWhere('slug', 'kontakti');

            // Передаємо всі змінні у вигляд
            $view->with(compact('categoryTree', 'infos', 'services', 'adresa', 'kontakti'));
        });
    }

    // Допоміжні функції
    private function buildCategoryTree($categories, $parentId = null)
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $children = $this->buildCategoryTree($categories, $category->id);
                if ($children) {
                    $category->childrenTree = $children;
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }
}
