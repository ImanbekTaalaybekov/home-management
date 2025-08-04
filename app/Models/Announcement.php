<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'residential_complex_id',
        'created_by'
    ];

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
