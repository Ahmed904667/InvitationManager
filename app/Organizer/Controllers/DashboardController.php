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
use App\Organizer\Services\EventReportService;
use App\Organizer\Services\EventReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    protected $organizerService;
    protected $eventReportService;
    protected $eventReportPdfService;

    public function __construct(OrganizerService $organizerService, EventReportService $eventReportService, EventReportPdfService $eventReportPdfService)
    {
        $this->organizerService = $organizerService;
        $this->eventReportService = $eventReportService;
        $this->eventReportPdfService = $eventReportPdfService;
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
     * Display comprehensive reports and analytics
     */
    public function reports()
    {
        Gate::authorize('organizer-access');

        $stats = $this->organizerService->getAccountStatistics();
        
        return view('organizer.reports', compact('stats'));
    }

    /**
     * Get statistics as JSON for AJAX requests
     */
    public function stats()
    {
        Gate::authorize('organizer-access');

        $stats = $this->organizerService->getAccountStatistics();
        
        return response()->json($stats);
    }

    /**
     * Get guest lists as JSON for dashboard widgets
     */
    public function guestListsJson(Request $request)
    {
        Gate::authorize('organizer-access');

        $search = $request->get('search', '');
        $health = $request->get('health', '');
        $guestCount = $request->get('guest_count', '');
        $sortBy = $request->get('sort_by', 'created_at_desc');
        $page = $request->get('page', 1);

        $guestLists = $this->organizerService->getMyGuestListsWithFilters($search, $health, $guestCount, $sortBy, $page);
        
        return response()->json($guestLists);
    }

    /**
     * Get detailed report data for a specific completed event
     */
        public function getEventReport($eventId)
    {
        Gate::authorize('organizer-access');
    
        try {
            $eventData = $this->eventReportService->getDetailedEventReport($eventId);
            return response()->json($eventData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Download event report as PDF
     */
    public function downloadEventReportPdf($eventId)
    {
        Gate::authorize('organizer-access');
    
        try {
            $eventData = $this->eventReportService->getDetailedEventReport($eventId);
            
            // Generate PDF
            $pdf = $this->eventReportPdfService->generateEventReportPdf($eventData);
            
            // Generate filename
            $eventName = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $eventData['event_name']);
            $filename = "Event_Report_{$eventName}_{$eventId}.pdf";
            
            // Download PDF
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }
} 