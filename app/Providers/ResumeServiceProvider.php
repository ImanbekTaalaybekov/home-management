<?php

namespace App\Providers;

use App\Services\ResumeService;
use Illuminate\Support\ServiceProvider;

class ResumeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(ResumeService::class, function ($app) {
            return new ResumeService();
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
