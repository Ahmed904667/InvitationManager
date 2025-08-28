<?php

/**
 * GoogleController
 * 
 * This controller handles all Google API integrations for the organizer panel.
 * It manages Google Contacts import, Google Sheets import, and related OAuth operations.
 * 
 * Responsibilities:
 * - Handle Google API requests and responses
 * - Manage Google OAuth operations
 * - Process Google API data for import
 */

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\GuestList;
use App\Organizer\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GoogleController extends Controller
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    /**
     * Get Google Contacts for import
     */
    public function getGoogleContacts(Request $request, GuestList $guestList)
    {
        Gate::authorize('import-guests', $guestList);

        $result = $this->googleService->getGoogleContacts();
        
        if (isset($result['error'])) {
            return response()->json($result, 401);
        }

        return response()->json($result);
    }

    /**
     * List valid Google Sheets for import
     */
    public function listValidGoogleSheets(Request $request, GuestList $guestList)
    {
        Gate::authorize('import-guests', $guestList);

        // Check if this is just an authentication check
        if ($request->get('check_auth')) {
            $result = $this->googleService->checkAuthentication();
            if (isset($result['error'])) {
                return response()->json($result, 401);
            }
            return response()->json(['authenticated' => true]);
        }

        $page = (int) $request->get('page', 1);
        $pageToken = $request->get('pageToken');
        $result = $this->googleService->listValidGoogleSheets($guestList, $page, $pageToken);
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
} 