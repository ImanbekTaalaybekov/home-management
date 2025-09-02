<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmUserToken extends Model
{
    protected $fillable = [
        'user_id',
        'device',
        'fcm_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
