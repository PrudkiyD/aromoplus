<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Main\IndexController;
use App\Http\Controllers\Page\PageController;
use App\Http\Controllers\Catalog\CatalogController;
use App\Http\Controllers\Order\BasketController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\PrintController;
use App\Http\Controllers\Order\TrackerController;
use App\Http\Controllers\Main\FeedbackController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\novaPoshtaController;
use App\Http\Controllers\Admin\SupplierOrderController;
use Filament\Http\Middleware\Authenticate;

#Головна
Route::get('/', [IndexController::class, 'index']);
Route::get('sitemap.xml', [IndexController::class, 'sitemap']);
Route::get('/test', [IndexController::class, 'test']);


#Редірект
Route::get('/index.php', [IndexController::class, 'index']);
Route::get('/main.php', [IndexController::class, 'index']);
Route::get('orenda', [IndexController::class, 'orenda']);
Route::get('kavove-obladnannia/orenda', [IndexController::class, 'orenda']);
Route::get('remont', [IndexController::class, 'remont']);
Route::get('kavove-obladnannia', [IndexController::class, 'remont']);

#Форма зворотного зв'язку
Route::post('feedback/', [FeedbackController::class, 'feedback']);

/*
Route::get('account/', [UserController::class, 'account']);
Route::get('account/login', [UserController::class, 'login']);
Route::get('account/register', [UserController::class, 'register']);
Route::post('account/register', [UserController::class, 'startRegister']);
Route::post('account/login/checkout', [UserController::class, 'checkout']);
Route::get('account/logout', [UserController::class, 'logout']);
Route::get('account/forgot', [UserController::class, 'forgot']);
Route::get('account/create-code', [UserController::class, 'createCode']);
Route::get('account/check-code', [UserController::class, 'checkCode']);
Route::get('/account/change-password', [UserController::class, 'changePassword']);
*/

#Замовлення та кошик
Route::get('basket/', [BasketController::class, 'basket']);
Route::get('/basket/{action}/{product_id}/{quantity}', [BasketController::class, 'change']);
Route::get('order/start', [OrderController::class, 'start']);
Route::post('order/save', [OrderController::class, 'save']);
Route::get('order/successful/{key}', [OrderController::class, 'successful']);
Route::get('order/tracker/{key}', [TrackerController::class, 'tracker']);
Route::get('order/check-ttn/{key}/', [PrintController::class, 'checkttn']);
Route::get('order/printed/{key}/{id}/', [PrintController::class, 'printed']);

#Послуги та інформація
Route::get('info/{pageSlug}', [PageController::class, 'info']);
Route::get('services/{pageSlug}', [PageController::class, 'services']);

#Товари
Route::get('catalog/search', [CatalogController::class, 'search']);
Route::get('catalog/product/{productId}', [CatalogController::class, 'product']);
Route::get('catalog/{slugs}', [CatalogController::class, 'category'])
    ->where('slugs', '.*');


#Адмін
Route::middleware([Authenticate::class])->group(function () {
        Route::get('/admin/status', [AdminController::class, 'status']);
        Route::get('/admin/change-product/{order_id}/{product_id}/{price_list_id}/{quantity}/{save}', [AdminController::class, 'change']);
        Route::get('/admin/update-total/{order_id}/{total}/', [AdminController::class, 'updateTotal']);
        Route::get('/admin/print-blanck/{blanck_id}/{pay_id}/', [AdminController::class, 'printBlanck']);
        Route::get('/admin/nova-poshta/create', [novaPoshtaController::class, 'novaPoshta']);
        Route::get('/admin/supplier-order', [SupplierOrderController::class, 'supplierOrder']);
});
