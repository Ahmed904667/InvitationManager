<?php

/**
 * GuestController
 * 
 * This controller handles individual guest CRUD operations within guest lists.
 * It manages the creation, updating, and deletion of individual guests
 * and handles both AJAX and regular form submissions.
 * 
 * Responsibilities:
 * - Handle HTTP requests for guest operations
 * - Request validation and authorization
 * - Response formatting and redirects
 * - AJAX and traditional form handling
 */

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Guest;
use App\Shared\Models\GuestList;
use App\Organizer\Services\OrganizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GuestController extends Controller
{
    protected $organizerService;

    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    /**
     * Store a new guest
     */
    public function store(Request $request, GuestList $guestList)
    {
        Gate::authorize('add-guest', $guestList);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'group_id' => 'nullable|exists:guest_groups,id',
            'language' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000'
        ]);

        $guest = $this->organizerService->addGuest($guestList, $validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'guest' => $guest->load('group')
            ]);
        }

        return redirect()->route('organizer.guest-lists.edit', $guestList)
            ->with('success', 'Guest added successfully!');
    }

    /**
     * Update an existing guest
     */
    public function update(Request $request, GuestList $guestList, Guest $guest)
    {
        Gate::authorize('update-guest', $guestList);



        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'group_id' => 'nullable|exists:guest_groups,id',
            'language' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000'
        ]);

        $this->organizerService->updateGuest($guest, $validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'guest' => $guest->load('group')
            ]);
        }

        return redirect()->route('organizer.guest-lists.edit', $guestList)
            ->with('success', 'Guest updated successfully!');
    }

    /**
     * Delete a guest
     */
    public function destroy(GuestList $guestList, Guest $guest)
    {
        Gate::authorize('delete-guest', $guestList);

        $this->organizerService->deleteGuest($guest);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('organizer.guest-lists.edit', $guestList)
            ->with('success', 'Guest deleted successfully!');
    }

    /**
     * Bulk delete guests
     */
    public function bulkDeleteGuests(Request $request, GuestList $guestList)
    {
        Gate::authorize('delete-guest', $guestList);

        $validated = $request->validate([
            'guest_ids' => 'required|array',
            'guest_ids.*' => 'required|integer|exists:guests,id'
        ]);

        $result = $this->organizerService->bulkDeleteGuests($guestList, $validated['guest_ids']);

        return response()->json($result);
    }
} 