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
        'name',
        'login',
        'personal_account',
        'password',
        'residential_complex_id',
        'block_number',
        'apartment_number',
        'phone_number',
        'non_residential_premises',
        'fcm_token',
        'role'
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

    public function residentialComplex()
    {
        return $this->belongsTo(ResidentialComplex::class, 'residential_complex_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }
}
