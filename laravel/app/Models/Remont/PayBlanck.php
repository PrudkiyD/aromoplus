<?php
namespace App\Models\Remont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayBlanck extends Model
{

    protected $table = 'remont_pay_blanck';
    public $timestamps = false;
    protected $fillable = [
        'blanck_id',
        'type',
        'status',
        'pay',
        'total',
        'chek',
        'date_get'
    ];

    // Константи для виборів (аналог CHOICES) "nal"  => "Післяплата",
    const PAY_CHOICES = [
        "kasa" => "Каса",
        "fop"  => "Реквізити ФОП",
        "tov"  => "Рахунок ТОВ",
        
    ];

    // "D" => "Оплата частинами", "S" => "Скасовано",
    const STATUS_CHOICES = [
        "N" => "Не оплачене",
        "O" => "Оплачено",
    ];

    const TYPE_CHOICES = [
        'dia' => 'Діагностика',
        'rem' => 'Ремонт',
    ];

    /**
     * Зв'язок з моделлю Blanck (ForeignKey)
     */
    public function blanck(): BelongsTo
    {
        return $this->belongsTo(Blanck::class, 'blanck_id', 'id');
    }

    /**
     * Аксесори для отримання читабельних назв (опціонально)
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_CHOICES[$this->status] ?? $this->status;
    }

    public function getPayLabelAttribute(): string
    {
        return self::PAY_CHOICES[$this->pay] ?? $this->pay;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_CHOICES[$this->type] ?? $this->type;
    }
}