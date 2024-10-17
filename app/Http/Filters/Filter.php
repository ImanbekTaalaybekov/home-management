<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class Filter extends AbstractFilter
{
   public const CITY_ID = 'city_id';
   public const FAVORITE = 'favorite';

    protected function getCallbacks(): array
    {
        return  [
            self::CITY_ID => [$this, 'cityId'],
            self::FAVORITE => [$this, 'favorite'],
        ];
    }

    public function cityId(Builder $builder, $value)
    {
       $builder->where('city_id', $value);
    }

    public function favorite(Builder $builder)
    {
        if (auth('sanctum')->check()) {
            $builder->whereHas('favorites', function ($query) {
                $query->where('user_id', auth('sanctum')->id());
            });
        }
    }

}
