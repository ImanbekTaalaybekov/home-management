<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputDebtDataIvc extends Model
{
    protected $fillable = [
        'account_number',
        'house',
        'apartment',
        'full_name',
        'phone',
        'service_name',
        'debt',
        'penalty'
    ];
}
