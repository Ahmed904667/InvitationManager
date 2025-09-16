<?php

/**
 * GuestListController
 * 
 * This controller handles guest list HTTP operations and delegates business logic to services.
 * 
 * Responsibilities:
 * - Handle HTTP requests and responses
 * - Request validation and authorization
 * - View rendering and redirects
 * - Session management (flash messages)
 */

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\GuestList;
use App\Shared\Models\GuestGroup;
use App\Organizer\Services\OrganizerService;
use App\Organizer\Services\GuestListValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use PragmaRX\Countries\Package\Countries;

class GuestListController extends Controller
{
    protected $organizerService;
    protected $validationService;

    public function __construct(OrganizerService $organizerService, GuestListValidationService $validationService)
    {
        $this->organizerService = $organizerService;
        $this->validationService = $validationService;
    }

    /**
     * Display the guest lists index page
     */
    public function index()
    {
        Gate::authorize('view-guest-lists');

        $guestLists = $this->organizerService->getMyGuestLists();
        
        return view('organizer.guest-lists.index', compact('guestLists'));
    }

    /**
     * Show the create guest list form
     */
    public function create()
    {
        Gate::authorize('create-guest-list');

        // Redirect to index page with create modal parameter
        return redirect()->route('organizer.guest-lists.index', ['create' => 'true']);
    }

    /**
     * Store a new guest list
     */
    public function store(Request $request)
    {
        Gate::authorize('create-guest-list');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'max_guests' => 'nullable|integer|min:1|max:10000',
            'settings' => 'array'
        ]);

        $guestList = $this->organizerService->createGuestList($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Guest list created successfully!',
                'guest_list' => $guestList
            ]);
        }

        return redirect()->route('organizer.guest-lists.edit', $guestList)
            ->with('success', 'Guest list created successfully!');
    }

    /**
     * Display a guest list with detailed view
     */
    public function show(GuestList $guestList)
    {
        Gate::authorize('view-guest-list', $guestList);

        $data = $this->organizerService->getGuestListDisplayData($guestList);
        
        return view('organizer.guest-lists.edit', $data);
    }

    /**
     * Display a guest list in display mode
     */
    public function display(GuestList $guestList)
    {
        Gate::authorize('view-guest-list', $guestList);

        $data = $this->organizerService->getGuestListDisplayData($guestList);
        
        return view('organizer.guest-lists.display', $data);
    }

    /**
     * Show the edit guest list form
     */
    public function edit(GuestList $guestList)
    {
        Gate::authorize('update-guest-list', $guestList);
        $data = $this->organizerService->getGuestListDisplayData($guestList);
        
        // Add validation errors to the data
        $data['validationErrors'] = $this->validationService->getValidationErrors($guestList);
        $data['errorSummary'] = $this->validationService->getErrorSummary($guestList);
        
        return view('organizer.guest-lists.edit', $data);
    }

    /**
     * Update a guest list
     */
    public function update(Request $request, GuestList $guestList)
    {
        Gate::authorize('update-guest-list', $guestList);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'max_guests' => 'nullable|integer|min:1|max:10000',
            'settings' => 'array'
        ]);

        $this->organizerService->updateGuestList($guestList, $validated);

        return redirect()->route('organizer.guest-lists.edit', $guestList)
            ->with('success', 'Guest list updated successfully!');
    }

    public function editCountries(GuestList $guestList)
    {
        $countries = Countries::all()
            ->map(fn($country) => $country->callingCodes ?? [])
            ->flatten()
            ->filter()   // removes null/empty
            ->unique()
            ->sort()
            ->values();

        return view('organizer.guest-lists.edit', compact('guestList', 'countries'));
    }

    /**
     * Update guest list settings via AJAX
     */
    public function updateSettings(Request $request, GuestList $guestList)
    {
        Gate::authorize('update-guest-list', $guestList);
    
        $validated = $request->validate([
            'enable_email' => 'nullable|string',
            'enable_phone' => 'nullable|string',
            'enable_group' => 'nullable|string',
            'enable_language' => 'nullable|string',
            'default_country_code' => 'nullable|string',
            'default_language' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Convert checkbox values to booleans
        $booleanFields = ['enable_email', 'enable_phone', 'enable_group', 'enable_language'];
        foreach ($booleanFields as $field) {
            // Checkbox is checked if the field exists in the request
            $validated[$field] = $request->has($field);
        }

        $result = $this->organizerService->updateGuestListSettings($guestList, $validated);

        return response()->json($result);
    }

    /**
     * Delete a guest list
     */
    public function destroy(GuestList $guestList)
    {
        Gate::authorize('delete-guest-list', $guestList);

        try {
            $this->organizerService->deleteGuestList($guestList);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Guest list deleted successfully!'
                ]);
            }

            return redirect()->route('organizer.guest-lists.index')
                ->with('success', 'Guest list deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting guest list: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('organizer.guest-lists.index')
                ->with('error', 'Error deleting guest list: ' . $e->getMessage());
        }
    }

    /**
     * Get guest groups for a guest list
     */
    public function getGuestGroups(GuestList $guestList)
    {
        Gate::authorize('view-guest-list', $guestList);
        
        $groups = $this->organizerService->getGuestGroups($guestList);
        
        return response()->json($groups);
    }

    /**
     * Get validation errors for a guest list
     */
    public function getValidationErrors(GuestList $guestList)
    {
        Gate::authorize('view-guest-list', $guestList);
        
        $errors = $this->validationService->getValidationErrors($guestList);
        $summary = $this->validationService->getErrorSummary($guestList);
        
        return response()->json([
            'errors' => $errors,
            'summary' => $summary
        ]);
    }

    /**
     * Add a new group to a guest list
     */
    public function addGroup(Request $request, GuestList $guestList)
    {
        Gate::authorize('update-guest-list', $guestList);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $group = $this->organizerService->addGuestGroup($guestList, $validated);
        
        return response()->json(['success' => true, 'group' => $group]);
    }

    /**
     * Update a group in a guest list
     */
    public function updateGroup(Request $request, GuestList $guestList, GuestGroup $group)
    {
        \Log::info('Update group called with guestList ID: ' . $guestList->id . ', group ID: ' . $group->id);
        
        Gate::authorize('update-guest-list', $guestList);
        
        // Ensure the group belongs to this guest list
        if ($group->guest_list_id !== $guestList->id) {
            \Log::error('Group does not belong to guest list. Group guest_list_id: ' . $group->guest_list_id . ', GuestList ID: ' . $guestList->id);
            return response()->json([
                'success' => false,
                'message' => 'Group not found in this guest list'
            ], 404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        
        try {
            $this->organizerService->updateGuestGroup($group, $validated);
            
            return response()->json(['success' => true, 'message' => 'Group updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating group: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a group from a guest list
     */
    public function deleteGroup(Request $request, GuestList $guestList, GuestGroup $group)
    {
        \Log::info('Delete group called with guestList ID: ' . $guestList->id . ', group ID: ' . $group->id);
        
        Gate::authorize('update-guest-list', $guestList);
        
        // Ensure the group belongs to this guest list
        if ($group->guest_list_id !== $guestList->id) {
            \Log::error('Group does not belong to guest list. Group guest_list_id: ' . $group->guest_list_id . ', GuestList ID: ' . $guestList->id);
            return response()->json([
                'success' => false,
                'message' => 'Group not found in this guest list'
            ], 404);
        }
        
        try {
            $this->organizerService->deleteGuestGroup($group);
            
            return response()->json(['success' => true, 'message' => 'Group deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Error deleting group: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk change group for selected guests
     */
    public function bulkChangeGroup(Request $request, GuestList $guestList)
    {
        Gate::authorize('update-guest-list', $guestList);
        
        $validated = $request->validate([
            'guest_ids' => 'required|array',
            'guest_ids.*' => 'required|integer|exists:guests,id',
            'group_id' => 'required|integer|exists:guest_groups,id'
        ]);

        $result = $this->organizerService->bulkChangeGuestGroup($guestList, $validated);

        return response()->json($result);
    }

    /**
     * Get guests for a guest list (JSON response)
     */
    public function getGuests(Request $request, GuestList $guestList)
    {
        Gate::authorize('view-guest-list', $guestList);

        $query = $guestList->guests()->with('group');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by language
        if ($request->filled('language')) {
            if ($request->language === 'no_language') {
                $query->whereNull('language');
            } else {
                $query->where('language', $request->language);
            }
        }

        // Filter by group
        if ($request->filled('group')) {
            if ($request->group === 'no_group') {
                $query->whereNull('group_id');
            } else {
                $query->where('group_id', $request->group);
            }
        }

        // Sort by name (default)
        $query->orderBy('name', 'asc');

        $guests = $query->get();

        return response()->json([
            'guests' => $guests->map(function($guest) {
                return [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'group' => $guest->group ? [
                        'id' => $guest->group->id,
                        'name' => $guest->group->name
                    ] : null,
                    'group_id' => $guest->group_id,
                    'language' => $guest->language,
                    'checked_in' => $guest->checked_in,
                    'checked_in_at' => $guest->checked_in_at,
                    'created_at' => $guest->created_at,
                    'notes' => $guest->notes
                ];
            })
        ]);
    }

    /**
     * Get health information for a guest list (JSON response)
     */
    public function getHealth(GuestList $guestList)
    {
        Gate::authorize('view-guest-list', $guestList);

        $health = $guestList->getHealth();

        return response()->json([
            'health' => $health,
            'guest_list' => [
                'id' => $guestList->id,
                'name' => $guestList->name
            ]
        ]);
    }
} 