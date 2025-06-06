<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected $commands = [
        Commands\StartPushNotificationCommand::class,
        Commands\CleanupPasswordResetTokens::class,
    ];
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('cleanup:password-reset-tokens')->hourly();
        $schedule->command('announcements:delete-old')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
