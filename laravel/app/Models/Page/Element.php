<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    use HasFactory;

    protected $table = 'page_element';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'content',
        'slug',
    ];
}
