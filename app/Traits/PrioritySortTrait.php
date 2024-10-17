<?php

namespace App\Traits;

trait PrioritySortTrait
{
    public function scopeSortByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
