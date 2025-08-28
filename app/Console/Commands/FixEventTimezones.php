<?php

namespace App\Console\Commands;

use App\Shared\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FixEventTimezones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:fix-timezones {--event-id= : Fix specific event ID} {--dry-run : Show what would be fixed without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix event timezones that were incorrectly stored without timezone conversion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $specificEventId = $this->option('event-id');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🕐 Fixing event timezone issues...');
        
        $query = Event::with('user');
        
        if ($specificEventId) {
            $query->where('id', $specificEventId);
        }
        
        $events = $query->get();
        $fixedCount = 0;
        
        foreach ($events as $event) {
            if (!$event->user || !$event->user->timezone) {
                continue;
            }
            
            $userTimezone = $event->user->timezone;
            
            // Skip if user is already in UTC
            if ($userTimezone === 'UTC') {
                continue;
            }
            
            $this->info("Checking event: {$event->name} (ID: {$event->id})");
            $this->line("  User timezone: {$userTimezone}");
            $this->line("  Current start date (UTC): {$event->start_date}");
            $this->line("  Current end date (UTC): " . ($event->end_date ?? 'NULL'));
            
            // Check if the event times make sense in user's timezone
            $startInUserTz = $event->start_date->setTimezone($userTimezone);
            $endInUserTz = $event->end_date ? $event->end_date->setTimezone($userTimezone) : null;
            
            $this->line("  Start in user TZ: {$startInUserTz}");
            $this->line("  End in user TZ: " . ($endInUserTz ?? 'NULL'));
            
            // Check if this event needs fixing based on current time
            $nowInUserTz = now()->setTimezone($userTimezone);
            $nowUtc = now();
            
            $shouldBeCompletedBasedOnUserTz = false;
            
            if ($event->end_date) {
                // For events with end dates, check if it has passed in user timezone
                $shouldBeCompletedBasedOnUserTz = $nowInUserTz->isAfter($endInUserTz);
            } else {
                // For events without end dates, check if start date has passed by day in user timezone
                $shouldBeCompletedBasedOnUserTz = $nowInUserTz->copy()->startOfDay()->isAfter($startInUserTz->copy()->startOfDay());
            }
            
            $isCompletedInUtc = $event->isCompleted();
            
            $this->line("  Should be completed (user TZ): " . ($shouldBeCompletedBasedOnUserTz ? 'YES' : 'NO'));
            $this->line("  Is completed (UTC logic): " . ($isCompletedInUtc ? 'YES' : 'NO'));
            
            // If there's a mismatch, we might need to fix the timezone
            if ($shouldBeCompletedBasedOnUserTz !== $isCompletedInUtc) {
                $this->warn("  ⚠️  Timezone mismatch detected!");
                
                if ($this->confirm("Fix this event's timezone?", true)) {
                    if (!$isDryRun) {
                        // Convert the current UTC times back to what they would be in user timezone
                        // Then treat them as if they were originally entered in user timezone
                        
                        // This is a complex fix - let's assume the dates were entered in user timezone
                        // but stored as if they were UTC
                        $originalStartInUserTz = Carbon::createFromFormat('Y-m-d H:i:s', $event->start_date->format('Y-m-d H:i:s'), $userTimezone);
                        $newStartUtc = $originalStartInUserTz->utc();
                        
                        $newEndUtc = null;
                        if ($event->end_date) {
                            $originalEndInUserTz = Carbon::createFromFormat('Y-m-d H:i:s', $event->end_date->format('Y-m-d H:i:s'), $userTimezone);
                            $newEndUtc = $originalEndInUserTz->utc();
                        }
                        
                        $this->line("  🔧 Converting dates:");
                        $this->line("    Old start (UTC): {$event->start_date}");
                        $this->line("    New start (UTC): {$newStartUtc}");
                        if ($event->end_date) {
                            $this->line("    Old end (UTC): {$event->end_date}");
                            $this->line("    New end (UTC): {$newEndUtc}");
                        }
                        
                        // Update the event
                        $event->start_date = $newStartUtc;
                        if ($newEndUtc) {
                            $event->end_date = $newEndUtc;
                        }
                        $event->save();
                        
                        Log::info('🕐 [TIMEZONE_FIX] Fixed event timezone', [
                            'event_id' => $event->id,
                            'event_name' => $event->name,
                            'user_timezone' => $userTimezone,
                            'old_start_utc' => $event->getOriginal('start_date'),
                            'new_start_utc' => $newStartUtc->toDateTimeString(),
                            'old_end_utc' => $event->getOriginal('end_date'),
                            'new_end_utc' => $newEndUtc ? $newEndUtc->toDateTimeString() : null,
                        ]);
                        
                        $fixedCount++;
                        $this->info("  ✅ Fixed!");
                    } else {
                        $this->line("  🔍 Would fix this event");
                        $fixedCount++;
                    }
                }
            } else {
                $this->line("  ✅ No fix needed");
            }
            
            $this->line("");
        }
        
        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would fix {$fixedCount} event(s).");
        } else {
            $this->info("✅ Fixed {$fixedCount} event(s).");
        }

        return 0;
    }
}

