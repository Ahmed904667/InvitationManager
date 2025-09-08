<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Shared\Models\Event;
use App\Shared\Models\User;
use App\Organizer\Services\OrganizerNotificationService;

class SendEventStartReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventId;
    public $minutesBefore;
    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(int $eventId, int $minutesBefore = 30)
    {
        $this->eventId = $eventId;
        $this->minutesBefore = $minutesBefore;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $event = Event::findOrFail($this->eventId);
            $organizer = $event->user;

            if (!$organizer) {
                Log::warning('Event has no organizer', [
                    'event_id' => $this->eventId
                ]);
                return;
            }

            // Send notification to organizer
            $notificationService = app(OrganizerNotificationService::class);
            $success = $notificationService->sendEventStartReminder($organizer, $event, $this->minutesBefore);

            if ($success) {
                Log::info('Event start reminder sent successfully', [
                    'event_id' => $this->eventId,
                    'event_name' => $event->name,
                    'organizer_id' => $organizer->id,
                    'minutes_before' => $this->minutesBefore
                ]);
            } else {
                Log::warning('Failed to send event start reminder', [
                    'event_id' => $this->eventId,
                    'organizer_id' => $organizer->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Event start reminder job failed', [
                'event_id' => $this->eventId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Event start reminder job failed permanently', [
            'event_id' => $this->eventId,
            'error' => $exception->getMessage()
        ]);
    }
}