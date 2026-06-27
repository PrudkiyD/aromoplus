<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'user_customer';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'notes',
        'created_at',
        'updated_at',
        'user_id'
    ];
    
    public function orders()
    {
        return $this->hasMany(\App\Models\Order\Order::class, 'customer_id');
    }
}

