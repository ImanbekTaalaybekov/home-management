<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationStatus extends Model
{
    protected $fillable = [
        'notification_id',
        'user_id',
    ];
}
