<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupPasswordResetTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:password-reset-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting expired password reset tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('password_reset_tokens')->where('created_at', '<', now()->subHour())->delete();
        $this->info('Expired password reset tokens have been deleted.');
    }
}
