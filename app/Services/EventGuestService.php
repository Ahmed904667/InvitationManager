<?php

namespace App\Services;

use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use App\EventGuest;
use Illuminate\Support\Facades\DB;

class EventGuestService
{
    /**
     * Add a guest to an event
     */
    public function addGuestToEvent(Event $event, Guest $guest): EventGuest
    {
        return EventGuest::updateOrCreate(
            [
                'event_id' => $event->id,
                'guest_id' => $guest->id,
            ],
            [
                'status' => EventGuest::STATUS_ACTIVE,
                'removed_at' => null,
                'removal_reason' => null,
                'removed_by' => null,
            ]
        );
    }

    /**
     * Add multiple guests to an event (from a guest list)
     */
    public function addGuestListToEvent(Event $event, $guestList, array $excludedGuestIds = []): array
    {
        $addedGuests = [];
        
        // Ensure guest list is loaded with guests
        if (!$guestList->relationLoaded('guests')) {
            $guestList->load('guests');
        }
        
        \Log::info('🔗 [EVENT_GUEST_SERVICE] Adding guest list to event', [
            'event_id' => $event->id,
            'guest_list_id' => $guestList->id,
            'guest_list_name' => $guestList->name,
            'guests_count' => $guestList->guests->count(),
            'guests_loaded' => $guestList->relationLoaded('guests'),
            'excluded_guest_ids' => $excludedGuestIds,
            'excluded_count' => count($excludedGuestIds)
        ]);
        
        DB::transaction(function () use ($event, $guestList, $excludedGuestIds, &$addedGuests) {
            foreach ($guestList->guests as $guest) {
                // Skip excluded guests (duplicates that were removed)
                if (in_array($guest->id, $excludedGuestIds)) {
                    \Log::info('🔗 [EVENT_GUEST_SERVICE] Skipping excluded guest', [
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'guest_name' => $guest->name,
                        'reason' => 'excluded_duplicate'
                    ]);
                    continue;
                }
                
                \Log::info('🔗 [EVENT_GUEST_SERVICE] Adding guest to event', [
                    'event_id' => $event->id,
                    'guest_id' => $guest->id,
                    'guest_name' => $guest->name
                ]);
                
                $eventGuest = $this->addGuestToEvent($event, $guest);
                $addedGuests[] = $eventGuest;
            }
        });
        
        \Log::info('🔗 [EVENT_GUEST_SERVICE] Completed adding guest list to event', [
            'event_id' => $event->id,
            'guest_list_id' => $guestList->id,
            'relationships_created' => count($addedGuests)
        ]);
        
        return $addedGuests;
    }

    /**
     * Remove a guest from an event
     */
    public function removeGuestFromEvent(
        Event $event, 
        Guest $guest, 
        ?string $reason = null, 
        ?int $removedBy = null
    ): EventGuest {
        $eventGuest = EventGuest::where('event_id', $event->id)
                               ->where('guest_id', $guest->id)
                               ->first();
        
        if (!$eventGuest) {
            throw new \Exception('Guest is not associated with this event');
        }
        
        $eventGuest->markAsRemoved($reason, $removedBy);
        
        return $eventGuest;
    }

    /**
     * Get active guests for an event
     */
    public function getActiveGuestsForEvent(Event $event)
    {
        return EventGuest::where('event_id', $event->id)
                        ->where('status', EventGuest::STATUS_ACTIVE)
                        ->with(['guest.guestList', 'guest.invitations' => function($query) use ($event) {
                            $query->where('event_id', $event->id);
                        }])
                        ->get();
    }

    /**
     * Get removed guests for an event
     */
    public function getRemovedGuestsForEvent(Event $event)
    {
        return EventGuest::where('event_id', $event->id)
                        ->where('status', EventGuest::STATUS_REMOVED)
                        ->with('guest.guestList')
                        ->get();
    }

    /**
     * Check if a guest is active for an event
     */
    public function isGuestActiveForEvent(Event $event, Guest $guest): bool
    {
        return EventGuest::where('event_id', $event->id)
                        ->where('guest_id', $guest->id)
                        ->where('status', EventGuest::STATUS_ACTIVE)
                        ->exists();
    }

    /**
     * Get all events a guest is active in
     */
    public function getActiveEventsForGuest(Guest $guest)
    {
        return EventGuest::where('guest_id', $guest->id)
                        ->where('status', EventGuest::STATUS_ACTIVE)
                        ->with('event')
                        ->get();
    }
}
