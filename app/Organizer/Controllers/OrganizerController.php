<?php

/**
 * OrganizerController
 * 
 * This controller handles core organizer functionality and serves as a central hub
 * for organizer operations. It now focuses on essential organizer tasks and
 * delegates specific operations to specialized controllers.
 * 
 * Responsibilities:
 * - Core organizer functionality
 * - System-wide organizer operations
 * - Coordination between specialized controllers
 * - Fallback and general organizer operations
 */

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use App\Organizer\Services\OrganizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganizerController extends Controller
{
    protected $organizerService;
    
    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    // This controller now serves as a minimal hub for core organizer functionality
    // All specific operations have been moved to specialized controllers:
    // - DashboardController: dashboard, reports, stats, guestListsJson
    // - GuestListController: guest list CRUD, settings, groups
    // - GuestController: guest CRUD, bulk operations
    // - ImportController: import/export, file processing
    // - GoogleController: Google API integrations
} 