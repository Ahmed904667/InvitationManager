<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all events that have guest lists but may not have event_guest records
        $events = DB::table('events')
            ->join('event_guest_list', 'events.id', '=', 'event_guest_list.event_id')
            ->select('events.id as event_id', 'event_guest_list.guest_list_id')
            ->get()
            ->groupBy('event_id');

        $eventGuestService = app(\App\Services\EventGuestService::class);
        
        foreach ($events as $eventId => $guestListRelations) {
            $event = \App\Shared\Models\Event::find($eventId);
            if (!$event) continue;
            
            foreach ($guestListRelations as $relation) {
                $guestList = \App\Shared\Models\GuestList::find($relation->guest_list_id);
                if (!$guestList) continue;
                
                // Check if event_guest records already exist for this event and guest list
                $existingRecords = DB::table('event_guest')
                    ->join('guests', 'event_guest.guest_id', '=', 'guests.id')
                    ->where('event_guest.event_id', $eventId)
                    ->where('guests.guest_list_id', $relation->guest_list_id)
                    ->exists();
                
                if (!$existingRecords) {
                    // Create event_guest records for all guests in this guest list
                    $guests = DB::table('guests')
                        ->where('guest_list_id', $relation->guest_list_id)
                        ->get();
                    
                    foreach ($guests as $guest) {
                        // Check if this specific guest-event relationship already exists
                        $exists = DB::table('event_guest')
                            ->where('event_id', $eventId)
                            ->where('guest_id', $guest->id)
                            ->exists();
                        
                        if (!$exists) {
                            DB::table('event_guest')->insert([
                                'event_id' => $eventId,
                                'guest_id' => $guest->id,
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
        
        // Also handle standalone guests (guests without guest_list_id but with invitations)
        $standaloneGuests = DB::table('guests')
            ->whereNull('guest_list_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('invitations')
                      ->whereColumn('invitations.guest_id', 'guests.id');
            })
            ->get();
        
        foreach ($standaloneGuests as $guest) {
            // Get all events this standalone guest has invitations for
            $eventIds = DB::table('invitations')
                ->where('guest_id', $guest->id)
                ->pluck('event_id');
            
            foreach ($eventIds as $eventId) {
                // Check if event_guest record already exists
                $exists = DB::table('event_guest')
                    ->where('event_id', $eventId)
                    ->where('guest_id', $guest->id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('event_guest')->insert([
                        'event_id' => $eventId,
                        'guest_id' => $guest->id,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is for data integrity, so we don't need to reverse it
        // The event_guest records should remain for audit purposes
    }
};
