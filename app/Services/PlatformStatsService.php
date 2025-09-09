<?php

namespace App\Services;

use App\Shared\Models\Event;
use App\Shared\Models\Invitation;
use App\Shared\Models\Guest;
use App\Shared\Models\User;
use App\Trial;

class PlatformStatsService
{
    /**
     * Get comprehensive platform statistics
     */
    public function getPlatformStats(): array
    {
        return [
            'total_events' => $this->getTotalEvents(),
            'total_invitations_sent' => $this->getTotalInvitationsSent(),
            'total_guests' => $this->getTotalGuests(),
            'total_users' => $this->getTotalUsers(),
            'events_this_month' => $this->getEventsThisMonth(),
            'invitations_this_month' => $this->getInvitationsThisMonth(),
            'active_events' => $this->getActiveEvents(),
            'completed_events' => $this->getCompletedEvents(),
            'total_trials' => $this->getTotalTrials(),
        ];
    }

    /**
     * Get total number of events created
     */
    public function getTotalEvents(): int
    {
        return Event::count();
    }

    /**
     * Get total number of invitations sent
     */
    public function getTotalInvitationsSent(): int
    {
        return Invitation::where('status', Invitation::STATUS_SENT)->count();
    }

    /**
     * Get total number of guests in the system
     */
    public function getTotalGuests(): int
    {
        return Guest::count();
    }

    /**
     * Get total number of users
     */
    public function getTotalUsers(): int
    {
        return User::count();
    }

    /**
     * Get events created this month
     */
    public function getEventsThisMonth(): int
    {
        return Event::whereMonth('created_at', now()->month)
                   ->whereYear('created_at', now()->year)
                   ->count();
    }

    /**
     * Get invitations sent this month
     */
    public function getInvitationsThisMonth(): int
    {
        return Invitation::where('status', Invitation::STATUS_SENT)
                        ->whereMonth('sent_at', now()->month)
                        ->whereYear('sent_at', now()->year)
                        ->count();
    }

    /**
     * Get number of active events (not completed)
     */
    public function getActiveEvents(): int
    {
        return Event::where('status', '!=', 'completed')->count();
    }

    /**
     * Get number of completed events
     */
    public function getCompletedEvents(): int
    {
        return Event::where('status', 'completed')->count();
    }

    /**
     * Get total number of trial requests
     */
    public function getTotalTrials(): int
    {
        return Trial::count();
    }

    /**
     * Get formatted statistics for display
     */
    public function getFormattedStats(): array
    {
        $stats = $this->getPlatformStats();
        
        return [
            'total_events' => $this->formatNumber($stats['total_events']),
            'total_invitations_sent' => $this->formatNumber($stats['total_invitations_sent']),
            'total_guests' => $this->formatNumber($stats['total_guests']),
            'total_users' => $this->formatNumber($stats['total_users']),
            'events_this_month' => $this->formatNumber($stats['events_this_month']),
            'invitations_this_month' => $this->formatNumber($stats['invitations_this_month']),
            'active_events' => $this->formatNumber($stats['active_events']),
            'completed_events' => $this->formatNumber($stats['completed_events']),
            'total_trials' => $this->formatNumber($stats['total_trials']),
        ];
    }

    /**
     * Format numbers for display (e.g., 1.2K, 1.5M)
     */
    private function formatNumber(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        
        return (string) $number;
    }

    /**
     * Get statistics for a specific time period
     */
    public function getStatsForPeriod(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        return [
            'events' => Event::where('created_at', '>=', $startDate)->count(),
            'invitations' => Invitation::where('status', Invitation::STATUS_SENT)
                                    ->where('sent_at', '>=', $startDate)
                                    ->count(),
            'guests' => Guest::where('created_at', '>=', $startDate)->count(),
            'users' => User::where('created_at', '>=', $startDate)->count(),
        ];
    }
}
