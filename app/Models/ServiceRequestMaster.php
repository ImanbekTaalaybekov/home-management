<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestMaster extends Model
{
    protected $fillable = [
        'name',
        'service_request_category_id',
    ];
}
