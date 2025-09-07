<?php

/**
 * ImportController
 * 
 * This controller handles file-based import operations for guest lists.
 * It manages the upload and processing of Excel/CSV files for bulk guest import.
 * 
 * Responsibilities:
 * - Handle file uploads and validation
 * - Manage import authorization
 * - Process import requests and responses
 * - Handle Google Sheets import requests
 */

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\GuestList;
use App\Organizer\Services\ImportService;
use App\Organizer\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ImportController extends Controller
{
    protected $importService;
    protected $googleService;

    public function __construct(ImportService $importService, GoogleService $googleService)
    {
        $this->importService = $importService;
        $this->googleService = $googleService;
    }

    /**
     * Import guests from file upload
     */
    public function import(Request $request, GuestList $guestList)
    {
        Gate::authorize('import-guests', $guestList);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        $file = $request->file('file');
        $result = $this->importService->importFromFile($guestList, $file);

        return response()->json($result);
    }

    /**
     * Import guests from Google Contacts
     */
    public function importFromGoogleContacts(Request $request, GuestList $guestList)
    {
        Gate::authorize('import-guests', $guestList);

        $validated = $request->validate([
            'selected_contacts' => 'required|array',
            'selected_contacts.*' => 'required|string',
            'group_id' => 'nullable|integer|exists:guest_groups,id'
        ]);

        $imported = 0;
        $failed = 0;
        $errors = [];
        $duplicates = [];

        // Log the group_id for debugging
        \Log::info('Google contacts import - group_id: ' . ($validated['group_id'] ?? 'null'));

        foreach ($validated['selected_contacts'] as $contactData) {
            $contact = json_decode($contactData, true);
            
            if (empty($contact['name'])) continue;

            try {
                // Check for duplicate email
                if (!empty($contact['email'])) {
                    $existingGuest = $guestList->guests()
                        ->where('email', $contact['email'])
                        ->first();
                    
                    if ($existingGuest) {
                        $duplicates[] = "Email '{$contact['email']}' already exists for guest '{$existingGuest->name}'";
                        $failed++;
                        continue;
                    }
                }

                // Check for duplicate phone
                if (!empty($contact['phone'])) {
                    $existingGuest = $guestList->guests()
                        ->where('phone', $contact['phone'])
                        ->first();
                    
                    if ($existingGuest) {
                        $duplicates[] = "Phone '{$contact['phone']}' already exists for guest '{$existingGuest->name}'";
                        $failed++;
                        continue;
                    }
                }

                $guestData = [
                    'name' => $contact['name'],
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'language' => $contact['language'] ?? null,
                    'guest_list_id' => $guestList->id,
                ];
                
                // Assign group if provided
                if (!empty($validated['group_id'])) {
                    $guestData['group_id'] = $validated['group_id'];
                }
                
                $guest = \App\Shared\Models\Guest::create($guestData);
                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }

        $message = "Successfully imported {$imported} contacts.";
        if ($failed > 0) {
            $message .= " Failed to import {$failed} contacts due to duplicates or errors.";
        }

        return response()->json([
            'success' => $failed === 0,
            'message' => $message,
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
            'duplicates' => $duplicates
        ]);
    }

    /**
     * Import guests from Google Sheet
     */
    public function importFromGoogleSheet(Request $request, GuestList $guestList)
    {
        Gate::authorize('import-guests', $guestList);

        $validated = $request->validate([
            'sheet' => 'required|array',
            'sheet.file_id' => 'required|string',
            'sheet.file_name' => 'required|string',
            'sheet.sheet_title' => 'required|string',
            'group_id' => 'nullable|integer|exists:guest_groups,id',
            'apply_default_language' => 'boolean'
        ]);

        // Log the import options for debugging
        \Log::info('Google Sheets import - options: ' . json_encode($validated));

        $result = $this->googleService->importFromGoogleSheet($validated['sheet']['file_id'], $guestList, $validated);

        return response()->json($result);
    }
} 