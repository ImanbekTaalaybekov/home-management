<?php

namespace App\Providers;

use App\Services\VacancyService;
use Illuminate\Support\ServiceProvider;

class VacancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(VacancyService::class, function ($app) {
            return new VacancyService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
