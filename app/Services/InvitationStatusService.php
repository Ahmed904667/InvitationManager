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
        // Get actual invitations from database - use fresh query for each count
        $actualStats = [
            'queued' => $event->invitations()->whereIn('status', ['queued', 'sending', 'pending'])->count(),
            'delivered' => $event->invitations()->whereIn('status', ['delivered', 'sent'])->count(),
            'read' => $event->invitations()->where('status', 'read')->count(),
            'failed' => $event->invitations()->whereIn('status', ['failed', 'undelivered', 'canceled', 'bounced'])->count(),
        ];
        
        // For scheduled events, show virtual queued invitations
        // For sent events, only show virtual queued if there are actual queued invitations
        if ($event->status === 'scheduled' || ($event->status === 'sent' && $actualStats['queued'] > 0)) {
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
        // Get removed guest IDs to exclude them from regular invitations
        $removedGuestIds = $event->eventGuests()
            ->where('status', EventGuest::STATUS_REMOVED)
            ->pluck('guest_id')
            ->toArray();
        
        // Get actual invitations from database, excluding removed guests
        $actualInvitations = $event->invitations()
            ->with(['guest'])
            ->whereNotIn('guest_id', $removedGuestIds)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // For scheduled events, create virtual "queued" invitations for event guests
        // For sent events, only create virtual invitations if there are actual queued invitations
        $virtualInvitations = collect();
        $hasActualQueued = $event->invitations()->whereIn('status', ['queued', 'sending', 'pending'])->exists();
        
        if ($event->status === 'scheduled' || ($event->status === 'sent' && $hasActualQueued)) {
            $virtualInvitations = $this->createVirtualInvitations($event);
        }
        
        // Merge only active and virtual invitations, group by channel
        $allInvitations = $actualInvitations->concat($virtualInvitations);
        return $allInvitations->groupBy('channel');
    }
    
    /**
     * Get removed guest invitations separately
     */
    public function getRemovedGuestInvitationsGrouped(Event $event): \Illuminate\Support\Collection
    {
        return $this->getRemovedGuestInvitations($event)->groupBy('channel');
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
     * Get invitations for removed guests
     */
    private function getRemovedGuestInvitations(Event $event): \Illuminate\Support\Collection
    {
        $removedInvitations = collect();
        
        // Get removed event guests
        $removedEventGuests = $event->eventGuests()
            ->where('status', EventGuest::STATUS_REMOVED)
            ->with('guest')
            ->get();
        
        foreach ($removedEventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            if ($guest) {
                // Get actual invitations for this guest
                $guestInvitations = $event->invitations()
                    ->where('guest_id', $guest->id)
                    ->get();
                
                // Mark each invitation as from a removed guest
                foreach ($guestInvitations as $invitation) {
                    $invitation->is_removed_guest = true;
                    $invitation->removed_at = $eventGuest->removed_at;
                    $invitation->removal_reason = $eventGuest->removal_reason;
                    $removedInvitations->push($invitation);
                }
            }
        }
        
        return $removedInvitations;
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
