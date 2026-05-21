<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Catalog\Product;

class ProductItem extends Model
{
    use HasFactory;

    protected $table = 'order_productitem';

    protected $fillable = [
        'basket_id',
        'order_id',
        'product_id',
        'price',
        'quantity',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function basket()
    {
        return $this->belongsTo(Basket::class, 'basket_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Catalog\Product::class, 'product_id');
    }

}
