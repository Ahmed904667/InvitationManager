<?php

namespace App\Organizer\Services;

use Illuminate\Support\Facades\Auth;
use App\EventGuest;

class EventReportService
{
    /**
     * Get detailed report data for a specific completed event
     */
    public function getDetailedEventReport($eventId): array
    {
        $user = Auth::user();
        $event = $user->events()->with(['invitations', 'eventGuests.guest', 'guestLists'])->findOrFail($eventId);
        
        if ($event->status !== 'completed' && !$event->isCompleted()) {
            throw new \Exception('Event is not completed');
        }
        
        $eventGuests = $event->eventGuests()->where('status', EventGuest::STATUS_ACTIVE)->get();
        $invitations = $event->invitations()->get();
        
        // Get check-in time distribution
        $checkinData = $this->getCheckinTimeDistribution($event);
        
        // Get RSVP time distribution
        $rsvpData = $this->getRSVPTimeDistribution($event);
        
        // Get detailed guest list
        $guests = $this->getDetailedGuestList($event);
        
        // Get comprehensive event information
        $eventInfo = $this->getComprehensiveEventInfo($event);
        
        // Get invitation statistics
        $invitationStats = $this->getEventInvitationStats($event);
        
        // Get check-in statistics
        $checkinStats = $this->getEventCheckinStats($event);
        
        // Get guest engagement metrics
        $engagementMetrics = $this->getEventEngagementMetrics($event);
        
        return [
            // Basic event info
            'id' => $event->id,
            'event_name' => $event->name ?? 'Unnamed Event',
            'description' => $event->description ?? null,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location ?? null,
            'venue_name' => $event->venue_name ?? null,
            'venue_address' => $event->venue_address ?? null,
            'status' => $event->status ?? 'unknown',
            'created_at' => $event->created_at,
            
            // Guest counts
            'total_guests' => $eventGuests->count(),
            'checked_in_guests' => $eventGuests->where('checked_in', true)->count(),
            'checkin_rate' => $eventGuests->count() > 0 ? round(($eventGuests->where('checked_in', true)->count() / $eventGuests->count()) * 100, 1) : 0,
            
            // Invitation data
            'invitations_sent' => $invitations->where('status', 'sent')->count(),
            'rsvp_yes' => $invitations
                ->where('status', 'sent')
                ->where('rsvp_status', 'yes')
                ->pluck('guest_id')
                ->unique()
                ->count(),
            'rsvp_maybe' => $invitations
                ->where('status', 'sent')
                ->where('rsvp_status', 'maybe')
                ->pluck('guest_id')
                ->unique()
                ->count(),
            'rsvp_no' => $invitations
                ->where('status', 'sent')
                ->where('rsvp_status', 'no')
                ->pluck('guest_id')
                ->unique()
                ->count(),
            'rsvp_pending' => $invitations
                ->where('status', 'sent')
                ->pluck('guest_id')
                ->unique()
                ->count() - $invitations
                ->where('status', 'sent')
                ->whereNotNull('rsvp_status')
                ->where('rsvp_status', '!=', 'none')
                ->pluck('guest_id')
                ->unique()
                ->count(),
            
            // Feature flags
            'checkin_enabled' => $event->qr_checkin_enabled ?? true,
            'rsvp_enabled' => $event->rsvp_enabled ?? true,
            
            // Charts data
            'checkin_data' => $checkinData,
            'rsvp_data' => $rsvpData,
            
            // Detailed data
            'guests' => $guests,
            'event_info' => $eventInfo,
            'invitation_stats' => $invitationStats,
            'checkin_stats' => $checkinStats,
            'engagement_metrics' => $engagementMetrics,
        ];
    }

    /**
     * Get check-in time distribution for an event
     */
    private function getCheckinTimeDistribution($event): array
    {
        $checkins = $event->eventGuests()
            ->where('checked_in', true)
            ->whereNotNull('checked_in_at')
            ->get();
        
        $timeSlots = [
            '9 AM' => 0, '10 AM' => 0, '11 AM' => 0, '12 PM' => 0,
            '1 PM' => 0, '2 PM' => 0, '3 PM' => 0, '4 PM' => 0, '5 PM' => 0
        ];
        
        foreach ($checkins as $checkin) {
            $hour = $checkin->checked_in_at->hour;
            if ($hour >= 9 && $hour < 10) $timeSlots['9 AM']++;
            elseif ($hour >= 10 && $hour < 11) $timeSlots['10 AM']++;
            elseif ($hour >= 11 && $hour < 12) $timeSlots['11 AM']++;
            elseif ($hour >= 12 && $hour < 13) $timeSlots['12 PM']++;
            elseif ($hour >= 13 && $hour < 14) $timeSlots['1 PM']++;
            elseif ($hour >= 14 && $hour < 15) $timeSlots['2 PM']++;
            elseif ($hour >= 15 && $hour < 16) $timeSlots['3 PM']++;
            elseif ($hour >= 16 && $hour < 17) $timeSlots['4 PM']++;
            elseif ($hour >= 17 && $hour < 18) $timeSlots['5 PM']++;
        }
        
        return [
            'time_labels' => array_keys($timeSlots),
            'time_data' => array_values($timeSlots),
        ];
    }

    /**
     * Get RSVP time distribution for an event
     */
    private function getRSVPTimeDistribution($event): array
    {
        $invitations = $event->invitations()
            ->whereNotNull('rsvp_status')
            ->where('rsvp_status', '!=', 'none')
            ->whereNotNull('rsvp_at')
            ->get();
        
        $timeCategories = [
            'Same Day' => 0, '1 Day' => 0, '2 Days' => 0, '3 Days' => 0,
            '1 Week' => 0, '2 Weeks' => 0, '1 Month+' => 0
        ];
        
        foreach ($invitations as $invitation) {
            $daysDiff = $event->start_date->diffInDays($invitation->rsvp_at);
            
            if ($daysDiff == 0) $timeCategories['Same Day']++;
            elseif ($daysDiff == 1) $timeCategories['1 Day']++;
            elseif ($daysDiff == 2) $timeCategories['2 Days']++;
            elseif ($daysDiff == 3) $timeCategories['3 Days']++;
            elseif ($daysDiff <= 7) $timeCategories['1 Week']++;
            elseif ($daysDiff <= 14) $timeCategories['2 Weeks']++;
            else $timeCategories['1 Month+']++;
        }
        
        return [
            'time_labels' => array_keys($timeCategories),
            'time_data' => array_values($timeCategories),
        ];
    }

    /**
     * Get detailed guest list for an event
     */
    private function getDetailedGuestList($event): array
    {
        $eventGuests = $event->eventGuests()
            ->where('status', EventGuest::STATUS_ACTIVE)
            ->with(['guest'])
            ->get();
        
        // Get invitations for this event to match with guests
        $invitations = $event->invitations()->get()->keyBy('guest_id');
        
        $guests = [];
        foreach ($eventGuests as $eventGuest) {
            // Find the invitation for this guest
            $invitation = $invitations->get($eventGuest->guest_id);
            
            $guests[] = [
                'name' => $eventGuest->guest ? $eventGuest->guest->name : 'N/A',
                'email' => $eventGuest->guest ? $eventGuest->guest->email : 'N/A',
                'rsvp_status' => $invitation ? $invitation->rsvp_status : null,
                'rsvp_date' => $invitation && $invitation->rsvp_at ? $invitation->rsvp_at->format('M j, Y g:i A') : null,
                'checkin_time' => $eventGuest->checked_in_at ? $eventGuest->checked_in_at->format('M j, Y g:i A') : null,
                'checkin_method' => $eventGuest->scanner_name ?? 'Manual',
            ];
        }
        
        return $guests;
    }

    /**
     * Get comprehensive event information
     */
    private function getComprehensiveEventInfo($event): array
    {
        return [
            'description' => $event->description ?? null,
            'location' => $event->location ?? null,
            'venue_name' => $event->venue_name ?? null,
            'venue_address' => $event->venue_address ?? null,
            'parking_info' => $event->parking_info ?? null,
            'additional_information' => $event->additional_information ?? null,
            'invitation_title' => $event->invitation_title ?? null,
            'invitation_subtitle' => $event->invitation_subtitle ?? null,
            'invitation_message' => $event->invitation_message ?? null,
            'rsvp_message' => $event->rsvp_message ?? null,
            'rsvp_deadline' => $event->rsvp_deadline ?? null,
            'rsvp_contact' => $event->rsvp_contact ?? null,
            'qr_code_url' => $event->qr_code_url ?? null,
            'qr_description' => $event->qr_description ?? null,
            'message_mode' => $event->message_mode ?? null,
            'general_message' => $event->general_message ?? null,
            'ai_generated' => $event->ai_generated ?? false,
            'hero_color1' => $event->hero_color1 ?? null,
            'hero_color2' => $event->hero_color2 ?? null,
            'accent_color' => $event->accent_color ?? null,
            'font_family' => $event->font_family ?? null,
            'guest_list_count' => $event->guestLists()->count(),
            'scanner_count' => $event->scanners()->count(),
            'active_scanner_count' => $event->activeScanners()->count(),
        ];
    }

    /**
     * Get detailed invitation statistics for an event
     */
    private function getEventInvitationStats($event): array
    {
        $invitations = $event->invitations()->get();
        
        // Count unique guests who have responded (regardless of platform)
        $uniqueGuestsWithRSVP = $invitations
            ->where('status', 'sent')
            ->whereNotNull('rsvp_status')
            ->where('rsvp_status', '!=', 'none')
            ->pluck('guest_id')
            ->unique()
            ->count();
        
        // Count total unique guests invited
        $totalUniqueGuests = $invitations
            ->where('status', 'sent')
            ->pluck('guest_id')
            ->unique()
            ->count();
        
        // Count unique guests by RSVP status
        $rsvpBreakdown = [
            'yes' => $invitations
                ->where('status', 'sent')
                ->where('rsvp_status', 'yes')
                ->pluck('guest_id')
                ->unique()
                ->count(),
            'maybe' => $invitations
                ->where('status', 'sent')
                ->where('rsvp_status', 'maybe')
                ->pluck('guest_id')
                ->unique()
                ->count(),
            'no' => $invitations
                ->where('status', 'sent')
                ->where('rsvp_status', 'no')
                ->pluck('guest_id')
                ->unique()
                ->count(),
            'pending' => $totalUniqueGuests - $uniqueGuestsWithRSVP,
        ];
        
        return [
            'total_invitations' => $invitations->count(),
            'total_unique_guests_invited' => $totalUniqueGuests,
            'sent_invitations' => $invitations->where('status', 'sent')->count(),
            'pending_invitations' => $invitations->where('status', 'pending')->count(),
            'failed_invitations' => $invitations->where('status', 'failed')->count(),
            'expired_invitations' => $invitations->where('status', 'expired')->count(),
            'delivery_rate' => $invitations->count() > 0 ? round(($invitations->where('status', 'sent')->count() / $invitations->count()) * 100, 1) : 0,
            'rsvp_response_rate' => $totalUniqueGuests > 0 ? round(($uniqueGuestsWithRSVP / $totalUniqueGuests) * 100, 1) : 0,
            'rsvp_breakdown' => $rsvpBreakdown,
            'channel_breakdown' => $invitations->groupBy('channel')->map->count()->toArray(),
        ];
    }

    /**
     * Get detailed check-in statistics for an event
     */
    private function getEventCheckinStats($event): array
    {
        $eventGuests = $event->eventGuests()->where('status', EventGuest::STATUS_ACTIVE)->get();
        
        return [
            'total_guests' => $eventGuests->count(),
            'checked_in_guests' => $eventGuests->where('checked_in', true)->count(),
            'not_checked_in' => $eventGuests->where('checked_in', false)->count(),
            'checkin_rate' => $eventGuests->count() > 0 ? round(($eventGuests->where('checked_in', true)->count() / $eventGuests->count()) * 100, 1) : 0,
            'first_checkin' => $eventGuests->where('checked_in', true)->min('checked_in_at'),
            'last_checkin' => $eventGuests->where('checked_in', true)->max('checked_in_at'),
            'average_checkin_time' => $this->getAverageCheckinTime($event),
            'scanner_usage' => $this->getScannerUsageStats($event),
        ];
    }

    /**
     * Get guest engagement metrics for an event
     */
    private function getEventEngagementMetrics($event): array
    {
        $totalGuests = $event->eventGuests()->where('status', EventGuest::STATUS_ACTIVE)->count();
        $rsvpResponses = $event->invitations()->whereNotNull('rsvp_status')->where('rsvp_status', '!=', 'none')->count();
        $checkins = $event->eventGuests()->where('checked_in', true)->count();
        
        return [
            'total_guests' => $totalGuests,
            'rsvp_engagement' => $rsvpResponses,
            'checkin_engagement' => $checkins,
            'overall_engagement_rate' => $totalGuests > 0 ? round((($rsvpResponses + $checkins) / ($totalGuests * 2)) * 100, 1) : 0,
            'rsvp_engagement_rate' => $totalGuests > 0 ? round(($rsvpResponses / $totalGuests) * 100, 1) : 0,
            'checkin_engagement_rate' => $totalGuests > 0 ? round(($checkins / $totalGuests) * 100, 1) : 0,
        ];
    }

    /**
     * Get average check-in time for an event
     */
    private function getAverageCheckinTime($event): ?string
    {
        $checkins = $event->eventGuests()
            ->where('checked_in', true)
            ->whereNotNull('checked_in_at')
            ->get();
        
        if ($checkins->isEmpty()) {
            return null;
        }
        
        $totalMinutes = $checkins->sum(function($checkin) use ($event) {
            return $event->start_date->diffInMinutes($checkin->checked_in_at, false);
        });
        
        $averageMinutes = round($totalMinutes / $checkins->count());
        
        if ($averageMinutes < 0) {
            return abs($averageMinutes) . ' minutes before event';
        } else {
            return $averageMinutes . ' minutes after event start';
        }
    }

    /**
     * Get scanner usage statistics for an event
     */
    private function getScannerUsageStats($event): array
    {
        $scanners = $event->scanners()->get();
        
        $stats = [];
        foreach ($scanners as $scanner) {
            // Count check-ins for this scanner directly from the event's eventGuests
            $checkinsCount = $event->eventGuests()
                ->where('scanned_by_scanner_id', $scanner->id)
                ->where('checked_in', true)
                ->count();
            
            $stats[] = [
                'scanner_name' => $scanner->name,
                'checkins_count' => $checkinsCount,
                'is_active' => $scanner->is_active ?? false,
            ];
        }
        
        return $stats;
    }
}
