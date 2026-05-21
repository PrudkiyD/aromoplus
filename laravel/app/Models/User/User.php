<?php

namespace App\Models\User;

use Filament\Models\Contracts\FilamentUser; 
use Filament\Panel;                        
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_registered',
        'price_list_id',
        'phone',
        'profile_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Доступ до адмінки
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'aromozapcasti@gmail.com';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}