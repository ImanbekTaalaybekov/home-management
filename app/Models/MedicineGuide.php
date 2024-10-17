<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'common_name',
        'company_name',
    ];
}
