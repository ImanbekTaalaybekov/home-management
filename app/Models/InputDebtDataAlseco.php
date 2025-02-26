<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InputDebtDataAlseco extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number', 'full_name', 'address', 'apartment_number', 'payment_date', 'debt_month',
        'housing_maintenance', 'hot_water_sewage_meter', 'heating', 'garbage_disposal', 'cold_water_meter',
        'electricity', 'hot_water_meter', 'cold_water_sewage_meter', 'previous_debts', 'duty_lighting',
        'capital_repair', 'total_utilities'
    ];
}