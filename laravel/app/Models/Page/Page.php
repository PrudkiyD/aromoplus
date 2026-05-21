<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $table = 'page_page';

    protected $fillable = [
        'is_published',
        'type',
        'name',
        'title',
        'content',
        'image',
        'out_slug',
        'slug',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'out_slug' => 'boolean',
    ];

    const TYPE_INFO = 'info';
    const TYPE_SERVICES = 'services';

    public static function getTypes()
    {
        return [
            self::TYPE_INFO => 'Інформація',
            self::TYPE_SERVICES => 'Послуги',
        ];
    }
}
