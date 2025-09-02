<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use App\Shared\Models\GuestList;
use Carbon\Carbon;

class ShowEventGuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:show-guests 
                            {--name= : Filter by event name (partial match)}
                            {--date= : Filter by event date (YYYY-MM-DD or "last year")}
                            {--event-id= : Filter by specific event ID}
                            {--detailed : Show detailed guest information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all guests and guest lists for a specific event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Event::with(['guestLists.guests', 'eventGuests.guest']);

        // Apply filters
        if ($eventId = $this->option('event-id')) {
            $query->where('id', $eventId);
        }

        if ($name = $this->option('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($date = $this->option('date')) {
            if ($date === 'last year') {
                $lastYear = Carbon::now()->subYear()->year;
                $query->whereYear('start_date', $lastYear);
            } else {
                try {
                    $dateObj = Carbon::parse($date);
                    $query->whereDate('start_date', $dateObj);
                } catch (\Exception $e) {
                    $this->error("Invalid date format. Use YYYY-MM-DD or 'last year'");
                    return 1;
                }
            }
        }

        $events = $query->orderBy('start_date', 'desc')->get();

        if ($events->isEmpty()) {
            $this->info('No events found matching the criteria.');
            return 0;
        }

        foreach ($events as $event) {
            $this->displayEvent($event);
        }

        return 0;
    }

    /**
     * Display information for a single event
     */
    private function displayEvent(Event $event): void
    {
        $this->newLine();
        $this->info("=" . str_repeat("=", 80));
        $this->info("EVENT: {$event->name}");
        $this->info("=" . str_repeat("=", 80));
        
        $this->line("ID: {$event->id}");
        $this->line("Date: " . $event->start_date->format('F j, Y g:i A'));
        $this->line("Location: " . ($event->location ?? 'N/A'));
        $this->line("Status: {$event->status}");
        $this->newLine();

        // Display Guest Lists
        $this->displayGuestLists($event);

        // Display Direct Event Guests
        $this->displayDirectEventGuests($event);

        // Summary
        $this->displaySummary($event);
    }

    /**
     * Display guest lists for the event
     */
    private function displayGuestLists(Event $event): void
    {
        $guestLists = $event->guestLists;
        
        if ($guestLists->isEmpty()) {
            $this->line("No guest lists associated with this event.");
            return;
        }

        $this->info("GUEST LISTS ({$guestLists->count()}):");
        $this->line(str_repeat("-", 40));

        foreach ($guestLists as $guestList) {
            $this->line("📋 {$guestList->name}");
            $this->line("   ID: {$guestList->id}");
            $this->line("   Description: " . ($guestList->description ?? 'N/A'));
            $this->line("   Max Guests: " . ($guestList->max_guests ?? 'Unlimited'));
            
            $guests = $guestList->guests;
            $this->line("   Total Guests: {$guests->count()}");
            
            if ($guests->isNotEmpty()) {
                $this->displayGuests($guests, "   ");
            }
            
            $this->newLine();
        }
    }

    /**
     * Display direct event guests
     */
    private function displayDirectEventGuests(Event $event): void
    {
        $eventGuests = $event->eventGuests;
        
        if ($eventGuests->isEmpty()) {
            $this->line("No direct event guests.");
            return;
        }

        $this->info("DIRECT EVENT GUESTS ({$eventGuests->count()}):");
        $this->line(str_repeat("-", 40));

        $guests = $eventGuests->map->guest->filter();
        $this->displayGuests($guests);
    }

    /**
     * Display guests in a formatted way
     */
    private function displayGuests($guests, string $prefix = ""): void
    {
        $isDetailed = $this->option('detailed');
        
        if ($isDetailed) {
            $headers = ['ID', 'Name', 'Email', 'Phone', 'Group', 'Checked In', 'Notes'];
            $rows = [];

            foreach ($guests as $guest) {
                $rows[] = [
                    $guest->id,
                    $guest->name,
                    $guest->email ?? 'N/A',
                    $guest->phone ?? 'N/A',
                    $guest->group ? $guest->group->name : 'N/A',
                    $guest->checked_in ? 'Yes' : 'No',
                    $guest->notes ?? 'N/A'
                ];
            }

            $this->table($headers, $rows);
        } else {
            foreach ($guests as $guest) {
                $checkInStatus = $guest->checked_in ? '✅' : '❌';
                $groupInfo = $guest->group ? " ({$guest->group->name})" : '';
                $this->line("{$prefix}   {$checkInStatus} {$guest->name}{$groupInfo}");
            }
        }
    }

    /**
     * Display summary statistics
     */
    private function displaySummary(Event $event): void
    {
        $this->newLine();
        $this->info("SUMMARY:");
        $this->line(str_repeat("-", 20));

        // Count guests from guest lists
        $guestListGuests = $event->guestLists->flatMap->guests->unique('id');
        $directEventGuests = $event->eventGuests->map->guest->filter();
        
        // Combine and deduplicate
        $allGuests = $guestListGuests->merge($directEventGuests)->unique('id');
        
        $checkedInCount = $allGuests->where('checked_in', true)->count();
        $totalCount = $allGuests->count();

        $this->line("Total Guest Lists: " . $event->guestLists->count());
        $this->line("Total Unique Guests: {$totalCount}");
        $this->line("Checked In: {$checkedInCount}");
        $this->line("Not Checked In: " . ($totalCount - $checkedInCount));
        
        if ($totalCount > 0) {
            $checkInRate = round(($checkedInCount / $totalCount) * 100, 1);
            $this->line("Check-in Rate: {$checkInRate}%");
        }
    }
}
