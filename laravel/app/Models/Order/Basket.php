<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Basket extends Model
{
    use HasFactory;

    protected $table = 'order_basket';

    protected $fillable = [
        'user_id',
        'session_key',
        'total',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function __toString()
    {
        return "Кошик #{$this->id} (Користувач: " . ($this->user_id ?? 'анонім') . ")";
    }

    public function productItems()
    {
        return $this->hasMany(ProductItem::class, 'basket_id');
    }
}
