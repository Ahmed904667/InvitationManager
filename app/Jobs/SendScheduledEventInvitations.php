<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Shared\Models\Event;
use App\Organizer\Services\EventCreationService;

class SendScheduledEventInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventId;
    public $timeout = 300; // 5 minutes timeout
    public $tries = 3; // Allow 3 attempts
    public $backoff = [60, 300, 600]; // Wait 1min, 5min, 10min between retries

    /**
     * Create a new job instance.
     */
    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find the event
            $event = Event::findOrFail($this->eventId);
            
            // DEBUG: Log complete event data received by the job
            Log::info('🔍 [SCHEDULED_EVENT_DEBUG] Complete event data received by job', [
                'event_id' => $this->eventId,
                'event_name' => $event->name,
                'event_status' => $event->status,
                'event_send_type' => $event->send_type,
                'event_scheduled_at' => $event->scheduled_at,
                'event_start_date' => $event->start_date,
                'event_end_date' => $event->end_date,
                'event_location' => $event->location,
                'event_description' => $event->description,
                'event_user_id' => $event->user_id,
                'event_created_at' => $event->created_at,
                'event_updated_at' => $event->updated_at,
                
                // Message data
                'event_general_message' => $event->general_message,
                'event_general_message_length' => strlen($event->general_message ?? ''),
                'event_group_messages' => $event->group_messages,
                'event_group_messages_count' => is_array($event->group_messages) ? count($event->group_messages) : 0,
                'event_per_guest_messages' => $event->per_guest_messages,
                'event_per_guest_messages_count' => is_array($event->per_guest_messages) ? count($event->per_guest_messages) : 0,
                'event_invitation_message' => $event->invitation_message,
                'event_message_template' => $event->message_template,
                'event_custom_messages' => $event->custom_messages,
                'event_ai_generated' => $event->ai_generated,
                
                // Platform and settings data
                'event_invitation_platforms' => $event->invitation_platforms,
                'event_invitation_platforms_type' => gettype($event->invitation_platforms),
                'event_invitation_platforms_count' => is_array($event->invitation_platforms) ? count($event->invitation_platforms) : 0,
                'event_guest_list_ids' => $event->guest_list_ids,
                'event_guest_list_ids_type' => gettype($event->guest_list_ids),
                'event_guest_list_ids_count' => is_array($event->guest_list_ids) ? count($event->guest_list_ids) : 0,
                'event_qr_checkin_enabled' => $event->qr_checkin_enabled,
                'event_rsvp_enabled' => $event->rsvp_enabled,
                'event_message_mode' => $event->message_mode,
                
                // Design data
                'event_invitation_title' => $event->invitation_title,
                'event_invitation_subtitle' => $event->invitation_subtitle,
                'event_hero_color1' => $event->hero_color1,
                'event_hero_color2' => $event->hero_color2,
                'event_accent_color' => $event->accent_color,
                'event_font_family' => $event->font_family,
                
                // Raw data for debugging
                'event_raw_attributes' => $event->getAttributes(),
                'event_raw_original' => $event->getOriginal(),
                'event_raw_changes' => $event->getChanges(),
            ]);
            
            // Check if event can still be sent
            if ($event->status !== 'scheduled') {
                Log::info('❌ [SCHEDULED_EVENT] Event is no longer scheduled for sending', [
                    'event_id' => $this->eventId,
                    'status' => $event->status,
                    'expected_status' => 'scheduled'
                ]);
                return;
            }
            
            // Load guest lists and guests
            $event->load('guestLists.guests');
            
            // DEBUG: Log guest list data after loading
            Log::info('🔍 [SCHEDULED_EVENT_DEBUG] Guest list data after loading', [
                'event_id' => $this->eventId,
                'guest_lists_count' => $event->guestLists->count(),
                'guest_lists_data' => $event->guestLists->map(function($list) {
                    return [
                        'list_id' => $list->id,
                        'list_name' => $list->name,
                        'guests_count' => $list->guests->count(),
                        'guests_data' => $list->guests->map(function($guest) {
                            return [
                                'guest_id' => $guest->id,
                                'guest_name' => $guest->name,
                                'guest_email' => $guest->email,
                                'guest_phone' => $guest->phone,
                                'guest_group_id' => $guest->guest_group_id,
                                'guest_checked_in' => $guest->checked_in,
                            ];
                        })->toArray()
                    ];
                })->toArray(),
                'total_guests_count' => $event->guestLists->sum(function($list) { return $list->guests->count(); })
            ]);
            
            Log::info('🔄 [SCHEDULED_EVENT] Starting to send scheduled event invitations', [
                'event_id' => $this->eventId,
                'event_name' => $event->name,
                'scheduled_at' => $event->scheduled_at,
                'has_stored_messages' => !empty($event->general_message) || !empty($event->group_messages) || !empty($event->per_guest_messages),
                'stored_platforms' => $event->invitation_platforms,
                'stored_general_message' => $event->general_message ? 'has_content(' . strlen($event->general_message) . ' chars)' : 'empty',
                'stored_group_messages' => $event->group_messages ? 'has_content(' . count($event->group_messages) . ' items)' : 'empty',
                'stored_per_guest_messages' => $event->per_guest_messages ? 'has_content(' . count($event->per_guest_messages) . ' items)' : 'empty',
                'guest_lists_count' => $event->guestLists->count(),
                'total_guests_count' => $event->guestLists->sum(function($list) { return $list->guests->count(); })
            ]);
            
            // Use the EventCreationService to send invitations (will use stored event data)
            $eventCreationService = app(EventCreationService::class);
            
            Log::info('📧 [SCHEDULED_EVENT] About to call sendInvitationsForEvent', [
                'event_id' => $this->eventId,
                'event_creation_service_class' => get_class($eventCreationService),
                'event_data_verification' => [
                    'has_general_message' => !empty($event->general_message),
                    'general_message_length' => strlen($event->general_message ?? ''),
                    'has_group_messages' => !empty($event->group_messages),
                    'has_per_guest_messages' => !empty($event->per_guest_messages),
                    'invitation_platforms' => $event->invitation_platforms,
                    'guest_lists_count' => $event->guestLists->count()
                ]
            ]);
            
            try {
                // Call the new dedicated method for scheduled invitations
                $eventCreationService->sendScheduledInvitationsForEvent($event);
                Log::info('📅 [SCHEDULED_EVENT] Successfully completed sendScheduledInvitationsForEvent call', [
                    'event_id' => $this->eventId
                ]);
            } catch (\Exception $e) {
                Log::error('❌ [SCHEDULED_EVENT] Error in sendScheduledInvitationsForEvent', [
                    'event_id' => $this->eventId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-throw to mark job as failed
            }
            
            // Update event status to 'sent'
            $event->update(['status' => 'sent']);
            
            Log::info('✅ [SCHEDULED_EVENT] Successfully sent scheduled event invitations', [
                'event_id' => $this->eventId,
                'event_name' => $event->name,
                'guest_count' => $event->guestLists->sum(function($list) {
                    return $list->guests->count();
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [SCHEDULED_EVENT] Failed to send scheduled event invitations', [
                'event_id' => $this->eventId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ [SCHEDULED_EVENT] Job failed for scheduled event invitations', [
            'event_id' => $this->eventId,
            'error' => $exception->getMessage()
        ]);
        
        // Optionally update event status to indicate failure
        $event = Event::find($this->eventId);
        if ($event && $event->status === 'scheduled') {
            $event->update(['status' => 'draft']); // Reset to draft so user can retry
        }
    }
}
