<?php

namespace App\Organizer\Services;

use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use App\Shared\Models\GuestGroup;
use Illuminate\Support\Facades\Auth;

class OrganizerService
{
    /**
     * Get comprehensive account statistics across all events
     */
    public function getAccountStatistics(): array
    {
        $user = Auth::user();
        
        // Get all events for this user
        $events = $user->events()->with(['invitations', 'eventGuests'])->get();
        
        $stats = [
            'overview' => $this->getOverviewStats($user, $events),
            'event_performance' => $this->getEventPerformanceStats($events),
            'invitation_metrics' => $this->getInvitationMetrics($events),
            'checkin_analytics' => $this->getCheckinAnalytics($events),
            'guest_engagement' => $this->getGuestEngagementStats($events),
            'guest_list_health' => $this->getGuestListHealthStats($user),
            'completed_events' => $this->getCompletedEventsForReports($events),
            'recent_activity' => $this->getRecentActivity($user),
        ];
        
        return $stats;
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats($user, $events): array
    {
        $totalEvents = $events->count();
        $totalGuestLists = $user->guestLists()->count();
        $totalGuests = $user->guestLists()->withCount('guests')->get()->sum('guests_count');
        
        // Calculate total invitations sent
        $totalInvitationsSent = $events->sum(function($event) {
            return $event->invitations()->where('status', 'sent')->count();
        });
        
        // Calculate total check-ins across all events
        $totalCheckins = $events->sum(function($event) {
            return $event->eventGuests()->where('checked_in', true)->count();
        });
        
        // Calculate overall check-in rate
        $overallCheckinRate = $totalGuests > 0 ? round(($totalCheckins / $totalGuests) * 100, 1) : 0;
        
        return [
            'total_events' => $totalEvents,
            'total_guest_lists' => $totalGuestLists,
            'total_guests' => $totalGuests,
            'total_invitations_sent' => $totalInvitationsSent,
            'total_checkins' => $totalCheckins,
            'overall_checkin_rate' => $overallCheckinRate,
            'active_events' => $events->where('status', 'active')->count(),
            'completed_events' => $events->where('status', 'completed')->count(),
        ];
    }

    /**
     * Get event performance statistics
     */
    private function getEventPerformanceStats($events): array
    {
        $performanceData = [];
        
        foreach ($events as $event) {
            $totalGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count();
            $checkedInGuests = $event->eventGuests()->where('checked_in', true)->count();
            $checkinRate = $totalGuests > 0 ? round(($checkedInGuests / $totalGuests) * 100, 1) : 0;
            
            $performanceData[] = [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'start_date' => $event->start_date,
                'status' => $event->status,
                'total_guests' => $totalGuests,
                'checked_in_guests' => $checkedInGuests,
                'checkin_rate' => $checkinRate,
                'invitations_sent' => $event->invitations()->where('status', 'sent')->count(),
                'rsvp_responses' => $event->invitations()->whereNotNull('rsvp_status')->where('rsvp_status', '!=', 'none')->count(),
            ];
        }
        
        // Sort by start date (most recent first)
        usort($performanceData, function($a, $b) {
            return $b['start_date'] <=> $a['start_date'];
        });
        
        return $performanceData;
    }

    /**
     * Get invitation metrics
     */
    private function getInvitationMetrics($events): array
    {
        $totalInvitations = 0;
        $sentInvitations = 0;
        $failedInvitations = 0;
        $expiredInvitations = 0;
        $rsvpResponses = 0;
        $rsvpYes = 0;
        $rsvpNo = 0;
        $rsvpMaybe = 0;
        
        foreach ($events as $event) {
            $invitations = $event->invitations();
            
            $totalInvitations += $invitations->count();
            $sentInvitations += $invitations->where('status', 'sent')->count();
            $failedInvitations += $invitations->where('status', 'failed')->count();
            $expiredInvitations += $invitations->where('status', 'expired')->count();
            
            $rsvpResponses += $invitations->whereNotNull('rsvp_status')->where('rsvp_status', '!=', 'none')->count();
            $rsvpYes += $invitations->where('rsvp_status', 'yes')->count();
            $rsvpNo += $invitations->where('rsvp_status', 'no')->count();
            $rsvpMaybe += $invitations->where('rsvp_status', 'maybe')->count();
        }
        
        $deliveryRate = $totalInvitations > 0 ? round(($sentInvitations / $totalInvitations) * 100, 1) : 0;
        $responseRate = $sentInvitations > 0 ? round(($rsvpResponses / $sentInvitations) * 100, 1) : 0;
        
        return [
            'total_invitations' => $totalInvitations,
            'sent_invitations' => $sentInvitations,
            'failed_invitations' => $failedInvitations,
            'expired_invitations' => $expiredInvitations,
            'delivery_rate' => $deliveryRate,
            'rsvp_responses' => $rsvpResponses,
            'rsvp_yes' => $rsvpYes,
            'rsvp_no' => $rsvpNo,
            'rsvp_maybe' => $rsvpMaybe,
            'response_rate' => $responseRate,
        ];
    }

    /**
     * Get check-in analytics
     */
    private function getCheckinAnalytics($events): array
    {
        $totalGuests = 0;
        $totalCheckins = 0;
        $checkinsByEvent = [];
        $checkinTrends = [];
        
        foreach ($events as $event) {
            $eventGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE);
            $eventTotalGuests = $eventGuests->count();
            $eventCheckins = $eventGuests->where('checked_in', true)->count();
            
            $totalGuests += $eventTotalGuests;
            $totalCheckins += $eventCheckins;
            
            if ($eventTotalGuests > 0) {
                $checkinRate = round(($eventCheckins / $eventTotalGuests) * 100, 1);
                
                $checkinsByEvent[] = [
                    'event_name' => $event->name,
                    'total_guests' => $eventTotalGuests,
                    'checked_in' => $eventCheckins,
                    'checkin_rate' => $checkinRate,
                ];
                
                // Get recent check-ins for trends
                $recentCheckins = $event->eventGuests()
                    ->where('checked_in', true)
                    ->where('checked_in_at', '>=', now()->subDays(7))
                    ->count();
                
                $checkinTrends[] = [
                    'event_name' => $event->name,
                    'recent_checkins' => $recentCheckins,
                    'total_checkins' => $eventCheckins,
                ];
            }
        }
        
        $overallCheckinRate = $totalGuests > 0 ? round(($totalCheckins / $totalGuests) * 100, 1) : 0;
        
        return [
            'overall_checkin_rate' => $overallCheckinRate,
            'total_guests' => $totalGuests,
            'total_checkins' => $totalCheckins,
            'checkins_by_event' => $checkinsByEvent,
            'checkin_trends' => $checkinTrends,
        ];
    }

    /**
     * Get guest engagement statistics
     */
    private function getGuestEngagementStats($events): array
    {
        $totalGuests = 0;
        $engagedGuests = 0;
        $rsvpEngagement = 0;
        $checkinEngagement = 0;
        
        foreach ($events as $event) {
            $eventGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE);
            $eventTotalGuests = $eventGuests->count();
            $eventCheckins = $eventGuests->where('checked_in', true)->count();
            
            $totalGuests += $eventTotalGuests;
            $checkinEngagement += $eventCheckins;
            
            // Count RSVP responses
            $rsvpResponses = $event->invitations()
                ->whereNotNull('rsvp_status')
                ->where('rsvp_status', '!=', 'none')
                ->count();
            
            $rsvpEngagement += $rsvpResponses;
        }
        
        $engagementRate = $totalGuests > 0 ? round((($rsvpEngagement + $checkinEngagement) / ($totalGuests * 2)) * 100, 1) : 0;
        $rsvpRate = $totalGuests > 0 ? round(($rsvpEngagement / $totalGuests) * 100, 1) : 0;
        $checkinRate = $totalGuests > 0 ? round(($checkinEngagement / $totalGuests) * 100, 1) : 0;
        
        return [
            'total_guests' => $totalGuests,
            'engagement_rate' => $engagementRate,
            'rsvp_rate' => $rsvpRate,
            'checkin_rate' => $checkinRate,
            'rsvp_engagement' => $rsvpEngagement,
            'checkin_engagement' => $checkinEngagement,
        ];
    }

    /**
     * Get recent activity for the user
     */
    private function getRecentActivity($user): array
    {
        $recentEvents = $user->events()
            ->latest('start_date')
            ->take(5)
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'start_date' => $event->start_date,
                    'status' => $event->status,
                ];
            });
        
        $recentGuestLists = $user->guestLists()
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function($guestList) {
                return [
                    'id' => $guestList->id,
                    'name' => $guestList->name,
                    'created_at' => $guestList->created_at,
                ];
            });
        
        return [
            'recent_events' => $recentEvents,
            'recent_guest_lists' => $recentGuestLists,
        ];
    }

    /**
     * Get completed events for reports dropdown
     */
    private function getCompletedEventsForReports($events): array
    {
        $completedEvents = [];
        foreach ($events as $event) {
            // Check both status and dates to ensure we catch all completed events
            if ($event->status === 'completed' || $event->isCompleted()) {
                $completedEvents[] = [
                    'id' => $event->id,
                    'name' => $event->name,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'total_guests' => $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count(),
                    'checked_in_guests' => $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->where('checked_in', true)->count(),
                    'checkin_rate' => $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count() > 0 ? round(($event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->where('checked_in', true)->count() / $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count()) * 100, 1) : 0,
                    'invitations_sent' => $event->invitations()->where('status', 'sent')->count(),
                    'rsvp_responses' => $event->invitations()->whereNotNull('rsvp_status')->where('rsvp_status', '!=', 'none')->count(),
                    'rsvp_yes' => $event->invitations()->where('rsvp_status', 'yes')->count(),
                    'rsvp_no' => $event->invitations()->where('rsvp_status', 'no')->count(),
                    'rsvp_maybe' => $event->invitations()->where('rsvp_status', 'maybe')->count(),
                ];
            }
        }
        return $completedEvents;
    }

    /**
     * Get guest list health statistics
     */
    private function getGuestListHealthStats($user): array
    {
        $guestLists = $user->guestLists()->withCount('guests')->get();
        
        $excellent = 0;
        $good = 0;
        $needsAttention = 0;
        $critical = 0;
        $totalGuests = 0;
        
        foreach ($guestLists as $guestList) {
            $guestCount = $guestList->guests_count;
            $totalGuests += $guestCount;
            
            // Simple health scoring based on guest count
            if ($guestCount >= 50) {
                $excellent++;
            } elseif ($guestCount >= 20) {
                $good++;
            } elseif ($guestCount >= 5) {
                $needsAttention++;
            } else {
                $critical++;
            }
        }
        
        $totalLists = $guestLists->count();
        $averageScore = $totalLists > 0 ? round(($excellent * 100 + $good * 75 + $needsAttention * 50 + $critical * 25) / $totalLists) : 0;
        
        return [
            'excellent' => $excellent,
            'good' => $good,
            'needs_attention' => $needsAttention,
            'critical' => $critical,
            'total_guests' => $totalGuests,
            'average_score' => $averageScore,
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        try {
            $user = Auth::user();
            $events = $user->events()->get(); // Convert to collection
            $eventIds = $events->pluck('id')->toArray();
            
            // Get total guests across all guest lists
            $totalGuests = $user->guestLists()->withCount('guests')->get()->sum('guests_count');
            
            // Get total check-ins across all events
            $totalCheckins = \App\EventGuest::whereIn('event_id', $eventIds)
                ->where('status', \App\EventGuest::STATUS_ACTIVE)
                ->where('checked_in', true)
                ->count();
            
            // Calculate total invitations sent using collection methods
            $totalInvitationsSent = $events->sum(function($event) {
                return $event->invitations()->where('status', 'sent')->count();
            });
            
            // Get upcoming events (events that haven't started yet)
            $upcomingEvents = $events->filter(function($event) {
                return $event->start_date > now() && $event->status !== 'completed';
            })->take(5);
            
            // Get recent activity (recent events and guest lists)
            $recentActivity = collect();
            
            // Add recent events
            $recentEvents = $events->sortByDesc('created_at')->take(3);
            foreach ($recentEvents as $event) {
                $recentActivity->push([
                    'type' => 'event',
                    'id' => $event->id,
                    'name' => $event->name,
                    'created_at' => $event->created_at,
                    'status' => $event->status,
                ]);
            }
            
            // Add recent guest lists
            $recentGuestLists = $user->guestLists()->latest('created_at')->take(3)->get();
            foreach ($recentGuestLists as $guestList) {
                $recentActivity->push([
                    'type' => 'guest_list',
                    'id' => $guestList->id,
                    'name' => $guestList->name,
                    'created_at' => $guestList->created_at,
                ]);
            }
            
            // Sort recent activity by creation date
            $recentActivity = $recentActivity->sortByDesc('created_at')->take(5);
            
            // Get recent guest lists for the dedicated section
            $recentGuestLists = $user->guestLists()->latest('created_at')->take(5)->get();
            
            return [
                'total_events' => $events->count(),
                'total_guest_lists' => $user->guestLists()->count(),
                'total_guests' => $totalGuests,
                'total_invitations_sent' => $totalInvitationsSent,
                'total_checkins' => $totalCheckins,
                'overall_checkin_rate' => $totalGuests > 0 ? round(($totalCheckins / $totalGuests) * 100, 1) : 0,
                'active_events' => $events->where('status', 'active')->count(),
                'completed_events' => $events->where('status', 'completed')->count(),
                'upcoming_events' => $upcomingEvents,
                'recent_activity' => $recentActivity,
                'recent_guest_lists' => $recentGuestLists,
            ];
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            // Return default values on error
            return [
                'total_events' => 0,
                'total_guest_lists' => 0,
                'total_guests' => 0,
                'total_invitations_sent' => 0,
                'total_checkins' => 0,
                'overall_checkin_rate' => 0,
                'active_events' => 0,
                'completed_events' => 0,
                'upcoming_events' => collect(),
                'recent_activity' => collect(),
                'recent_guest_lists' => collect(),
            ];
        }
    }

    /**
     * Get my guest lists
     */
    public function getMyGuestLists()
    {
        $user = Auth::user();
        $guestLists = $user->guestLists()->withCount('guests')->latest()->paginate(10);
        
        return $guestLists;
    }

    /**
     * Get my guest lists with filters
     */
    public function getMyGuestListsWithFilters($search = '', $health = '', $guestCount = '', $sortBy = 'created_at_desc', $page = 1)
    {
        $user = Auth::user();
        $query = $user->guestLists()->withCount('guests');

        // Search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Health filter
        if (!empty($health)) {
            $query->whereJsonContains('health->status', $health);
        }

        // Guest count filter
        if (!empty($guestCount)) {
            switch ($guestCount) {
                case 'empty':
                    $query->whereDoesntHave('guests');
                    break;
                case 'small':
                    $query->whereRaw('(SELECT COUNT(*) FROM guests WHERE guests.guest_list_id = guest_lists.id) > 0')
                          ->whereRaw('(SELECT COUNT(*) FROM guests WHERE guests.guest_list_id = guest_lists.id) <= 10');
                    break;
                case 'medium':
                    $query->whereRaw('(SELECT COUNT(*) FROM guests WHERE guests.guest_list_id = guest_lists.id) > 10')
                          ->whereRaw('(SELECT COUNT(*) FROM guests WHERE guests.guest_list_id = guest_lists.id) <= 50');
                    break;
                case 'large':
                    $query->whereRaw('(SELECT COUNT(*) FROM guests WHERE guests.guest_list_id = guest_lists.id) > 50');
                    break;
            }
        }

        // Sort by
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'guests_asc':
                $query->orderBy('guests_count', 'asc');
                break;
            case 'guests_desc':
                $query->orderBy('guests_count', 'desc');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_at_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(10, ['*'], 'page', $page);
    }

    /**
     * Get guest list display data
     */
    public function getGuestListDisplayData(GuestList $guestList): array
    {
        $guests = $this->getGuestListGuests($guestList);
        $stats = $this->getGuestListStats($guestList);
        $allGuests = $guestList->guests()->with('group')->orderBy('name')->get();
        $guestGroups = $guestList->guestGroups()->orderBy('name')->get();
        
        $lastFiveGuests = $guestList->guests()->latest('id')->take(5)->get()->map(function($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'group_id' => $guest->group_id,
                'group_name' => $guest->guestGroup ? $guest->guestGroup->name : '',
                'language' => $guest->language ?? '',
            ];
        })->toArray();

        return compact('guestList', 'guests', 'stats', 'lastFiveGuests', 'allGuests', 'guestGroups');
    }

    /**
     * Get guest list guests
     */
    public function getGuestListGuests(GuestList $guestList): array
    {
        $guests = $guestList->guests()->with('group')->orderBy('name')->get();
        
        return $guests->map(function($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'group_id' => $guest->group_id,
                'group_name' => $guest->guestGroup ? $guest->guestGroup->name : '',
                'language' => $guest->language ?? '',
                'created_at' => $guest->created_at,
            ];
        })->toArray();
    }

    /**
     * Get guest list statistics
     */
    public function getGuestListStats(GuestList $guestList): array
    {
        $totalGuests = $guestList->guests()->count();
        $guestsWithEmail = $guestList->guests()->whereNotNull('email')->count();
        $guestsWithPhone = $guestList->guests()->whereNotNull('phone')->count();
        $guestsWithGroup = $guestList->guests()->whereNotNull('group_id')->count();
        $guestsWithLanguage = $guestList->guests()->whereNotNull('language')->count();
        
        return [
            'total_guests' => $totalGuests,
            'guests_with_email' => $guestsWithEmail,
            'guests_with_phone' => $guestsWithPhone,
            'guests_with_group' => $guestsWithGroup,
            'guests_with_language' => $guestsWithLanguage,
            'email_percentage' => $totalGuests > 0 ? round(($guestsWithEmail / $totalGuests) * 100, 1) : 0,
            'phone_percentage' => $totalGuests > 0 ? round(($guestsWithPhone / $totalGuests) * 100, 1) : 0,
            'group_percentage' => $totalGuests > 0 ? round(($guestsWithGroup / $totalGuests) * 100, 1) : 0,
            'language_percentage' => $totalGuests > 0 ? round(($guestsWithLanguage / $totalGuests) * 100, 1) : 0,
        ];
    }

    /**
     * Update guest list settings
     */
    public function updateGuestListSettings(GuestList $guestList, array $data): array
    {
        $fields = [
            'email' => $data['enable_email'] ?? false,
            'phone' => $data['enable_phone'] ?? false,
            'group' => $data['enable_group'] ?? false,
            'language' => $data['enable_language'] ?? false,
        ];
        
        $settings = $guestList->settings ?? $guestList->getDefaultSettings();
        $settings['fields'] = array_merge($settings['fields'] ?? [], $fields);
        $settings['default_country_code'] = $data['default_country_code'] ?? $settings['default_country_code'] ?? '+1';
        $settings['default_language'] = $data['default_language'] ?? $settings['default_language'] ?? 'en';
        
        $guestList->update(['settings' => $settings]);
        
        return [
            'success' => true,
            'name' => $guestList->name,
            'description' => $guestList->description,
            'settings' => $guestList->settings,
        ];
    }

    /**
     * Get guest groups for a guest list
     */
    public function getGuestGroups(GuestList $guestList)
    {
        return $guestList->guestGroups()->get(['id', 'name', 'description']);
    }

    /**
     * Add a new guest group
     */
    public function addGuestGroup(GuestList $guestList, array $data): GuestGroup
    {
        return $guestList->guestGroups()->create($data);
    }

    /**
     * Update a guest group
     */
    public function updateGuestGroup(GuestGroup $group, array $data): void
    {
        $group->update($data);
    }

    /**
     * Delete a guest group
     */
    public function deleteGuestGroup(GuestGroup $group): void
    {
        // Delete all guests in this group
        $group->guests()->delete();
        
        // Delete the group
        $group->delete();
    }

    /**
     * Bulk change group for selected guests
     */
    public function bulkChangeGuestGroup(GuestList $guestList, array $data): array
    {
        $updated = 0;
        foreach ($data['guest_ids'] as $guestId) {
            $guest = Guest::where('id', $guestId)->where('guest_list_id', $guestList->id)->first();
            if ($guest) {
                $guest->group_id = $data['group_id'];
                $guest->save();
                $updated++;
            }
        }

        return [
            'success' => true,
            'message' => "Changed group for {$updated} guest(s)."
        ];
    }

    /**
     * Bulk delete guests
     */
    public function bulkDeleteGuests(GuestList $guestList, array $guestIds): array
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($guestIds as $guestId) {
            $guest = Guest::find($guestId);
            if ($guest && $guest->guest_list_id == $guestList->id) {
                try {
                    $this->deleteGuest($guest);
                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                }
            } else {
                $failCount++;
            }
        }

        $message = "Successfully deleted {$successCount} guest(s).";
        if ($failCount > 0) {
            $message .= " Failed to delete {$failCount} guest(s).";
        }

        return [
            'success' => $failCount === 0,
            'message' => $message,
            'deleted_count' => $successCount
        ];
    }

    /**
     * Delete a guest
     */
    private function deleteGuest(Guest $guest): void
    {
        $guest->delete();
    }
}
