<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPaymentCheck extends Model
{
    protected $fillable = [
        'user_id',
        'debt_id'
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
}
