<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'language',
        'notifications',
        'status',
        'level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
