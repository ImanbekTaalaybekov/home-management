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

    public function residentialComplex()
    {
        return $this->belongsTo(ResidentialComplex::class, 'residential_complex_id');
    }
}