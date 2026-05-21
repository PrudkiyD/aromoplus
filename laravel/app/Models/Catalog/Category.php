<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $table = 'product_category';
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'title',
        'description',
        'slug',
        'label',
        'image',
        'is_published',
        'parent_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($category) {
            if (empty($category->slug)) {
                $baseSlug = Str::slug($category->name);
                $slug = $baseSlug;
                $num = 1;

                while (self::where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists()) {
                    $slug = "{$baseSlug}-{$num}";
                    $num++;
                }

                $category->slug = $slug;
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_category', 'category_id', 'product_id');
    }
}
