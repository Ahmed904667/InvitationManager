<?php

namespace App\Services;

use App\Shared\Models\Event;
use App\EventGuest;

class InvitationStatusService
{
    /**
     * Get invitation statistics for an event including virtual invitations for scheduled events
     */
    public function getInvitationStats(Event $event): array
    {
        // Get actual invitations from database
        $actualInvitations = $event->invitations();
        
        // Calculate actual invitation stats
        $actualStats = [
            'queued' => $actualInvitations->whereIn('status', ['queued', 'sending', 'pending'])->count(),
            'delivered' => $actualInvitations->whereIn('status', ['delivered', 'sent'])->count(),
            'read' => $actualInvitations->where('status', 'read')->count(),
            'failed' => $actualInvitations->whereIn('status', ['failed', 'undelivered', 'canceled', 'bounced'])->count(),
        ];
        
        // For scheduled events, replace queued count with virtual queued invitations
        if ($event->status === 'scheduled') {
            $virtualQueuedCount = $this->getVirtualQueuedCount($event);
            $actualStats['queued'] = $virtualQueuedCount; // Replace instead of adding
        }
        
        return $actualStats;
    }
    
    /**
     * Get all invitations for an event including virtual invitations for scheduled events
     */
    public function getAllInvitations(Event $event): \Illuminate\Support\Collection
    {
        // Get actual invitations from database
        $actualInvitations = $event->invitations()
            ->with(['guest'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('channel');
        
        // For scheduled events, create virtual "queued" invitations for event guests
        $virtualInvitations = collect();
        if ($event->status === 'scheduled') {
            $virtualInvitations = $this->createVirtualInvitations($event);
        }
        
        // Merge actual and virtual invitations
        $allInvitations = $actualInvitations->flatten()->concat($virtualInvitations);
        return $allInvitations->groupBy('channel');
    }
    
    /**
     * Create virtual invitations for scheduled events
     */
    private function createVirtualInvitations(Event $event): \Illuminate\Support\Collection
    {
        $virtualInvitations = collect();
        
        $eventGuests = $event->eventGuests()
            ->where('status', EventGuest::STATUS_ACTIVE)
            ->with('guest')
            ->get();
        
        // Get enabled invitation platforms from event
        $enabledPlatforms = $event->invitation_platforms ?? ['email'];
        
        foreach ($eventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            if ($guest) {
                // Create virtual invitations only for enabled platforms
                if (in_array('email', $enabledPlatforms) && $guest->email) {
                    $virtualInvitations->push((object)[
                        'id' => 'virtual_email_' . $eventGuest->id,
                        'guest' => $guest,
                        'channel' => 'email',
                        'status' => 'queued',
                        'sent_at' => null,
                        'created_at' => $eventGuest->created_at,
                        'is_virtual' => true
                    ]);
                }
                if (in_array('whatsapp', $enabledPlatforms) && $guest->phone) {
                    $virtualInvitations->push((object)[
                        'id' => 'virtual_whatsapp_' . $eventGuest->id,
                        'guest' => $guest,
                        'channel' => 'whatsapp',
                        'status' => 'queued',
                        'sent_at' => null,
                        'created_at' => $eventGuest->created_at,
                        'is_virtual' => true
                    ]);
                }
            }
        }
        
        return $virtualInvitations;
    }
    
    /**
     * Get count of virtual queued invitations for scheduled events
     */
    private function getVirtualQueuedCount(Event $event): int
    {
        $enabledPlatforms = $event->invitation_platforms ?? ['email'];
        $eventGuests = $event->eventGuests()
            ->where('status', EventGuest::STATUS_ACTIVE)
            ->with('guest')
            ->get();
        
        $count = 0;
        foreach ($eventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            if ($guest) {
                if (in_array('email', $enabledPlatforms) && $guest->email) {
                    $count++;
                }
                if (in_array('whatsapp', $enabledPlatforms) && $guest->phone) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Get invitation status CSS class
     */
    public function getStatusClass(string $status): string
    {
        switch ($status) {
            case 'delivered':
            case 'sent':
                return 'success';
            case 'read':
                return 'emerald';
            case 'failed':
            case 'undelivered':
            case 'canceled':
            case 'bounced':
                return 'danger';
            default:
                return 'warning';
        }
    }
    
    /**
     * Get invitation status display text
     */
    public function getStatusText(string $status): string
    {
        switch ($status) {
            case 'delivered':
            case 'sent':
                return 'Delivered';
            case 'read':
                return 'Read';
            case 'failed':
            case 'undelivered':
            case 'canceled':
            case 'bounced':
                return 'Failed';
            default:
                return 'Queued';
        }
    }
}
