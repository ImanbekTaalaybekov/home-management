<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmAdminToken extends Model
{
    protected $fillable = [
        'user_id',
        'device',
        'fcm_token',
    ];

    public function user()
    {
        return $this->belongsTo(Admin::class);
    }
}