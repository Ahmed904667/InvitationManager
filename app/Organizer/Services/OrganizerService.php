<?php

namespace App\Organizer\Services;

use App\Shared\Models\Guest;
use App\Shared\Models\GuestList;
use App\Shared\Models\GuestGroup;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use PragmaRX\Countries\Package\Countries;

class OrganizerService
{
    /**
     * Get comprehensive account statistics across all events
     */
    public function getAccountStatistics(): array
    {
        $user = Auth::user();
        
        // Get all events for this user with necessary relationships
        $events = $user->events()
            ->with(['invitations', 'eventGuests', 'guestLists'])
            ->get();
        
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
        
        // Validate data consistency
        $stats = $this->validateDataConsistency($stats);
        
        return $stats;
    }

    /**
     * Validate data consistency across all statistics
     */
    private function validateDataConsistency(array $stats): array
    {
        // Ensure all numeric values are properly formatted
        $stats['overview'] = $this->sanitizeNumericValues($stats['overview']);
        $stats['invitation_metrics'] = $this->sanitizeNumericValues($stats['invitation_metrics']);
        $stats['checkin_analytics'] = $this->sanitizeNumericValues($stats['checkin_analytics']);
        $stats['guest_engagement'] = $this->sanitizeNumericValues($stats['guest_engagement']);
        
        // Validate that percentages don't exceed 100%
        if (isset($stats['overview']['overall_checkin_rate']) && $stats['overview']['overall_checkin_rate'] > 100) {
            $stats['overview']['overall_checkin_rate'] = 100;
        }
        
        if (isset($stats['invitation_metrics']['delivery_rate']) && $stats['invitation_metrics']['delivery_rate'] > 100) {
            $stats['invitation_metrics']['delivery_rate'] = 100;
        }
        
        if (isset($stats['invitation_metrics']['response_rate']) && $stats['invitation_metrics']['response_rate'] > 100) {
            $stats['invitation_metrics']['response_rate'] = 100;
        }
        
        return $stats;
    }

    /**
     * Sanitize numeric values to ensure they're valid numbers
     */
    private function sanitizeNumericValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $data[$key] = (float) $value;
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeNumericValues($value);
            }
        }
        
        return $data;
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats($user, $events): array
    {
        $totalEvents = $events->count();
        $totalGuestLists = $user->guestLists()->count();
        
        // Calculate total guests across all guest lists (not events)
        $totalGuests = $user->guestLists()->withCount('guests')->get()->sum('guests_count');
        
        // Calculate total invitations sent
        $totalInvitationsSent = $events->sum(function($event) {
            return $event->invitations()->where('status', 'sent')->count();
        });
        
        // Calculate total check-ins across all events (only for events with QR check-in enabled)
        $totalCheckins = $events->where('qr_checkin_enabled', true)->sum(function($event) {
            return $event->eventGuests()->where('checked_in', true)->count();
        });
        
        // Calculate overall check-in rate (only for events with QR check-in enabled)
        $checkinEnabledGuests = $events->where('qr_checkin_enabled', true)->sum(function($event) {
            return $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count();
        });
        $overallCheckinRate = $checkinEnabledGuests > 0 ? round(($totalCheckins / $checkinEnabledGuests) * 100, 1) : 0;
        
        // Count active events (running, scheduled, but not completed, draft, or cancelled)
        $activeEvents = $events->whereNotIn('status', ['completed', 'draft', 'cancelled'])->count();
        
        return [
            'total_events' => $totalEvents,
            'total_guest_lists' => $totalGuestLists,
            'total_guests' => $totalGuests,
            'total_invitations_sent' => $totalInvitationsSent,
            'total_checkins' => $totalCheckins,
            'overall_checkin_rate' => $overallCheckinRate,
            'active_events' => $activeEvents,
            'completed_events' => $events->where('status', 'completed')->count(),
        ];
    }

    /**
     * Get event performance statistics
     */
    private function getEventPerformanceStats($events): array
    {
        $performanceData = [];
        $user = Auth::user();
        $userTimezone = $user->timezone ?? 'UTC';
        
        foreach ($events as $event) {
            $totalGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count();
            
            // Only calculate check-in data if QR check-in is enabled
            $checkedInGuests = 0;
            $checkinRate = 0;
            if ($event->qr_checkin_enabled) {
                $checkedInGuests = $event->eventGuests()->where('checked_in', true)->count();
                $checkinRate = $totalGuests > 0 ? round(($checkedInGuests / $totalGuests) * 100, 1) : 0;
            }
            
            // Only calculate RSVP data if RSVP is enabled
            $rsvpResponses = 0;
            if ($event->rsvp_enabled) {
                // Count unique guests who have responded (not total invitations)
                $rsvpResponses = $event->invitations()
                    ->whereNotNull('rsvp_status')
                    ->where('rsvp_status', '!=', \App\Shared\Models\Invitation::RSVP_NONE)
                    ->distinct('guest_id')
                    ->count('guest_id');
            }
            
            // Convert start_date to user's timezone
            $startDate = $event->start_date ? $event->start_date->setTimezone($userTimezone) : null;
            
            $performanceData[] = [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'start_date' => $startDate,
                'status' => $event->status,
                'total_guests' => $totalGuests,
                'checked_in_guests' => $checkedInGuests,
                'checkin_rate' => $checkinRate,
                'qr_checkin_enabled' => $event->qr_checkin_enabled,
                'rsvp_enabled' => $event->rsvp_enabled,
                'invitations_sent' => $event->invitations()->where('status', 'sent')->count(),
                'rsvp_responses' => $rsvpResponses,
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
        $whatsappInvitations = 0;
        $emailInvitations = 0;
        $rsvpResponses = 0;
        $rsvpYes = 0;
        $rsvpNo = 0;
        $rsvpMaybe = 0;
        
        foreach ($events as $event) {
            // Use fresh relationship queries for each count
            $totalInvitations += $event->invitations()->count();
            $sentInvitations += $event->invitations()->where('status', \App\Shared\Models\Invitation::STATUS_SENT)->count();
            $failedInvitations += $event->invitations()->where('status', \App\Shared\Models\Invitation::STATUS_FAILED)->count();
            $expiredInvitations += $event->invitations()->where('status', \App\Shared\Models\Invitation::STATUS_EXPIRED)->count();
            
            // Count invitations by channel (with flexible matching)
            $whatsappCount = $event->invitations()->where('status', \App\Shared\Models\Invitation::STATUS_SENT)->where(function($query) {
                $query->where('channel', 'whatsapp')
                      ->orWhere('channel', 'WhatsApp')
                      ->orWhere('channel', 'whatsapp_business')
                      ->orWhere('channel', 'WHATSAPP');
            })->count();
            
            $emailCount = $event->invitations()->where('status', \App\Shared\Models\Invitation::STATUS_SENT)->where(function($query) {
                $query->where('channel', 'email')
                      ->orWhere('channel', 'Email')
                      ->orWhere('channel', 'EMAIL')
                      ->orWhere('channel', 'email_smtp');
            })->count();
            
            
            $whatsappInvitations += $whatsappCount;
            $emailInvitations += $emailCount;
            
            // Only count RSVP responses if RSVP is enabled for this event
            if ($event->rsvp_enabled) {
                // Count unique guests who have responded (not total invitations)
                $rsvpResponses += $event->invitations()->whereNotNull('rsvp_status')->where('rsvp_status', '!=', \App\Shared\Models\Invitation::RSVP_NONE)->distinct('guest_id')->count('guest_id');
                $rsvpYes += $event->invitations()->where('rsvp_status', \App\Shared\Models\Invitation::RSVP_YES)->distinct('guest_id')->count('guest_id');
                $rsvpNo += $event->invitations()->where('rsvp_status', \App\Shared\Models\Invitation::RSVP_NO)->distinct('guest_id')->count('guest_id');
                $rsvpMaybe += $event->invitations()->where('rsvp_status', \App\Shared\Models\Invitation::RSVP_MAYBE)->distinct('guest_id')->count('guest_id');
            }
        }
        
        $deliveryRate = $totalInvitations > 0 ? round(($sentInvitations / $totalInvitations) * 100, 1) : 0;
        
        return [
            'total_invitations' => $totalInvitations,
            'sent_invitations' => $sentInvitations,
            'failed_invitations' => $failedInvitations,
            'expired_invitations' => $expiredInvitations,
            'delivery_rate' => $deliveryRate,
            'whatsapp_invitations' => $whatsappInvitations,
            'email_invitations' => $emailInvitations,
            'rsvp_responses' => $rsvpResponses,
            'rsvp_yes' => $rsvpYes,
            'rsvp_no' => $rsvpNo,
            'rsvp_maybe' => $rsvpMaybe,
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
            // For summary: include guests from events with either QR check-in OR RSVP enabled
            // For individual event data: only include events with QR check-in enabled
            $eventGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE);
            $eventTotalGuests = $eventGuests->count();
            
            // Always add to total guests if event has RSVP or QR check-in enabled
            if ($event->rsvp_enabled || $event->qr_checkin_enabled) {
                $totalGuests += $eventTotalGuests;
            }
            
            // Only process check-in data for events with QR check-in enabled
            if (!$event->qr_checkin_enabled) {
                continue;
            }
            
            $eventCheckins = $eventGuests->where('checked_in', true)->count();
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
        $rsvpEngagement = 0;
        $checkinEngagement = 0;
        $rsvpYesAndCheckin = 0;
        $rsvpEnabledEvents = 0;
        $checkinEnabledEvents = 0;
        $bothEnabledEvents = 0;
        $rsvpYesCount = 0;
        
        foreach ($events as $event) {
            $eventGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE);
            $eventTotalGuests = $eventGuests->count();
            
            $totalGuests += $eventTotalGuests;
            
            // Count RSVP responses only if RSVP is enabled for this event
            if ($event->rsvp_enabled) {
                $rsvpEnabledEvents++;
                // Count unique guests who have responded (not total invitations)
                $rsvpResponses = $event->invitations()
                    ->whereNotNull('rsvp_status')
                    ->where('rsvp_status', '!=', \App\Shared\Models\Invitation::RSVP_NONE)
                    ->distinct('guest_id')
                    ->count('guest_id');
                
                $rsvpEngagement += $rsvpResponses;
            }
            
            // Count check-ins only if QR check-in is enabled for this event
            if ($event->qr_checkin_enabled) {
                $checkinEnabledEvents++;
                $eventCheckins = $eventGuests->where('checked_in', true)->count();
                $checkinEngagement += $eventCheckins;
            }
            
            // Calculate RSVP Yes + Check-in for events with both features enabled
            if ($event->rsvp_enabled && $event->qr_checkin_enabled) {
                $bothEnabledEvents++;
                
                // Get guests who RSVP'd "Yes"
                $rsvpYesGuestIds = $event->invitations()
                    ->where('rsvp_status', \App\Shared\Models\Invitation::RSVP_YES)
                    ->distinct('guest_id')
                    ->pluck('guest_id')
                    ->toArray();
                
                $rsvpYesCount += count($rsvpYesGuestIds);
                
                // Get guests who checked in
                $checkedInGuestIds = $eventGuests->where('checked_in', true)->pluck('guest_id')->toArray();
                
                // Find intersection: guests who both RSVP'd "Yes" AND checked in
                $rsvpYesAndCheckinIds = array_intersect($rsvpYesGuestIds, $checkedInGuestIds);
                $rsvpYesAndCheckin += count($rsvpYesAndCheckinIds);
            }
        }
        
        // Calculate RSVP Yes + Check-in rate (for events with both features enabled)
        $rsvpYesAndCheckinRate = 0;
        if ($bothEnabledEvents > 0 && $rsvpYesCount > 0) {
            $rsvpYesAndCheckinRate = round(($rsvpYesAndCheckin / $rsvpYesCount) * 100, 1);
        }
        
        // Calculate RSVP engagement rate only for events with RSVP enabled
        $rsvpEngagementRate = 0;
        if ($rsvpEnabledEvents > 0) {
            $rsvpEnabledGuests = $events->where('rsvp_enabled', true)->sum(function($event) {
                return $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count();
            });
            $rsvpEngagementRate = $rsvpEnabledGuests > 0 ? round(($rsvpEngagement / $rsvpEnabledGuests) * 100, 1) : 0;
        }
        
        // Calculate check-in engagement rate only for events with QR check-in enabled
        $checkinEngagementRate = 0;
        if ($checkinEnabledEvents > 0) {
            $checkinEnabledGuests = $events->where('qr_checkin_enabled', true)->sum(function($event) {
                return $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count();
            });
            $checkinEngagementRate = $checkinEnabledGuests > 0 ? round(($checkinEngagement / $checkinEnabledGuests) * 100, 1) : 0;
        }
        
        return [
            'total_guests' => $totalGuests,
            'overall_engagement_rate' => $rsvpYesAndCheckinRate, // Changed to RSVP Yes + Check-in rate
            'rsvp_engagement_rate' => $rsvpEngagementRate,
            'checkin_engagement_rate' => $checkinEngagementRate,
            'rsvp_engagement' => $rsvpEngagement,
            'checkin_engagement' => $checkinEngagement,
            'rsvp_yes_and_checkin' => $rsvpYesAndCheckin,
            'rsvp_yes_count' => $rsvpYesCount,
        ];
    }

    /**
     * Get recent activity for the user
     */
    private function getRecentActivity($user): array
    {
        $userTimezone = $user->timezone ?? 'UTC';
        
        $recentEvents = $user->events()
            ->latest('start_date')
            ->take(5)
            ->get()
            ->map(function($event) use ($userTimezone) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'start_date' => $event->start_date ? $event->start_date->setTimezone($userTimezone) : null,
                    'status' => $event->status,
                ];
            });
        
        $recentGuestLists = $user->guestLists()
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function($guestList) use ($userTimezone) {
                return [
                    'id' => $guestList->id,
                    'name' => $guestList->name,
                    'created_at' => $guestList->created_at ? $guestList->created_at->setTimezone($userTimezone) : null,
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
        $user = Auth::user();
        $userTimezone = $user->timezone ?? 'UTC';
        
        foreach ($events as $event) {
            // Check both status and dates to ensure we catch all completed events
            if ($event->status === 'completed' || $event->isCompleted()) {
                // Convert dates to user's timezone
                $startDate = $event->start_date ? $event->start_date->setTimezone($userTimezone) : null;
                $endDate = $event->end_date ? $event->end_date->setTimezone($userTimezone) : null;
                
                $totalGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count();
                
                // Only calculate check-in data if QR check-in is enabled
                $checkedInGuests = 0;
                $checkinRate = 0;
                if ($event->qr_checkin_enabled) {
                    $checkedInGuests = $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->where('checked_in', true)->count();
                    $checkinRate = $totalGuests > 0 ? round(($checkedInGuests / $totalGuests) * 100, 1) : 0;
                }
                
                // Only calculate RSVP data if RSVP is enabled
                $rsvpResponses = 0;
                $rsvpYes = 0;
                $rsvpNo = 0;
                $rsvpMaybe = 0;
                if ($event->rsvp_enabled) {
                    // Count unique guests who have responded (not total invitations)
                    $rsvpResponses = $event->invitations()->whereNotNull('rsvp_status')->where('rsvp_status', '!=', \App\Shared\Models\Invitation::RSVP_NONE)->distinct('guest_id')->count('guest_id');
                    $rsvpYes = $event->invitations()->where('rsvp_status', \App\Shared\Models\Invitation::RSVP_YES)->distinct('guest_id')->count('guest_id');
                    $rsvpNo = $event->invitations()->where('rsvp_status', \App\Shared\Models\Invitation::RSVP_NO)->distinct('guest_id')->count('guest_id');
                    $rsvpMaybe = $event->invitations()->where('rsvp_status', \App\Shared\Models\Invitation::RSVP_MAYBE)->distinct('guest_id')->count('guest_id');
                }
                
                $completedEvents[] = [
                    'id' => $event->id,
                    'name' => $event->name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_guests' => $totalGuests,
                    'checked_in_guests' => $checkedInGuests,
                    'checkin_rate' => $checkinRate,
                    'invitations_sent' => $event->invitations()->where('status', 'sent')->count(),
                    'rsvp_responses' => $rsvpResponses,
                    'rsvp_yes' => $rsvpYes,
                    'rsvp_no' => $rsvpNo,
                    'rsvp_maybe' => $rsvpMaybe,
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
            
            // Get upcoming events (events with status sent or scheduled that haven't started yet)
            $upcomingEvents = $events->filter(function($event) {
                return in_array($event->status, ['sent', 'scheduled']) && $event->start_date > now();
            })->take(3);
            
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
            $recentGuestLists = $user->guestLists()->withCount('guests')->latest('created_at')->take(5)->get();
            
            // Get completed events count (all user guests with completed status)
            $completedEventsCount = $events->where('status', 'completed')->count();
            
            return [
                'total_events' => $events->count(),
                'total_guest_lists' => $user->guestLists()->count(),
                'total_guests' => $totalGuests,
                'total_invitations_sent' => $totalInvitationsSent,
                'total_checkins' => $totalCheckins,
                'overall_checkin_rate' => $totalGuests > 0 ? round(($totalCheckins / $totalGuests) * 100, 1) : 0,
                'active_events' => $events->where('status', 'active')->count(),
                'completed_events' => $completedEventsCount,
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

    public function updateGuestList(GuestList $guestList, array $data): void
    {
        $guestList->update($data);
    }

    public function deleteGuestList(GuestList $guestList): void
    {
        // Convert all guests to standalone before deleting the list
        // This preserves all event data (EventGuest records, check-ins, RSVPs, etc.)
        $guestList->guests()->update(['guest_list_id' => null]);
        
        // Now safely delete the guest list
        $guestList->delete();
    }

    public function updateGuest(Guest $guest, array $data): void
    {
        $guest->update($data);
        
        // Recalculate health after updating guest
        $guest->guestList->calculateAndStoreHealth();
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
        $countryCodes = $this->getCountryCodes();
        
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

        return compact('guestList', 'guests', 'stats', 'lastFiveGuests', 'allGuests', 'guestGroups', 'countryCodes');
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
        $settings['default_country_code'] = $data['default_country_code'] ?? $settings['default_country_code'] ?? '+60';
        $settings['default_language'] = $data['default_language'] ?? $settings['default_language'] ?? 'English';
        
        // Also persist name/description if provided via settings form
        $updatePayload = ['settings' => $settings];
        if (array_key_exists('name', $data)) {
            $updatePayload['name'] = $data['name'] ?: $guestList->name;
        }
        if (array_key_exists('description', $data)) {
            $updatePayload['description'] = $data['description'] ?? null;
        }

        $guestList->update($updatePayload);
        
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
        // Soft delete all guests in this group
        $group->guests()->each(function($guest) {
            $guest->softDelete();
        });
        
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
     * Create a new guest list
     */
    public function createGuestList(array $data): GuestList
    {
        $user = Auth::user();
        
        $guestList = new GuestList();
        $guestList->user_id = $user->id;
        $guestList->name = $data['name'];
        $guestList->description = $data['description'] ?? null;
        $guestList->max_guests = $data['max_guests'] ?? 1000; // Default max guests
        
        // Use provided settings or fallback to defaults
        $guestList->settings = $data['settings'] ?? $this->getDefaultGuestListSettings();
        $guestList->save();

        return $guestList;
    }

    /**
     * Get default settings for new guest lists
     */
    private function getDefaultGuestListSettings(): array
    {
        return [
            'fields' => [
                'email' => true,
                'phone' => true,
                'group' => true,
                'language' => true,
                'notes' => true,
            ],
            'notifications' => [
                'email_reminders' => false,
                'sms_reminders' => false,
            ],
            'defaults' => [
                'country_code' => '+60',
                'language' => 'English',
            ],
            'auto_archive_events' => false,
            'auto_archive_days' => 30,
        ];
    }

    public function addGuest(GuestList $guestList, array $data): Guest
    {
        $guest = new Guest();
        $guest->guest_list_id = $guestList->id;
        $guest->name = $data['name'];
        $guest->email = $data['email'] ?? null;
        $guest->phone = $data['phone'] ?? null;
        $guest->group_id = $data['group_id'] ?? null;
        $guest->language = $data['language'] ?? null;
        $guest->notes = $data['notes'] ?? null;
        $guest->save();

        // Recalculate health after adding guest
        $guestList->calculateAndStoreHealth();

        return $guest;
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
                    // Don't use $this->deleteGuest() here to avoid multiple health calculations
                    $guest->softDelete();
                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                }
            } else {
                $failCount++;
            }
        }

        // Recalculate health once after all deletions
        $guestList->calculateAndStoreHealth();

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
     * Delete a guest (soft delete)
     */
    public function deleteGuest(Guest $guest): void
    {
        $guestList = $guest->guestList;
        $guest->softDelete();
        
        // Recalculate health after deleting guest
        if ($guestList) {
            $guestList->calculateAndStoreHealth();
        }
    }

    /**
     * Get all country codes with country names
     */
    public function getCountryCodes(): array
    {
        $countries = new Countries();
        
        return $countries->all()
            ->map(function ($country) {
                $countryData = $country->toArray();
                $callingCodes = $countryData['calling_codes'] ?? [];
                $name = $countryData['name']['common'] ?? $countryData['name_en'] ?? 'Unknown';
                
                // Skip countries without calling codes
                if (empty($callingCodes)) {
                    return null;
                }
                
                // Use the first calling code if multiple exist
                $callingCode = $callingCodes[0];
                
                return [
                    'code' => $callingCode,
                    'name' => $name,
                    'display' => $callingCode . ' (' . $name . ')'
                ];
            })
            ->filter() // Remove null entries
            ->sortBy('name') // Sort by country name
            ->values()
            ->toArray();
    }
}
