<?php

namespace App\Console\Commands;

use App\Shared\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateEventStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-statuses {--dry-run : Show what would be updated without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event statuses: mark as running when started, completed when ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('📅 Checking for events that need status updates...');
        
        $now = now();
        $runningCount = 0;
        $completedCount = 0;

        // First, mark events as running if they've started but haven't ended
        $eventsToMarkRunning = $this->getEventsToMarkRunning($now);
        
        if (!$eventsToMarkRunning->isEmpty()) {
            $this->info("🟡 Found {$eventsToMarkRunning->count()} event(s) to mark as running:");
            
            foreach ($eventsToMarkRunning as $event) {
                $reason = $this->getRunningReason($event, $now);
                $this->line("  • {$event->name} (ID: {$event->id}) - {$reason}");
                
                if (!$isDryRun) {
                    $event->markAsRunning();
                    $runningCount++;
                    
                    Log::info('📅 [STATUS_UPDATE] Event marked as running', [
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'previous_status' => $event->getOriginal('status'),
                        'new_status' => 'running',
                        'start_date' => $event->start_date->toISOString(),
                        'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                        'update_time' => $now->toISOString(),
                    ]);
                }
            }
        }

        // Then, mark events as completed if they've ended
        $eventsToMarkCompleted = $this->getEventsToMarkCompleted($now);
        
        if (!$eventsToMarkCompleted->isEmpty()) {
            $this->info("🔴 Found {$eventsToMarkCompleted->count()} event(s) to mark as completed:");
            
            foreach ($eventsToMarkCompleted as $event) {
                $reason = $this->getCompletionReason($event, $now);
                $this->line("  • {$event->name} (ID: {$event->id}) - {$reason}");
                
                if (!$isDryRun) {
                    $event->markAsCompleted();
                    $completedCount++;
                    
                    Log::info('📅 [STATUS_UPDATE] Event marked as completed', [
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'previous_status' => $event->getOriginal('status'),
                        'new_status' => 'completed',
                        'start_date' => $event->start_date->toISOString(),
                        'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                        'update_time' => $now->toISOString(),
                    ]);
                }
            }
        }

        if ($eventsToMarkRunning->isEmpty() && $eventsToMarkCompleted->isEmpty()) {
            $this->info('✅ No events need status updates.');
            return 0;
        }

        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would mark {$eventsToMarkRunning->count()} event(s) as running and {$eventsToMarkCompleted->count()} event(s) as completed.");
        } else {
            $this->info("✅ Successfully updated statuses: {$runningCount} running, {$completedCount} completed.");
        }

        return 0;
    }

    /**
     * Get events that should be marked as running
     */
    private function getEventsToMarkRunning($now)
    {
        return Event::where('status', '!=', 'running')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'draft')  // Exclude draft events from auto-transition
            ->where('start_date', '<=', $now)
            ->where(function ($query) use ($now) {
                // Events with end date that hasn't passed yet
                $query->where(function ($q) use ($now) {
                    $q->whereNotNull('end_date')
                      ->where('end_date', '>', $now);
                })->orWhere(function ($q) use ($now) {
                    // Events without end date that started today
                    $q->whereNull('end_date')
                      ->whereDate('start_date', $now->toDateString());
                });
            })
            ->get();
    }

    /**
     * Get events that should be marked as completed
     */
    private function getEventsToMarkCompleted($now)
    {
        $startOfDay = $now->copy()->startOfDay();
        
        return Event::where('status', '!=', 'completed')
            ->where('status', '!=', 'draft')  // Exclude draft events from auto-transition
            ->where(function ($query) use ($now, $startOfDay) {
                $query->where(function ($q) use ($now) {
                    // Events with end date that has passed
                    $q->whereNotNull('end_date')
                      ->where('end_date', '<', $now);
                })->orWhere(function ($q) use ($startOfDay) {
                    // Events without end date where start date has passed (by day)
                    $q->whereNull('end_date')
                      ->where('start_date', '<', $startOfDay);
                });
            })
            ->get();
    }

    /**
     * Get the reason why an event should be marked as running
     */
    private function getRunningReason(Event $event, $now): string
    {
        if ($event->end_date) {
            return "Event started and ongoing (started: {$event->start_date->format('Y-m-d H:i')}, ends: {$event->end_date->format('Y-m-d H:i')})";
        }
        
        return "Event started today (started: {$event->start_date->format('Y-m-d H:i')})";
    }

    /**
     * Get the reason why an event should be marked as completed
     */
    private function getCompletionReason(Event $event, $now): string
    {
        if ($event->end_date && $now->isAfter($event->end_date)) {
            return "End date passed ({$event->end_date->format('Y-m-d H:i')})";
        }
        
        if (!$event->end_date && $event->start_date->copy()->startOfDay()->isBefore($now->copy()->startOfDay())) {
            return "Start date passed (by day) ({$event->start_date->format('Y-m-d')})";
        }
        
        return "Unknown reason";
    }
}
