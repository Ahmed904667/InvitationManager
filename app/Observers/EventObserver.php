<?php

namespace App\Observers;

use App\Shared\Models\Event;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "retrieved" event.
     * This is called whenever an event is retrieved from the database.
     * 
     * NOTE: We've disabled automatic completion on retrieval to avoid
     * performance issues and unexpected behavior. Event completion should
     * be handled by the scheduled command or manual triggers.
     */
    public function retrieved(Event $event): void
    {
        // Disabled for performance and reliability reasons
        // Event completion is now handled by:
        // 1. Scheduled command: `events:mark-completed` (runs every 5 minutes)
        // 2. Manual command: `event:complete {id} --force`
        // 3. Organizer interface actions
        
        // If you need to re-enable automatic completion on retrieval,
        // uncomment the code below, but be aware of performance implications:
        
        /*
        // Only check events that are not already completed
        if ($event->status === 'completed') {
            return;
        }

        // Check if the event should be completed
        if ($this->shouldBeCompleted($event)) {
            $this->markEventAsCompleted($event);
        }
        */
    }

    /**
     * Check if an event should be marked as completed
     */
    private function shouldBeCompleted(Event $event): bool
    {
        // Use the timezone-aware isCompleted method
        return $event->isCompleted();
    }

    /**
     * Mark an event as completed
     */
    private function markEventAsCompleted(Event $event): void
    {
        try {
            $event->markAsCompleted();
            
            Log::info('📅 [AUTO_COMPLETION] Event automatically marked as completed', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'start_date' => $event->start_date->toISOString(),
                'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                'completion_time' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('📅 [AUTO_COMPLETION_ERROR] Failed to mark event as completed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
