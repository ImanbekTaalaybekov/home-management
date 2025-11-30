<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestMaster extends Model
{
    protected $fillable = [
        'name',
        'service_request_category_id',
        'phone_number',
        'client_id'
    ];

    public function category()
    {
        return $this->belongsTo(ServiceRequestCategory::class, 'service_request_category_id');
    }
}
