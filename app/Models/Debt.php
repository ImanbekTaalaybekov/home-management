<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'user_id', 'type', 'name', 'amount', 'due_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
