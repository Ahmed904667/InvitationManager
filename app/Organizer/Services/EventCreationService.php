<?php

namespace App\Organizer\Services;

use App\Services\InvitationMessageService;
use App\Services\EventMessageGenerationService;
use App\Shared\Models\Event;
use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use App\Shared\Models\Invitation;
use App\EventGuest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventCreationService
{
    private $invitationMessageService;
    private $eventMessageGenerationService;

    public function __construct(InvitationMessageService $invitationMessageService, EventMessageGenerationService $eventMessageGenerationService)
    {
        $this->invitationMessageService = $invitationMessageService;
        $this->eventMessageGenerationService = $eventMessageGenerationService;
    }

    /**
     * Store event creation data in session
     */
    public function storeStep(int $step, array $data): void
    {
        $sessionKey = 'event_creation_step_' . $step;
        
        // Debug what we're storing
        Log::info('🔍 [SESSION] Storing step data', [
            'step' => $step,
            'session_key' => $sessionKey,
            'data_keys' => array_keys($data),
            'data_sample' => array_slice($data, 0, 5, true),
            'user_id' => Auth::id()
        ]);
        
        Session::put($sessionKey, $data);
        
        // Verify what was stored
        $storedData = Session::get($sessionKey, []);
        Log::info('🔍 [SESSION] Verified stored data', [
            'step' => $step,
            'session_key' => $sessionKey,
            'stored_keys' => array_keys($storedData),
            'stored_sample' => array_slice($storedData, 0, 5, true)
        ]);
    }

    /**
     * Get event creation data from session
     */
    public function getStep(int $step): array
    {
        $sessionKey = 'event_creation_step_' . $step;
        return Session::get($sessionKey, []);
    }

    /**
     * Get all event creation data from session
     */
    public function getAllStepsData(): array
    {
        $data = [];
        for ($i = 1; $i <= 4; $i++) {
            $stepData = $this->getStep($i);
            if (!empty($stepData)) {
                $data = array_merge($data, $stepData);
            }
        }
        
        // Debug the merged data
        Log::info('🔍 [GET_ALL_STEPS] Merged data structure', [
            'user_id' => Auth::id(),
            'total_keys' => count($data),
            'has_general_message' => isset($data['general_message']),
            'has_group_messages' => isset($data['group_messages']),
            'has_per_guest_messages' => isset($data['per_guest_messages']),
            'has_invitation_platforms' => isset($data['invitation_platforms']),
            'all_keys' => array_keys($data)
        ]);
        
        return $data;
    }

    /**
     * Clear all event creation data from session
     */
    public function clearSession(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            Session::forget('event_creation_step_' . $i);
        }
        
        // Clear draft-related session variables
        Session::forget('editing_draft_event_id');
        
        // Clear duplicate guest related session variables
        Session::forget('excluded_guest_ids');
        Session::forget('event_duplicate_guests');
        Session::forget('event_guest_list_ids');
        
        Log::info('🧹 [SESSION] Cleared all event creation session data', [
            'user_id' => Auth::id()
        ]);
    }

    /**
     * Check if session data is intact and attempt to recover if needed
     */
    public function validateAndRepairSessionData(): bool
    {
        $allData = $this->getAllStepsData();
        $step2Data = $this->getStep(2);
        
        Log::info('🔍 [SESSION_REPAIR] Validating session data', [
            'has_guest_list_ids_in_all' => isset($allData['guest_list_ids']),
            'has_guest_list_ids_in_step2' => isset($step2Data['guest_list_ids']),
            'all_data_keys' => array_keys($allData),
            'step2_keys' => array_keys($step2Data)
        ]);
        
        // If guest_list_ids is missing from allData but exists in step2, repair it
        if (!isset($allData['guest_list_ids']) && isset($step2Data['guest_list_ids'])) {
            Log::info('🔧 [SESSION_REPAIR] Repairing missing guest_list_ids in session');
            
            // Get current step 2 data and re-store it to ensure it's properly merged
            $this->storeStep(2, $step2Data);
            
            return true;
        }
        
        // Check if we have valid guest list IDs
        $guestListIds = $allData['guest_list_ids'] ?? $step2Data['guest_list_ids'] ?? [];
        if (empty($guestListIds)) {
            Log::warning('🔧 [SESSION_REPAIR] No guest list IDs found in session');
            return false;
        }
        
        // Verify the guest lists actually exist for this user
        $existingLists = GuestList::whereIn('id', $guestListIds)
            ->where('user_id', Auth::id())
            ->pluck('id')
            ->toArray();
            
        if (empty($existingLists)) {
            Log::warning('🔧 [SESSION_REPAIR] Guest lists in session do not exist for user', [
                'session_ids' => $guestListIds,
                'user_id' => Auth::id()
            ]);
            return false;
        }
        
        Log::info('🔧 [SESSION_REPAIR] Session data is valid', [
            'guest_list_ids' => $guestListIds,
            'existing_lists' => $existingLists
        ]);
        
        return true;
    }

    /**
     * Validate step completion
     */
    public function isStepValid(int $step): bool
    {
        $data = $this->getStep($step);
        
        // If we're editing a draft event, be more lenient with validation
        $isDraftEditing = Session::has('editing_draft_event_id');
        
        switch ($step) {
            case 1:
                $isValid = !empty($data['name']) && !empty($data['start_date']);
                if (!$isValid && $isDraftEditing) {
                    Log::info('Step 1 validation failed for draft editing', ['data' => $data]);
                }
                return $isValid;
            case 2:
                $isValid = !empty($data['invitation_platforms']) && !empty($data['guest_list_ids']);
                if (!$isValid && $isDraftEditing) {
                    Log::info('Step 2 validation failed for draft editing', ['data' => $data]);
                }
                return $isValid;
            case 3:
                // Step 3 is valid if it has been completed or if we're editing a draft
                $hasBeenCompleted = !empty($data['step3_completed']) || !empty($data);
                $isValid = $hasBeenCompleted || $isDraftEditing;
                
                Log::info('Step 3 validation check', [
                    'data' => $data,
                    'step3_completed' => $data['step3_completed'] ?? false,
                    'has_been_completed' => $hasBeenCompleted,
                    'is_draft' => $isDraftEditing,
                    'is_valid' => $isValid
                ]);
                
                return $isValid;
            case 4:
                $isValid = !empty($data['send_type']);
                if (!$isValid && $isDraftEditing) {
                    Log::info('Step 4 validation failed for draft editing', ['data' => $data]);
                }
                return $isValid;
            default:
                return false;
        }
    }

    /**
     * Generate AI message based on mode and context using the new EventMessageGenerationService
     */
    public function generateAIMessage(string $mode, array $eventData, array $guestData = [], array $userInstructions = []): array
    {
        try {
            \Log::info('AI message generation started', [
                'mode' => $mode,
                'eventData' => $eventData,
                'guestData' => $guestData,
                'userInstructions' => $userInstructions
            ]);
            
            \Log::info('Event data keys', ['keys' => array_keys($eventData)]);
            
            // Create a temporary event object for the service
            $event = new Event();
            
            // Fill event with available data, using defaults for missing fields
            $event->name = $this->extractEventName($eventData);
            $event->description = $this->extractAdditionalInfo($eventData);
            $event->start_date = $this->extractEventDate($eventData) !== 'TBD' ? $this->extractEventDate($eventData) : null;
            $event->end_date = $eventData['end_date'] ?? null;
            
            // Use the EventMessageGenerationService for AI generation
            $result = $this->eventMessageGenerationService->generateMessages(
                $event,
                $mode,
                $guestData,
                $userInstructions
            );
            
            // Convert the response format to match our expected format
            if ($result['success']) {
                switch ($mode) {
                    case 'general':
                        // Already in correct format
                        break;
                    case 'group':
                        // Convert group_messages to group_templates
                        if (isset($result['group_messages'])) {
                            $result['group_templates'] = $result['group_messages'];
                            unset($result['group_messages']);
                        }
                        // Also handle per_guest_messages if they exist
                        if (isset($result['per_guest_messages'])) {
                            $result['guest_messages'] = $result['per_guest_messages'];
                            unset($result['per_guest_messages']);
                        }
                        break;
                    case 'per_guest':
                        // Convert per_guest_messages to guest_messages
                        if (isset($result['per_guest_messages'])) {
                            $result['guest_messages'] = $result['per_guest_messages'];
                            unset($result['per_guest_messages']);
                        }
                        break;
                    case 'list':
                        // Handle list generation (combines group and guest messages)
                        if (isset($result['group_messages'])) {
                            $result['group_templates'] = $result['group_messages'];
                            unset($result['group_messages']);
                        }
                        if (isset($result['per_guest_messages'])) {
                            $result['guest_messages'] = $result['per_guest_messages'];
                            unset($result['per_guest_messages']);
                        }
                        break;
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('AI message generation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to generate AI message: ' . $e->getMessage()
            ];
        }
    }



    /**
     * Get guests from selected guest lists
     */
    public function getGuestsFromLists(array $guestListIds): array
    {
        $guests = [];
        $guestLists = GuestList::whereIn('id', $guestListIds)
            ->where('user_id', Auth::id())
            ->with(['guests', 'guestGroups'])
            ->get();

        foreach ($guestLists as $guestList) {
            $guests[$guestList->id] = [
                'list_name' => $guestList->name,
                'guests' => $guestList->guests,
                'groups' => $guestList->guestGroups
            ];
        }

        return $guests;
    }

    /**
     * Get organized guest data with groups and ungrouped guests
     */
    public function getOrganizedGuestData(array $guestListIds): array
    {
        // Log the input for debugging
        Log::info('🔍 [GUEST_DATA] getOrganizedGuestData called', [
            'guest_list_ids' => $guestListIds,
            'user_id' => Auth::id(),
            'is_array' => is_array($guestListIds),
            'count' => count($guestListIds)
        ]);
        
        // Handle empty or invalid input
        if (empty($guestListIds) || !is_array($guestListIds)) {
            Log::warning('🔍 [GUEST_DATA] Empty or invalid guest list IDs', [
                'guest_list_ids' => $guestListIds,
                'type' => gettype($guestListIds)
            ]);
            return [];
        }
        
        $organizedData = [];
        $guestLists = GuestList::whereIn('id', $guestListIds)
            ->where('user_id', Auth::id())
            ->with(['guests.guestGroup', 'guestGroups.guests'])
            ->get();

        Log::info('🔍 [GUEST_DATA] Guest lists query result', [
            'found_lists' => $guestLists->count(),
            'list_ids' => $guestLists->pluck('id')->toArray(),
            'list_names' => $guestLists->pluck('name')->toArray()
        ]);

        foreach ($guestLists as $guestList) {
            $organizedData[$guestList->id] = [
                'list_name' => $guestList->name,
                'list_id' => $guestList->id,
                'groups' => [],
                'ungrouped_guests' => []
            ];

            // Get all groups for this list
            foreach ($guestList->guestGroups as $group) {
                $organizedData[$guestList->id]['groups'][$group->id] = [
                    'group_name' => $group->name,
                    'group_id' => $group->id,
                    'group_description' => $group->description,
                    'group_color' => $group->color,
                    'guests' => $group->guests
                ];
            }

            // Get ungrouped guests (guests without a group)
            $ungroupedGuests = $guestList->guests()->whereNull('group_id')->get();
            $organizedData[$guestList->id]['ungrouped_guests'] = $ungroupedGuests;
            
            Log::info('🔍 [GUEST_DATA] Processed list', [
                'list_id' => $guestList->id,
                'list_name' => $guestList->name,
                'groups_count' => count($organizedData[$guestList->id]['groups']),
                'ungrouped_count' => count($ungroupedGuests)
            ]);
        }

        Log::info('🔍 [GUEST_DATA] Final organized data', [
            'total_lists' => count($organizedData),
            'list_ids' => array_keys($organizedData)
        ]);

        return $organizedData;
    }

    /**
     * Create the event with all step data
     */
    public function createEvent(): Event
    {
        $data = $this->getAllStepsData();
        $data['user_id'] = Auth::id();
        
        // Get excluded guest IDs from session (duplicates that were removed)
        $excludedGuestIds = Session::get('excluded_guest_ids', []);
        
        Log::info('🔍 [EVENT_CREATION] Excluded guest IDs from session', [
            'user_id' => Auth::id(),
            'excluded_guest_ids' => $excludedGuestIds,
            'excluded_count' => count($excludedGuestIds)
        ]);
        
        // Check if we're editing a draft event
        $editingDraftId = Session::get('editing_draft_event_id');
        
        // Debug the data being passed to event creation
        Log::info('🔍 [EVENT_CREATION] Data received for event creation', [
            'user_id' => Auth::id(),
            'editing_draft_id' => $editingDraftId,
            'data_keys' => array_keys($data),
            'guest_list_ids' => $data['guest_list_ids'] ?? 'not_set',
            'guest_list_ids_type' => gettype($data['guest_list_ids'] ?? 'not_set'),
            'guest_list_ids_count' => is_array($data['guest_list_ids'] ?? null) ? count($data['guest_list_ids']) : 'not_array',
        ]);
        
        // Set default status based on send_type
        if ($data['send_type'] === 'scheduled') {
            $data['status'] = 'scheduled';
        } elseif ($data['send_type'] === 'now') {
            $data['status'] = 'sent';
        } else {
            $data['status'] = 'draft';
        }

        // Normalize required event fields (defensive defaults if session lost some data)
        $data['name'] = $data['name'] ?? 'Untitled Event';
        $data['start_date'] = $data['start_date'] ?? now();
        
        // Handle end_date - if not provided, the Event model mutator will automatically set it to day after start_date
        if (isset($data['end_date'])) {
            if (empty($data['end_date'])) {
                // Don't set to null - let the Event model mutator handle it automatically
                unset($data['end_date']);
                Log::info('🔧 [EVENT_CREATION] End date not provided - will be auto-set to day after start_date', [
                    'user_id' => Auth::id(),
                    'action' => 'end_date_auto_set',
                    'start_date' => $data['start_date'] ?? 'not_set'
                ]);
            } else {
                Log::info('✅ [EVENT_CREATION] End date provided', [
                    'user_id' => Auth::id(),
                    'action' => 'end_date_set',
                    'value' => $data['end_date']
                ]);
            }
        } else {
            Log::info('🔧 [EVENT_CREATION] No end_date in data - will be auto-set to day after start_date', [
                'user_id' => Auth::id(),
                'action' => 'end_date_auto_set',
                'start_date' => $data['start_date'] ?? 'not_set'
            ]);
        }

        // Ensure dates are in UTC format (simple approach)
        $data['dates_in_utc'] = true;

        // CRITICAL: Ensure message data is properly stored
        // Don't set to null if it exists in session
        if (!isset($data['general_message'])) {
            $data['general_message'] = null;
        }
        if (!isset($data['group_messages'])) {
            $data['group_messages'] = null;
        }
        if (!isset($data['per_guest_messages'])) {
            $data['per_guest_messages'] = null;
        }
        
        // CRITICAL: Ensure invitation_platforms is stored
        if (!isset($data['invitation_platforms']) || empty($data['invitation_platforms'])) {
            $data['invitation_platforms'] = ['email']; // Default fallback
            Log::warning('🔧 [EVENT_CREATION] Using default email platform - no platforms provided', [
                'user_id' => Auth::id(),
                'data_keys' => array_keys($data),
                'has_invitation_platforms' => isset($data['invitation_platforms'])
            ]);
        } else {
            Log::info('✅ [EVENT_CREATION] Using provided platforms', [
                'user_id' => Auth::id(),
                'platforms' => $data['invitation_platforms']
            ]);
        }
        
        // Store excluded guest IDs in the event for scheduled invitations
        $data['excluded_guest_ids'] = $excludedGuestIds;
        
        // CRITICAL: For scheduled events, ensure we have message content
        if ($data['send_type'] === 'scheduled') {
            $hasMessageContent = !empty($data['general_message']) || 
                                !empty($data['group_messages']) || 
                                !empty($data['per_guest_messages']);
            
            if (!$hasMessageContent) {
                Log::error('❌ [EVENT_CREATION] Scheduled event missing message content', [
                    'user_id' => Auth::id(),
                    'send_type' => $data['send_type'],
                    'general_message' => $data['general_message'] ?? 'not_set',
                    'group_messages' => $data['group_messages'] ?? 'not_set',
                    'per_guest_messages' => $data['per_guest_messages'] ?? 'not_set'
                ]);
                
                // Provide a fallback message for scheduled events
                $data['general_message'] = $data['general_message'] ?? 'You are invited to our event!';
                Log::info('🔧 [EVENT_CREATION] Added fallback message for scheduled event');
            }
        }
        
        // Detailed logging of what data is being stored
        Log::info('💾 [EVENT_CREATION] Detailed data analysis before storing', [
            'user_id' => Auth::id(),
            'send_type' => $data['send_type'] ?? 'not_set',
            'status' => $data['status'] ?? 'not_set',
            'start_date' => $data['start_date'] ?? 'not_set',
            'end_date' => $data['end_date'] ?? 'not_set',
            'end_date_type' => gettype($data['end_date'] ?? null),
            'has_general_message' => !empty($data['general_message']),
            'general_message_length' => strlen($data['general_message'] ?? ''),
            'general_message_preview' => substr($data['general_message'] ?? '', 0, 50),
            'has_group_messages' => !empty($data['group_messages']),
            'group_messages_count' => is_array($data['group_messages']) ? count($data['group_messages']) : 'not_array',
            'has_per_guest_messages' => !empty($data['per_guest_messages']),
            'per_guest_messages_count' => is_array($data['per_guest_messages']) ? count($data['per_guest_messages']) : 'not_array',
            'invitation_platforms' => $data['invitation_platforms'] ?? 'not_set',
            'invitation_platforms_type' => gettype($data['invitation_platforms'] ?? null),
            'all_data_keys' => array_keys($data),
            'step1_data' => $this->getStep(1),
            'step2_data' => $this->getStep(2),
            'step3_data' => $this->getStep(3),
            'step4_data' => $this->getStep(4)
        ]);

        // If we're editing a draft or scheduled event, update it instead of creating a new one
        if ($editingDraftId) {
            $draftEvent = Event::where('id', $editingDraftId)
                ->where('user_id', Auth::id())
                ->whereIn('status', ['draft', 'scheduled'])
                ->first();
            
            Log::info('🔍 [EVENT_CREATION] Looking for existing event to update', [
                'user_id' => Auth::id(),
                'editing_draft_id' => $editingDraftId,
                'found_existing_event' => $draftEvent ? true : false,
                'existing_event_status' => $draftEvent ? $draftEvent->status : 'NOT_FOUND',
                'existing_event_name' => $draftEvent ? $draftEvent->name : 'NOT_FOUND'
            ]);
            
            if ($draftEvent) {
                // Delete any existing invitations for this draft event
                $draftEvent->invitations()->delete();
                
                // Update the existing draft event
                $draftEvent->update($data);
                
                // Sync guest lists (remove old ones, add new ones)
                if (!empty($data['guest_list_ids'])) {
                    $draftEvent->guestLists()->sync($data['guest_list_ids']);
                    
                    // Use EventGuestService to create event-guest relationships
                    $eventGuestService = app(\App\Services\EventGuestService::class);
                    
                    Log::info('🔗 [EVENT_CREATION] Creating event-guest relationships for updated draft', [
                        'event_id' => $draftEvent->id,
                        'guest_list_ids' => $data['guest_list_ids'],
                        'guest_list_count' => count($data['guest_list_ids'])
                    ]);
                    
                    foreach ($data['guest_list_ids'] as $guestListId) {
                        $guestList = \App\Shared\Models\GuestList::find($guestListId);
                        if ($guestList) {
                            Log::info('🔗 [EVENT_CREATION] Processing guest list for updated draft', [
                                'event_id' => $draftEvent->id,
                                'guest_list_id' => $guestListId,
                                'guest_list_name' => $guestList->name,
                                'guests_count' => $guestList->guests->count()
                            ]);
                            
                            $eventGuests = $eventGuestService->addGuestListToEvent($draftEvent, $guestList, $excludedGuestIds);
                            
                            Log::info('🔗 [EVENT_CREATION] Created event-guest relationships for updated draft', [
                                'event_id' => $draftEvent->id,
                                'guest_list_id' => $guestListId,
                                'relationships_created' => count($eventGuests)
                            ]);
                        } else {
                            Log::warning('⚠️ [EVENT_CREATION] Guest list not found for updated draft', [
                                'event_id' => $draftEvent->id,
                                'guest_list_id' => $guestListId
                            ]);
                        }
                    }
                } else {
                    $draftEvent->guestLists()->detach();
                    
                    Log::info('🔗 [EVENT_CREATION] No guest lists to attach for updated draft', [
                        'event_id' => $draftEvent->id,
                        'guest_list_ids' => $data['guest_list_ids'] ?? 'not_set'
                    ]);
                }
                
                $event = $draftEvent;
                
                Log::info('✅ [EVENT_CREATION] Updated existing draft/scheduled event', [
                    'user_id' => Auth::id(),
                    'draft_id' => $editingDraftId,
                    'existing_status' => $draftEvent->status,
                    'new_status' => $data['status'],
                    'send_type' => $data['send_type'] ?? 'unknown',
                    'deleted_invitations' => true
                ]);
            } else {
                // Draft not found, create new event
                $event = Event::create($data);
                
                // Attach guest lists and create event-guest relationships
                if (!empty($data['guest_list_ids'])) {
                    $event->guestLists()->attach($data['guest_list_ids']);
                    
                    // Use EventGuestService to create event-guest relationships
                    $eventGuestService = app(\App\Services\EventGuestService::class);
                    
                    Log::info('🔗 [EVENT_CREATION] Creating event-guest relationships', [
                        'event_id' => $event->id,
                        'guest_list_ids' => $data['guest_list_ids'],
                        'guest_list_count' => count($data['guest_list_ids'])
                    ]);
                    
                    foreach ($data['guest_list_ids'] as $guestListId) {
                        $guestList = \App\Shared\Models\GuestList::find($guestListId);
                        if ($guestList) {
                            Log::info('🔗 [EVENT_CREATION] Processing guest list', [
                                'event_id' => $event->id,
                                'guest_list_id' => $guestListId,
                                'guest_list_name' => $guestList->name,
                                'guests_count' => $guestList->guests->count()
                            ]);
                            
                            $eventGuests = $eventGuestService->addGuestListToEvent($event, $guestList, $excludedGuestIds);
                            
                            Log::info('🔗 [EVENT_CREATION] Created event-guest relationships', [
                                'event_id' => $event->id,
                                'guest_list_id' => $guestListId,
                                'relationships_created' => count($eventGuests)
                            ]);
                        } else {
                            Log::warning('⚠️ [EVENT_CREATION] Guest list not found', [
                                'event_id' => $event->id,
                                'guest_list_id' => $guestListId
                            ]);
                        }
                    }
                } else {
                    Log::info('🔗 [EVENT_CREATION] No guest lists to attach', [
                        'event_id' => $event->id,
                        'guest_list_ids' => $data['guest_list_ids'] ?? 'not_set'
                    ]);
                }
                
                Log::info('🔄 [DRAFT] Draft not found, created new event', [
                    'user_id' => Auth::id(),
                    'draft_id' => $editingDraftId,
                    'new_event_id' => $event->id
                ]);
            }
        } else {
            // Create new event (not editing a draft)
            $event = Event::create($data);
            
            // Attach guest lists and create event-guest relationships
            if (!empty($data['guest_list_ids'])) {
                $event->guestLists()->attach($data['guest_list_ids']);
                
                // Use EventGuestService to create event-guest relationships
                $eventGuestService = app(\App\Services\EventGuestService::class);
                
                Log::info('🔗 [EVENT_CREATION] Creating event-guest relationships (new event)', [
                    'event_id' => $event->id,
                    'guest_list_ids' => $data['guest_list_ids'],
                    'guest_list_count' => count($data['guest_list_ids'])
                ]);
                
                foreach ($data['guest_list_ids'] as $guestListId) {
                    $guestList = \App\Shared\Models\GuestList::find($guestListId);
                    if ($guestList) {
                        Log::info('🔗 [EVENT_CREATION] Processing guest list (new event)', [
                            'event_id' => $event->id,
                            'guest_list_id' => $guestListId,
                            'guest_list_name' => $guestList->name,
                            'guests_count' => $guestList->guests->count()
                        ]);
                        
                        $eventGuests = $eventGuestService->addGuestListToEvent($event, $guestList, $excludedGuestIds);
                        
                        Log::info('🔗 [EVENT_CREATION] Created event-guest relationships (new event)', [
                            'event_id' => $event->id,
                            'guest_list_id' => $guestListId,
                            'relationships_created' => count($eventGuests)
                        ]);
                    } else {
                        Log::warning('⚠️ [EVENT_CREATION] Guest list not found (new event)', [
                            'event_id' => $event->id,
                            'guest_list_id' => $guestListId
                        ]);
                    }
                }
            } else {
                Log::info('🔗 [EVENT_CREATION] No guest lists to attach (new event)', [
                    'event_id' => $event->id,
                    'guest_list_ids' => $data['guest_list_ids'] ?? 'not_set'
                ]);
            }
            
            Log::info('🔄 [EVENT] Created new event', [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'status' => $data['status']
            ]);
        }

        // Log what was actually stored in the database
        Log::info('💾 [EVENT_CREATION] Data verification after storing', [
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'stored_general_message' => $event->general_message ? 'has_content(' . strlen($event->general_message) . ' chars)' : 'empty',
            'stored_group_messages' => $event->group_messages ? 'has_content(' . count($event->group_messages) . ' items)' : 'empty',
            'stored_per_guest_messages' => $event->per_guest_messages ? 'has_content(' . count($event->per_guest_messages) . ' items)' : 'empty',
            'stored_invitation_platforms' => $event->invitation_platforms,
            'stored_status' => $event->status,
            'stored_send_type' => $event->send_type ?? 'not_set',
            'stored_guest_list_ids' => $event->guest_list_ids ?? 'not_set',
            'guest_lists_count' => $event->guestLists->count(),
            'event_guest_relationships_count' => \App\EventGuest::where('event_id', $event->id)->count()
        ]);

        // If this is a scheduled event, dispatch the job to send invitations later
        if ($event->status === 'scheduled' && $event->scheduled_at) {
            // Parse the scheduled_at as UTC since it's already stored in UTC format
            // Use createFromFormat to ensure proper UTC parsing
            $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $event->scheduled_at, 'UTC');
            $now = \Carbon\Carbon::now('UTC');
            
            // Calculate the delay in seconds from now until the scheduled time
            // Use diffInSeconds with the correct order: scheduled time - current time
            $delaySeconds = $now->diffInSeconds($scheduledAt, false);
            
            // Debug logging for timing calculation
            Log::info('📅 [EVENT_CREATION] Job dispatch timing calculation', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'raw_scheduled_at' => $event->scheduled_at,
                'parsed_scheduled_at' => $scheduledAt->toISOString(),
                'current_utc' => $now->toISOString(),
                'delay_seconds' => $delaySeconds,
                'scheduled_at_timezone' => $scheduledAt->timezoneName,
                'current_timezone' => $now->timezoneName
            ]);
            
            // Only dispatch if the scheduled time is in the future
            if ($delaySeconds > 0) {
                dispatch(new \App\Jobs\SendScheduledEventInvitations($event->id))->delay($delaySeconds);
                
                Log::info('📅 [EVENT_CREATION] Dispatched job for scheduled event invitations', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'scheduled_at' => $scheduledAt,
                    'scheduled_at_utc' => $scheduledAt->toISOString(),
                    'current_utc' => $now->toISOString(),
                    'job_delay_seconds' => $delaySeconds,
                    'job_delay' => $scheduledAt->diffForHumans(),
                    'has_stored_messages' => !empty($event->general_message) || !empty($event->group_messages) || !empty($event->per_guest_messages),
                    'stored_platforms' => $event->invitation_platforms
                ]);
            } else {
                Log::warning('📅 [EVENT_CREATION] Scheduled time is in the past, dispatching immediately', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'scheduled_at' => $scheduledAt,
                    'current_utc' => $now->toISOString(),
                    'delay_seconds' => $delaySeconds
                ]);
                
                // Dispatch immediately if scheduled time is in the past
                dispatch(new \App\Jobs\SendScheduledEventInvitations($event->id));
            }
        }

        // Clear session AFTER successful creation and verification
        $this->clearSession();

        return $event;
    }

    /**
     * Generate and send invitations for an event to all guests in selected lists.
     */
    public function sendInvitationsForEvent(Event $event, array $allData = [], array $excludedGuestIds = []): void
    {
        // DEBUG: Log complete input data received by sendInvitationsForEvent
        Log::info('🔍 [SEND_INVITATIONS_DEBUG] Complete input data received by sendInvitationsForEvent', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'allData_count' => count($allData),
            'allData_keys' => array_keys($allData),
            'allData_content' => $allData,
            'excluded_guest_ids' => $excludedGuestIds,
            'excluded_count' => count($excludedGuestIds),
            
            // Event data before loading relationships
            'event_before_load' => [
                'id' => $event->id,
                'name' => $event->name,
                'status' => $event->status,
                'general_message' => $event->general_message,
                'general_message_length' => strlen($event->general_message ?? ''),
                'group_messages' => $event->group_messages,
                'group_messages_type' => gettype($event->group_messages),
                'per_guest_messages' => $event->per_guest_messages,
                'per_guest_messages_type' => gettype($event->per_guest_messages),
                'invitation_platforms' => $event->invitation_platforms,
                'invitation_platforms_type' => gettype($event->invitation_platforms),
                'guest_list_ids' => $event->guest_list_ids,
                'guest_list_ids_type' => gettype($event->guest_list_ids),
                'raw_attributes' => $event->getAttributes(),
            ]
        ]);
        
        $event->load('guestLists.guests');
        
        // Get active guests for this event (excludes removed duplicates)
        $activeEventGuests = EventGuest::where('event_id', $event->id)
            ->where('status', EventGuest::STATUS_ACTIVE)
            ->with('guest')
            ->get();
        
        Log::info('📧 [INVITATION] Active guests for invitation sending', [
            'event_id' => $event->id,
            'active_guests_count' => $activeEventGuests->count(),
            'active_guest_ids' => $activeEventGuests->pluck('guest_id')->toArray()
        ]);
        
        // DEBUG: Log event data after loading relationships
        Log::info('🔍 [SEND_INVITATIONS_DEBUG] Event data after loading relationships', [
            'event_id' => $event->id,
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
                        ];
                    })->toArray()
                ];
            })->toArray(),
            'total_guests_count' => $event->guestLists->sum(function($list) { return $list->guests->count(); })
        ]);
        
        // Use stored messages from event database as primary source
        $messageGeneral = $event->general_message ?? $allData['general_message'] ?? null;
        $messagesGroup = $event->group_messages ?? $allData['group_messages'] ?? [];
        $messagesPerGuest = $event->per_guest_messages ?? $allData['per_guest_messages'] ?? [];
        
        // Use event's stored invitation_platforms as primary source
        $platforms = $event->invitation_platforms ?? $allData['invitation_platforms'] ?? ['email'];
        
        // DEBUG: Log message and platform resolution
        Log::info('🔍 [SEND_INVITATIONS_DEBUG] Message and platform resolution', [
            'event_id' => $event->id,
            'message_resolution' => [
                'general_from_event' => $event->general_message,
                'general_from_allData' => $allData['general_message'] ?? 'not_set',
                'final_general' => $messageGeneral,
                'group_from_event' => $event->group_messages,
                'group_from_allData' => $allData['group_messages'] ?? 'not_set',
                'final_group' => $messagesGroup,
                'per_guest_from_event' => $event->per_guest_messages,
                'per_guest_from_allData' => $allData['per_guest_messages'] ?? 'not_set',
                'final_per_guest' => $messagesPerGuest,
            ],
            'platform_resolution' => [
                'platforms_from_event' => $event->invitation_platforms,
                'platforms_from_allData' => $allData['invitation_platforms'] ?? 'not_set',
                'final_platforms' => $platforms,
            ]
        ]);
        
        // Log the message sources for debugging
        Log::info('📧 [INVITATION] Sending invitations with stored data', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'platforms_from_event' => $event->invitation_platforms ?? 'not_set',
            'platforms_from_allData' => $allData['invitation_platforms'] ?? 'not_set',
            'final_platforms' => $platforms,
            'message_general_from_event' => $event->general_message ? 'has_content' : 'empty',
            'message_general_from_allData' => $allData['general_message'] ?? 'not_set',
            'final_general_message' => $messageGeneral ? 'has_content' : 'empty',
            'group_messages_from_event' => $event->group_messages ? 'has_content' : 'empty',
            'per_guest_messages_from_event' => $event->per_guest_messages ? 'has_content' : 'empty',
            'guest_lists_count' => $event->guestLists->count(),
            'total_guests_count' => $event->guestLists->sum(function($list) { return $list->guests->count(); })
        ]);

        // Check if we have any guests to send to
        if ($event->guestLists->isEmpty()) {
            Log::warning('📧 [INVITATION] No guest lists found for event', [
                'event_id' => $event->id,
                'event_name' => $event->name
            ]);
            return;
        }

        $totalGuestsProcessed = 0;
        $totalInvitationsSent = 0;

        // Send invitations only to active guests (excludes removed duplicates)
        foreach ($activeEventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            $totalGuestsProcessed++;
            
            // Find the guest list for this guest
            $guestList = $guest->guestList;
                
            // Determine message for this guest (per-guest > group > general)
            $message = $messagesPerGuest[$guest->id] ?? null;
            if (!$message && isset($guest->group_id) && isset($messagesGroup[$guestList->id]) && is_array($messagesGroup[$guestList->id])) {
                $message = $messagesGroup[$guestList->id][$guest->group_id] ?? null;
                } elseif (!$message && isset($messagesGroup[$guestList->id]) && is_string($messagesGroup[$guestList->id])) {
                    $message = $messagesGroup[$guestList->id];
                }
                if (!$message) {
                    $message = $messageGeneral;
                }

                // Fallback message for scheduled events when no message is found
                if (empty($message) && $event->send_type === 'scheduled') {
                    $platformNames = array_map(function($platform) {
                        return ucfirst($platform);
                    }, $platforms);
                    $platformText = implode(', ', $platformNames);
                    $message = "No message found (user selected platform \"{$platformText}\")";
                    
                    Log::info('📧 [INVITATION] Using fallback message for scheduled event', [
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'platforms' => $platforms,
                        'fallback_message' => $message
                    ]);
                }

                // Generate unique token per guest (shared across all platforms)
                $token = $this->generateUniqueInvitationToken();
                $inviteUrl = route('public.invite.show', ['token' => $token]);

                // Apply simple placeholders before sending (shared across platforms)
                $personalized = $this->applyMessagePlaceholders(
                    $message,
                    $guest,
                    $event
                );

                // Append invite URL to message (shared across platforms)
                $completeMessage = trim(($personalized ?? '') . "\n\n" . $inviteUrl);

                foreach ($platforms as $platform) {
                    
                    // Log the message content for debugging
                    Log::info('📧 [INVITATION] Sending message to guest', [
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'guest_name' => $guest->name,
                        'platform' => $platform,
                        'original_message' => $message ? 'has_content' : 'empty',
                        'personalized_message' => $personalized ? 'has_content' : 'empty',
                        'complete_message_length' => strlen($completeMessage),
                        'message_preview' => substr($completeMessage, 0, 100) . '...'
                    ]);
                    
                    $recipient = $platform === 'email' ? ($guest->email ?: '') : ($guest->phone ?: '');
                    if (!$recipient) {
                        Log::warning('Skipping guest without recipient for platform', [
                            'guest_id' => $guest->id,
                            'platform' => $platform
                        ]);
                        continue;
                    }

                    // Create invitation record (pending)
                    $invitation = Invitation::create([
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'token' => $token,
                        'channel' => $platform,
                        'recipient' => $recipient,
                        'message' => $completeMessage,
                        'status' => 'pending',
                    ]);

                    // Send via channel
                    $sent = false;
                    $twilioResponse = null;
                    try {
                        if ($platform === 'email') {
                            // Use rich email template
                            \Mail::to($recipient)->send(new \App\Mail\EventInvitationMail(
                                $event,
                                $guest,
                                $personalized,
                                $inviteUrl
                            ));
                            $sent = true;
                        } elseif ($platform === 'whatsapp') {
                            $twilio = app(\App\Services\TwilioService::class);
                            $twilioResponse = $twilio->sendWhatsAppMessage($recipient, $completeMessage);
                            $sent = $twilioResponse['success'] ?? false;
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed sending invitation', [
                            'invitation_id' => $invitation->id,
                            'error' => $e->getMessage(),
                        ]);
                        $sent = false;
                    }

                    // Update status and store Twilio details
                    $updateData = [
                        'status' => $sent ? 'sent' : 'failed',
                        'sent_at' => $sent ? Carbon::now() : null,
                    ];
                    
                    if ($platform === 'whatsapp' && $twilioResponse) {
                        $updateData['external_id'] = $twilioResponse['message_sid'] ?? null;
                        $updateData['delivery_details'] = [
                            'twilio_response' => $twilioResponse,
                            'twilio_status' => $twilioResponse['status'] ?? 'unknown',
                            'sent_at' => now()->toISOString()
                        ];
                    }
                    
                    $invitation->update($updateData);
                    
                    // Debug logging after update
                    Log::info('📅 [SCHEDULED_INVITATIONS] Invitation updated', [
                        'event_id' => $event->id,
                        'invitation_id' => $invitation->id,
                        'guest_id' => $guest->id,
                        'platform' => $platform,
                        'update_data' => $updateData,
                        'stored_external_id' => $invitation->fresh()->external_id,
                        'stored_delivery_details' => $invitation->fresh()->delivery_details
                    ]);
                    
                    if ($sent) {
                        $totalInvitationsSent++;
                    }
                }
        }
        
        // Log final results
        Log::info('📧 [INVITATION] Completed sending invitations', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'total_guests_processed' => $totalGuestsProcessed,
            'total_invitations_sent' => $totalInvitationsSent,
            'platforms_used' => $platforms
        ]);
    }

    private function generateUniqueInvitationToken(int $lengthBytes = 10): string
    {
        do {
            $token = bin2hex(random_bytes($lengthBytes));
        } while (Invitation::where('token', $token)->exists());
        return $token;
    }

    private function applyMessagePlaceholders(?string $message, Guest $guest, Event $event): string
    {
        $message = $message ?? '';
        $userTimezone = Auth::user()->timezone ?? 'UTC';
        $eventDateInTz = $event->start_date ? Carbon::parse($event->start_date)->setTimezone($userTimezone)->format('l, F j, Y g:i A') : '';
        $tzAbbrev = $userTimezone;
        try {
            $tzAbbrev = (new \DateTimeZone($userTimezone))->getName();
        } catch (\Throwable $e) {
            // keep fallback
        }
        $replacements = [
            '{name}' => $guest->name ?? '',
            '{event_name}' => $event->name ?? '',
            '{date}' => $eventDateInTz,
            '{timezone}' => $tzAbbrev,
        ];
        return strtr($message, $replacements);
    }

    /**
     * Generate default messages for all modes
     */
    public function generateDefaultMessages(array $eventData, array $guestListIds): array
    {
        $messages = [
            'general' => '',
            'group' => [],
            'per_guest' => []
        ];

        // Generate general message
        $generalResult = $this->generateAIMessage('general', $eventData);
        if ($generalResult['success']) {
            $messages['general'] = $generalResult['invitation_message'];
        }

        // Generate group messages
        foreach ($guestListIds as $listId) {
            $groupResult = $this->generateAIMessage('group', $eventData, $listId);
            if ($groupResult['success']) {
                $messages['group'][$listId] = $groupResult['invitation_message'];
            }
        }

        return $messages;
    }

    /**
     * Get the next step number
     */
    public function getNextStep(int $currentStep): ?int
    {
        return $currentStep < 4 ? $currentStep + 1 : null;
    }

    /**
     * Get the previous step number
     */
    public function getPreviousStep(int $currentStep): ?int
    {
        return $currentStep > 1 ? $currentStep - 1 : null;
    }

    /**
     * Check if user can access a specific step
     */
    public function canAccessStep(int $step): bool
    {
        // Step 1 is always accessible
        if ($step === 1) {
            return true;
        }

        // If we're editing a draft event, allow access to all steps
        if (Session::has('editing_draft_event_id')) {
            Log::info('Draft editing mode - allowing access to step ' . $step);
            return true;
        }

        // For step 2, only check if step 1 is valid
        if ($step === 2) {
            return $this->isStepValid(1);
        }

        // For step 3, check if step 1 is valid and we have some step 2 data
        if ($step === 3) {
            $step1Valid = $this->isStepValid(1);
            $step2Data = $this->getStep(2);
            $hasStep2Data = !empty($step2Data);
            
            Log::info('Step 3 access check', [
                'step1_valid' => $step1Valid,
                'has_step2_data' => $hasStep2Data,
                'step2_data' => $step2Data
            ]);
            
            return $step1Valid && $hasStep2Data;
        }

        // For step 4, check if step 1 is valid and we have some step 2 data
        if ($step === 4) {
            $step1Valid = $this->isStepValid(1);
            $step2Data = $this->getStep(2);
            $step3Data = $this->getStep(3);
            $hasStep2Data = !empty($step2Data);
            $hasStep3Data = !empty($step3Data);
            
            Log::info('Step 4 access check', [
                'step1_valid' => $step1Valid,
                'has_step2_data' => $hasStep2Data,
                'has_step3_data' => $hasStep3Data,
                'step2_data' => $step2Data,
                'step3_data' => $step3Data
            ]);
            
            return $step1Valid && $hasStep2Data && $hasStep3Data;
        }

        // For other steps, check if previous steps are completed
        for ($i = 1; $i < $step; $i++) {
            if (!$this->isStepValid($i)) {
                Log::info('Step validation failed for step ' . $i . ' when trying to access step ' . $step);
                return false;
            }
        }

        return true;
    }

    /**
     * Get the next available step the user should go to
     */
    public function getNextAvailableStep(): int
    {
        // If we're editing a draft, allow access to any step, but start from 1
        if (Session::has('editing_draft_event_id')) {
            return 1;
        }

        // Check each step in order
        for ($step = 1; $step <= 4; $step++) {
            if (!$this->canAccessStep($step)) {
                // Return the previous step if current step is not accessible
                return max(1, $step - 1);
            }
        }

        // If all steps are accessible, return step 4
        return 4;
    }

    /**
     * Extract event name with better fallbacks
     */
    private function extractEventName(array $eventData): string
    {
        // Try multiple possible keys for event name
        $possibleKeys = ['name', 'event_name', 'title', 'event_title'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key]) && $eventData[$key] !== 'Event') {
                return $eventData[$key];
            }
        }
        
        return 'Event';
    }

    /**
     * Extract event date with better fallbacks
     */
    private function extractEventDate(array $eventData): string
    {
        // Try multiple possible keys for event date
        $possibleKeys = ['start_date', 'event_date', 'date', 'event_start_date'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key]) && $eventData[$key] !== 'TBD') {
                // Try to format the date nicely
                try {
                    $date = \Carbon\Carbon::parse($eventData[$key]);
                    return $date->format('F j, Y \a\t g:i A');
                } catch (\Exception $e) {
                    return $eventData[$key];
                }
            }
        }
        
        return 'TBD';
    }

    /**
     * Extract additional information with better fallbacks
     */
    private function extractAdditionalInfo(array $eventData): string
    {
        // Try multiple possible keys for additional info
        $possibleKeys = ['additional_information', 'description', 'event_description', 'notes', 'event_notes'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key])) {
                return $eventData[$key];
            }
        }
        
        return '';
    }

    /**
     * Send scheduled invitations for an event using stored event data only.
     * This method is specifically designed for scheduled events and uses
     * only the data stored in the event database, not session data.
     */
    public function sendScheduledInvitationsForEvent(Event $event): void
    {
        Log::info('📅 [SCHEDULED_INVITATIONS] Starting scheduled invitations for event', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'event_status' => $event->status,
            'event_send_type' => $event->send_type,
            'scheduled_at' => $event->scheduled_at
        ]);

        // Load event relationships
        $event->load('guestLists.guests');
        
        // Get active guests for this event (excludes removed duplicates)
        $activeEventGuests = EventGuest::where('event_id', $event->id)
            ->where('status', EventGuest::STATUS_ACTIVE)
            ->with('guest')
            ->get();
        
        Log::info('📅 [SCHEDULED_INVITATIONS] Active guests for scheduled invitation sending', [
            'event_id' => $event->id,
            'active_guests_count' => $activeEventGuests->count(),
            'active_guest_ids' => $activeEventGuests->pluck('guest_id')->toArray()
        ]);
        
        // Use ONLY stored event data (no session data)
        $messageGeneral = $event->general_message;
        $messagesGroup = $event->group_messages ?? [];
        $messagesPerGuest = $event->per_guest_messages ?? [];
        $platforms = $event->invitation_platforms ?? ['email'];
        $excludedGuestIds = $event->excluded_guest_ids ?? [];
        
        Log::info('📅 [SCHEDULED_INVITATIONS] Data resolution for scheduled event', [
            'event_id' => $event->id,
            'general_message' => $messageGeneral ? 'has_content(' . strlen($messageGeneral) . ' chars)' : 'empty',
            'general_message_preview' => $messageGeneral ? substr($messageGeneral, 0, 100) . '...' : 'empty',
            'group_messages' => is_array($messagesGroup) ? 'has_content(' . count($messagesGroup) . ' items)' : 'empty',
            'per_guest_messages' => is_array($messagesPerGuest) ? 'has_content(' . count($messagesPerGuest) . ' items)' : 'empty',
            'platforms' => $platforms,
            'platforms_type' => gettype($platforms),
            'guest_lists_count' => $event->guestLists->count(),
            'total_guests_count' => $event->guestLists->sum(function($list) { return $list->guests->count(); })
        ]);

        // Check if we have any guests to send to
        if ($event->guestLists->isEmpty()) {
            Log::warning('📅 [SCHEDULED_INVITATIONS] No guest lists found for scheduled event', [
                'event_id' => $event->id,
                'event_name' => $event->name
            ]);
            return;
        }

        $totalGuestsProcessed = 0;
        $totalInvitationsSent = 0;

        // Send invitations only to active guests (excludes removed duplicates)
        foreach ($activeEventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            $totalGuestsProcessed++;
            
            // Find the guest list for this guest
            $guestList = $guest->guestList;
            
            // Determine message for this guest (per-guest > group > general)
            $message = $messagesPerGuest[$guest->id] ?? null;
            if (!$message && isset($guest->guest_group_id) && isset($messagesGroup[$guestList->id]) && is_array($messagesGroup[$guestList->id])) {
                    $message = $messagesGroup[$guestList->id][$guest->guest_group_id] ?? null;
                } elseif (!$message && isset($messagesGroup[$guestList->id]) && is_string($messagesGroup[$guestList->id])) {
                    $message = $messagesGroup[$guestList->id];
                }
                if (!$message) {
                    $message = $messageGeneral;
                }
                
                // Debug message selection
                Log::info('📅 [SCHEDULED_INVITATIONS] Message selection for guest', [
                    'event_id' => $event->id,
                    'guest_id' => $guest->id,
                    'guest_name' => $guest->name,
                    'guest_group_id' => $guest->guest_group_id,
                    'guest_list_id' => $guestList->id,
                    'per_guest_message' => $messagesPerGuest[$guest->id] ?? 'not_found',
                    'group_message' => isset($messagesGroup[$guestList->id]) ? 'available' : 'not_found',
                    'general_message' => $messageGeneral ? 'available' : 'not_found',
                    'selected_message' => $message ? 'has_content(' . strlen($message) . ' chars)' : 'empty'
                ]);

                // Fallback message for scheduled events when no message is found
                if (empty($message)) {
                    // Try to use a more meaningful fallback message
                    $message = $event->general_message ?? "You are invited to our event!";
                    
                    Log::info('📅 [SCHEDULED_INVITATIONS] Using fallback message', [
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'platforms' => $platforms,
                        'fallback_message' => $message,
                        'has_general_message' => !empty($event->general_message)
                    ]);
                }

                // Generate unique token per guest (shared across all platforms)
                $token = $this->generateUniqueInvitationToken();
                $inviteUrl = route('public.invite.show', ['token' => $token]);

                // Apply simple placeholders before sending (shared across platforms)
                $personalized = $this->applyMessagePlaceholders(
                    $message,
                    $guest,
                    $event
                );

                // Append invite URL to message (shared across platforms)
                $completeMessage = trim(($personalized ?? '') . "\n\n" . $inviteUrl);

                foreach ($platforms as $platform) {
                    // Debug platform selection
                    Log::info('📅 [SCHEDULED_INVITATIONS] Processing platform for guest', [
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'guest_name' => $guest->name,
                        'platform' => $platform,
                        'guest_email' => $guest->email,
                        'guest_phone' => $guest->phone
                    ]);
                    
                    // Ensure we have actual message content, not just the URL
                    if (empty(trim($personalized ?? ''))) {
                        Log::warning('📅 [SCHEDULED_INVITATIONS] Empty personalized message for guest', [
                            'event_id' => $event->id,
                            'guest_id' => $guest->id,
                            'guest_name' => $guest->name,
                            'platform' => $platform,
                            'original_message' => $message,
                            'personalized_message' => $personalized
                        ]);
                    }
                    
                    Log::info('📅 [SCHEDULED_INVITATIONS] Sending scheduled invitation', [
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'guest_name' => $guest->name,
                        'platform' => $platform,
                        'message_length' => strlen($completeMessage),
                        'message_preview' => substr($completeMessage, 0, 100) . '...'
                    ]);
                    
                    $recipient = $platform === 'email' ? ($guest->email ?: '') : ($guest->phone ?: '');
                    if (!$recipient) {
                        Log::warning('📅 [SCHEDULED_INVITATIONS] Skipping guest without recipient for platform', [
                            'event_id' => $event->id,
                            'guest_id' => $guest->id,
                            'guest_name' => $guest->name,
                            'platform' => $platform,
                            'guest_email' => $guest->email,
                            'guest_phone' => $guest->phone
                        ]);
                        continue;
                    }

                    // Create invitation record (pending)
                    $invitation = Invitation::create([
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                        'token' => $token,
                        'channel' => $platform,
                        'recipient' => $recipient,
                        'message' => $completeMessage,
                        'status' => 'pending',
                    ]);

                    // Send via channel
                    $sent = false;
                    $twilioResponse = null;
                    try {
                        if ($platform === 'email') {
                            // Use rich email template
                            \Mail::to($recipient)->send(new \App\Mail\EventInvitationMail(
                                $event,
                                $guest,
                                $personalized,
                                $inviteUrl
                            ));
                            $sent = true;
                        } elseif ($platform === 'whatsapp') {
                            $twilio = app(\App\Services\TwilioService::class);
                            $twilioResponse = $twilio->sendWhatsAppMessage($recipient, $completeMessage);
                            $sent = $twilioResponse['success'] ?? false;
                            
                            // Debug logging for Twilio response
                            Log::info('📅 [SCHEDULED_INVITATIONS] Twilio response captured', [
                                'event_id' => $event->id,
                                'invitation_id' => $invitation->id,
                                'guest_id' => $guest->id,
                                'platform' => $platform,
                                'twilio_response' => $twilioResponse,
                                'response_type' => gettype($twilioResponse),
                                'has_message_sid' => isset($twilioResponse['message_sid']),
                                'message_sid' => $twilioResponse['message_sid'] ?? 'NOT_SET'
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('📅 [SCHEDULED_INVITATIONS] Failed sending scheduled invitation', [
                            'event_id' => $event->id,
                            'invitation_id' => $invitation->id,
                            'error' => $e->getMessage(),
                        ]);
                        $sent = false;
                    }

                    // Update status and store Twilio details
                    $updateData = [
                        'status' => $sent ? 'sent' : 'failed',
                        'sent_at' => $sent ? Carbon::now() : null,
                    ];
                    
                    // Store Twilio response details for WhatsApp
                    if ($platform === 'whatsapp' && $twilioResponse) {
                        $updateData['external_id'] = $twilioResponse['message_sid'] ?? null;
                        $updateData['delivery_details'] = [
                            'twilio_response' => $twilioResponse,
                            'twilio_status' => $twilioResponse['status'] ?? 'unknown',
                            'sent_at' => now()->toISOString()
                        ];
                        
                        // Debug logging for update data
                        Log::info('📅 [SCHEDULED_INVITATIONS] WhatsApp update data prepared', [
                            'event_id' => $event->id,
                            'invitation_id' => $invitation->id,
                            'guest_id' => $guest->id,
                            'platform' => $platform,
                            'update_data' => $updateData,
                            'external_id' => $updateData['external_id'],
                            'delivery_details' => $updateData['delivery_details']
                        ]);
                    } else {
                        // Debug logging for non-WhatsApp or missing response
                        Log::info('📅 [SCHEDULED_INVITATIONS] Update data prepared (no WhatsApp SID)', [
                            'event_id' => $event->id,
                            'invitation_id' => $invitation->id,
                            'guest_id' => $guest->id,
                            'platform' => $platform,
                            'is_whatsapp' => $platform === 'whatsapp',
                            'has_twilio_response' => !empty($twilioResponse),
                            'twilio_response' => $twilioResponse,
                            'update_data' => $updateData
                        ]);
                    }
                    
                    $invitation->update($updateData);
                    
                    if ($sent) {
                        $totalInvitationsSent++;
                    }
                }
        }

        Log::info('📅 [SCHEDULED_INVITATIONS] Completed scheduled invitations', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'total_guests_processed' => $totalGuestsProcessed,
            'total_invitations_sent' => $totalInvitationsSent,
            'platforms_used' => $platforms
        ]);
    }
}