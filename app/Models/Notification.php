<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title', 'message', 'type', 'user_id', 'residential_complex_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function residentialComplex()
    {
        return $this->belongsTo(ResidentialComplex::class);
    }
}
