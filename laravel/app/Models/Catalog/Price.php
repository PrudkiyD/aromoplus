<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Price extends Model
{
    use HasFactory;

    protected $table = 'product_price';
    public $timestamps = false;
    protected $fillable = [
        'quantity',
        'discount',
        'vat_included',
        'price_list_id',
        'product_id',
    ];
    

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getFinalPriceAttribute()
    {
        return $this->product->price * $this->discount;
    }
}
