<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsAlsecoData extends Model
{
    protected $fillable = [
        'account_number',
        'management_code',
        'management_name',
        'supplier_code',
        'supplier_name',
        'region',
        'locality',
        'locality_part',
        'house',
        'apartment',
        'full_name',
        'people_count',
        'supplier_people_count',
        'area',
        'tariff',
        'service',
        'balance_start',
        'balance_change',
        'initial_accrual',
        'accrual_change',
        'accrual_end',
        'payment_date',
        'payment',
        'payment_transfer',
        'balance_end',
        'month',
        'year',
        'note',
    ];
}
