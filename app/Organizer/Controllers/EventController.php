<?php

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Event;
use App\Shared\Models\GuestList;
use App\Organizer\Services\EventCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use AuthorizesRequests;

    private $eventCreationService;

    public function __construct(EventCreationService $eventCreationService)
    {
        $this->eventCreationService = $eventCreationService;
    }

    public function index()
    {
        // Check and update event statuses when page is loaded
        $this->updateEventStatuses();
        
        $activeEvents = Event::where('user_id', Auth::id())
            ->where('status', '!=', 'completed')
            ->orderBy('start_date', 'asc') // Order by start date for active events
            ->get();
            
        $completedEvents = Event::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->orderBy('start_date', 'desc') // Order by start date descending for completed events
            ->get();
            
        // Log for debugging
        Log::info('Events index loaded', [
            'user_id' => Auth::id(),
            'total_active_events' => $activeEvents->count(),
            'total_completed_events' => $completedEvents->count(),
            'active_event_statuses' => $activeEvents->pluck('status')->toArray()
        ]);
        
        return view('organizer.events.index', compact('activeEvents', 'completedEvents'));
    }

    /**
     * Update event statuses based on current time
     * This method checks all user's events and marks them as completed if needed
     */
    private function updateEventStatuses()
    {
        $userEvents = Event::where('user_id', Auth::id())
            ->where('status', '!=', 'completed')
            ->get();
        
        $runningCount = 0;
        $completedCount = 0;
        $now = now();
        
        foreach ($userEvents as $event) {
            $originalStatus = $event->status;
            
            // Check if event should be marked as running
            if ($event->status !== 'running' && $event->isOngoing()) {
                $event->markAsRunning();
                $runningCount++;
                
                Log::info('📅 [PAGE_LOAD_RUNNING] Event marked as running on page load', [
                    'user_id' => Auth::id(),
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'previous_status' => $originalStatus,
                    'start_date' => $event->start_date->toISOString(),
                    'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                    'update_time' => $now->toISOString(),
                ]);
            }
            // Check if event should be marked as completed
            elseif ($event->isCompleted()) {
                $event->markAsCompleted();
                $completedCount++;
                
                Log::info('📅 [PAGE_LOAD_COMPLETION] Event marked as completed on page load', [
                    'user_id' => Auth::id(),
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'previous_status' => $originalStatus,
                    'start_date' => $event->start_date->toISOString(),
                    'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                    'completion_time' => $now->toISOString(),
                ]);
            }
        }
        
        if ($runningCount > 0 || $completedCount > 0) {
            Log::info('📅 [PAGE_LOAD_STATUS_UPDATE] Updated event statuses', [
                'user_id' => Auth::id(),
                'events_marked_running' => $runningCount,
                'events_marked_completed' => $completedCount
            ]);
        }
    }

    /**
     * Display completed events
     */
    public function completed()
    {
        // Check and update event statuses when page is loaded
        $this->updateEventStatuses();
        
        $completedEvents = Event::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->orderBy('start_date', 'desc')
            ->paginate(20);
        
        // Use EventGuestService to get accurate guest counts for completed events
        $eventGuestService = app(\App\Services\EventGuestService::class);
        
        // Load active guests for each completed event to calculate accurate statistics
        foreach ($completedEvents as $event) {
            $activeEventGuests = $eventGuestService->getActiveGuestsForEvent($event);
            $event->active_guests_count = $activeEventGuests->count();
            $event->active_guests = $activeEventGuests;
        }
            
        return view('organizer.events.completed', compact('completedEvents'));
    }

    /**
     * Manually mark an event as completed
     */
    public function markAsCompleted(Event $event)
    {
        // Ensure the user owns this event
        if ($event->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You can only complete your own events.'
            ], 403);
        }

        // Check if already completed
        if ($event->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Event is already completed.'
            ]);
        }

        try {
            $event->markAsCompleted();
            
            Log::info('📅 [MANUAL_COMPLETION] Event manually marked as completed', [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'event_name' => $event->name,
                'start_date' => $event->start_date->toISOString(),
                'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                'completion_time' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event marked as completed successfully.',
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'status' => $event->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('📅 [MANUAL_COMPLETION_ERROR] Failed to mark event as completed', [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark event as completed. Please try again.'
            ], 500);
        }
    }

    /**
     * Manually mark an event as running
     */
    public function markAsRunning(Event $event)
    {
        // Ensure the user owns this event
        if ($event->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You can only manage your own events.'
            ], 403);
        }

        // Check if already completed
        if ($event->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot start a completed event.'
            ]);
        }

        // Check if already running
        if ($event->status === 'running') {
            return response()->json([
                'success' => false,
                'message' => 'Event is already running.'
            ]);
        }

        try {
            $previousStatus = $event->status;
            $event->markAsRunning();
            
            Log::info('📅 [MANUAL_RUNNING] Event manually marked as running', [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'event_name' => $event->name,
                'previous_status' => $previousStatus,
                'new_status' => 'running',
                'start_date' => $event->start_date->toISOString(),
                'end_date' => $event->end_date ? $event->end_date->toISOString() : null,
                'update_time' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event marked as running successfully.',
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'status' => $event->status,
                    'previous_status' => $previousStatus
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('📅 [MANUAL_RUNNING_ERROR] Failed to mark event as running', [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark event as running. Please try again.'
            ], 500);
        }
    }

    public function create()
    {
        // Check if we're editing a draft event
        if (Session::has('editing_draft_event_id')) {
            $draftEventId = Session::get('editing_draft_event_id');
            $draftEvent = Event::find($draftEventId);
            
            // Verify the draft event exists and belongs to the user
            if ($draftEvent && $draftEvent->user_id === Auth::id() && $draftEvent->status === 'draft') {
                // Pre-populate session with draft data
                $this->prepopulateDraftData($draftEvent);
                
                // Log the loaded data for debugging
                Log::info('Loading draft event data', [
                    'user_id' => Auth::id(),
                    'event_id' => $draftEventId,
                    'continuing_draft' => true
                ]);
                
                // Redirect to step 1 with draft data loaded and update mode
                return redirect()->route('organizer.events.create.step1', ['mode' => 'update'])
                    ->with('success', 'Continuing to edit your draft event: ' . $draftEvent->name);
            } else {
                // Invalid draft event, clear session and start fresh
                Session::forget('editing_draft_event_id');
            }
        }
        
        // For new event creation, always start fresh and redirect to step 1
        $this->startFreshEventCreation();
        
        // Log that we're starting fresh
        Log::info('Starting fresh event creation', [
            'user_id' => Auth::id(),
            'session_cleared' => true
        ]);
        
        // Redirect to step 1 for new event creation
        return redirect()->route('organizer.events.create.step1');
    }

    /**
     * Start fresh event creation by clearing all session data
     */
    private function startFreshEventCreation()
    {
        // Clear all event creation session data
        $this->eventCreationService->clearSession();
        Session::forget('editing_draft_event_id');
        Session::forget('step_data');
        
        // Clear individual step session keys
        Session::forget('event_creation_step_1');
        Session::forget('event_creation_step_2');
        Session::forget('event_creation_step_3');
        Session::forget('event_creation_step_4');
    }

    // -------------------- Multi-Step Event Creation Flow --------------------

    /**
     * Step 1: Event Details
     */
    public function createStep1()
    {
        // Get data from multiple sources with fallbacks
        $data = $this->eventCreationService->getStep(1);
        $allStepsData = $this->eventCreationService->getAllStepsData();
        
        // Also check for flash data from previous steps
        $flashData = Session::get('step_data', []);
        
        // Merge all data sources with priority: flash data > step data > all steps data
        // But only merge non-empty values from flash data to prevent empty values from overriding existing data
        $mergedData = $allStepsData;
        
        // Merge step 1 specific data first
        if (!empty($data)) {
            $mergedData = array_merge($mergedData, $data);
        }
        
        // Merge flash data, but only non-empty values
        if (!empty($flashData)) {
            foreach ($flashData as $key => $value) {
                // Only merge non-empty values to prevent empty values from overriding existing data
                if (!empty($value) || $value === '0' || $value === 0) {
                    $mergedData[$key] = $value;
                }
            }
        }
        
        // Debug step 1 data loading
        Log::info('Loading step 1 data', [
            'user_id' => Auth::id(),
            'step1_data' => $data,
            'all_steps_data' => $allStepsData,
            'flash_data' => $flashData,
            'merged_data' => $mergedData,
            'has_editing_draft' => Session::has('editing_draft_event_id'),
            'editing_draft_id' => Session::get('editing_draft_event_id'),
            'session_keys' => [
                'step1' => Session::get('event_creation_step_1'),
                'step2' => Session::get('event_creation_step_2'),
                'step3' => Session::get('event_creation_step_3'),
                'step4' => Session::get('event_creation_step_4')
            ]
        ]);
        
        // If we're editing a draft, ensure we have the session data
        if (Session::has('editing_draft_event_id')) {
            Log::info('Loading step 1 data for draft editing', [
                'user_id' => Auth::id(),
                'step1_data' => $data,
                'all_steps_data' => $allStepsData
            ]);
        }
        
        return view('organizer.events.create-step1', compact('data', 'allStepsData', 'mergedData'));
    }

    private function validateEndDate($request)
    {
        return [
            'nullable',
            'date',
            'after:start_date',
            function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->start_date) {
                    $startDate = new \DateTime($request->start_date);
                    $endDate = new \DateTime($value);
                    $interval = $startDate->diff($endDate);
                    $minutesDiff = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                    
                    // Minimum duration: 15 minutes (configurable)
                    $minDurationMinutes = config('app.min_event_duration', 15);
                    
                    if ($minutesDiff < $minDurationMinutes) {
                        $fail("End time must be at least {$minDurationMinutes} minutes after start time.");
                    }
                }
            }
        ];
    }

    public function processStep1(Request $request)
    {
        // Handle both JSON and form data
        $inputData = $request->all();
        
        // If it's a JSON request, get the JSON content
        if ($request->isJson() || $request->header('Content-Type') === 'application/json') {
            $inputData = $request->json()->all();
        }
        
        // Debug the incoming data
        Log::info('🔍 [STEP1] Incoming request data', [
            'user_id' => Auth::id(),
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'input_data' => $inputData,
            'request_all' => $request->all()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => $this->validateEndDate($request),
            'additional_information' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Convert dates from user timezone to UTC
        $userTimezone = Auth::user()->timezone ?? 'UTC';
        
        if (!empty($validated['start_date'])) {
            $startDateInUserTz = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['start_date'], $userTimezone);
            $validated['start_date'] = $startDateInUserTz->utc()->format('Y-m-d H:i:s');
            
            Log::info('🕐 [TIMEZONE] Converted start date to UTC', [
                'user_id' => Auth::id(),
                'user_timezone' => $userTimezone,
                'input_start_date' => $request->input('start_date'),
                'utc_start_date' => $validated['start_date'],
            ]);
        }
        
        if (!empty($validated['end_date'])) {
            $endDateInUserTz = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['end_date'], $userTimezone);
            $validated['end_date'] = $endDateInUserTz->utc()->format('Y-m-d H:i:s');
            
            Log::info('🕐 [TIMEZONE] Converted end date to UTC', [
                'user_id' => Auth::id(),
                'user_timezone' => $userTimezone,
                'input_end_date' => $request->input('end_date'),
                'utc_end_date' => $validated['end_date'],
            ]);
        }

        // Debug Step 1 data before storing
        Log::info('🔍 [STEP1] Processing step 1 data', [
            'user_id' => Auth::id(),
            'validated_data' => $validated,
            'request_data' => $request->all(),
            'has_name' => !empty($validated['name']),
            'has_start_date' => !empty($validated['start_date']),
            'has_location' => !empty($validated['location'])
        ]);

        $this->eventCreationService->storeStep(1, $validated);

        // Debug Step 1 data after storing
        $storedData = $this->eventCreationService->getStep(1);
        Log::info('🔍 [STEP1] Step 1 data after storing', [
            'user_id' => Auth::id(),
            'stored_data' => $storedData,
            'session_key' => Session::get('event_creation_step_1')
        ]);

        // Preserve mode parameter for redirects
        $mode = $request->input('mode');
        $redirectUrl = $mode ? route('organizer.events.create.step2', ['mode' => $mode]) : route('organizer.events.create.step2');
        
        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->header('X-Form-Submission')) {
            return response()->json([
                'success' => true,
                'redirect' => $redirectUrl,
                'message' => 'Step 1 completed successfully'
            ]);
        }

        return redirect($redirectUrl);
    }

    /**
     * Step 2: Event Settings
     */
    public function createStep2()
    {
        if (!$this->eventCreationService->canAccessStep(2)) {
            $mode = request()->get('mode');
            $redirectUrl = $mode ? route('organizer.events.create.step1', ['mode' => $mode]) : route('organizer.events.create.step1');
            return redirect($redirectUrl)
                ->with('error', 'Please complete step 1 first.');
        }

        $data = $this->eventCreationService->getStep(2);
        $step1Data = $this->eventCreationService->getStep(1);
        $allStepsData = $this->eventCreationService->getAllStepsData();
        
        // Also check for flash data from previous steps
        $flashData = Session::get('step_data', []);
        
        // Merge all data sources with priority: flash data > step data > all steps data
        // But only merge non-empty values from flash data to prevent empty values from overriding existing data
        $mergedData = $allStepsData;
        
        // Merge step 2 specific data first
        if (!empty($data)) {
            $mergedData = array_merge($mergedData, $data);
        }
        
        // Merge flash data, but only non-empty values.
        // Important: Do NOT override step 2 booleans from flash if step 2 data exists for them.
        if (!empty($flashData)) {
            foreach ($flashData as $key => $value) {
                // For toggles, prefer latest step 2 data when available
                if (in_array($key, ['qr_checkin_enabled', 'rsvp_enabled'], true)) {
                    if (array_key_exists($key, $data)) {
                        // Keep $data value; skip flash
                        continue;
                    }
                }
                // Only merge non-empty values to prevent empty values from overriding existing data
                if (!empty($value) || $value === '0' || $value === 0 || is_bool($value)) {
                    $mergedData[$key] = $value;
                }
            }
        }
        
        $guestLists = GuestList::where('user_id', Auth::id())->get();

        // Debug guest lists loading
        Log::info('Loading step 2 with guest lists', [
            'user_id' => Auth::id(),
            'guest_lists_count' => $guestLists->count(),
            'guest_lists' => $guestLists->pluck('name', 'id')->toArray(),
            'step2_data' => $data,
            'step1_data' => $step1Data,
            'all_steps_data' => $allStepsData,
            'flash_data' => $flashData,
            'merged_data' => $mergedData,
            'session_keys' => [
                'step1' => Session::get('event_creation_step_1'),
                'step2' => Session::get('event_creation_step_2'),
                'step3' => Session::get('event_creation_step_3'),
                'step4' => Session::get('event_creation_step_4')
            ]
        ]);

        // If we're editing a draft, ensure we have the session data
        if (Session::has('editing_draft_event_id')) {
            Log::info('Loading step 2 data for draft editing', [
                'user_id' => Auth::id(),
                'step2_data' => $data,
                'step1_data' => $step1Data,
                'all_steps_data' => $allStepsData
            ]);
        }

        return view('organizer.events.create-step2', compact('data', 'step1Data', 'guestLists', 'allStepsData', 'mergedData'));
    }

    public function processStep2(Request $request)
    {
        try {
            $validated = $request->validate([
                'qr_checkin_enabled' => 'nullable|boolean',
                'rsvp_enabled' => 'nullable|boolean',
                'invitation_platforms' => 'required|array|min:1',
                'invitation_platforms.*' => 'in:email,whatsapp',
                'guest_list_ids' => 'required|array|min:1',
                'guest_list_ids.*' => 'exists:guest_lists,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->hasHeader('X-Form-Submission')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        // Ensure boolean fields are properly set, preserving prior values if absent
        $existingStep2 = $this->eventCreationService->getStep(2);
        $qrRaw = $request->input('qr_checkin_enabled', null);
        $rsvpRaw = $request->input('rsvp_enabled', null);
        $validated['qr_checkin_enabled'] = $qrRaw !== null
            ? in_array($qrRaw, [true, 1, '1', 'on'], true)
            : (bool)($existingStep2['qr_checkin_enabled'] ?? false);
        $validated['rsvp_enabled'] = $rsvpRaw !== null
            ? in_array($rsvpRaw, [true, 1, '1', 'on'], true)
            : (bool)($existingStep2['rsvp_enabled'] ?? false);

        // Debug step 2 data
        Log::info('🔍 [STEP2] Processing step 2 data', [
            'validated' => $validated,
            'guest_list_ids' => $validated['guest_list_ids'] ?? 'not_set',
            'selected_lists' => $validated['guest_list_ids'] ?? [],
            'list_count' => count($validated['guest_list_ids'] ?? []),
            'invitation_platforms' => $validated['invitation_platforms'] ?? 'not_set',
            'platforms_count' => count($validated['invitation_platforms'] ?? []),
            'raw_request' => $request->all()
        ]);

        $this->eventCreationService->storeStep(2, $validated);

        // Preserve mode parameter for redirects
        $mode = $request->input('mode');
        $redirectUrl = $mode ? route('organizer.events.create.step3', ['mode' => $mode]) : route('organizer.events.create.step3');
        
        // Check if this is an AJAX request
        if ($request->expectsJson() || $request->hasHeader('X-Form-Submission')) {
            return response()->json([
                'success' => true,
                'message' => 'Step 2 completed successfully!',
                'redirect' => $redirectUrl
            ]);
        }

        return redirect($redirectUrl);
    }

    /**
     * Step 3: Message Customization & AI Assistance
     */
    public function createStep3()
    {
        if (!$this->eventCreationService->canAccessStep(3)) {
            // Determine which step the user should go to
            $nextStep = $this->eventCreationService->getNextAvailableStep();
            
            $mode = request()->get('mode');
            $redirectUrl = $mode ? route('organizer.events.create.step' . $nextStep, ['mode' => $mode]) : route('organizer.events.create.step' . $nextStep);
            return redirect($redirectUrl)
                ->with('error', 'Please complete the previous steps first.');
        }

        $data = $this->eventCreationService->getStep(3);
        // If editing a draft, prefer the DB value for general_message to avoid stale session
        if (Session::has('editing_draft_event_id')) {
            $draftId = Session::get('editing_draft_event_id');
            $draftEvent = \App\Shared\Models\Event::where('id', $draftId)
                ->where('user_id', Auth::id())
                ->where('status', 'draft')
                ->first();
            if ($draftEvent) {
                $data['general_message'] = $draftEvent->general_message ?? '';
                if (!empty($draftEvent->per_guest_messages) && is_array($draftEvent->per_guest_messages)) {
                    $data['per_guest_messages'] = $draftEvent->per_guest_messages;
                }
                if (!empty($draftEvent->group_messages) && is_array($draftEvent->group_messages)) {
                    $data['group_messages'] = $draftEvent->group_messages;
                }
            }
        }
        $allData = $this->eventCreationService->getAllStepsData();
        
        // Debug step 3 data loading
        Log::info('🔍 [STEP3] Loading step 3 data', [
            'user_id' => Auth::id(),
            'step3_data' => $data,
            'step3_data_keys' => array_keys($data),
            'group_messages' => $data['group_messages'] ?? 'not_set',
            'per_guest_messages' => $data['per_guest_messages'] ?? 'not_set',
            'general_message' => $data['general_message'] ?? 'not_set',
            'all_steps_data' => $allData,
            'has_editing_draft' => Session::has('editing_draft_event_id'),
            'editing_draft_id' => Session::get('editing_draft_event_id')
        ]);
        
        // Debug the guest list data
        Log::info('🔍 [STEP3] Debug guest list data', [
            'allData' => $allData,
            'guest_list_ids' => $allData['guest_list_ids'] ?? 'not_set',
            'step2_data' => $this->eventCreationService->getStep(2)
        ]);
        
        $guestData = $this->eventCreationService->getGuestsFromLists($allData['guest_list_ids'] ?? []);
        $organizedGuestData = $this->eventCreationService->getOrganizedGuestData($allData['guest_list_ids'] ?? []);
        
        Log::info('🔍 [STEP3] Guest data result', [
            'guestData_count' => count($guestData),
            'guestData_keys' => array_keys($guestData),
            'organizedGuestData_count' => count($organizedGuestData),
            'organizedGuestData_keys' => array_keys($organizedGuestData)
        ]);

        return view('organizer.events.create-step3', compact('data', 'allData', 'guestData', 'organizedGuestData'));
    }

    public function processStep3(Request $request)
    {
        // Process complex field names for group_messages and per_guest_messages
        $groupMessages = [];
        $perGuestMessages = [];
        
        foreach ($request->all() as $key => $value) {
            // Handle group_messages[listId][groupId] format
            if (preg_match('/^group_messages\[(\d+)\]\[(\d+)\]$/', $key, $matches)) {
                $listId = $matches[1];
                $groupId = $matches[2];
                if (!isset($groupMessages[$listId])) {
                    $groupMessages[$listId] = [];
                }
                $groupMessages[$listId][$groupId] = $value;
            }
            // Handle per_guest_messages[guestId] format
            elseif (preg_match('/^per_guest_messages\[(\d+)\]$/', $key, $matches)) {
                $guestId = $matches[1];
                $perGuestMessages[$guestId] = $value;
            }
        }
        
        $validated = [
            'general_message' => $request->input('general_message', ''),
            'group_messages' => $groupMessages,
            'per_guest_messages' => $perGuestMessages,
            'ai_generated' => $request->has('ai_generated'),
            'step3_completed' => true
        ];

        Log::info('🔍 [STEP3] Processing step 3 data', [
            'user_id' => Auth::id(),
            'validated_data' => $validated,
            'group_messages_count' => count($groupMessages),
            'per_guest_messages_count' => count($perGuestMessages),
            'request_data_keys' => array_keys($request->all())
        ]);

        $this->eventCreationService->storeStep(3, $validated);

        // Preserve mode parameter for redirects
        $mode = $request->input('mode');
        $redirectUrl = $mode ? route('organizer.events.create.step4', ['mode' => $mode]) : route('organizer.events.create.step4');
        
        return redirect($redirectUrl);
    }

    /**
     * Step 4: Send Invitations
     */
    public function createStep4()
    {
        // Add detailed logging for step 4 access
        $step1Valid = $this->eventCreationService->isStepValid(1);
        $step2Valid = $this->eventCreationService->isStepValid(2);
        $step3Valid = $this->eventCreationService->isStepValid(3);
        $canAccess = $this->eventCreationService->canAccessStep(4);
        
        Log::info('Step 4 access attempt', [
            'user_id' => Auth::id(),
            'step1_valid' => $step1Valid,
            'step2_valid' => $step2Valid,
            'step3_valid' => $step3Valid,
            'can_access_step4' => $canAccess,
            'step1_data' => $this->eventCreationService->getStep(1),
            'step2_data' => $this->eventCreationService->getStep(2),
            'step3_data' => $this->eventCreationService->getStep(3),
        ]);
        
        if (!$canAccess) {
            // Determine which step the user should go to
            $nextStep = $this->eventCreationService->getNextAvailableStep();
            
            Log::info('Step 4 access denied, redirecting to step ' . $nextStep, [
                'user_id' => Auth::id(),
                'can_access_step_4' => false,
                'redirecting_to_step' => $nextStep
            ]);
            
            $mode = request()->get('mode');
            $redirectUrl = $mode ? route('organizer.events.create.step' . $nextStep, ['mode' => $mode]) : route('organizer.events.create.step' . $nextStep);
            return redirect($redirectUrl)
                ->with('error', 'Please complete the previous steps first.');
        }

        $data = $this->eventCreationService->getStep(4);
        $allData = $this->eventCreationService->getAllStepsData();
        
        // Also check for flash data from previous steps
        $flashData = Session::get('step_data', []);
        
        // Merge all data sources with priority: flash data > step data > all steps data
        // But only merge non-empty values from flash data to prevent empty values from overriding existing data
        $mergedData = $allData;
        
        // Merge step 4 specific data first
        if (!empty($data)) {
            $mergedData = array_merge($mergedData, $data);
        }
        
        // Merge flash data, but only non-empty values
        if (!empty($flashData)) {
            foreach ($flashData as $key => $value) {
                // Only merge non-empty values to prevent empty values from overriding existing data
                if (!empty($value) || $value === '0' || $value === 0 || is_bool($value)) {
                    $mergedData[$key] = $value;
                }
            }
        }

        // Calculate total guests from selected guest lists
        $totalGuests = 0;
        $guestLists = collect();
        
        if (!empty($mergedData['guest_list_ids']) && is_array($mergedData['guest_list_ids'])) {
            $guestLists = \App\Shared\Models\GuestList::whereIn('id', $mergedData['guest_list_ids'])
                ->where('user_id', Auth::id())
                ->with(['guests', 'guestGroups'])
                ->get();
            
            $totalGuests = $guestLists->sum(function ($guestList) {
                return $guestList->guests->count();
            });
        }

        // Validate that we have message data from Step 3 (any type)
        // But be more lenient - allow proceeding even without messages
        $hasMessages = !empty($mergedData['general_message'])
            || (!empty($mergedData['group_messages']) && is_array($mergedData['group_messages']))
            || (!empty($mergedData['per_guest_messages']) && is_array($mergedData['per_guest_messages']));

        // Log the message validation for debugging
        Log::info('Step 4 data loading', [
            'user_id' => Auth::id(),
            'has_messages' => $hasMessages,
            'general_message' => !empty($mergedData['general_message']),
            'group_messages' => !empty($mergedData['group_messages']),
            'per_guest_messages' => !empty($mergedData['per_guest_messages']),
            'guest_list_ids' => $mergedData['guest_list_ids'] ?? [],
            'total_guests' => $totalGuests,
            'guest_lists_count' => $guestLists->count(),
            'all_data_keys' => array_keys($mergedData),
            'flash_data' => $flashData,
            'merged_data' => $mergedData
        ]);

        // Don't redirect back - allow proceeding even without messages
        // Users can add messages later or use default messages

        return view('organizer.events.create-step4', compact('data', 'allData', 'mergedData', 'totalGuests', 'guestLists'));
    }

    public function processStep4(Request $request)
    {
        $allData = $this->eventCreationService->getAllStepsData();

        $rules = [
            'send_type' => 'required|in:now,scheduled',
            'scheduled_at' => 'nullable|date|required_if:send_type,scheduled',
        ];

        if (!empty($allData['start_date'])) {
            $eventStart = \Carbon\Carbon::parse($allData['start_date'])->toDateTimeString();
            $rules['scheduled_at'] .= '|before:' . $eventStart;
        }

        $messages = [
            'scheduled_at.before' => 'Scheduled time must be before the event start time.',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ];

        try {
            $validated = $request->validate($rules, $messages);
            
            // Custom validation for scheduled_at timezone handling
            if (($validated['send_type'] ?? null) === 'scheduled' && !empty($validated['scheduled_at'])) {
                $userTimezone = Auth::user()->timezone ?? 'UTC';
                $inputTime = $validated['scheduled_at'];
                
                // Create Carbon instance from input time (interpreted as user's local time)
                $userTime = \Carbon\Carbon::parse($inputTime);
                
                // Convert to UTC for comparison with current time
                $utcTime = $userTime->copy();
                if ($userTimezone !== 'UTC') {
                    $timezone = new \DateTimeZone($userTimezone);
                    $offset = $timezone->getOffset($userTime) / 3600; // Convert seconds to hours
                    $utcTime = $userTime->subHours($offset);
                }
                
                // Check if the time is in the future (in UTC)
                $now = \Carbon\Carbon::now('UTC');
                if ($utcTime->lte($now)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        response()->json([
                            'success' => false,
                            'message' => 'Scheduled time must be in the future.',
                            'errors' => ['scheduled_at' => ['Scheduled time must be in the future.']]
                        ], 422)
                    );
                }
                
                // Update validated data with UTC time for storage
                $validated['scheduled_at'] = $utcTime->toDateTimeString();
                
                Log::info('🕐 [TIMEZONE] Converted scheduled time to UTC', [
                    'user_id' => Auth::id(),
                    'user_timezone' => $userTimezone,
                    'input_time' => $inputTime,
                    'utc_time' => $utcTime->toDateTimeString(),
                    'timezone_offset_hours' => $userTimezone !== 'UTC' ? $offset : 0
                ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->hasHeader('X-Form-Submission')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }



        // Ensure essential Step 1 data still exists (session can expire)
        $allDataCheck = $this->eventCreationService->getAllStepsData();
        if (empty($allDataCheck['name']) || empty($allDataCheck['start_date'])) {
            if ($request->expectsJson() || $request->hasHeader('X-Form-Submission')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session expired or is incomplete. Please re-enter event details (Step 1).',
                    'redirect' => route('organizer.events.create.step1')
                ], 400);
            }
            return redirect()->route('organizer.events.create.step1')
                ->with('error', 'Your session expired or is incomplete. Please re-enter event details (Step 1).');
        }

        // Extra guard in case of timezone mismatches
        if (($validated['send_type'] ?? null) === 'scheduled' && !empty($validated['scheduled_at']) && !empty($allData['start_date'])) {
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);
            $eventStartAt = \Carbon\Carbon::parse($allData['start_date']);
            if ($scheduledAt->gte($eventStartAt)) {
                if ($request->expectsJson() || $request->hasHeader('X-Form-Submission')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled time must be before the event start time.',
                        'errors' => ['scheduled_at' => ['Scheduled time must be before the event start time.']]
                    ], 422);
                }
                return back()
                    ->withErrors(['scheduled_at' => 'Scheduled time must be before the event start time.'])
                    ->withInput();
            }

            // Enforce at least one minute before start (strictly less than start)
            $eventStartMinusOne = $eventStartAt->copy()->subMinute();
            if ($scheduledAt->gt($eventStartMinusOne)) {
                if ($request->expectsJson() || $request->hasHeader('X-Form-Submission')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled time must be at least one minute before the event start time.',
                        'errors' => ['scheduled_at' => ['Scheduled time must be at least one minute before the event start time.']]
                    ], 422);
                }
                return back()
                    ->withErrors(['scheduled_at' => 'Scheduled time must be at least one minute before the event start time.'])
                    ->withInput();
            }
        }

        $this->eventCreationService->storeStep(4, $validated);

        // Capture all step data BEFORE event creation clears the session
        $allDataSnapshot = $this->eventCreationService->getAllStepsData();

        // Create the event
        $event = $this->eventCreationService->createEvent();

        // Clear any editing draft session variables
        Session::forget('editing_draft_event_id');

        // Send invitations according to selection
        if ($validated['send_type'] === 'now') {
            app(\App\Organizer\Services\EventCreationService::class)->sendInvitationsForEvent($event, $allDataSnapshot);
        }
        // Note: For scheduled events, the job is already dispatched by EventCreationService::createEvent()

        // Check if this is an AJAX request
        if ($request->expectsJson() || $request->hasHeader('X-Form-Submission')) {
            return response()->json([
                'success' => true,
                'message' => 'Event created successfully! Invitations ' . ($validated['send_type'] === 'now' ? 'are being sent.' : 'will be sent at the scheduled time.'),
                'redirect' => route('organizer.events.show', $event)
            ]);
        }

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Event created successfully! Invitations ' . ($validated['send_type'] === 'now' ? 'are being sent.' : 'will be sent at the scheduled time.'));
    }

    /**
     * Auto-fix empty guest list selection by selecting all available guest lists
     */
    public function autoFixGuestLists(Request $request)
    {
        try {
            $userGuestLists = \App\Shared\Models\GuestList::where('user_id', Auth::id())
                ->pluck('id')
                ->toArray();
                
            if (empty($userGuestLists)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No guest lists found. Please create guest lists first.'
                ]);
            }
            
            // Update step 2 data with all available guest lists
            $step2Data = $this->eventCreationService->getStep(2);
            $step2Data['guest_list_ids'] = $userGuestLists;
            $this->eventCreationService->storeStep(2, $step2Data);
            
            \Log::info('🔧 [AUTO_FIX] Auto-selected guest lists', [
                'user_id' => Auth::id(),
                'selected_lists' => $userGuestLists,
                'count' => count($userGuestLists)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully selected ' . count($userGuestLists) . ' guest lists. You can now use the chat feature!',
                'selected_count' => count($userGuestLists)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Auto-fix guest lists error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-select guest lists. Please go to Step 2 manually.'
            ]);
        }
    }

    /**
     * Get correct event data for chatbot with proper fallback logic
     */
    private function getCorrectEventDataForChat()
    {
        // Get all step data
        $allData = $this->eventCreationService->getAllStepsData();
        
        // Get individual step data for better control
        $step1Data = $this->eventCreationService->getStep(1);
        $step2Data = $this->eventCreationService->getStep(2);
        $step3Data = $this->eventCreationService->getStep(3);
        $step4Data = $this->eventCreationService->getStep(4);
        
        \Log::info('🔍 [EVENT_DATA] Step data analysis', [
            'step1_has_name' => !empty($step1Data['name']),
            'step1_has_date' => !empty($step1Data['start_date']),
            'step1_name' => $step1Data['name'] ?? 'NOT_SET',
            'step1_date' => $step1Data['start_date'] ?? 'NOT_SET',
            'all_data_has_name' => !empty($allData['name']),
            'all_data_has_date' => !empty($allData['start_date']),
            'all_data_name' => $allData['name'] ?? 'NOT_SET',
            'all_data_date' => $allData['start_date'] ?? 'NOT_SET'
        ]);
        
        // Priority order: Step 1 data > All data > Fallback defaults
        $eventData = [];
        
        // First, try to get event data from Step 1 (most reliable)
        if (!empty($step1Data['name']) || !empty($step1Data['start_date'])) {
            $eventData = array_merge($eventData, $step1Data);
            \Log::info('🔍 [EVENT_DATA] Using Step 1 data', [
                'name' => $step1Data['name'] ?? 'NOT_SET',
                'start_date' => $step1Data['start_date'] ?? 'NOT_SET'
            ]);
        }
        // If Step 1 is empty, try all data
        elseif (!empty($allData['name']) || !empty($allData['start_date'])) {
            $eventData = array_merge($eventData, $allData);
            \Log::info('🔍 [EVENT_DATA] Using all data', [
                'name' => $allData['name'] ?? 'NOT_SET',
                'start_date' => $allData['start_date'] ?? 'NOT_SET'
            ]);
        }
        // If still no data, use fallback defaults
        else {
            \Log::warning('🔍 [EVENT_DATA] No event data found, using fallback defaults');
            $eventData = [
                'name' => 'Your Event',
                'start_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'location' => 'Event Venue',
                'description' => 'A special event',
                'using_fallback_data' => true
            ];
        }
        
        // Always merge with other step data to ensure we have complete information
        $eventData = array_merge($eventData, $step2Data, $step3Data, $step4Data);
        
        // Ensure we have guest list IDs
        if (empty($eventData['guest_list_ids']) && !empty($step2Data['guest_list_ids'])) {
            $eventData['guest_list_ids'] = $step2Data['guest_list_ids'];
        }
        
        \Log::info('🔍 [EVENT_DATA] Final merged event data', [
            'name' => $eventData['name'] ?? 'NOT_SET',
            'start_date' => $eventData['start_date'] ?? 'NOT_SET',
            'location' => $eventData['location'] ?? 'NOT_SET',
            'description' => $eventData['description'] ?? 'NOT_SET',
            'has_guest_list_ids' => !empty($eventData['guest_list_ids']),
            'using_fallback' => $eventData['using_fallback_data'] ?? false
        ]);
        
        return $eventData;
    }

    /**
     * Chat endpoint for AI assistant in Step 3
     */
    public function chat(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string',
                'current_messages' => 'nullable|array',
                'current_messages.general' => 'nullable|string',
                'current_messages.group_templates' => 'nullable|array',
                'current_messages.per_guest_messages' => 'nullable|array',
            ]);

            // Validate and repair session data if needed
            $sessionValid = $this->eventCreationService->validateAndRepairSessionData();
            
            // Get correct event data using our new method
            $allData = $this->getCorrectEventDataForChat();
            
            \Log::info('🔍 [CHAT] Final event data for chatbot', [
                'event_name' => $allData['name'] ?? 'NOT_SET',
                'event_date' => $allData['start_date'] ?? 'NOT_SET',
                'event_location' => $allData['location'] ?? 'NOT_SET',
                'event_description' => $allData['description'] ?? 'NOT_SET',
                'using_fallback' => $allData['using_fallback_data'] ?? false
            ]);
            
            // Debug session data
            \Log::info('🔍 [CHAT] Session data check', [
                'session_valid' => $sessionValid,
                'all_data_keys' => array_keys($allData),
                'guest_list_ids' => $allData['guest_list_ids'] ?? 'missing',
                'step1_exists' => !empty($this->eventCreationService->getStep(1)),
                'step2_exists' => !empty($this->eventCreationService->getStep(2)),
                'step2_data' => $this->eventCreationService->getStep(2),
                'step1_data' => $this->eventCreationService->getStep(1),
                'step3_data' => $this->eventCreationService->getStep(3),
                'step4_data' => $this->eventCreationService->getStep(4),
                'all_data_sample' => array_slice($allData, 0, 10, true)
            ]);
            
            // Try to get guest list IDs from multiple sources
            $guestListIds = $allData['guest_list_ids'] ?? [];
            
            // If no guest list IDs found in merged data, try step 2 directly
            if (empty($guestListIds)) {
                $step2Data = $this->eventCreationService->getStep(2);
                $guestListIds = $step2Data['guest_list_ids'] ?? [];
                
                \Log::info('🔍 [CHAT] Fallback to step 2 data', [
                    'step2_guest_list_ids' => $guestListIds,
                    'step2_data_keys' => array_keys($step2Data)
                ]);
                
                // If we found guest list IDs in step 2, update allData
                if (!empty($guestListIds)) {
                    $allData['guest_list_ids'] = $guestListIds;
                }
            }
            
            // Get organized guest data
            $guestData = $this->eventCreationService->getOrganizedGuestData($guestListIds);

            // Enhanced check with more detailed error information
            if (empty($guestData)) {
                \Log::warning('🔍 [CHAT] No guest data found', [
                    'guest_list_ids' => $guestListIds,
                    'all_data_keys' => array_keys($allData),
                    'step2_data' => $this->eventCreationService->getStep(2),
                    'user_id' => Auth::id()
                ]);
                
                // Check if user has guest lists available but didn't select any
                $availableGuestLists = \App\Shared\Models\GuestList::where('user_id', Auth::id())->count();
                
                if ($availableGuestLists > 0) {
                    return response()->json([
                        'success' => false,
                        'response' => '🚫 **No Guest Lists Selected**

I found that you have **' . $availableGuestLists . ' guest lists** available, but none are currently selected for this event.

**Quick Fix Options:**

🔧 **Auto-Select All Lists** (Recommended)
Click the button below to automatically select all your guest lists for this event.

📝 **Manual Selection**
1. Go back to **Step 2**
2. **Select the guest lists** you want to invite by clicking on them
3. Make sure they are highlighted/checked
4. Click **"Next Step"** to save your selection
5. Return to Step 3 to use the chat

**Tip:** Look for the checkboxes next to your guest list names in Step 2. Selected lists should be highlighted in blue.

Once you select guest lists, I\'ll be ready to help generate amazing messages! 🎉',
                        'actions' => [
                            [
                                'type' => 'auto_fix_guest_lists',
                                'message' => '🔧 Auto-Select All Guest Lists (' . $availableGuestLists . ')',
                                'url' => route('organizer.events.create.auto-fix-guest-lists')
                            ],
                            [
                                'type' => 'redirect_to_step2',
                                'message' => '📝 Go to Step 2 (Manual Selection)'
                            ]
                        ]
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'response' => '📋 **No Guest Lists Available**

You don\'t have any guest lists created yet. You need to create guest lists before you can generate invitation messages.

**To fix this:**
1. Go to **Guest Lists** section
2. **Create a new guest list**
3. **Add guests** to your list
4. Return to **Step 2** and select your new list
5. Come back to Step 3 for message generation

**Need help?** Guest lists help you organize your invitees into groups like "Family", "Friends", "Colleagues", etc.',
                        'actions' => [
                            [
                                'type' => 'redirect_to_guest_lists',
                                'message' => 'Go to Guest Lists'
                            ]
                        ]
                    ]);
                }
            }

            \Log::info('🔍 [CHAT] Guest data loaded successfully', [
                'guest_data_count' => count($guestData),
                'guest_list_ids' => $guestListIds
            ]);

            // Debug event data before passing to ChatService
            \Log::info('🔍 [CHAT] Event data debug', [
                'all_data_keys' => array_keys($allData),
                'all_data_sample' => array_slice($allData, 0, 10, true),
                'has_name' => isset($allData['name']),
                'has_start_date' => isset($allData['start_date']),
                'has_location' => isset($allData['location']),
                'name_value' => $allData['name'] ?? 'NOT_SET',
                'start_date_value' => $allData['start_date'] ?? 'NOT_SET',
                'location_value' => $allData['location'] ?? 'NOT_SET'
            ]);

            // Ensure event data is properly formatted
            if (!empty($allData['name']) && !empty($allData['start_date'])) {
                \Log::info('🔍 [CHAT] Event data is properly formatted, proceeding with ChatService');
            } else {
                \Log::warning('🔍 [CHAT] Event data is still missing critical fields');
            }

            // Use the ChatService to process the message
            $chatService = app(\App\Organizer\Services\ChatService::class);
            $result = $chatService->processMessage($validated['message'], [], $allData, $guestData, $validated['current_messages'] ?? []);

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Chat error: ' . $e->getMessage(), [
                'message' => $request->input('message'),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'response' => 'Sorry, I encountered an error while processing your request. Please try again or go back to previous steps to ensure all data is properly set up.',
                'actions' => []
            ], 500);
        }
    }

    /**
     * Generate AI message for Step 3 using the new EventMessageGenerationService
     */
    public function generateAIMessage(Request $request)
    {
        $validated = $request->validate([
            'mode' => 'required|in:general,group,per_guest',
            'user_instructions' => 'nullable|array',
            'guest_data' => 'nullable|array',
        ]);

        $allData = $this->eventCreationService->getAllStepsData();
        
        // Get guest data if not provided
        $guestData = $validated['guest_data'] ?? [];
        if (empty($guestData) && !empty($allData['guest_list_ids'])) {
            $guestData = $this->eventCreationService->getGuestsFromLists($allData['guest_list_ids']);
        }
        
        $result = $this->eventCreationService->generateAIMessage(
            $validated['mode'],
            $allData,
            $guestData,
            $validated['user_instructions'] ?? []
        );

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => $this->validateEndDate($request),
            'location' => 'nullable|string|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'parking_info' => 'nullable|string|max:255',
            
            // Invitation Settings
            'invitation_title' => 'nullable|string|max:255',
            'invitation_subtitle' => 'nullable|string|max:255',
            'invitation_message' => 'nullable|string',
            'rsvp_message' => 'nullable|string',
            'rsvp_deadline' => 'nullable|string|max:255',
            'rsvp_contact' => 'nullable|string|max:255',
            'rsvp_enabled' => 'boolean',
            
            // QR Code Settings
            'qr_checkin_enabled' => 'boolean',
            'qr_code_url' => 'nullable|url',
            'qr_description' => 'nullable|string|max:255',
            
            // Design Settings
            'hero_color1' => 'nullable|string|max:7',
            'hero_color2' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'font_family' => 'nullable|string|max:255',
            
            // Guest Lists
            'guest_list_ids' => 'nullable|array',
            'guest_list_ids.*' => 'exists:guest_lists,id',
            
            // Message Template
            'message_template' => 'nullable|string',
            'custom_messages' => 'nullable|array',
            'attachments' => 'nullable|array',
            
            // Scheduling
            'send_type' => 'required|in:now,scheduled',
            'scheduled_at' => 'nullable|date|required_if:send_type,scheduled',
            'status' => 'required|in:draft,scheduled,sent,cancelled',
        ]);

        $validated['user_id'] = Auth::id();
        
        // Check if we're editing an existing draft event
        $editingEventId = Session::get('editing_draft_event_id');
        $isEditing = false;
        
        if ($editingEventId) {
            $existingEvent = Event::find($editingEventId);
            if ($existingEvent && in_array($existingEvent->status, ['draft', 'scheduled']) && $existingEvent->user_id === Auth::id()) {
                $isEditing = true;
                $event = $existingEvent;
            }
        }
        
        // Handle file uploads for attachments
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('event-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
            $validated['attachments'] = $attachments;
        }

        if ($isEditing) {
            // Update existing draft event
            $event->update($validated);
            
            // Sync guest lists
            if (!empty($validated['guest_list_ids'])) {
                $event->guestLists()->sync($validated['guest_list_ids']);
            }
            
            // Clear the editing session
            Session::forget('editing_draft_event_id');
            $this->eventCreationService->clearSession();
            
            $message = 'Draft event updated successfully!';
        } else {
            // Create new event
            $event = Event::create($validated);

            // Attach guest lists and create event-guest relationships
            if (!empty($validated['guest_list_ids'])) {
                $event->guestLists()->attach($validated['guest_list_ids']);
                
                // Use EventGuestService to create event-guest relationships
                $eventGuestService = app(\App\Services\EventGuestService::class);
                foreach ($validated['guest_list_ids'] as $guestListId) {
                    $guestList = \App\Shared\Models\GuestList::find($guestListId);
                    if ($guestList) {
                        $eventGuestService->addGuestListToEvent($event, $guestList);
                    }
                }
            }
            
            // Clear any auto-saved session data
            $this->eventCreationService->clearSession();
            
            $message = 'Event created successfully!';
        }

        // Handle scheduling
        if ($validated['send_type'] === 'scheduled' && $validated['scheduled_at']) {
            // Here you would dispatch a job to send invitations at the scheduled time
            // dispatch(new SendEventInvitations($event))->delay($validated['scheduled_at']);
        } elseif ($validated['send_type'] === 'now') {
            // Send invitations immediately
            // dispatch(new SendEventInvitations($event));
        }

        return redirect()->route('organizer.events.show', $event)
            ->with('success', $message);
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        
        // Check and update this specific event's status
        if ($event->status !== 'completed' && $event->isCompleted()) {
            $event->markAsCompleted();
            
            Log::info('📅 [EVENT_SHOW_COMPLETION] Event marked as completed when viewed', [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'event_name' => $event->name,
                'completion_time' => now()->toISOString(),
            ]);
        }
        
        // Use EventGuestService to get active guests
        $eventGuestService = app(\App\Services\EventGuestService::class);
        $activeEventGuests = $eventGuestService->getActiveGuestsForEvent($event);
        
        // Debug the event-guest relationships
        Log::info('🔍 [EVENT_SHOW] Event guest relationships debug', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'active_event_guests_count' => $activeEventGuests->count(),
            'guest_lists_count' => $event->guestLists->count(),
            'total_event_guest_records' => \App\EventGuest::where('event_id', $event->id)->count(),
            'active_event_guest_records' => \App\EventGuest::where('event_id', $event->id)->where('status', 'active')->count()
        ]);
        
        // Load event with all related data
        $event->load([
            'guestLists.guests.invitations' => function($query) use ($event) {
                $query->where('event_id', $event->id);
            },
            'guestLists.guests.checkedInBy'
        ]);
        
        // Get statistics using the new system
        $stats = $this->getEventStats($event, $activeEventGuests);
        
        return view('organizer.events.show', compact('event', 'stats', 'activeEventGuests'));
    }
    
    private function getEventStats(Event $event, $activeEventGuests = null): array
    {
        // If activeEventGuests is not provided, get it from the service
        if ($activeEventGuests === null) {
            $eventGuestService = app(\App\Services\EventGuestService::class);
            $activeEventGuests = $eventGuestService->getActiveGuestsForEvent($event);
        }
        
        $totalGuests = 0;
        $rsvpStats = [
            'yes' => 0,
            'no' => 0,
            'maybe' => 0,
            'no_response' => 0
        ];
        $attendanceStats = [
            'checked_in' => 0,
            'not_checked_in' => 0
        ];
        
        // Use activeEventGuests instead of iterating through guest lists
        foreach ($activeEventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            $totalGuests++;
            
            // Check RSVP status
            $invitation = $guest->invitations->where('event_id', $event->id)->first();
            if ($invitation) {
                $rsvpStatus = $invitation->rsvp_status ?? 'no_response';
                // Treat 'none' as 'no_response'
                if ($rsvpStatus === 'none') {
                    $rsvpStatus = 'no_response';
                }
                $rsvpStats[$rsvpStatus]++;
            } else {
                $rsvpStats['no_response']++;
            }
            
            // Check attendance
            if ($guest->checked_in) {
                $attendanceStats['checked_in']++;
            } else {
                $attendanceStats['not_checked_in']++;
            }
        }
        
        return [
            'total_guests' => $totalGuests,
            'rsvp_stats' => $rsvpStats,
            'attendance_stats' => $attendanceStats,
            'guest_lists_count' => $event->guestLists->count(),
            'invitations_sent' => $event->invitations()->count(),
            'invitations_pending' => $event->invitations()->where('status', 'pending')->count(),
            'invitations_sent_count' => $event->invitations()->where('status', 'sent')->count(),
            'invitations_failed' => $event->invitations()->where('status', 'failed')->count(),
        ];
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        
        // For sent events, redirect to the new update page
        if ($event->status === 'sent') {
            return redirect()->route('organizer.events.update-sent', $event);
        }
        
        // For draft or scheduled events, redirect to create page with pre-filled data
        if (in_array($event->status, ['draft', 'scheduled'])) {
            // Clear any existing session data first
            $this->eventCreationService->clearSession();
            Session::forget('editing_draft_event_id');
            
            // Pre-populate the EventCreationService with draft data
            $this->prepopulateDraftData($event);
            
            // Log the session data after pre-population for debugging
            Log::info('Draft event pre-population completed', [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'editing_draft_event_id' => Session::get('editing_draft_event_id'),
                'step1_data' => $this->eventCreationService->getStep(1),
                'step2_data' => $this->eventCreationService->getStep(2),
                'step3_data' => $this->eventCreationService->getStep(3),
                'step4_data' => $this->eventCreationService->getStep(4),
            ]);
            
            // Redirect directly to Step 1 of the create wizard with update mode
            return redirect()->route('organizer.events.create.step1', ['mode' => 'update'])
                ->with('info', $event->status === 'scheduled'
                    ? 'Editing scheduled event. Your data has been loaded.'
                    : 'Editing draft event. Your previous data has been loaded.');
        }
        
        // For other statuses, use the simple edit page
        $guestLists = GuestList::where('user_id', Auth::id())->get();
        $event->load('guestLists');
        return view('organizer.events.edit', compact('event', 'guestLists'));
    }

    /**
     * Auto-save event creation progress
     */
    public function autoSave(Request $request)
    {
        try {
            $data = $request->all();
            
            // Debug: Log the incoming data
            Log::info('🔄 [AUTO_SAVE] Auto-save triggered', [
                'user_id' => Auth::id(),
                'data_keys' => array_keys($data),
                'name' => $data['name'] ?? 'NOT_SET',
                'start_date' => $data['start_date'] ?? 'NOT_SET',
                'has_name' => !empty($data['name']),
                'has_start_date' => !empty($data['start_date']),
                'has_guest_list_ids' => isset($data['guest_list_ids']),
                'guest_list_ids_value' => $data['guest_list_ids'] ?? 'NOT_PROVIDED',
                'request_method' => $request->method(),
                'request_url' => $request->url(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            // Determine which step we're currently on based on the form data
            $currentStep = 1;
            
            // Check if we have step 3 data (messages) - check for complex field names first
            $hasStep3Data = false;
            foreach ($data as $key => $value) {
                Log::info('🔄 [AUTO_SAVE] Checking key for step 3', [
                    'user_id' => Auth::id(),
                    'key' => $key,
                    'matches_group' => preg_match('/^group_messages\[(\d+)\]\[(\d+)\]$/', $key),
                    'matches_per_guest' => preg_match('/^per_guest_messages\[(\d+)\]$/', $key),
                    'has_general_message' => isset($data['general_message'])
                ]);
                
                if (preg_match('/^group_messages\[(\d+)\]\[(\d+)\]$/', $key) || 
                    preg_match('/^per_guest_messages\[(\d+)\]$/', $key) ||
                    isset($data['general_message'])) {
                    $hasStep3Data = true;
                    Log::info('🔄 [AUTO_SAVE] Step 3 data detected', [
                        'user_id' => Auth::id(),
                        'key' => $key,
                        'value' => $value
                    ]);
                    break;
                }
            }
            
            if ($hasStep3Data) {
                $currentStep = 3;
                Log::info('🔄 [AUTO_SAVE] Setting current step to 3', [
                    'user_id' => Auth::id(),
                    'has_step3_data' => $hasStep3Data
                ]);
            }
            // Check if we have step 2 data (guest lists and platforms)
            elseif (isset($data['guest_list_ids']) && is_array($data['guest_list_ids']) && !empty($data['guest_list_ids']) && 
                isset($data['invitation_platforms']) && is_array($data['invitation_platforms']) && !empty($data['invitation_platforms'])) {
                $currentStep = 2;
            }
            
            // Check if we have step 4 data (send type)
            if (isset($data['send_type'])) {
                $currentStep = 4;
            }
            
            Log::info('Auto-save detected current step', [
                'user_id' => Auth::id(),
                'current_step' => $currentStep,
                'form_data_keys' => array_keys($data)
            ]);
            
            // Initialize step1Data variable
            $step1Data = null;
            
            // Store data based on current step
            if ($currentStep >= 1) {
                // Only store Step 1 data if it's actually provided in the request
                if (isset($data['name']) || isset($data['start_date']) || isset($data['description']) || isset($data['end_date'])) {
                    $step1Data = [
                        'name' => $data['name'] ?? '',
                        'description' => $data['description'] ?? '',
                        'start_date' => $data['start_date'] ?? '',
                        'end_date' => $data['end_date'] ?? '',
                        'additional_information' => $data['additional_information'] ?? '',
                        'location' => $data['location'] ?? '',
                        'venue_name' => $data['venue_name'] ?? '',
                        'venue_address' => $data['venue_address'] ?? '',
                        'latitude' => $data['latitude'] ?? '',
                        'longitude' => $data['longitude'] ?? '',
                    ];
                    
                    // Store in EventCreationService
                    $this->eventCreationService->storeStep(1, $step1Data);
                    
                    Log::info('🔄 [AUTO_SAVE] Stored Step 1 data', [
                        'user_id' => Auth::id(),
                        'step1_data' => $step1Data,
                        'has_name' => !empty($step1Data['name']),
                        'has_start_date' => !empty($step1Data['start_date']),
                        'has_end_date' => !empty($step1Data['end_date']),
                        'end_date_value' => $step1Data['end_date']
                    ]);
                } else {
                    Log::info('🔄 [AUTO_SAVE] Skipping Step 1 data storage - no Step 1 fields provided', [
                        'user_id' => Auth::id(),
                        'current_step' => $currentStep,
                        'provided_fields' => array_keys($data)
                    ]);
                }
            }
            
            if ($currentStep >= 2) {
                // Get existing step 2 data to preserve values not being updated
                $existingStep2Data = $this->eventCreationService->getStep(2);
                
                // Normalize boolean toggles: if field present, cast to boolean; if absent, preserve previous value
                $qrToggleProvided = array_key_exists('qr_checkin_enabled', $data);
                $rsvpToggleProvided = array_key_exists('rsvp_enabled', $data);

                $step2Data = [
                    'qr_checkin_enabled' => $qrToggleProvided
                        ? (bool) ($data['qr_checkin_enabled'] === true || $data['qr_checkin_enabled'] === '1' || $data['qr_checkin_enabled'] === 1 || $data['qr_checkin_enabled'] === 'on')
                        : ($existingStep2Data['qr_checkin_enabled'] ?? false),
                    'rsvp_enabled' => $rsvpToggleProvided
                        ? (bool) ($data['rsvp_enabled'] === true || $data['rsvp_enabled'] === '1' || $data['rsvp_enabled'] === 1 || $data['rsvp_enabled'] === 'on')
                        : ($existingStep2Data['rsvp_enabled'] ?? false),
                    'invitation_platforms' => isset($data['invitation_platforms']) ? $this->processPlatformData($data['invitation_platforms']) : ($existingStep2Data['invitation_platforms'] ?? []),
                    'guest_list_ids' => isset($data['guest_list_ids']) && is_array($data['guest_list_ids']) ? $data['guest_list_ids'] : ($existingStep2Data['guest_list_ids'] ?? []),
                ];
                
                // Debug step 2 data storage
                Log::info('🔄 [AUTO_SAVE] Storing step 2 data', [
                    'user_id' => Auth::id(),
                    'step2_data' => $step2Data,
                    'existing_step2_data' => $existingStep2Data,
                    'guest_list_ids_raw' => $data['guest_list_ids'] ?? 'not_provided',
                    'guest_list_ids_processed' => $step2Data['guest_list_ids'],
                    'guest_list_ids_preserved' => !isset($data['guest_list_ids']) ? 'YES' : 'NO',
                    'invitation_platforms_raw' => $data['invitation_platforms'] ?? 'not_provided',
                    'invitation_platforms_processed' => $step2Data['invitation_platforms'],
                    'qr_checkin_enabled_raw' => $data['qr_checkin_enabled'] ?? 'not_provided',
                    'rsvp_enabled_raw' => $data['rsvp_enabled'] ?? 'not_provided',
                    'qr_checkin_enabled_processed' => $step2Data['qr_checkin_enabled'],
                    'rsvp_enabled_processed' => $step2Data['rsvp_enabled']
                ]);
                
                $this->eventCreationService->storeStep(2, $step2Data);
            }
            
            if ($currentStep >= 3) {
                // Get existing step 3 data to preserve values not being updated
                $existingStep3Data = $this->eventCreationService->getStep(3);
                
                // Process group_messages and per_guest_messages from both flat keys and nested arrays
                $groupMessages = [];
                $perGuestMessages = [];

                // 1) Accept flat bracketed keys from standard form posts
                foreach ($data as $key => $value) {
                    if (!is_string($key)) continue;
                    // Handle group_messages[listId][groupId] format
                    if (preg_match('/^group_messages\[(\d+)\]\[(\d+)\]$/', $key, $matches)) {
                        $listId = $matches[1];
                        $groupId = $matches[2];
                        if (!isset($groupMessages[$listId])) {
                            $groupMessages[$listId] = [];
                        }
                        $groupMessages[$listId][$groupId] = $value;
                        continue;
                    }
                    // Handle per_guest_messages[guestId] format
                    if (preg_match('/^per_guest_messages\[(\d+)\]$/', $key, $matches)) {
                        $guestId = $matches[1];
                        $perGuestMessages[$guestId] = $value;
                        continue;
                    }
                }

                // 2) Accept nested arrays from JSON posts (our step 3 autosave uses JSON)
                if (isset($data['group_messages']) && is_array($data['group_messages'])) {
                    // Merge nested structure over anything collected from flat keys
                    $groupMessages = array_replace_recursive($groupMessages, $data['group_messages']);
                }
                if (isset($data['per_guest_messages']) && is_array($data['per_guest_messages'])) {
                    $perGuestMessages = array_replace($perGuestMessages, $data['per_guest_messages']);
                }
                
                // Preserve existing data if no new data is provided
                if (empty($groupMessages) && !empty($existingStep3Data['group_messages'])) {
                    $groupMessages = $existingStep3Data['group_messages'];
                }
                if (empty($perGuestMessages) && !empty($existingStep3Data['per_guest_messages'])) {
                    $perGuestMessages = $existingStep3Data['per_guest_messages'];
                }
                
                $step3Data = [
                    'general_message' => $data['general_message'] ?? ($existingStep3Data['general_message'] ?? ''),
                    'group_messages' => $groupMessages,
                    'per_guest_messages' => $perGuestMessages,
                    'ai_generated' => isset($data['ai_generated']) ? true : ($existingStep3Data['ai_generated'] ?? false),
                ];
                
                // Debug step 3 data storage
                Log::info('🔄 [AUTO_SAVE] Storing step 3 data', [
                    'user_id' => Auth::id(),
                    'step3_data' => $step3Data,
                    'has_general_message' => !empty($data['general_message']),
                    'group_messages_count' => count($groupMessages),
                    'per_guest_messages_count' => count($perGuestMessages),
                    'ai_generated' => isset($data['ai_generated']),
                    'raw_data_keys' => array_keys($data)
                ]);
                
                $this->eventCreationService->storeStep(3, $step3Data);
            }
            
            if ($currentStep >= 4) {
                $this->eventCreationService->storeStep(4, [
                    'send_type' => $data['send_type'] ?? 'now',
                    'scheduled_at' => $data['scheduled_at'] ?? '',
                ]);
            }
            
            // Store in session for immediate access, but only non-empty values
            // This prevents empty values from overriding existing data
            $currentStepData = Session::get('step_data', []);
            
            // Merge step 1 data only if it has non-empty values
            if ($currentStep >= 1 && $step1Data !== null) {
                foreach ($step1Data as $key => $value) {
                    // For all step 1 fields, save non-empty values
                    if (!empty($value) || $value === '0' || $value === 0) {
                        $currentStepData[$key] = $value;
                    }
                }
            }
            
            // Merge step 2 data - handle boolean values properly
            if ($currentStep >= 2) {
                foreach ($step2Data as $key => $value) {
                    // For boolean fields, always save the value (true or false)
                    if (in_array($key, ['qr_checkin_enabled', 'rsvp_enabled'])) {
                        $currentStepData[$key] = $value;
                    }
                    // For other fields, only save non-empty values
                    elseif (!empty($value) || $value === '0' || $value === 0 || is_bool($value)) {
                        $currentStepData[$key] = $value;
                    }
                }
            }
            
            // Merge step 3 data - allow explicit clears and save arrays even when general is empty
            if ($currentStep >= 3) {
                foreach ($step3Data as $key => $value) {
                    // Always persist boolean flag
                    if ($key === 'ai_generated') {
                        $currentStepData[$key] = (bool)$value;
                        continue;
                    }
                    // For general_message: if key exists, persist even if empty string (allows clearing)
                    if ($key === 'general_message') {
                        if (array_key_exists('general_message', $step3Data)) {
                            $currentStepData['general_message'] = $value ?? '';
                        }
                        continue;
                    }
                    // For group_messages and per_guest_messages: persist arrays as provided
                    if (in_array($key, ['group_messages', 'per_guest_messages'], true)) {
                        if (is_array($value)) {
                            $currentStepData[$key] = $value;
                        }
                        continue;
                    }
                }
            }
            
            // Store current step data as flash data for this request only
            // This prevents it from persisting across requests and overriding session data
            Session::flash('step_data', $currentStepData);
            
            // Check if we should create a draft event in database
            $allStepsData = $this->eventCreationService->getAllStepsData();
            $hasSignificantProgress = !empty($allStepsData['name']) && !empty($allStepsData['start_date']);
            
            // Debug: Log the progress check
            Log::info('Progress check', [
                'user_id' => Auth::id(),
                'all_steps_data' => $allStepsData,
                'has_name' => !empty($allStepsData['name']),
                'has_start_date' => !empty($allStepsData['start_date']),
                'has_significant_progress' => $hasSignificantProgress
            ]);
            
            // Create or update draft as soon as there's meaningful progress:
            // - Step 1 basics (name or start_date), OR
            // - Step 3 messages (general/group/per-guest)
            $hasMinimalProgress = !empty($allStepsData['name']) ||
                                  !empty($allStepsData['start_date']) ||
                                  !empty($allStepsData['general_message']) ||
                                  !empty($allStepsData['group_messages']) ||
                                  !empty($allStepsData['per_guest_messages']);

            // When editing an existing draft: if message fields are explicitly present in the request
            // (even empty), force a draft update so edits are persisted immediately.
            $forceDraftUpdate = Session::has('editing_draft_event_id') && (
                array_key_exists('general_message', $data) ||
                array_key_exists('group_messages', $data) ||
                array_key_exists('per_guest_messages', $data)
            );
            
            if ($hasMinimalProgress || $forceDraftUpdate) {
                Log::info('Attempting to create/update draft event', [
                    'user_id' => Auth::id(),
                    'has_minimal_progress' => $hasMinimalProgress,
                    'force_draft_update' => $forceDraftUpdate,
                    'has_name' => !empty($allStepsData['name']),
                    'has_start_date' => !empty($allStepsData['start_date']),
                    'has_general_message' => !empty($allStepsData['general_message']),
                    'has_group_messages' => !empty($allStepsData['group_messages']),
                    'has_per_guest_messages' => !empty($allStepsData['per_guest_messages'])
                ]);
                
                // If request explicitly contains general_message, override merged value before draft sync
                if (array_key_exists('general_message', $data)) {
                    $allStepsData['general_message'] = $data['general_message'] ?? '';
                    Log::info('Overriding merged general_message with request payload for draft sync', [
                        'user_id' => Auth::id(),
                        'override_length' => strlen($allStepsData['general_message'] ?? ''),
                        'override_preview' => substr($allStepsData['general_message'] ?? '', 0, 20)
                    ]);
                }
                if (array_key_exists('group_messages', $data) && is_array($data['group_messages'] ?? null)) {
                    $allStepsData['group_messages'] = $data['group_messages'];
                    Log::info('Overriding merged group_messages with request payload for draft sync', [
                        'user_id' => Auth::id(),
                        'group_messages_keys' => array_keys($data['group_messages'])
                    ]);
                }
                if (array_key_exists('per_guest_messages', $data) && is_array($data['per_guest_messages'] ?? null)) {
                    $allStepsData['per_guest_messages'] = $data['per_guest_messages'];
                    Log::info('Overriding merged per_guest_messages with request payload for draft sync', [
                        'user_id' => Auth::id(),
                        'per_guest_messages_count' => count($data['per_guest_messages'])
                    ]);
                }

                // If request explicitly contains general_message, override merged value before draft sync
                if (array_key_exists('general_message', $data)) {
                    $allStepsData['general_message'] = $data['general_message'] ?? '';
                    Log::info('Overriding merged general_message with request payload for draft sync', [
                        'user_id' => Auth::id(),
                        'override_length' => strlen($allStepsData['general_message'] ?? ''),
                        'override_preview' => substr($allStepsData['general_message'] ?? '', 0, 20)
                    ]);
                }

                // Create or update draft event in database
                $draftEvent = $this->createOrUpdateDraftEvent($allStepsData);
                
                if ($draftEvent) {
                    // Only set the session ID if we don't already have one (i.e., we're not editing an existing event)
                    if (!Session::has('editing_draft_event_id')) {
                        Session::put('editing_draft_event_id', $draftEvent->id);
                    }
                    // Keep session step 3 in sync with what we just stored in DB
                    $sessionStep3 = $this->eventCreationService->getStep(3);
                    $didSync = false;
                    if (array_key_exists('general_message', $allStepsData)) {
                        $sessionStep3['general_message'] = $allStepsData['general_message'] ?? '';
                        $didSync = true;
                    }
                    if (array_key_exists('group_messages', $allStepsData) && is_array($allStepsData['group_messages'])) {
                        $sessionStep3['group_messages'] = $allStepsData['group_messages'];
                        $didSync = true;
                    }
                    if (array_key_exists('per_guest_messages', $allStepsData) && is_array($allStepsData['per_guest_messages'])) {
                        $sessionStep3['per_guest_messages'] = $allStepsData['per_guest_messages'];
                        $didSync = true;
                    }
                    if ($didSync) {
                        $this->eventCreationService->storeStep(3, $sessionStep3);
                        Log::info('Synced session step3 messages with DB', [
                            'user_id' => Auth::id(),
                            'general_len' => strlen($sessionStep3['general_message'] ?? ''),
                            'group_present' => isset($sessionStep3['group_messages']),
                            'per_guest_count' => isset($sessionStep3['per_guest_messages']) && is_array($sessionStep3['per_guest_messages']) ? count($sessionStep3['per_guest_messages']) : 0
                        ]);
                    }
                    Log::info('Draft event created/updated successfully', [
                        'user_id' => Auth::id(),
                        'event_id' => $draftEvent->id,
                        'event_name' => $draftEvent->name,
                        'was_new_draft' => !Session::has('editing_draft_event_id')
                    ]);
                } else {
                    Log::error('Failed to create/update draft event', [
                        'user_id' => Auth::id(),
                        'all_steps_data' => $allStepsData
                    ]);
                }
            } else {
                Log::info('No minimal progress to create draft', [
                    'user_id' => Auth::id(),
                    'has_minimal_progress' => $hasMinimalProgress,
                    'has_name' => !empty($allStepsData['name']),
                    'has_start_date' => !empty($allStepsData['start_date']),
                    'has_general_message' => !empty($allStepsData['general_message']),
                    'has_group_messages' => !empty($allStepsData['group_messages']),
                    'has_per_guest_messages' => !empty($allStepsData['per_guest_messages']),
                    'all_steps_data' => $allStepsData
                ]);
            }
            
            // Log the saved data for debugging
            Log::info('Auto-save completed', [
                'user_id' => Auth::id(),
                'current_step' => $currentStep,
                'has_draft_event' => Session::has('editing_draft_event_id'),
                'steps_data' => [
                    'step1' => $this->eventCreationService->getStep(1),
                    'step2' => $this->eventCreationService->getStep(2),
                    'step3' => $this->eventCreationService->getStep(3),
                    'step4' => $this->eventCreationService->getStep(4),
                ],
                'session_keys_after_save' => [
                    'step1' => Session::get('event_creation_step_1'),
                    'step2' => Session::get('event_creation_step_2'),
                    'step3' => Session::get('event_creation_step_3'),
                    'step4' => Session::get('event_creation_step_4')
                ]
            ]);
            
            // Check if this is a form submission (not just auto-save)
            // If we're editing a draft and this is a form submission, redirect to next step
            if (Session::has('editing_draft_event_id') && $request->has('_token') && $request->header('X-Form-Submission') === 'true') {
                Log::info('Form submission detected, redirecting to next step', [
                    'user_id' => Auth::id(),
                    'current_step' => $currentStep,
                    'is_draft_editing' => true,
                    'form_data_keys' => array_keys($data),
                    'has_guest_list_ids' => isset($data['guest_list_ids']),
                    'guest_list_ids_count' => isset($data['guest_list_ids']) ? count($data['guest_list_ids']) : 0,
                    'has_general_message' => isset($data['general_message']),
                    'has_invitation_message' => isset($data['invitation_message']),
                    'has_send_type' => isset($data['send_type'])
                ]);
                
                // For form submissions, return JSON with redirect info instead of actual redirect
                $nextStep = $currentStep + 1;
                
                // Preserve mode parameter for redirects
                $mode = $request->input('mode');
                
                if ($nextStep <= 4) {
                    $redirectUrl = $mode ? route('organizer.events.create.step' . $nextStep, ['mode' => $mode]) : route('organizer.events.create.step' . $nextStep);
                    return response()->json([
                        'success' => true,
                        'redirect' => $redirectUrl,
                        'message' => 'Progress saved and advancing to next step',
                        'timestamp' => now()->format('H:i:s')
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('organizer.events.create'),
                        'message' => 'Progress saved',
                        'timestamp' => now()->format('H:i:s')
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Progress saved automatically',
                'timestamp' => now()->format('H:i:s')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Auto-save failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process platform data from form submission
     */
    private function processPlatformData($platformData)
    {
        // Debug the platform data
        Log::info('Processing platform data', [
            'user_id' => Auth::id(),
            'platform_data_raw' => $platformData,
            'platform_data_type' => gettype($platformData),
            'is_array' => is_array($platformData),
            'is_null' => is_null($platformData),
            'is_string' => is_string($platformData)
        ]);
        
        // If it's already an array, return it
        if (is_array($platformData)) {
            return $platformData;
        }
        
        // If it's a string, it might be a single value
        if (is_string($platformData)) {
            return [$platformData];
        }
        
        // If it's null or empty, return default
        if (empty($platformData)) {
            return ['email'];
        }
        
        // Fallback to default
        return ['email'];
    }

    /**
     * Create or update draft event in database
     */
    private function createOrUpdateDraftEvent($data)
    {
        try {
            // First check if we're already editing a specific draft
            $currentDraftId = Session::get('editing_draft_event_id');
            $existingDraft = null;
            
            if ($currentDraftId) {
                // We're editing a specific draft, use that one
                $existingDraft = Event::where('id', $currentDraftId)
                    ->where('user_id', Auth::id())
                    ->whereIn('status', ['draft', 'scheduled'])
                    ->first();
                
                Log::info('🔍 [DRAFT_UPDATE] Looking for existing draft/scheduled event', [
                    'user_id' => Auth::id(),
                    'current_draft_id' => $currentDraftId,
                    'found_existing_draft' => $existingDraft ? true : false,
                    'existing_draft_status' => $existingDraft ? $existingDraft->status : 'NOT_FOUND',
                    'existing_draft_name' => $existingDraft ? $existingDraft->name : 'NOT_FOUND'
                ]);
            }
            
            // If no specific draft found and we don't have a name, only block if there's truly no progress
            if (!$existingDraft && empty($data['name'])) {
                $hasMessageProgress = !empty($data['general_message']) || !empty($data['group_messages']) || !empty($data['per_guest_messages']);
                $hasAnyProgress = $hasMessageProgress || !empty($data['start_date']) || !empty($data['invitation_platforms']) || !empty($data['guest_list_ids']);
                if (!$hasAnyProgress) {
                    return null;
                }
            }
            
            // Prepare event data with better defaults
            $eventData = [
                'user_id' => Auth::id(),
                'name' => $data['name'] ?? 'Untitled Event',
                'description' => $data['description'] ?? '',
                // Ensure non-null start_date for drafts to satisfy DB constraint
                'start_date' => !empty($data['start_date']) ? $data['start_date'] : now(),
                'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
                'additional_information' => $data['additional_information'] ?? '',
                'location' => $data['location'] ?? '',
                'venue_name' => $data['venue_name'] ?? '',
                'venue_address' => $data['venue_address'] ?? '',
                'latitude' => !empty($data['latitude']) ? $data['latitude'] : null,
                'longitude' => !empty($data['longitude']) ? $data['longitude'] : null,
                'invitation_title' => $data['invitation_title'] ?? "You're Invited!",
                'invitation_subtitle' => $data['invitation_subtitle'] ?? '',
                'invitation_message' => $data['invitation_message'] ?? '',
                'general_message' => $data['general_message'] ?? '',
                'group_messages' => $data['group_messages'] ?? null,
                'per_guest_messages' => $data['per_guest_messages'] ?? null,
                'invitation_platforms' => $data['invitation_platforms'] ?? ['email'],
                'rsvp_enabled' => $data['rsvp_enabled'] ?? false,
                'rsvp_message' => $data['rsvp_message'] ?? '',
                'rsvp_deadline' => $data['rsvp_deadline'] ?? '',
                'rsvp_contact' => $data['rsvp_contact'] ?? '',
                'qr_checkin_enabled' => $data['qr_checkin_enabled'] ?? false,
                'qr_description' => $data['qr_description'] ?? '',
                'send_type' => $data['send_type'] ?? 'now',
                'scheduled_at' => !empty($data['scheduled_at']) ? $data['scheduled_at'] : null,
                // If editing an existing draft/scheduled event, preserve its status
                'status' => $existingDraft ? $existingDraft->status : 'draft',
            ];
            
            if ($existingDraft) {
                // Update existing draft
                $existingDraft->update($eventData);
                
                // Sync guest lists if provided
                if (!empty($data['guest_list_ids']) && is_array($data['guest_list_ids'])) {
                    $existingDraft->guestLists()->sync($data['guest_list_ids']);
                }
                
                Log::info('✅ [DRAFT_UPDATE] Updated existing draft/scheduled event', [
                    'user_id' => Auth::id(),
                    'event_id' => $existingDraft->id,
                    'event_name' => $existingDraft->name,
                    'event_status' => $existingDraft->status,
                    'was_scheduled' => $existingDraft->status === 'scheduled'
                ]);
                
                return $existingDraft;
            } else {
                Log::info('⚠️ [DRAFT_UPDATE] No existing draft found, creating new event', [
                    'user_id' => Auth::id(),
                    'current_draft_id' => $currentDraftId,
                    'has_name' => !empty($data['name']),
                    'name' => $data['name'] ?? 'NOT_SET'
                ]);
                
                // Create new draft only if we have a name
                if (!empty($data['name'])) {
                    $draftEvent = Event::create($eventData);
                    
                    // Attach guest lists if provided and create event-guest relationships
                    if (!empty($data['guest_list_ids']) && is_array($data['guest_list_ids'])) {
                        $draftEvent->guestLists()->attach($data['guest_list_ids']);
                        
                        // Use EventGuestService to create event-guest relationships
                        $eventGuestService = app(\App\Services\EventGuestService::class);
                        foreach ($data['guest_list_ids'] as $guestListId) {
                            $guestList = \App\Shared\Models\GuestList::find($guestListId);
                            if ($guestList) {
                                $eventGuestService->addGuestListToEvent($draftEvent, $guestList);
                            }
                        }
                    }
                    
                    Log::info('🆕 [DRAFT_UPDATE] Created new draft event', [
                        'user_id' => Auth::id(),
                        'event_id' => $draftEvent->id,
                        'event_name' => $draftEvent->name,
                        'event_status' => $draftEvent->status
                    ]);
                    
                    return $draftEvent;
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Failed to create/update draft event', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return null;
        }
    }

    /**
     * Start a completely new event creation
     */
    public function createNew()
    {
        // Clear all session data
        $this->startFreshEventCreation();
        
        // Redirect to step 1 for fresh event creation
        return redirect()->route('organizer.events.create.step1')
            ->with('success', 'Starting fresh event creation');
    }

    /**
     * Continue editing a draft event
     */
    public function continueEditing(Event $event)
    {
        // Verify this is a draft event that belongs to the user
        if ($event->user_id !== Auth::id() || $event->status !== 'draft') {
            return redirect()->route('organizer.events.index')
                ->with('error', 'You can only continue editing draft events that belong to you.');
        }

        // Clear any existing session data first
        $this->startFreshEventCreation();
        
        // Set the editing draft event ID
        Session::put('editing_draft_event_id', $event->id);
        
        // Pre-populate session with draft data
        $this->prepopulateDraftData($event);
        
        // Redirect to step 1 which will now load the draft data with update mode
        return redirect()->route('organizer.events.create.step1', ['mode' => 'update'])
            ->with('success', 'Continuing to edit your draft event: ' . $event->name);
    }

    /**
     * Clear session data
     */
    public function clearSession()
    {
        try {
            $this->eventCreationService->clearSession();
            Session::forget('editing_draft_event_id');
            
            // Also clear any draft event ID to ensure fresh start
            Session::forget('editing_draft_event_id');
            
            return response()->json([
                'success' => true,
                'message' => 'Session cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pre-populate EventCreationService with draft event data
     */
    private function prepopulateDraftData(Event $event)
    {
        // Store the event ID for updating later
        Session::put('editing_draft_event_id', $event->id);
        
        // Step 1: Basic event details
        $step1Data = [
            'name' => $event->name ?? '',
            'description' => $event->description ?? '',
            'start_date' => $event->start_date ? $event->start_date->format('Y-m-d\TH:i') : '',
            'end_date' => $event->end_date ? $event->end_date->format('Y-m-d\TH:i') : '',
            'additional_information' => $event->additional_information ?? '',
            'location' => $event->location ?? '',
            'venue_name' => $event->venue_name ?? '',
            'venue_address' => $event->venue_address ?? '',
            'latitude' => $event->latitude ?? '',
            'longitude' => $event->longitude ?? '',
            'invitation_title' => $event->invitation_title ?? "You're Invited!",
            'invitation_subtitle' => $event->invitation_subtitle ?? '',
            'invitation_message' => $event->invitation_message ?? '',
        ];
        $this->eventCreationService->storeStep(1, $step1Data);

        // Step 2: Event settings
        $step2Data = [
            'qr_checkin_enabled' => $event->qr_checkin_enabled ?? false,
            'rsvp_enabled' => $event->rsvp_enabled ?? false,
            'invitation_platforms' => $event->invitation_platforms ?? ['email'],
            'guest_list_ids' => $event->guestLists->pluck('id')->toArray(),
        ];
        $this->eventCreationService->storeStep(2, $step2Data);

        // Step 3: Message configuration
        $step3Data = [
            // Preserve explicit empty string; only fallback to invitation_message when value is truly null
            'general_message' => ($event->general_message !== null) ? $event->general_message : ($event->invitation_message ?? ''),
            'group_messages' => $event->group_messages ?? [],
            'per_guest_messages' => $event->per_guest_messages ?? [],
            'rsvp_message' => $event->rsvp_message ?? '',
            'rsvp_deadline' => $event->rsvp_deadline ?? '',
            'rsvp_contact' => $event->rsvp_contact ?? '',
            'qr_description' => $event->qr_description ?? '',
            'ai_generated' => $event->ai_generated ?? false,
        ];
        $this->eventCreationService->storeStep(3, $step3Data);

        // Step 4: Scheduling
        $step4Data = [
            'send_type' => $event->send_type ?? 'now',
            'scheduled_at' => $event->scheduled_at ? $event->scheduled_at->format('Y-m-d\TH:i') : '',
        ];
        $this->eventCreationService->storeStep(4, $step4Data);
        
        // Log the pre-populated data for debugging
        Log::info('Pre-populated draft data', [
            'event_id' => $event->id,
            'step1_data' => $step1Data,
            'step2_data' => $step2Data,
            'step3_data' => $step3Data,
            'step4_data' => $step4Data,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        // Different validation rules based on event status
        if ($event->status === 'sent') {
            // For sent events, allow updates but don't change status
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => $this->validateEndDate($request),
                'venue_name' => 'nullable|string|max:255',
                'venue_address' => 'nullable|string',
                'parking_info' => 'nullable|string|max:255',
                
                // Invitation Settings
                'invitation_title' => 'nullable|string|max:255',
                'invitation_subtitle' => 'nullable|string|max:255',
                'invitation_message' => 'nullable|string',
                'rsvp_message' => 'nullable|string',
                'rsvp_deadline' => 'nullable|string|max:255',
                'rsvp_contact' => 'nullable|string|max:255',
                'rsvp_enabled' => 'boolean',
                
                // QR Code Settings
                'qr_checkin_enabled' => 'boolean',
                'qr_code_url' => 'nullable|url',
                'qr_description' => 'nullable|string|max:255',
                
                // Design Settings
                'hero_color1' => 'nullable|string|max:7',
                'hero_color2' => 'nullable|string|max:7',
                'accent_color' => 'nullable|string|max:7',
                'font_family' => 'nullable|string|max:255',
                
                // Guest Lists (can add new ones)
                'guest_list_ids' => 'nullable|array',
                'guest_list_ids.*' => 'exists:guest_lists,id',
                
                // Update notification
                'notify_guests' => 'boolean',
                'update_message' => 'nullable|string',
            ]);
        } else {
            // For draft/scheduled events, full editing capabilities
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => $this->validateEndDate($request),
                'venue_name' => 'nullable|string|max:255',
                'venue_address' => 'nullable|string',
                'parking_info' => 'nullable|string|max:255',
                
                // Invitation Settings
                'invitation_title' => 'nullable|string|max:255',
                'invitation_subtitle' => 'nullable|string|max:255',
                'invitation_message' => 'nullable|string',
                'rsvp_message' => 'nullable|string',
                'rsvp_deadline' => 'nullable|string|max:255',
                'rsvp_contact' => 'nullable|string|max:255',
                'rsvp_enabled' => 'boolean',
                
                // QR Code Settings
                'qr_checkin_enabled' => 'boolean',
                'qr_code_url' => 'nullable|url',
                'qr_description' => 'nullable|string|max:255',
                
                // Design Settings
                'hero_color1' => 'nullable|string|max:7',
                'hero_color2' => 'nullable|string|max:7',
                'accent_color' => 'nullable|string|max:7',
                'font_family' => 'nullable|string|max:255',
                
                // Guest Lists
                'guest_list_ids' => 'nullable|array',
                'guest_list_ids.*' => 'exists:guest_lists,id',
                
                // Message Template
                'message_template' => 'nullable|string',
                'custom_messages' => 'nullable|array',
                'attachments' => 'nullable|array',
                
                // Scheduling
                'send_type' => 'required|in:now,scheduled',
                'scheduled_at' => 'nullable|date|required_if:send_type,scheduled',
                'status' => 'required|in:draft,scheduled',
            ]);
        }

        // Handle file uploads for attachments (only for draft/scheduled)
        if ($event->status !== 'sent' && $request->hasFile('attachments')) {
            $attachments = $event->attachments ?? [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('event-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
            $validated['attachments'] = $attachments;
        }

        // Store original values for comparison
        $originalEvent = $event->toArray();
        
        // Update the event
        $event->update($validated);

        // Handle guest lists
        if (isset($validated['guest_list_ids'])) {
            $originalGuestListIds = $event->guestLists->pluck('id')->toArray();
            $newGuestListIds = $validated['guest_list_ids'];
            
            // Sync guest lists
            $event->guestLists()->sync($newGuestListIds);
            
            // If this is a sent event and new guest lists were added, send invitations to new guests
            if ($event->status === 'sent') {
                $newGuestLists = $event->guestLists()->whereNotIn('guest_lists.id', $originalGuestListIds)->get();
                foreach ($newGuestLists as $guestList) {
                    foreach ($guestList->guests as $guest) {
                        // Create invitation for new guest
                        $invitation = $event->invitations()->create([
                            'guest_id' => $guest->id,
                            'token' => \Str::random(32),
                            'status' => 'pending',
                        ]);
                        
                        // Send invitation to new guest
                        // TODO: Implement invitation sending logic
                        // dispatch(new SendEventInvitation($invitation));
                    }
                }
            }
        }

        // Handle guest notifications for sent events
        if ($event->status === 'sent' && $request->boolean('notify_guests')) {
            $this->sendUpdateNotifications($event, $originalEvent, $validated, $request->input('update_message'));
        }

        $successMessage = $event->status === 'sent' ? 'Event updated successfully! Guests have been notified of changes.' : 'Event updated successfully!';
        
        return redirect()->route('organizer.events.show', $event)
            ->with('success', $successMessage);
    }

    private function sendUpdateNotifications($event, $originalEvent, $newData, $updateMessage = null)
    {
        // Get all invitations for this event
        $invitations = $event->invitations()->with('guest')->get();
        
        foreach ($invitations as $invitation) {
            if ($invitation->guest) {
                // Prepare update notification
                $changes = $this->detectChanges($originalEvent, $newData);
                
                if (!empty($changes)) {
                    // TODO: Implement update notification sending
                    // dispatch(new SendEventUpdateNotification($invitation, $changes, $updateMessage));
                    
                    \Log::info('Update notification would be sent to guest: ' . $invitation->guest->email, [
                        'event_id' => $event->id,
                        'guest_id' => $invitation->guest->id,
                        'changes' => $changes,
                        'update_message' => $updateMessage
                    ]);
                }
            }
        }
    }

    private function detectChanges($original, $new)
    {
        $changes = [];
        
        $fieldsToCheck = [
            'name' => 'Event Name',
            'description' => 'Event Description',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'venue_name' => 'Venue Name',
            'venue_address' => 'Venue Address',
            'parking_info' => 'Parking Information',
            'invitation_title' => 'Invitation Title',
            'invitation_subtitle' => 'Invitation Subtitle',
            'invitation_message' => 'Invitation Message',
            'rsvp_message' => 'RSVP Message',
            'rsvp_deadline' => 'RSVP Deadline',
            'rsvp_contact' => 'RSVP Contact',
        ];
        
        foreach ($fieldsToCheck as $field => $label) {
            if (isset($new[$field]) && $original[$field] !== $new[$field]) {
                $changes[$field] = [
                    'label' => $label,
                    'old' => $original[$field],
                    'new' => $new[$field]
                ];
            }
        }
        
        return $changes;
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        
        try {
            $event->delete();
            
            // Return JSON response for AJAX requests
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event deleted successfully.'
                ]);
            }
            
            // Return redirect for regular requests
            return redirect()->route('organizer.events.index')
                ->with('success', 'Event deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error deleting event: ' . $e->getMessage());
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting event. Please try again.'
                ], 500);
            }
            
            return redirect()->route('organizer.events.index')
                ->with('error', 'Error deleting event. Please try again.');
        }
    }

    public function preview(Event $event)
    {
        $this->authorize('view', $event);
        
        // Create a sample invitation for preview purposes
        $invitation = new \App\Shared\Models\Invitation([
            'event_id' => $event->id,
            'guest_id' => null, // No specific guest for preview
            'token' => 'preview-' . uniqid(),
            'rsvp_status' => 'none',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        
        // Create a sample guest for preview
        $guest = new \App\Shared\Models\Guest([
            'name' => 'Sample Guest',
            'email' => 'guest@example.com',
            'phone' => '+1 (555) 123-4567',
        ]);
        
        // Generate the invite URL for preview
        $inviteUrl = route('public.invite.show', ['token' => $invitation->token]);
        
        // Load the event with guest lists for context
        $event->load('guestLists.guests');
        
        return view('invitations.show', compact('event', 'invitation', 'inviteUrl', 'guest'));
    }

    public function sendInvitations(Event $event)
    {
        $this->authorize('update', $event);
        
        // Here you would implement the logic to send invitations
        // dispatch(new SendEventInvitations($event));
        
        $event->update(['status' => 'sent']);
        
        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Invitations sent successfully!');
    }

    /**
     * Display the scheduled messages view
     */
    public function scheduledMessagesView()
    {
        return view('organizer.scheduled-messages');
    }

    /**
     * Get all scheduled messages for the authenticated user
     * This includes both scheduled events and individual reminder messages
     */
    public function getScheduledMessages()
    {
        $userId = Auth::id();
        $userTimezone = Auth::user()->timezone ?? 'UTC';
        
        // Get scheduled events
        $scheduledEvents = Event::where('user_id', $userId)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->with(['guestLists.guests'])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // Get scheduled reminders
        $scheduledReminders = \App\Shared\Models\Reminder::whereHas('event', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('status', 'pending')
        ->where('scheduled_for', '>', now())
        ->orderBy('scheduled_for', 'asc')
        ->get();

        // Get past reminders
        $pastReminders = \App\Shared\Models\Reminder::whereHas('event', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereIn('status', ['sent', 'failed'])
        ->orderBy('scheduled_for', 'desc')
        ->limit(50)
        ->get();

        $scheduledMessages = [
            'scheduled_events' => $scheduledEvents->map(function($event) use ($userTimezone) {
                return [
                    'id' => $event->id,
                    'type' => 'event_invitations',
                    'name' => $event->name,
                    'scheduled_at' => $event->scheduled_at,
                    'scheduled_at_local' => $event->scheduled_at ? $event->scheduled_at->setTimezone($userTimezone)->toDateTimeString() : null,
                    'event_date' => $event->start_date,
                    'event_date_local' => $event->start_date ? $event->start_date->setTimezone($userTimezone)->toDateTimeString() : null,
                    'status' => $event->status,
                    'guest_count' => $event->guestLists->sum(function($list) {
                        return $list->guests->count();
                    }),
                    'platforms' => $event->invitation_platforms ?? ['email'],
                    'description' => $event->description,
                    'location' => $event->location ?? $event->venue_name,
                ];
            }),
            'scheduled_reminders' => $scheduledReminders->map(function($reminder) use ($userTimezone) {
                return [
                    'id' => $reminder->id,
                    'type' => 'reminder',
                    'event_name' => $reminder->event_name,
                    'guest_name' => $reminder->guest_name,
                    'platform' => $reminder->platform,
                    'scheduled_for' => $reminder->scheduled_for,
                    'scheduled_for_local' => $reminder->scheduled_for ? $reminder->scheduled_for->setTimezone($userTimezone)->toDateTimeString() : null,
                    'event_date' => $reminder->event_date,
                    'event_date_local' => $reminder->event_date ? $reminder->event_date->setTimezone($userTimezone)->toDateTimeString() : null,
                    'status' => $reminder->status,
                    'message_content' => $reminder->message_content,
                    'subject' => $reminder->subject,
                    'attempts' => $reminder->attempts,
                    'guest_email' => $reminder->guest_email,
                    'guest_phone' => $reminder->guest_phone,
                ];
            }),
            'past_reminders' => $pastReminders->map(function($reminder) use ($userTimezone) {
                return [
                    'id' => $reminder->id,
                    'type' => 'reminder',
                    'event_name' => $reminder->event_name,
                    'guest_name' => $reminder->guest_name,
                    'platform' => $reminder->platform,
                    'scheduled_for' => $reminder->scheduled_for,
                    'scheduled_for_local' => $reminder->scheduled_for ? $reminder->scheduled_for->setTimezone($userTimezone)->toDateTimeString() : null,
                    'sent_at' => $reminder->sent_at,
                    'sent_at_local' => $reminder->sent_at ? $reminder->sent_at->setTimezone($userTimezone)->toDateTimeString() : null,
                    'event_date' => $reminder->event_date,
                    'event_date_local' => $reminder->event_date ? $reminder->event_date->setTimezone($userTimezone)->toDateTimeString() : null,
                    'status' => $reminder->status,
                    'attempts' => $reminder->attempts,
                    'error_message' => $reminder->error_message,
                ];
            }),
            'summary' => [
                'total_scheduled_events' => $scheduledEvents->count(),
                'total_scheduled_reminders' => $scheduledReminders->count(),
                'total_past_reminders' => $pastReminders->count(),
                'next_scheduled_event' => $scheduledEvents->first()?->scheduled_at,
                'next_scheduled_event_local' => $scheduledEvents->first() ? $scheduledEvents->first()->scheduled_at->setTimezone($userTimezone)->toDateTimeString() : null,
                'next_scheduled_reminder' => $scheduledReminders->first()?->scheduled_for,
                'next_scheduled_reminder_local' => $scheduledReminders->first() ? $scheduledReminders->first()->scheduled_for->setTimezone($userTimezone)->toDateTimeString() : null,
                'user_timezone' => $userTimezone,
            ]
        ];
        
        Log::info('📅 [SCHEDULED_MESSAGES] Retrieved scheduled messages', [
            'user_id' => $userId,
            'user_timezone' => $userTimezone,
            'scheduled_events_count' => $scheduledEvents->count(),
            'scheduled_reminders_count' => $scheduledReminders->count(),
            'past_reminders_count' => $pastReminders->count(),
        ]);
        
        return response()->json($scheduledMessages);
    }

    /**
     * Get scheduled messages with additional filtering options
     */
    public function getScheduledMessagesFiltered(Request $request)
    {
        $userId = Auth::id();
        $filters = $request->only(['type', 'status', 'date_from', 'date_to', 'platform']);
        
        $query = \App\Shared\Models\Reminder::whereHas('event', function($query) use ($userId) {
            $query->where('user_id', $userId);
        });
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('scheduled_for', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('scheduled_for', '<=', $filters['date_to']);
        }
        
        $reminders = $query->orderBy('scheduled_for', 'desc')
            ->with(['event', 'guest', 'invitation'])
            ->paginate(20);
        
        return response()->json($reminders);
    }

    // -------------------- Sent Event Update Methods --------------------

    /**
     * Show the sent event update page
     */
    public function updateSentEvent(Event $event)
    {
        $this->authorize('update', $event);
        
        // Only allow updates for sent events
        if ($event->status !== 'sent') {
            return redirect()->route('organizer.events.edit', $event)
                ->with('error', 'This feature is only available for sent events.');
        }

        // Load event with all related data
        $event->load([
            'guestLists.guests.invitations' => function($query) use ($event) {
                $query->where('event_id', $event->id);
            },
            'guestLists.guests.checkedInBy',
            'guestLists.guestGroups'
        ]);

        // Use EventGuestService to get active guests
        $eventGuestService = app(\App\Services\EventGuestService::class);
        $activeEventGuests = $eventGuestService->getActiveGuestsForEvent($event);
        
        // Calculate statistics
        $totalGuests = $activeEventGuests->count();

        $invitationsSent = $event->invitations()->where('status', 'sent')->count();
        $invitationsPending = $event->invitations()->where('status', 'pending')->count();

        // Get available guest lists (not already attached to this event)
        $availableGuestLists = GuestList::where('user_id', Auth::id())
            ->whereNotIn('id', $event->guestLists->pluck('id'))
            ->withCount('guests')
            ->get();

        // Get standalone guests (guests without guest_list_id)
        $standaloneGuests = $activeEventGuests->filter(function($eventGuest) {
            return $eventGuest->guest->isStandalone();
        })->map(function($eventGuest) {
            return $eventGuest->guest;
        });

        // Calculate new guests count (guests without messages)
        $newGuestsCount = 0;
        
        // Check all active guests for messages
        foreach ($activeEventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            $hasMessage = false;
            
            // Check if guest has a message in any of the message types
            if (!empty($event->general_message)) {
                $hasMessage = true;
            } elseif (!empty($event->group_messages)) {
                foreach ($event->group_messages as $listId => $groupMessages) {
                    if ($guest->guest_list_id && $listId == $guest->guest_list_id) {
                        foreach ($groupMessages as $groupId => $message) {
                            if ($guest->group_id == $groupId && !empty($message)) {
                                $hasMessage = true;
                                break 2;
                            }
                        }
                    }
                }
            } elseif (!empty($event->per_guest_messages) && isset($event->per_guest_messages[$guest->id])) {
                $hasMessage = !empty($event->per_guest_messages[$guest->id]);
            }
            
            if (!$hasMessage) {
                $newGuestsCount++;
            }
        }

        // Get recent notifications (you'll need to create a notifications table)
        $recentNotifications = collect(); // Placeholder - implement notifications table later

        return view('organizer.events.update-sent', compact(
            'event',
            'totalGuests',
            'invitationsSent',
            'invitationsPending',
            'availableGuestLists',
            'standaloneGuests',
            'activeEventGuests',
            'newGuestsCount',
            'recentNotifications'
        ));
    }

    /**
     * Update basic information for a sent event
     */
    public function updateSentEventBasic(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return back()->with('error', 'This feature is only available for sent events.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
        ]);

        // Store original values for comparison
        $originalEvent = $event->toArray();
        
        // Update the event
        $event->update($validated);

        // Detect changes and notify guests if needed
        $changes = $this->detectChanges($originalEvent, $validated);
        
        if (!empty($changes)) {
            // Log the changes for potential notification
            Log::info('Event updated with changes', [
                'event_id' => $event->id,
                'changes' => $changes,
                'user_id' => Auth::id()
            ]);
        }

        return back()->with('success', 'Event information updated successfully!');
    }

    /**
     * Add a guest list to a sent event
     */
    public function addGuestListToSentEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return back()->with('error', 'This feature is only available for sent events.');
        }

        $validated = $request->validate([
            'guest_list_id' => 'required|exists:guest_lists,id'
        ]);

        // Check if guest list belongs to user
        $guestList = GuestList::where('id', $validated['guest_list_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$guestList) {
            return back()->with('error', 'Guest list not found or access denied.');
        }

        // Check if guest list is already attached
        if ($event->guestLists()->where('guest_lists.id', $guestList->id)->exists()) {
            return back()->with('error', 'This guest list is already attached to the event.');
        }

        // Attach the guest list
        $event->guestLists()->attach($guestList->id);

        // Use EventGuestService to add guest list to event
        $eventGuestService = app(\App\Services\EventGuestService::class);
        $eventGuests = $eventGuestService->addGuestListToEvent($event, $guestList);

        // Create invitations for all guests in the new list
        $guestsData = [];
        foreach ($guestList->guests as $guest) {
            $invitation = $event->invitations()->create([
                'guest_id' => $guest->id,
                'token' => Str::random(32),
                'status' => 'pending',
            ]);
            
            $guestsData[] = [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'guest_list_name' => $guestList->name
            ];
        }

        return response()->json([
            'success' => true,
            'message' => "Guest list '{$guestList->name}' added successfully! {$guestList->guests->count()} new invitations created.",
            'guests' => $guestsData
        ]);
    }

    /**
     * Add an individual guest to a sent event
     */
    public function addGuestToSentEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return back()->with('error', 'This feature is only available for sent events.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'guest_list_id' => 'nullable|exists:guest_lists,id'
        ]);

        // Create the guest
        $guest = new \App\Shared\Models\Guest();
        $guest->name = $validated['name'];
        $guest->email = $validated['email'];
        $guest->phone = $validated['phone'];
        
        // If guest_list_id is provided, assign to that list
        if (!empty($validated['guest_list_id'])) {
            // Check if guest list belongs to the event
            $guestList = $event->guestLists()->where('guest_lists.id', $validated['guest_list_id'])->first();
            if (!$guestList) {
                return back()->with('error', 'Invalid guest list selected.');
            }
            $guest->guest_list_id = $validated['guest_list_id'];
        } else {
            // This is a standalone guest (no guest list)
            $guest->guest_list_id = null;
        }
        
        $guest->save();

        // Use EventGuestService to add guest to event
        $eventGuestService = app(\App\Services\EventGuestService::class);
        $eventGuest = $eventGuestService->addGuestToEvent($event, $guest);

        // Create invitation for the new guest
        $invitation = $event->invitations()->create([
            'guest_id' => $guest->id,
            'token' => Str::random(32),
            'status' => 'pending',
        ]);

        // Prepare response data
        $responseData = [
            'success' => true,
            'message' => !empty($validated['guest_list_id']) 
                ? "Guest '{$guest->name}' added successfully to guest list! Invitation created."
                : "Guest '{$guest->name}' added successfully as standalone guest! Invitation created.",
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'guest_list_name' => $guest->getGuestListName()
            ]
        ];

        return response()->json($responseData);
    }

    /**
     * Remove a guest from a sent event
     */
    public function removeGuestFromSentEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return back()->with('error', 'This feature is only available for sent events.');
        }

        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'notification_message' => 'required|string|max:1000',
            'notify_email' => 'nullable|boolean',
            'notify_whatsapp' => 'nullable|boolean'
        ]);

        // Find the guest
        $guest = \App\Shared\Models\Guest::findOrFail($validated['guest_id']);

        // Use the EventGuestService to manage the relationship
        $eventGuestService = app(\App\Services\EventGuestService::class);
        
        // Check if guest is active for this event
        if (!$eventGuestService->isGuestActiveForEvent($event, $guest)) {
            return back()->with('error', 'Guest is not active for this event.');
        }

        // Get the invitation before expiring it
        $invitation = $event->invitations()->where('guest_id', $guest->id)->first();
        
        // Store guest info for notification
        $guestName = $guest->name;
        $guestEmail = $guest->email;
        $guestPhone = $guest->phone;
        $isStandalone = $guest->isStandalone();

        // Expire the invitation
        if ($invitation) {
            $invitation->update([
                'status' => \App\Shared\Models\Invitation::STATUS_EXPIRED,
                'expired_at' => now()
            ]);
        }

        // Remove guest from event using the service
        $eventGuest = $eventGuestService->removeGuestFromEvent(
            $event, 
            $guest, 
            'Removed by organizer', 
            Auth::id()
        );

        // Send notification messages
        $notificationSent = false;
        $notificationErrors = [];

        // Send email notification
        if (!empty($validated['notify_email']) && $guestEmail) {
            try {
                $this->sendGuestRemovalEmail($guest, $event, $validated['notification_message']);
                $notificationSent = true;
            } catch (\Exception $e) {
                $notificationErrors[] = 'Email: ' . $e->getMessage();
            }
        }

        // Send WhatsApp notification
        if (!empty($validated['notify_whatsapp']) && $guestPhone) {
            try {
                $this->sendGuestRemovalWhatsApp($guest, $event, $validated['notification_message']);
                $notificationSent = true;
            } catch (\Exception $e) {
                $notificationErrors[] = 'WhatsApp: ' . $e->getMessage();
            }
        }

        // Log the removal action
        \Log::info('Guest removed from event', [
            'event_id' => $event->id,
            'guest_id' => $guest->id,
            'guest_name' => $guestName,
            'guest_list_id' => $guest->guest_list_id,
            'is_standalone' => $isStandalone,
            'removed_by' => Auth::id(),
            'notification_sent' => $notificationSent,
            'notification_errors' => $notificationErrors,
            'event_guest_id' => $eventGuest->id
        ]);

        // Prepare success message
        $message = $isStandalone 
            ? "Standalone guest '{$guestName}' removed successfully from the event."
            : "Guest '{$guestName}' removed successfully from the event (remains in their guest list).";

        if ($notificationSent) {
            $message .= " Notification sent successfully.";
        }

        if (!empty($notificationErrors)) {
            $message .= " Some notifications failed: " . implode(', ', $notificationErrors);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a guest list from a sent event
     */
    public function removeGuestListFromSentEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return response()->json(['success' => false, 'message' => 'This feature is only available for sent events.'], 400);
        }

        $validated = $request->validate([
            'guest_list_id' => 'required|exists:guest_lists,id'
        ]);

        // Check if guest list is attached to the event
        $guestList = $event->guestLists()->where('guest_lists.id', $validated['guest_list_id'])->first();
        if (!$guestList) {
            return response()->json(['success' => false, 'message' => 'Guest list not found in this event.'], 400);
        }

        // Delete all invitations for guests in this list
        $guestIds = $guestList->guests->pluck('id');
        $event->invitations()->whereIn('guest_id', $guestIds)->delete();

        // Detach the guest list
        $event->guestLists()->detach($guestList->id);

        return response()->json([
            'success' => true,
            'message' => "Guest list '{$guestList->name}' removed successfully!"
        ]);
    }

    /**
     * Generate messages for new guests
     */
    public function generateMessagesForNewGuests(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return back()->with('error', 'This feature is only available for sent events.');
        }

        $validated = $request->validate([
            'message_type' => 'required|in:general,group,individual'
        ]);

        // Use EventGuestService to get active guests
        $eventGuestService = app(\App\Services\EventGuestService::class);
        $activeEventGuests = $eventGuestService->getActiveGuestsForEvent($event);

        // Get all guests without messages
        $guestsWithoutMessages = [];
        
        foreach ($activeEventGuests as $eventGuest) {
            $guest = $eventGuest->guest;
            $hasMessage = false;
            
            // Check if guest has a message
            if (!empty($event->general_message)) {
                $hasMessage = true;
            } elseif (!empty($event->group_messages)) {
                foreach ($event->group_messages as $listId => $groupMessages) {
                    if ($guest->guest_list_id && $listId == $guest->guest_list_id) {
                        foreach ($groupMessages as $groupId => $message) {
                            if ($guest->group_id == $groupId && !empty($message)) {
                                $hasMessage = true;
                                break 2;
                            }
                        }
                    }
                }
            } elseif (!empty($event->per_guest_messages) && isset($event->per_guest_messages[$guest->id])) {
                $hasMessage = !empty($event->per_guest_messages[$guest->id]);
            }
            
            if (!$hasMessage) {
                $guestsWithoutMessages[] = $guest;
            }
        }

        if (empty($guestsWithoutMessages)) {
            return back()->with('info', 'All guests already have messages assigned.');
        }

        // Generate messages based on type
        switch ($validated['message_type']) {
            case 'general':
                // Use existing general message or create a new one
                if (empty($event->general_message)) {
                    $event->update([
                        'general_message' => "You're invited to {$event->name}! We hope you can join us for this special event."
                    ]);
                }
                break;

            case 'group':
                // Generate group messages
                $groupMessages = $event->group_messages ?? [];
                foreach ($event->guestLists as $guestList) {
                    if (!isset($groupMessages[$guestList->id])) {
                        $groupMessages[$guestList->id] = [];
                    }
                    
                    foreach ($guestList->guestGroups as $group) {
                        if (!isset($groupMessages[$guestList->id][$group->id])) {
                            $groupMessages[$guestList->id][$group->id] = "Hello {$group->name}! You're invited to {$event->name}. We look forward to seeing you!";
                        }
                    }
                }
                $event->update(['group_messages' => $groupMessages]);
                break;

            case 'individual':
                // Generate individual messages
                $perGuestMessages = $event->per_guest_messages ?? [];
                foreach ($guestsWithoutMessages as $guest) {
                    $perGuestMessages[$guest->id] = "Hello {$guest->name}! You're invited to {$event->name}. We hope you can join us for this special event.";
                }
                $event->update(['per_guest_messages' => $perGuestMessages]);
                break;
        }

        return back()->with('success', "Messages generated successfully for {$validated['message_type']} type!");
    }

    /**
     * Save individual guest message
     */
    public function saveGuestMessage(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return response()->json(['success' => false, 'message' => 'This feature is only available for sent events.'], 400);
        }

        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'message' => 'required|string|max:1000'
        ]);

        // Verify guest belongs to this event
        $guest = \App\Shared\Models\Guest::whereHas('invitations', function($query) use ($event) {
            $query->where('event_id', $event->id);
        })->findOrFail($validated['guest_id']);

        // Update or create per-guest message
        $perGuestMessages = $event->per_guest_messages ?? [];
        $perGuestMessages[$guest->id] = $validated['message'];
        
        $event->update(['per_guest_messages' => $perGuestMessages]);

        return response()->json([
            'success' => true,
            'message' => "Message saved successfully for {$guest->name}!"
        ]);
    }

    /**
     * Send email notification for guest removal
     */
    private function sendGuestRemovalEmail($guest, $event, $message)
    {
        if (!$guest->email) {
            throw new \Exception('Guest has no email address');
        }

        // Replace placeholders in the message
        $personalizedMessage = str_replace(
            ['[Guest Name]', '[Event Name]', '[Your Name]'],
            [$guest->name, $event->name, Auth::user()->name],
            $message
        );

        // Send email using Laravel's mail system
        \Mail::raw($personalizedMessage, function($mail) use ($guest, $event) {
            $mail->to($guest->email)
                 ->subject("Invitation Cancelled - {$event->name}")
                 ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }

    /**
     * Send WhatsApp notification for guest removal
     */
    private function sendGuestRemovalWhatsApp($guest, $event, $message)
    {
        if (!$guest->phone) {
            throw new \Exception('Guest has no phone number');
        }

        // Replace placeholders in the message
        $personalizedMessage = str_replace(
            ['[Guest Name]', '[Event Name]', '[Your Name]'],
            [$guest->name, $event->name, Auth::user()->name],
            $message
        );

        // Use Twilio service to send WhatsApp message
        $twilioService = app(\App\Services\TwilioService::class);
        
        $twilioService->sendWhatsAppMessage(
            $guest->phone,
            $personalizedMessage
        );
    }

    /**
     * Notify guests of event updates
     */
    public function notifyGuestsOfUpdates(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        
        if ($event->status !== 'sent') {
            return back()->with('error', 'This feature is only available for sent events.');
        }

        $validated = $request->validate([
            'platform' => 'required|in:email,whatsapp,both',
            'notification_type' => 'required|in:update,reminder,custom',
            'custom_message' => 'nullable|string'
        ]);

        // Get all invitations for this event
        $invitations = $event->invitations()->with('guest')->get();
        
        $sentCount = 0;
        $failedCount = 0;

        foreach ($invitations as $invitation) {
            if ($invitation->guest) {
                try {
                    // Prepare notification message
                    $message = $this->prepareNotificationMessage($event, $validated['notification_type'], $validated['custom_message']);
                    
                    // Send notification based on platform
                    if (in_array($validated['platform'], ['email', 'both'])) {
                        // Send email notification
                        // TODO: Implement email sending
                        Log::info('Email notification would be sent', [
                            'guest_email' => $invitation->guest->email,
                            'message' => $message
                        ]);
                        $sentCount++;
                    }
                    
                    if (in_array($validated['platform'], ['whatsapp', 'both'])) {
                        // Send WhatsApp notification
                        // TODO: Implement WhatsApp sending
                        Log::info('WhatsApp notification would be sent', [
                            'guest_phone' => $invitation->guest->phone,
                            'message' => $message
                        ]);
                        $sentCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send notification', [
                        'guest_id' => $invitation->guest->id,
                        'error' => $e->getMessage()
                    ]);
                    $failedCount++;
                }
            }
        }

        $message = "Notifications sent: {$sentCount} successful, {$failedCount} failed.";
        return back()->with('success', $message);
    }

    /**
     * Get individual messages for an event
     */
    public function getIndividualMessages(Event $event)
    {
        $this->authorize('view', $event);
        
        $messages = [];
        
        if (!empty($event->per_guest_messages)) {
            foreach ($event->guestLists as $guestList) {
                foreach ($guestList->guests as $guest) {
                    if (isset($event->per_guest_messages[$guest->id])) {
                        $messages[] = [
                            'guest_name' => $guest->name,
                            'guest_list_name' => $guestList->name,
                            'message' => $event->per_guest_messages[$guest->id]
                        ];
                    }
                }
            }
        }

        return response()->json(['messages' => $messages]);
    }

    /**
     * Prepare notification message based on type
     */
    private function prepareNotificationMessage(Event $event, string $type, ?string $customMessage = null): string
    {
        switch ($type) {
            case 'update':
                return "Update for {$event->name}: There have been changes to the event details. Please check your invitation for the latest information.";
            
            case 'reminder':
                return "Reminder: {$event->name} is coming up on {$event->start_date->format('M j, Y g:i A')}. We look forward to seeing you!";
            
            case 'custom':
                return $customMessage ?? "Update for {$event->name}: Please check your invitation for the latest information.";
            
            default:
                return "Update for {$event->name}: Please check your invitation for the latest information.";
        }
    }
} 