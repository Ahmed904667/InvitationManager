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
        // Get all events with guest lists
        $events = DB::table('events')->get();
        
        foreach ($events as $event) {
            // Get guest lists for this event
            $guestListIds = DB::table('event_guest_list')
                ->where('event_id', $event->id)
                ->pluck('guest_list_id');
            
            foreach ($guestListIds as $guestListId) {
                // Get all guests in this guest list
                $guests = DB::table('guests')
                    ->where('guest_list_id', $guestListId)
                    ->get();
                
                foreach ($guests as $guest) {
                    // Check if event_guest record already exists
                    $exists = DB::table('event_guest')
                        ->where('event_id', $event->id)
                        ->where('guest_id', $guest->id)
                        ->exists();
                    
                    if (!$exists) {
                        // Create event_guest record
                        DB::table('event_guest')->insert([
                            'event_id' => $event->id,
                            'guest_id' => $guest->id,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            
            // Handle standalone guests (guests without guest_list_id but with invitations)
            $standaloneGuests = DB::table('guests')
                ->whereNull('guest_list_id')
                ->whereExists(function ($query) use ($event) {
                    $query->select(DB::raw(1))
                          ->from('invitations')
                          ->whereColumn('invitations.guest_id', 'guests.id')
                          ->where('invitations.event_id', $event->id);
                })
                ->get();
            
            foreach ($standaloneGuests as $guest) {
                // Check if event_guest record already exists
                $exists = DB::table('event_guest')
                    ->where('event_id', $event->id)
                    ->where('guest_id', $guest->id)
                    ->exists();
                
                if (!$exists) {
                    // Create event_guest record
                    DB::table('event_guest')->insert([
                        'event_id' => $event->id,
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
        // Clear all event_guest records
        DB::table('event_guest')->truncate();
    }
};
