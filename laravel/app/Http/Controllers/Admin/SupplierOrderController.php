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


class SupplierOrderController extends Controller
{
    public function supplierOrder()
    {

        return view('admin.supplierOrder');
    }
    
}
