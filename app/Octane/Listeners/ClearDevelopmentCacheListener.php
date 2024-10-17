<?php

namespace App\Octane\Listeners;

class ClearDevelopmentCacheListener
{
    public function handle($event): void
    {
        if (! app()->isProduction()) {
            shell_exec('rm -rf '.base_path('bootstrap/cache/*.php'));
        }
    }
}
