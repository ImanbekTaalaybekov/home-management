<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'residential_complex_id',
        'start_date',
        'end_date'
    ];

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }
}
