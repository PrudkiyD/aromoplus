<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product_product';
    public $timestamps = false;
    protected $fillable = [
        'popularity',
        'abc_class',
        'xyz_class',
        'safety_stock',
        'main_image',
        'availability',
        'is_published',
        'name',
        'description',
        'price',
        'quantity',
        'internal_sku',
        'manufacturer_sku',
        'atel_sku',
        'one_c_sku',
        'one_c_path',
        'search_words',
    ];

    const AVAILABILITY_IN_STOCK = 'in_stock';
    const AVAILABILITY_OUT_OF_STOCK = 'out_of_stock';
    const AVAILABILITY_ON_ORDER = 'on_order';

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_product_category', 'product_id', 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function labels()
    {
        return $this->hasMany(ProductLabel::class, 'product_id');
    }

    public function discounts()
    {
        return $this->hasMany(Price::class, 'product_id');
    }

    public function views(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(View::class, 'product_id', 'id');
    }

    public function productItems()
    {
        return $this->hasMany(\App\Models\Order\ProductItem::class, 'product_id');
    }
}
