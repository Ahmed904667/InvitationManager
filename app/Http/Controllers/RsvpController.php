<?php

namespace App\Http\Controllers;

use App\Shared\Models\Invitation;
use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RsvpController extends Controller
{
    /**
     * Handle RSVP submission from guest invitation
     */
    public function submit(Request $request, string $token)
    {
        try {
            // Find the invitation (any one with this token will work since they all point to the same guest/event)
            $invitation = Invitation::where('token', $token)->firstOrFail();
            $event = Event::findOrFail($invitation->event_id);
            $guest = Guest::findOrFail($invitation->guest_id);

            // Validate the request
            $validated = $request->validate([
                'rsvp_status' => 'required|in:yes,no,maybe',
                'rsvp_note' => 'nullable|string|max:500',
            ]);

            // Check if RSVP is enabled for this event
            if (!$event->rsvp_enabled) {
                return back()->with('error', 'RSVP is not enabled for this event.');
            }

            // Update ALL invitation records with this token (both email and WhatsApp)
            $previousStatus = $invitation->rsvp_status;
            Invitation::where('token', $token)->update([
                'rsvp_status' => $validated['rsvp_status'],
                'rsvp_note' => $validated['rsvp_note'] ?? null,
                'rsvp_at' => now(),
            ]);

            // Log the RSVP response
            Log::info('📋 [RSVP] Guest responded to invitation', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'guest_id' => $guest->id,
                'guest_name' => $guest->name,
                'guest_email' => $guest->email,
                'previous_status' => $previousStatus,
                'new_status' => $validated['rsvp_status'],
                'has_note' => !empty($validated['rsvp_note']),
                'response_time' => now()->toISOString(),
            ]);

            // Determine success message
            if ($previousStatus === null || $previousStatus === 'none') {
                $message = 'Thank you for your RSVP response!';
            } else {
                $message = 'Your RSVP has been updated successfully!';
            }

            // Add status-specific messages
            switch ($validated['rsvp_status']) {
                case 'yes':
                    $message .= ' We look forward to seeing you at the event!';
                    break;
                case 'maybe':
                    $message .= ' We hope you can make it!';
                    break;
                case 'no':
                    $message .= ' We\'ll miss you, but thank you for letting us know.';
                    break;
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('❌ [RSVP] Error processing RSVP response', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Sorry, there was an error processing your RSVP. Please try again.');
        }
    }

    /**
     * Get RSVP statistics for an event (AJAX endpoint)
     */
    public function getStats(Request $request, Event $event)
    {
        try {
            $this->authorize('view', $event);

            $stats = $this->calculateRsvpStats($event);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'last_updated' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [RSVP] Error getting RSVP stats', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading RSVP statistics',
            ], 500);
        }
    }

    /**
     * Get detailed RSVP list for an event
     */
    public function getDetails(Request $request, Event $event)
    {
        try {
            $this->authorize('view', $event);

            $rsvpDetails = $this->getRsvpDetails($event);

            return response()->json([
                'success' => true,
                'rsvp_details' => $rsvpDetails,
                'last_updated' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [RSVP] Error getting RSVP details', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading RSVP details',
            ], 500);
        }
    }

    /**
     * Calculate RSVP statistics for an event
     */
    private function calculateRsvpStats(Event $event): array
    {
        $stats = [
            'total_guests' => 0,
            'rsvp_responses' => [
                'yes' => 0,
                'no' => 0,
                'maybe' => 0,
                'no_response' => 0,
            ],
            'response_rate' => 0,
            'attendance_rate' => 0,
        ];

        foreach ($event->guestLists as $guestList) {
            foreach ($guestList->guests as $guest) {
                $stats['total_guests']++;
                
                $invitation = $guest->invitations->where('event_id', $event->id)->first();
                if ($invitation && $invitation->rsvp_status && $invitation->rsvp_status !== 'none') {
                    $stats['rsvp_responses'][$invitation->rsvp_status]++;
                } else {
                    $stats['rsvp_responses']['no_response']++;
                }
            }
        }

        // Calculate response rate
        if ($stats['total_guests'] > 0) {
            $responded = $stats['rsvp_responses']['yes'] + $stats['rsvp_responses']['no'] + $stats['rsvp_responses']['maybe'];
            $stats['response_rate'] = round(($responded / $stats['total_guests']) * 100, 1);
            
            // Calculate attendance rate (yes + maybe)
            $attending = $stats['rsvp_responses']['yes'] + $stats['rsvp_responses']['maybe'];
            $stats['attendance_rate'] = round(($attending / $stats['total_guests']) * 100, 1);
        }

        return $stats;
    }

    /**
     * Get detailed RSVP information for each guest
     */
    private function getRsvpDetails(Event $event): array
    {
        $details = [];

        foreach ($event->guestLists as $guestList) {
            foreach ($guestList->guests as $guest) {
                $invitation = $guest->invitations->where('event_id', $event->id)->first();
                
                $guestDetail = [
                    'guest_id' => $guest->id,
                    'guest_name' => $guest->name,
                    'guest_email' => $guest->email,
                    'guest_phone' => $guest->phone,
                    'guest_list_name' => $guestList->name,
                    'rsvp_status' => $invitation ? ($invitation->rsvp_status ?? 'no_response') : 'no_response',
                    'rsvp_note' => $invitation ? $invitation->rsvp_note : null,
                    'rsvp_at' => $invitation ? $invitation->rsvp_at : null,
                    'invitation_sent' => $invitation ? ($invitation->status === 'sent') : false,
                    'checked_in' => $guest->checked_in ?? false,
                ];

                $details[] = $guestDetail;
            }
        }

        // Sort by RSVP status and then by name
        usort($details, function ($a, $b) {
            $statusOrder = ['yes' => 1, 'maybe' => 2, 'no' => 3, 'no_response' => 4];
            $aOrder = $statusOrder[$a['rsvp_status']] ?? 5;
            $bOrder = $statusOrder[$b['rsvp_status']] ?? 5;
            
            if ($aOrder !== $bOrder) {
                return $aOrder - $bOrder;
            }
            
            return strcasecmp($a['guest_name'], $b['guest_name']);
        });

        return $details;
    }
}

