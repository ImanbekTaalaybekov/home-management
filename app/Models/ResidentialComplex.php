<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentialComplex extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'client_id'
    ];

    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function client()
    {
        return $this->belongsTo(ProgramClient::class, 'client_id');
    }
}
