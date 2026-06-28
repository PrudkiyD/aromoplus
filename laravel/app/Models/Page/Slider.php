<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $table = 'page_slider';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'image',
        'url',
    ];
}
