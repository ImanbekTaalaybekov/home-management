<?php

namespace App\Traits;

trait PublishedTrait
{
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePublishedWithStatus($query)
    {
        return $query->where('status', 'published');
    }
}
