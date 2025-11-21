<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyReport extends Model
{
    protected $fillable = [
        'title',
        'message',
        'residential_complex_id',
        'document'
    ];

    public function residentialComplex()
    {
        return $this->belongsTo(ResidentialComplex::class, 'residential_complex_id');
    }
}