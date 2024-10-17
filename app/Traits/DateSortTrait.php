<?php

namespace App\Traits;

trait DateSortTrait
{
    public function scopeSortByDate($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
}
