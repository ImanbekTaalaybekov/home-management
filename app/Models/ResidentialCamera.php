<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResidentialCamera extends Model
{
    protected $fillable = [
        'residential_complex_id',
        'type',
        'name',
        'link'
    ];
}