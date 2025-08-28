<?php

namespace App\Organizer\Services;

use App\Shared\Models\GuestList;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleService
{
    /**
     * Check if user is authenticated with Google
     */
    public function checkAuthentication(): array
    {
        $token = session('google_token');
        if (!$token) {
            return ['error' => 'Not authenticated with Google'];
        }

        // Test the token by making a simple API call
        try {
            $response = Http::withToken($token)->timeout(5)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($response->failed()) {
                return ['error' => 'Invalid or expired Google token'];
            }

            return ['authenticated' => true];
        } catch (\Exception $e) {
            Log::error('Google authentication check error: ' . $e->getMessage());
            return ['error' => 'Failed to verify Google authentication'];
        }
    }

    /**
     * Get Google Contacts for import
     */
    public function getGoogleContacts(): array
    {
        $token = session('google_token');
        if (!$token) {
            return ['error' => 'Not authenticated with Google'];
        }

        $response = Http::withToken($token)
            ->get('https://people.googleapis.com/v1/people/me/connections', [
                'personFields' => 'names,emailAddresses,phoneNumbers',
                'pageSize' => 1000,
            ]);

        if ($response->failed()) {
            return ['error' => 'Failed to fetch contacts'];
        }

        $contacts = [];
        foreach ($response->json('connections', []) as $person) {
            $contacts[] = [
                'name' => $person['names'][0]['displayName'] ?? '',
                'email' => $person['emailAddresses'][0]['value'] ?? '',
                'phone' => $person['phoneNumbers'][0]['value'] ?? '',
            ];
        }

        return ['contacts' => $contacts];
    }

    /**
     * List valid Google Sheets for import
     */
    public function listValidGoogleSheets(GuestList $guestList, int $page = 1, string $pageToken = null): array
    {
        $token = session('google_token');
        if (!$token) {
            return ['error' => 'Not authenticated with Google'];
        }

        // Get guest list settings
        $settings = $guestList->settings['fields'] ?? [];
        $required = [
            ['name', 'guest name', 'full name']
        ];
        if (!empty($settings['email'])) $required[] = ['email', 'email address'];
        if (!empty($settings['phone'])) $required[] = ['phone', 'phone number', 'mobile'];
        if (!empty($settings['language'])) $required[] = ['language', 'preferred language'];
        if (!empty($settings['group'])) $required[] = ['group', 'group name'];

        try {
            // Get Google Drive files with pagination - limit to 5 sheets for faster loading
            $pageSize = 5; // Limit to 5 sheets per page for faster loading
            $driveRes = Http::withToken($token)->timeout(15)
                ->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => "mimeType='application/vnd.google-apps.spreadsheet' and trashed=false",
                    'fields' => 'files(id, name), nextPageToken',
                    'pageSize' => $pageSize,
                    'orderBy' => 'modifiedTime desc', // Get most recently modified sheets first
                    'pageToken' => $pageToken
                ]);

            if ($driveRes->failed()) {
                return ['error' => 'Failed to fetch Google Drive files'];
            }

            $files = $driveRes->json('files', []);
            $nextPageToken = $driveRes->json('nextPageToken');
            $validSheets = [];
            $unvalidSheets = [];

            // Process sheets in parallel for faster loading
            $promises = [];
            foreach ($files as $file) {
                $promises[] = $this->processSheetAsync($token, $file, $required);
            }
            
            // Wait for all promises to complete
            foreach ($promises as $promise) {
                $result = $promise();
                if ($result) {
                    if ($result['valid']) {
                        $validSheets[] = $result['data'];
                    } else {
                        $unvalidSheets[] = $result['data'];
                    }
                }
            }

            return [
                'valid_sheets' => $validSheets,
                'unvalid_sheets' => $unvalidSheets,
                'has_more' => !empty($nextPageToken),
                'next_page_token' => $nextPageToken,
                'current_page' => $page
            ];

        } catch (\Exception $e) {
            Log::error('Google Sheets listing error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return ['error' => 'Failed to list Google Sheets: ' . $e->getMessage()];
        }
    }

    /**
     * Get sheet data from Google Sheets API - optimized for faster loading
     */
    protected function getSheetData(string $token, string $sheetId): ?array
    {
        try {
            // Only fetch first 10 rows for validation - much faster
            $response = Http::withToken($token)->timeout(8)
                ->get("https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}/values/A1:Z10");

            if ($response->failed()) {
                return null;
            }

            return $response->json('values', []);
        } catch (\Exception $e) {
            Log::error('Google Sheets data fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate sheet structure against required columns
     */
    protected function validateSheetStructure(array $sheetData, array $required): array
    {
        if (empty($sheetData) || empty($sheetData[0])) {
            return ['valid' => false, 'missing' => ['No headers found']];
        }

        $headers = array_map('strtolower', $sheetData[0]);
        $missing = [];

        foreach ($required as $requiredGroup) {
            $found = false;
            foreach ($requiredGroup as $possibleName) {
                if (in_array(strtolower($possibleName), $headers)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missing[] = $requiredGroup[0]; // Use first name as representative
            }
        }

        return [
            'valid' => empty($missing),
            'missing' => $missing
        ];
    }

    /**
     * Get sheet columns for display
     */
    protected function getSheetColumns(array $headers): array
    {
        return array_map(function($header) {
            return [
                'name' => $header,
                'type' => $this->detectColumnType($header)
            ];
        }, $headers);
    }

    /**
     * Detect column type based on header name
     */
    protected function detectColumnType(string $header): string
    {
        $header = strtolower($header);
        
        if (in_array($header, ['name', 'guest name', 'full name', 'first name', 'last name'])) {
            return 'name';
        }
        if (in_array($header, ['email', 'email address', 'e-mail'])) {
            return 'email';
        }
        if (in_array($header, ['phone', 'phone number', 'mobile', 'cell', 'telephone'])) {
            return 'phone';
        }
        if (in_array($header, ['group', 'group name', 'category'])) {
            return 'group';
        }
        if (in_array($header, ['language', 'preferred language', 'lang'])) {
            return 'language';
        }
        
        return 'other';
    }

    /**
     * Get sheet title from Google Sheets API - optimized for speed
     */
    protected function getSheetTitle(string $token, string $sheetId): string
    {
        try {
            // Use a more specific endpoint for faster response
            $response = Http::withToken($token)->timeout(3)
                ->get("https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}?fields=sheets.properties.title");

            if ($response->successful()) {
                $data = $response->json();
                return $data['sheets'][0]['properties']['title'] ?? 'Sheet1';
            }
        } catch (\Exception $e) {
            Log::error('Failed to get sheet title: ' . $e->getMessage());
        }
        
        return 'Sheet1';
    }

    /**
     * Get page token for pagination (simplified implementation)
     */
    protected function getPageToken(int $page): ?string
    {
        // For now, return null to disable pagination
        // In a full implementation, you'd store and retrieve page tokens
        return null;
    }

    /**
     * Process a single sheet asynchronously for better performance
     */
    protected function processSheetAsync(string $token, array $file, array $required): callable
    {
        return function() use ($token, $file, $required) {
            try {
                $sheetData = $this->getSheetData($token, $file['id']);
                if (!$sheetData) {
                    return null;
                }

                $validation = $this->validateSheetStructure($sheetData, $required);
                $sheetTitle = $this->getSheetTitle($token, $file['id']);
                
                if ($validation['valid']) {
                    return [
                        'valid' => true,
                        'data' => [
                            'file_id' => $file['id'],
                            'file_name' => $file['name'],
                            'sheet_title' => $sheetTitle,
                            'header' => $sheetData[0] ?? []
                        ]
                    ];
                } else {
                    return [
                        'valid' => false,
                        'data' => [
                            'file_id' => $file['id'],
                            'file_name' => $file['name'],
                            'sheet_title' => $sheetTitle,
                            'missing' => $validation['missing']
                        ]
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error processing sheet ' . $file['id'] . ': ' . $e->getMessage());
                return null;
            }
        };
    }

    /**
     * Import data from Google Sheet
     */
    public function importFromGoogleSheet(string $sheetId, GuestList $guestList, array $options = []): array
    {
        $token = session('google_token');
        if (!$token) {
            return ['error' => 'Not authenticated with Google'];
        }

        $sheetData = $this->getSheetData($token, $sheetId);
        if (!$sheetData) {
            return ['error' => 'Failed to fetch sheet data'];
        }

        // Use ImportService to process the data
        $importService = app(ImportService::class);
        return $importService->importFromGoogleSheet($sheetData, $guestList, $options);
    }
} 