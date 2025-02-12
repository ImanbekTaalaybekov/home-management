<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Builder::macro('orderByPosition', function (array $values, string $field = 'id') {
            if (count($values) > 0) {
                $this->orderByRaw(sprintf("position(%s::text in '%s')", $field, implode(', ', $values)));
            }
        });
    }

}
