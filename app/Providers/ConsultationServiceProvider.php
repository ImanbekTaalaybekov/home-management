<?php

namespace App\Providers;

use App\Services\ConsultationService;
use Illuminate\Support\ServiceProvider;

class ConsultationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(ConsultationService::class, function ($app) {
            return new ConsultationService();
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
