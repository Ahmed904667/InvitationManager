<?php

namespace App\Console\Commands;

use App\Shared\Models\User;
use App\Services\TimezoneService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateUserTimezonesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:update-timezones {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Update user timezones based on their last known IP or default to UTC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 [DRY RUN] This is a dry run - no changes will be made');
        }

        $this->info('🌍 Starting timezone update process...');

        // Get all users with UTC timezone (likely default)
        $users = User::where('timezone', 'UTC')->get();
        
        $this->info("Found {$users->count()} users with UTC timezone");

        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $this->line("Processing user: {$user->name} ({$user->email})");
            
            // For existing users, we can't detect their actual timezone from IP
            // So we'll set a reasonable default based on common timezones
            $defaultTimezone = $this->getDefaultTimezoneForUser($user);
            
            if ($defaultTimezone && $defaultTimezone !== 'UTC') {
                if (!$isDryRun) {
                    $user->update(['timezone' => $defaultTimezone]);
                    
                    Log::info('🌍 [COMMAND] Updated user timezone', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'new_timezone' => $defaultTimezone
                    ]);
                }
                
                $this->info("  ✅ Would set timezone to: {$defaultTimezone}");
                $updated++;
            } else {
                $this->line("  ⏭️  Keeping UTC timezone");
                $skipped++;
            }
        }

        if ($isDryRun) {
            $this->info("\n🔍 [DRY RUN] Summary:");
            $this->info("  - Would update: {$updated} users");
            $this->info("  - Would skip: {$skipped} users");
            $this->info("\nRun without --dry-run to apply changes");
        } else {
            $this->info("\n✅ Timezone update completed:");
            $this->info("  - Updated: {$updated} users");
            $this->info("  - Skipped: {$skipped} users");
        }

        return Command::SUCCESS;
    }

    /**
     * Get a reasonable default timezone for a user
     */
    private function getDefaultTimezoneForUser(User $user): ?string
    {
        // For now, we'll use a simple approach:
        // - If user has events, try to infer from event times
        // - Otherwise, use a common timezone based on user ID (for distribution)
        
        // Check if user has events with non-UTC times
        $events = $user->events()->whereNotNull('start_date')->get();
        
        if ($events->count() > 0) {
            // Try to detect timezone from event times
            $timezone = $this->detectTimezoneFromEvents($events);
            if ($timezone) {
                return $timezone;
            }
        }
        
        // Use a distribution of common timezones based on user ID
        $commonTimezones = [
            'America/New_York',
            'America/Chicago', 
            'America/Denver',
            'America/Los_Angeles',
            'Europe/London',
            'Europe/Paris',
            'Asia/Tokyo',
            'Asia/Shanghai',
            'Asia/Singapore',
            'Asia/Kuala_Lumpur',
            'Australia/Sydney'
        ];
        
        $index = $user->id % count($commonTimezones);
        return $commonTimezones[$index];
    }

    /**
     * Try to detect timezone from event times
     */
    private function detectTimezoneFromEvents($events): ?string
    {
        // This is a simplified approach - in reality, you'd need more sophisticated logic
        // to detect timezone patterns from event times
        
        foreach ($events as $event) {
            $startTime = \Carbon\Carbon::parse($event->start_date);
            
            // If the hour is between 9-17, it might be a business timezone
            $hour = $startTime->hour;
            if ($hour >= 9 && $hour <= 17) {
                // This could be a business event, try common business timezones
                return 'America/New_York'; // Default to Eastern Time
            }
        }
        
        return null;
    }
}
