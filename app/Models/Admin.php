<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = [
        'username',
        'role',
        'password',
        'client_id',
        'accesses',
        'name'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'password' => 'hashed'
    ];

    public function client()
    {
        return $this->belongsTo(ProgramClient::class, 'client_id');
    }
}
