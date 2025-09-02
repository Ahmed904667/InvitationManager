<?php

namespace App\Console\Commands;

use App\Shared\Models\OTP;
use Illuminate\Console\Command;

class CleanupExpiredOTPs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting OTP cleanup...');

        // Count expired OTPs
        $expiredCount = OTP::where('expires_at', '<', now())->count();
        
        if ($expiredCount === 0) {
            $this->info('No expired OTPs found.');
            return 0;
        }

        $this->info("Found {$expiredCount} expired OTP(s).");

        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to delete {$expiredCount} expired OTP(s)?")) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        // Clean up expired OTPs
        $deletedCount = OTP::cleanupExpired();

        $this->info("Successfully deleted {$deletedCount} expired OTP(s).");

        // Also clean up used OTPs that are older than 1 hour
        $usedCount = OTP::where('used', true)
            ->where('created_at', '<', now()->subHour())
            ->delete();

        if ($usedCount > 0) {
            $this->info("Also cleaned up {$usedCount} used OTP(s) older than 1 hour.");
        }

        return 0;
    }
}
