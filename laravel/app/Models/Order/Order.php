<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'order_order';

    protected $fillable = [
        'number',
        'status',
        'user_id',
        'customer_id',
        'last_name',
        'first_name',
        'middle_name',
        'organization',
        'phone_number',
        'delivery',
        'city',
        'department',
        'ttn',
        'street',
        'addresses',
        'payment_type',
        'price_list_id',
        'total',
        'pay',
        'comment',
        'key',
        'send',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    // Статуси
    public const STATUS_NEW = 'new';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_SUCCESSFUL = 'successful';

    // Доставка
    public const DELIVERY_NP_ADDRESS = 'cat';
    public const DELIVERY_NP_BRANCH = 'nova';
    public const DELIVERY_PICKUP = 'sum';
    public const DELIVERY_UKR = 'ukr';

    // Оплата
    public const PAYMENT_COD = 'nal';
    public const PAYMENT_FOP = 'fop';
    public const PAYMENT_LLC = 'tov';
    public const PAYMENT_KASA = 'kasa';

    public function priceList()
    {
        return $this->belongsTo(\App\Models\Catalog\PriceList::class, 'price_list_id');
    }

    public function productItems()
    {
        return $this->hasMany(ProductItem::class, 'order_id');
    }

    public static function generateNumber()
    {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->number)) {
                $order->number = self::generateNumber();
            }
        });
    }

    public function __toString()
    {
        return "Замовлення #{$this->number} ({$this->last_name} {$this->first_name})";
    }

    public function user()
    {
        
        return $this->belongsTo(\App\Models\User\User::class, 'user_id');
    }

    public function customer()
    {
        
        return $this->belongsTo(\App\Models\User\Customer::class, 'customer_id');
    }

    public function ttn(): HasMany
    {
        return $this->hasMany(OrderAttachment::class, 'order_id', 'id');
    }
}
