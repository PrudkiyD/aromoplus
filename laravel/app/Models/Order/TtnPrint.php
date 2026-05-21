<?php
namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TtnPrint extends Model
{
    protected $table = 'ttn_print';
    public $timestamps = false;
    protected $fillable = ['path', 'printed', 'order_id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}