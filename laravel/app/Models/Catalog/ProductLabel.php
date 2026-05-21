<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductLabel extends Model
{
    use HasFactory;

    protected $table = 'product_productlabel';

    protected $fillable = [
        'product_id',
        'image',
        'name'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
