<?php

namespace App\Organizer\Services;

use App\Shared\Models\Guest;
use App\Shared\Models\GuestList;
use App\Shared\Models\GuestGroup;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Rules\UniqueInGuestList;

class ImportService
{
    protected $organizerService;

    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    /**
     * Import guests from file
     */
    public function importFromFile(GuestList $guestList, $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        
        if (in_array($ext, ['csv', 'txt'])) {
            $contacts = $this->parseCsvFile($file, $guestList);
        } elseif (in_array($ext, ['xls', 'xlsx'])) {
            $contacts = $this->parseExcelFile($file, $guestList);
        } else {
            return ['success' => false, 'message' => 'Unsupported file type.'];
        }

        return $this->processContacts($contacts, $guestList);
    }

    /**
     * Import guests from Google Sheet
     */
    public function importFromGoogleSheet($sheetData, GuestList $guestList, array $options = []): array
    {
        $contacts = [];
        $headers = array_map('strtolower', $sheetData[0] ?? []);
        
        // Find column indices
        $nameIndex = $this->findColumnIndex($headers, ['name', 'guest name', 'full name', 'first name', 'last name']);
        $emailIndex = $this->findColumnIndex($headers, ['email', 'email address', 'e-mail']);
        $phoneIndex = $this->findColumnIndex($headers, ['phone', 'phone number', 'mobile', 'cell', 'telephone']);
        $groupIndex = $this->findColumnIndex($headers, ['group', 'group name', 'category']);
        $languageIndex = $this->findColumnIndex($headers, ['language', 'preferred language', 'lang']);

        if ($nameIndex === -1) {
            return ['success' => false, 'message' => 'Name column not found in sheet.'];
        }

        $groupMapping = [];
        
        for ($i = 1; $i < count($sheetData); $i++) {
            $row = $sheetData[$i];
            if (empty(array_filter($row))) continue;

            $contact = [
                'name' => trim($row[$nameIndex] ?? ''),
                'email' => trim($row[$emailIndex] ?? ''),
                'phone' => trim($row[$phoneIndex] ?? ''),
                'language' => trim($row[$languageIndex] ?? ''),
            ];

            if (!empty($contact['name'])) {
                $this->processGroupAssignment($contact, $headers, $row, $guestList, $groupMapping, $groupIndex);
                $contacts[] = $contact;
            }
        }

        return $this->processContacts($contacts, $guestList, $options);
    }

    /**
     * Process contacts and create guests
     */
    protected function processContacts(array $contacts, GuestList $guestList, array $options = []): array
    {
        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($contacts as $contact) {
            try {
                $guestData = [
                    'name' => $contact['name'],
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                ];
                
                // Handle group assignment
                if (isset($contact['group_id'])) {
                    $guestData['group_id'] = $contact['group_id'];
                } elseif (!empty($options['group_id'])) {
                    // Use default group if no group assigned from sheet
                    $guestData['group_id'] = $options['group_id'];
                }
                
                // Handle language assignment
                if (!empty($contact['language'])) {
                    $guestData['language'] = $contact['language'];
                } elseif (!empty($options['apply_default_language']) && !empty($guestList->settings['default_language'])) {
                    // Apply default language if option is enabled
                    $guestData['language'] = $guestList->settings['default_language'];
                }
                
                $this->organizerService->addGuestWithoutValidation($guestList, $guestData);
                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }

        $message = "Successfully imported {$imported} guests.";
        if ($failed > 0) {
            $message .= " Failed to import {$failed} guests.";
        }

        return [
            'success' => $failed === 0,
            'message' => $message,
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Parse CSV file
     */
    protected function parseCsvFile($file, GuestList $guestList = null): array
    {
        $contacts = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle) {
            $headers = fgetcsv($handle);
            if ($headers) {
                $headers = array_map('strtolower', $headers);
                
                $nameIndex = $this->findColumnIndex($headers, ['name', 'guest name', 'full name']);
                $emailIndex = $this->findColumnIndex($headers, ['email', 'email address']);
                $phoneIndex = $this->findColumnIndex($headers, ['phone', 'phone number', 'mobile']);
                $groupIndex = $this->findColumnIndex($headers, ['group', 'group name']);
                $languageIndex = $this->findColumnIndex($headers, ['language', 'preferred language']);

                $groupMapping = [];
                
                while (($row = fgetcsv($handle)) !== false) {
                    if (empty(array_filter($row))) continue;

                    $contact = [
                        'name' => trim($row[$nameIndex] ?? ''),
                        'email' => trim($row[$emailIndex] ?? ''),
                        'phone' => trim($row[$phoneIndex] ?? ''),
                        'language' => trim($row[$languageIndex] ?? ''),
                    ];

                    if (!empty($contact['name'])) {
                        $this->processGroupAssignment($contact, $headers, $row, $guestList, $groupMapping, $groupIndex);
                        $contacts[] = $contact;
                    }
                }
            }
            fclose($handle);
        }

        return $contacts;
    }

    /**
     * Parse Excel file
     */
    protected function parseExcelFile($file, GuestList $guestList = null): array
    {
        $contacts = [];
        
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) return $contacts;

            $headers = array_map('strtolower', $rows[0]);
            array_shift($rows); // Remove header row

            $nameIndex = $this->findColumnIndex($headers, ['name', 'guest name', 'full name']);
            $emailIndex = $this->findColumnIndex($headers, ['email', 'email address']);
            $phoneIndex = $this->findColumnIndex($headers, ['phone', 'phone number', 'mobile']);
            $groupIndex = $this->findColumnIndex($headers, ['group', 'group name']);
            $languageIndex = $this->findColumnIndex($headers, ['language', 'preferred language']);

            $groupMapping = [];

            foreach ($rows as $row) {
                if (empty(array_filter($row))) continue;

                $contact = [
                    'name' => trim($row[$nameIndex] ?? ''),
                    'email' => trim($row[$emailIndex] ?? ''),
                    'phone' => trim($row[$phoneIndex] ?? ''),
                    'language' => trim($row[$languageIndex] ?? ''),
                ];

                if (!empty($contact['name'])) {
                    $this->processGroupAssignment($contact, $headers, $row, $guestList, $groupMapping, $groupIndex);
                    $contacts[] = $contact;
                }
            }
        } catch (\Exception $e) {
            Log::error('Excel parsing error: ' . $e->getMessage());
        }

        return $contacts;
    }

    /**
     * Find column index by possible names
     */
    protected function findColumnIndex($headers, $possibleNames): int
    {
        foreach ($possibleNames as $name) {
            $index = array_search(strtolower($name), $headers);
            if ($index !== false) {
                return $index;
            }
        }
        return -1;
    }

    /**
     * Process group assignment
     */
    protected function processGroupAssignment(&$contact, $headers, $row, $guestList, &$groupMapping, $groupIndex): void
    {
        if ($groupIndex !== -1 && !empty($row[$groupIndex]) && $guestList) {
            $groupName = trim($row[$groupIndex]);
            
            if (!isset($groupMapping[$groupName])) {
                $group = $guestList->guestGroups()->where('name', $groupName)->first();
                
                if (!$group) {
                    $group = $guestList->guestGroups()->create([
                        'name' => $groupName,
                        'description' => 'Imported from file'
                    ]);
                }
                
                $groupMapping[$groupName] = $group->id;
            }
            
            $contact['group_id'] = $groupMapping[$groupName];
        }
    }
} 