<?php

/**
 * DashboardController
 * 
 * This controller handles the organizer dashboard functionality.
 * It provides the main dashboard view with statistics and overview
 * of the organizer's guest lists and activities.
 * 
 * Responsibilities:
 * - Handle dashboard HTTP requests and responses
 * - Manage dashboard authorization
 * - Return dashboard views and JSON data
 */

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Organizer\Services\OrganizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    protected $organizerService;

    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    /**
     * Show the main dashboard view
     */
    public function dashboard()
    {
        Gate::authorize('organizer-access');

        $stats = $this->organizerService->getDashboardStats();
        
        return view('organizer.dashboard', compact('stats'));
    }

    /**
     * Show organizer reports view
     */
    public function reports()
    {
        Gate::authorize('view-organizer-reports');

        $reports = $this->organizerService->getReports();
        
        return view('reports', compact('reports'));
    }

    /**
     * Get dashboard statistics as JSON
     */
    public function stats(Request $request)
    {
        Gate::authorize('organizer-access');

        $stats = $this->organizerService->getDashboardStats();
        
        return response()->json([
            'total_lists' => $stats['total_guest_lists'],
            'total_guests' => $stats['total_guests'],
            'upcoming_events' => $stats['upcoming_events']->count(),
            'checkin_rate' => $this->organizerService->getCheckInRate(),
        ]);
    }

    /**
     * Get guest lists as JSON for dashboard widgets
     */
    public function guestListsJson(Request $request)
    {
        Gate::authorize('organizer-access');

        $guestLists = $this->organizerService->getMyGuestLists();
        
        return response()->json($guestLists);
    }
} 