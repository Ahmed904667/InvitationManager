<?php

namespace App\Jobs;

use App\Shared\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateEventStatuses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🔄 [EVENT_STATUS_UPDATE] Starting automatic event status update job');

        $now = Carbon::now();
        $updatedCount = 0;

        // Get all events that are not already completed or cancelled
        $events = Event::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('start_date')
            ->get();

        Log::info('🔄 [EVENT_STATUS_UPDATE] Found ' . $events->count() . ' events to check');

        foreach ($events as $event) {
            $originalStatus = $event->status;
            $statusChanged = false;

            try {
                // Check if event should be marked as running
                if ($event->isOngoing() && $event->status !== 'running') {
                    $event->markAsRunning();
                    $statusChanged = true;
                    
                    Log::info('🏃 [EVENT_STATUS_UPDATE] Event marked as running', [
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'previous_status' => $originalStatus,
                        'start_date' => $event->start_date->toISOString(),
                        'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                        'update_time' => $now->toISOString(),
                    ]);
                }
                // Check if event should be marked as completed
                elseif ($event->isCompleted() && $event->status !== 'completed') {
                    $event->markAsCompleted();
                    $statusChanged = true;
                    
                    Log::info('✅ [EVENT_STATUS_UPDATE] Event marked as completed', [
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'previous_status' => $originalStatus,
                        'start_date' => $event->start_date->toISOString(),
                        'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                        'completion_time' => $now->toISOString(),
                    ]);
                }

                if ($statusChanged) {
                    $updatedCount++;
                }

            } catch (\Exception $e) {
                Log::error('❌ [EVENT_STATUS_UPDATE] Error updating event status', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('🔄 [EVENT_STATUS_UPDATE] Completed automatic event status update', [
            'total_events_checked' => $events->count(),
            'events_updated' => $updatedCount,
            'execution_time' => $now->toISOString()
        ]);
    }
}
