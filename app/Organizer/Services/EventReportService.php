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
            'checkin_enabled' => $event->qr_checkin_enabled ?? false,
            'rsvp_enabled' => $event->rsvp_enabled ?? false,
            
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
        $user = Auth::user();
        $userTimezone = $user->timezone ?? 'UTC';
        
        $checkins = $event->eventGuests()
            ->where('checked_in', true)
            ->whereNotNull('checked_in_at')
            ->get();
        
        // Create time slots based on event start time
        $eventStartHour = $event->start_date ? $event->start_date->setTimezone($userTimezone)->hour : 9;
        $timeSlots = [];
        
        // Generate time slots from 2 hours before event start to 4 hours after
        for ($i = -2; $i <= 4; $i++) {
            $hour = $eventStartHour + $i;
            if ($hour < 0) $hour += 24;
            if ($hour >= 24) $hour -= 24;
            
            $timeLabel = $this->formatHourToTimeLabel($hour);
            $timeSlots[$timeLabel] = 0;
        }
        
        foreach ($checkins as $checkin) {
            $checkinTime = $checkin->checked_in_at->setTimezone($userTimezone);
            $hour = $checkinTime->hour;
            
            // Find the appropriate time slot
            foreach ($timeSlots as $label => $count) {
                $slotHour = $this->parseTimeLabelToHour($label);
                if ($hour == $slotHour) {
                    $timeSlots[$label]++;
                    break;
                }
            }
        }
        
        return [
            'time_labels' => array_keys($timeSlots),
            'time_data' => array_values($timeSlots),
        ];
    }
    
    /**
     * Format hour to time label (e.g., 9 -> "9 AM", 13 -> "1 PM")
     */
    private function formatHourToTimeLabel($hour): string
    {
        if ($hour == 0) return '12 AM';
        if ($hour < 12) return $hour . ' AM';
        if ($hour == 12) return '12 PM';
        return ($hour - 12) . ' PM';
    }
    
    /**
     * Parse time label to hour (e.g., "9 AM" -> 9, "1 PM" -> 13)
     */
    private function parseTimeLabelToHour($label): int
    {
        $parts = explode(' ', $label);
        $hour = (int) $parts[0];
        $period = $parts[1];
        
        if ($period == 'AM') {
            if ($hour == 12) return 0;
            return $hour;
        } else { // PM
            if ($hour == 12) return 12;
            return $hour + 12;
        }
    }

    /**
     * Get RSVP time distribution for an event
     */
    private function getRSVPTimeDistribution($event): array
    {
        $user = Auth::user();
        $userTimezone = $user->timezone ?? 'UTC';
        
        // Get unique guests who have RSVP'd (not total invitations)
        $rsvpGuests = $event->invitations()
            ->whereNotNull('rsvp_status')
            ->where('rsvp_status', '!=', 'none')
            ->whereNotNull('rsvp_at')
            ->distinct('guest_id')
            ->get(['guest_id', 'rsvp_at']);
        
        $timeCategories = [
            'Same Day' => 0, '1 Day' => 0, '2 Days' => 0, '3 Days' => 0,
            '1 Week' => 0, '2 Weeks' => 0, '1 Month+' => 0
        ];
        
        // Keep event start date in UTC for calculation
        $eventStartDate = $event->start_date ? $event->start_date->utc() : null;
        
        if (!$eventStartDate) {
            return [
                'time_labels' => array_keys($timeCategories),
                'time_data' => array_values($timeCategories),
            ];
        }
        
        foreach ($rsvpGuests as $rsvpGuest) {
            // Keep RSVP date in UTC for calculation
            $rsvpDate = $rsvpGuest->rsvp_at->utc();
            
            // Calculate the difference in days from event start date in UTC
            // This gives us the actual time difference regardless of timezone
            $daysDiff = $eventStartDate->diffInDays($rsvpDate, false);
            
            // Categorize based on how many days before the event they RSVP'd
            if ($daysDiff <= 0) {
                // RSVP was on or after event start date
                $timeCategories['Same Day']++;
            } else {
                // RSVP was before event start date
                if ($daysDiff == 1) $timeCategories['1 Day']++;
                elseif ($daysDiff == 2) $timeCategories['2 Days']++;
                elseif ($daysDiff == 3) $timeCategories['3 Days']++;
                elseif ($daysDiff <= 7) $timeCategories['1 Week']++;
                elseif ($daysDiff <= 14) $timeCategories['2 Weeks']++;
                else $timeCategories['1 Month+']++;
            }
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
        $user = Auth::user();
        $userTimezone = $user->timezone ?? 'UTC';
        
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
            
            // Format dates in user timezone
            $rsvpDate = null;
            if ($invitation && $invitation->rsvp_at) {
                $rsvpDate = $invitation->rsvp_at->setTimezone($userTimezone)->format('M j, Y g:i A');
            }
            
            $checkinTime = null;
            $checkinMethod = 'Not checked in';
            if ($eventGuest->checked_in && $eventGuest->checked_in_at) {
                $checkinTime = $eventGuest->checked_in_at->setTimezone($userTimezone)->format('M j, Y g:i A');
                $checkinMethod = $eventGuest->scanner_name ?? 'Manual';
            }
            
            $guests[] = [
                'name' => $eventGuest->guest ? $eventGuest->guest->name : 'N/A',
                'email' => $eventGuest->guest ? $eventGuest->guest->email : 'N/A',
                'rsvp_status' => $invitation ? $invitation->rsvp_status : null,
                'rsvp_date' => $rsvpDate,
                'checkin_time' => $checkinTime,
                'checkin_method' => $checkinMethod,
                'checked_in' => $eventGuest->checked_in,
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
        
        // Count invitations by channel
        $channelBreakdown = $invitations->groupBy('channel')->map->count()->toArray();
        $whatsappInvitations = $channelBreakdown['whatsapp'] ?? 0;
        $emailInvitations = $channelBreakdown['email'] ?? 0;
        
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
            'channel_breakdown' => $channelBreakdown,
            'whatsapp_invitations' => $whatsappInvitations,
            'email_invitations' => $emailInvitations,
            'rsvp_responses' => $uniqueGuestsWithRSVP,
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
        
        // Initialize engagement metrics
        $rsvpResponses = 0;
        $checkins = 0;
        $rsvpEngagementRate = 0;
        $checkinEngagementRate = 0;
        $overallEngagementRate = 0;
        
        // Calculate RSVP engagement only if RSVP is enabled
        if ($event->rsvp_enabled) {
            $rsvpResponses = $event->invitations()
                ->whereNotNull('rsvp_status')
                ->where('rsvp_status', '!=', 'none')
                ->distinct('guest_id')
                ->count('guest_id');
            
            $rsvpEngagementRate = $totalGuests > 0 ? round(($rsvpResponses / $totalGuests) * 100, 1) : 0;
        }
        
        // Calculate check-in engagement only if check-in is enabled
        if ($event->qr_checkin_enabled) {
            $checkins = $event->eventGuests()->where('checked_in', true)->count();
            $checkinEngagementRate = $totalGuests > 0 ? round(($checkins / $totalGuests) * 100, 1) : 0;
        }
        
        // Calculate RSVP Yes + Check-in engagement (only if both features are enabled)
        $rsvpYesAndCheckin = 0;
        $rsvpYesAndCheckinRate = 0;
        
        if ($event->rsvp_enabled && $event->qr_checkin_enabled) {
            // Get guests who RSVP'd "Yes"
            $rsvpYesGuestIds = $event->invitations()
                ->where('rsvp_status', 'yes')
                ->distinct('guest_id')
                ->pluck('guest_id')
                ->toArray();
            
            // Get guests who checked in
            $checkedInGuestIds = $event->eventGuests()
                ->where('checked_in', true)
                ->pluck('guest_id')
                ->toArray();
            
            // Find intersection: guests who both RSVP'd "Yes" AND checked in
            $rsvpYesAndCheckinIds = array_intersect($rsvpYesGuestIds, $checkedInGuestIds);
            $rsvpYesAndCheckin = count($rsvpYesAndCheckinIds);
            
            // Calculate rate based on total guests who RSVP'd "Yes"
            $rsvpYesAndCheckinRate = count($rsvpYesGuestIds) > 0 ? 
                round(($rsvpYesAndCheckin / count($rsvpYesGuestIds)) * 100, 1) : 0;
        }
        
        // Calculate overall engagement: unique guests who either RSVP'd OR checked in
        $engagedGuestIds = [];
        
        if ($event->rsvp_enabled) {
            $rsvpGuestIds = $event->invitations()
                ->whereNotNull('rsvp_status')
                ->where('rsvp_status', '!=', 'none')
                ->distinct('guest_id')
                ->pluck('guest_id')
                ->toArray();
            $engagedGuestIds = array_merge($engagedGuestIds, $rsvpGuestIds);
        }
        
        if ($event->qr_checkin_enabled) {
            $checkedInGuestIds = $event->eventGuests()
                ->where('checked_in', true)
                ->pluck('guest_id')
                ->toArray();
            $engagedGuestIds = array_merge($engagedGuestIds, $checkedInGuestIds);
        }
        
        $uniqueEngagedGuests = count(array_unique($engagedGuestIds));
        $overallEngagementRate = $totalGuests > 0 ? round(($uniqueEngagedGuests / $totalGuests) * 100, 1) : 0;
        
        return [
            'total_guests' => $totalGuests,
            'rsvp_engagement' => $rsvpResponses,
            'checkin_engagement' => $checkins,
            'overall_engagement_rate' => $overallEngagementRate,
            'rsvp_engagement_rate' => $rsvpEngagementRate,
            'checkin_engagement_rate' => $checkinEngagementRate,
            'rsvp_yes_and_checkin' => $rsvpYesAndCheckin,
            'rsvp_yes_and_checkin_rate' => $rsvpYesAndCheckinRate,
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
