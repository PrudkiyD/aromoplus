<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'user_feedback';

    protected $fillable = [
        'name',
        'phone',
        'message',
        'subject',
        'send',
        'created_at',
        'updated_at'
    ];
}
