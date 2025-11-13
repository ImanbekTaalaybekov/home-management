<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtNameTranslation extends Model
{
    protected $fillable = [
        'original',
        'ru',
        'kg',
        'uz',
        'kk',
        'en',
        'es',
        'zh',
    ];
}