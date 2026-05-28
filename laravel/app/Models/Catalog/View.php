<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\User; // Імпортуємо модель User з правильної папки

class View extends Model
{
    protected $table = 'product_product_views';

    protected $fillable = [
        'product_id',
        'user_id',
        'ip_address',
    ];

    public function product(): BelongsTo
    {
        // Змінено з ProductProduct::class на Product::class відповідно до файлової структури
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}