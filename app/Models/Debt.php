<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'amount',
        'due_date',
        'current_charges',
        'payment_amount',
        'initial_amount',
        'period_start_balance'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function translation()
    {
        return $this->hasOne(DebtNameTranslation::class, 'original', 'name');
    }
}
