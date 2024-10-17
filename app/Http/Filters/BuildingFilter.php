<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class BuildingFilter extends Filter
{
   public const CATEGORY_ID = 'category_id';

    protected function getCallbacks(): array
    {
       return [
            self::CATEGORY_ID => [$this, 'categoryId'],
            ... parent::getCallbacks()
        ];
    }


    public function categoryId(Builder $builder, $value)
    {
        $builder->where('category_id', $value);
    }

}
