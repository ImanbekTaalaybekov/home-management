<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InputDebtDataUrta extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'management_body_code',
        'management_body_name',
        'supplier_code',
        'supplier_name',
        'owner_full_name',
        'region',
        'locality',
        'locality_part',
        'house',
        'apartment',
        'service',
        'debt_months_count',
        'last_payment_date',
        'debt_amount',
        'current_charges',
        'document_type',
        'document_date',
        'comment'
    ];
}
