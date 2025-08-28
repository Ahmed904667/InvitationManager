<?php

namespace App\Console\Commands;

use App\Shared\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkCompletedEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:mark-completed {--dry-run : Show what would be marked as completed without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark events as completed based on their start/end dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('📅 Checking for events that should be marked as completed...');

        // Get events that should be completed but aren't marked as such
        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        
        $eventsToComplete = Event::where('status', '!=', 'completed')
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

        if ($eventsToComplete->isEmpty()) {
            $this->info('✅ No events need to be marked as completed.');
            return 0;
        }

        $this->info("📋 Found {$eventsToComplete->count()} event(s) to mark as completed:");

        $completedCount = 0;
        foreach ($eventsToComplete as $event) {
            $completionReason = $this->getCompletionReason($event);
            
            $this->line("  • {$event->name} (ID: {$event->id}) - {$completionReason}");
            
            if (!$isDryRun) {
                $event->markAsCompleted();
                $completedCount++;
                
                Log::info('📅 [EVENT_COMPLETION] Event marked as completed', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'completion_reason' => $completionReason,
                    'start_date' => $event->start_date->toISOString(),
                    'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                ]);
            }
        }

        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would mark {$eventsToComplete->count()} event(s) as completed.");
        } else {
            $this->info("✅ Successfully marked {$completedCount} event(s) as completed.");
        }

        return 0;
    }

    /**
     * Get the reason why an event should be marked as completed
     */
    private function getCompletionReason(Event $event): string
    {
        $now = now();
        
        if ($event->end_date && $now->isAfter($event->end_date)) {
            return "End date passed ({$event->end_date->format('Y-m-d H:i')})";
        }
        
        if (!$event->end_date && $event->start_date->copy()->startOfDay()->isBefore($now->copy()->startOfDay())) {
            return "Start date passed (by day) ({$event->start_date->format('Y-m-d')})";
        }
        
        return "Unknown reason";
    }
}
