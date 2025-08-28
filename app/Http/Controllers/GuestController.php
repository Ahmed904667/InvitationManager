<?php

namespace App\Http\Controllers;

use App\Shared\Models\Guest;
use App\Shared\Models\Event;
use App\Shared\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GuestController extends Controller
{
    /**
     * Show individual guest details
     */
    public function show(Request $request, Event $event, Guest $guest)
    {
        // Check if user has access to this event
        $this->authorize('view', $event);
        
        // Check if guest belongs to this event
        $guestList = $event->guestLists->where('id', $guest->guest_list_id)->first();
        if (!$guestList) {
            abort(404, 'Guest not found in this event.');
        }

        // Get invitation for this guest and event
        $invitation = $guest->invitations->where('event_id', $event->id)->first();
        
        // Get RSVP history if any
        $rsvpHistory = [];
        if ($invitation) {
            $rsvpHistory = [
                'status' => $invitation->rsvp_status ?? 'no_response',
                'note' => $invitation->rsvp_note,
                'submitted_at' => $invitation->rsvp_at,
                'invitation_sent' => $invitation->status === 'sent',
                'sent_at' => $invitation->sent_at,
            ];
        }

        // Get check-in information
        $checkInInfo = [
            'checked_in' => $guest->checked_in ?? false,
            'checked_in_at' => $guest->checked_in_at,
            'checked_in_by' => $guest->checkedInBy ?? null,
        ];

        // Get guest's group information
        $group = $guest->group;

        Log::info('📋 [GUEST] Guest details viewed', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'user_id' => Auth::id(),
        ]);

        return view('organizer.guests.show', compact(
            'event',
            'guest',
            'guestList',
            'invitation',
            'rsvpHistory',
            'checkInInfo',
            'group'
        ));
    }



    /**
     * Get guest's RSVP history (AJAX endpoint)
     */
    public function getRsvpHistory(Request $request, Event $event, Guest $guest)
    {
        $this->authorize('view', $event);
        
        $invitation = $guest->invitations->where('event_id', $event->id)->first();
        
        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'No invitation found for this guest',
            ]);
        }

        $history = [
            'status' => $invitation->rsvp_status ?? 'no_response',
            'note' => $invitation->rsvp_note,
            'submitted_at' => $invitation->rsvp_at,
            'invitation_sent' => $invitation->status === 'sent',
            'sent_at' => $invitation->sent_at,
            'last_updated' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'rsvp_history' => $history,
        ]);
    }

    /**
     * Get guest's check-in status (AJAX endpoint)
     */
    public function getCheckInStatus(Request $request, Event $event, Guest $guest)
    {
        $this->authorize('view', $event);
        
        $checkInInfo = [
            'checked_in' => $guest->checked_in ?? false,
            'checked_in_at' => $guest->checked_in_at,
            'checked_in_by' => $guest->checkedInBy ? [
                'id' => $guest->checkedInBy->id,
                'name' => $guest->checkedInBy->name,
            ] : null,
            'last_updated' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'check_in_status' => $checkInInfo,
        ]);
    }
}
