<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\User;
use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use App\Organizer\Services\OrganizerNotificationService;

class TestOrganizerNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:organizer-notifications {--organizer-id= : ID of organizer to test with} {--event-id= : ID of event to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test organizer notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $organizerId = $this->option('organizer-id');
        $eventId = $this->option('event-id');

        // Find organizer
        if ($organizerId) {
            $organizer = User::find($organizerId);
        } else {
            $organizer = User::where('role', 'organizer')->first();
        }

        if (!$organizer) {
            $this->error('No organizer found. Please specify an organizer ID or ensure there are organizers in the system.');
            return 1;
        }

        $this->info("Testing notifications for organizer: {$organizer->name} ({$organizer->email})");

        // Find event
        if ($eventId) {
            $event = Event::find($eventId);
        } else {
            $event = $organizer->events()->first();
        }

        if (!$event) {
            $this->error('No event found. Please specify an event ID or ensure the organizer has events.');
            return 1;
        }

        $this->info("Using event: {$event->name}");

        $notificationService = app(OrganizerNotificationService::class);

        // Test event start reminder
        $this->info('Testing event start reminder notification...');
        $success = $notificationService->sendEventStartReminder($organizer, $event, 30);
        $this->info($success ? '✅ Event start reminder sent successfully' : '❌ Event start reminder failed');

        // Test RSVP notification (create a dummy guest if needed)
        $guest = $event->eventGuests()->with('guest')->first()?->guest;
        if (!$guest) {
            $this->warn('No guests found for this event. Creating a test guest...');
            
            // Create a guest list first if none exists
            $guestList = $organizer->guestLists()->first();
            if (!$guestList) {
                $guestList = $organizer->guestLists()->create([
                    'name' => 'Test Guest List',
                    'description' => 'Test guest list for notifications'
                ]);
            }
            
            $guest = Guest::create([
                'guest_list_id' => $guestList->id,
                'name' => 'Test Guest',
                'email' => 'test@example.com'
            ]);
        }

        $this->info('Testing RSVP notification...');
        $success = $notificationService->sendRsvpNotification($organizer, $event, $guest, 'yes', 'Looking forward to it!');
        $this->info($success ? '✅ RSVP notification sent successfully' : '❌ RSVP notification failed');

        // Show notification settings
        $settings = $notificationService->getNotificationSettings($organizer);
        $this->info("\nOrganizer notification settings:");
        $this->info("- Muted: " . ($settings['mute_notifications'] ? 'Yes' : 'No'));
        $this->info("- Preferred platform: " . $settings['preferred_platform']);
        $this->info("- Has phone: " . ($settings['has_phone'] ? 'Yes' : 'No'));

        $this->info("\nTest completed!");

        return 0;
    }
}