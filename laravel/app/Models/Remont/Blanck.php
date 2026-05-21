<?php
namespace App\Models\Remont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blanck extends Model
{
    protected $table = 'remont_blanck';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id', 'number', 'client', 'phone', 'aparat', 
        'defect', 'fact_defect', 'empty', 'data', 
        'status', 'coment'
    ];

    // Аналог STATUS_CHOICES
    const STATUSES = [
        "1" => "Нове",
        "2" => "В ремонті",
        "22" => "Оплата",
        "3" => "Готові",
        "4" => "Відмова",
    ];

    /**
     * Логіка, яка спрацьовує при створенні запису (аналог save у Django)
     */
    protected static function booted()
    {
        static::creating(function ($blanck) {
            if (!$blanck->id) {
                if ($blanck->number) {
                    $blanck->id = (int)$blanck->number;
                } else {
                    $generated = static::generateUniqueId();
                    $blanck->id = $generated;
                    $blanck->number = (string)$generated;
                }
            }
        });
    }

    /**
     * Генерація рандомного 6-значного ID
     */
    protected static function generateUniqueId()
    {
        return (int)collect(range(1, 6))
            ->map(fn() => rand(0, 9))
            ->implode('');
    }

    /**
     * Отримати назву статусу (аксесор)
     */
    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function payments(): HasMany
    {
        // Вказуємо клас і зовнішній ключ
        return $this->hasMany(PayBlanck::class, 'blanck_id');
    }
}