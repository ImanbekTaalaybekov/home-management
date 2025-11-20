<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestCategory extends Model
{
    protected $fillable = [
        'name',
        'name_rus',
        'client_id'
    ];

    public function masters()
    {
        return $this->hasMany(ServiceRequestMaster::class, 'service_request_category_id');
    }
}
