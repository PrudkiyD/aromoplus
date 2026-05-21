<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceList extends Model
{
    use HasFactory;

    protected $table = 'product_pricelist';

    protected $fillable = [
        'name',
        'main',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($priceList) {
            if ($priceList->main) {
                self::where('main', true)
                    ->where('id', '!=', $priceList->id)
                    ->update(['main' => false]);
            }
        });
    }

    public function discounts()
    {
        return $this->hasMany(Price::class, 'price_list_id');
    }
}
