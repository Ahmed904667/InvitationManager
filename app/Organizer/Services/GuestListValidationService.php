<?php

namespace App\Organizer\Services;

use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use Illuminate\Support\Collection;

class GuestListValidationService
{
    /**
     * Get all validation errors for a guest list
     */
    public function getValidationErrors(GuestList $guestList): array
    {
        $errors = [];
        
        // Get all guests for this list
        $guests = $guestList->guests()->get();
        
        // Check for duplicate emails
        $duplicateEmails = $this->findDuplicateEmails($guests);
        if (!empty($duplicateEmails)) {
            $errors[] = [
                'type' => 'duplicate_email',
                'message' => 'Duplicate email addresses found',
                'count' => count($duplicateEmails),
                'details' => $duplicateEmails,
                'severity' => 'warning'
            ];
        }
        
        // Check for duplicate phones
        $duplicatePhones = $this->findDuplicatePhones($guests);
        if (!empty($duplicatePhones)) {
            $errors[] = [
                'type' => 'duplicate_phone',
                'message' => 'Duplicate phone numbers found',
                'count' => count($duplicatePhones),
                'details' => $duplicatePhones,
                'severity' => 'warning'
            ];
        }
        
        // Check for missing required fields
        $missingFields = $this->findMissingRequiredFields($guestList, $guests);
        if (!empty($missingFields)) {
            $errors[] = [
                'type' => 'missing_fields',
                'message' => 'Missing required fields',
                'count' => count($missingFields),
                'details' => $missingFields,
                'severity' => 'error'
            ];
        }
        
                       // Check for invalid phone formats (missing country code)
               $invalidPhones = $this->findInvalidPhoneFormats($guests);
               if (!empty($invalidPhones)) {
                   $errors[] = [
                       'type' => 'invalid_phone_format',
                       'message' => 'Phone numbers missing country code',
                       'count' => count($invalidPhones),
                       'details' => $invalidPhones,
                       'severity' => 'warning'
                   ];
               }
        
        return $errors;
    }
    
    /**
     * Find duplicate email addresses
     */
    private function findDuplicateEmails(Collection $guests): array
    {
        $duplicates = [];
        $emailCounts = $guests->whereNotNull('email')
                             ->where('email', '!=', '')
                             ->groupBy('email')
                             ->filter(function ($group) {
                                 return $group->count() > 1;
                             });
        
        foreach ($emailCounts as $email => $group) {
            $duplicates[] = [
                'value' => $email,
                'count' => $group->count(),
                'guest_ids' => $group->pluck('id')->toArray(),
                'guest_names' => $group->pluck('name')->toArray()
            ];
        }
        
        return $duplicates;
    }
    
    /**
     * Find duplicate phone numbers
     */
    private function findDuplicatePhones(Collection $guests): array
    {
        $duplicates = [];
        $phoneCounts = $guests->whereNotNull('phone')
                             ->where('phone', '!=', '')
                             ->groupBy('phone')
                             ->filter(function ($group) {
                                 return $group->count() > 1;
                             });
        
        foreach ($phoneCounts as $phone => $group) {
            $duplicates[] = [
                'value' => $phone,
                'count' => $group->count(),
                'guest_ids' => $group->pluck('id')->toArray(),
                'guest_names' => $group->pluck('name')->toArray()
            ];
        }
        
        return $duplicates;
    }
    
    /**
     * Find missing required fields based on guest list settings
     */
    private function findMissingRequiredFields(GuestList $guestList, Collection $guests): array
    {
        $missing = [];
        $settings = $guestList->settings ?? [];
        $requiredFields = [];
        
        // Determine which fields are required based on settings
        if (isset($settings['fields']['email']) && $settings['fields']['email']) {
            $requiredFields[] = 'email';
        }
        if (isset($settings['fields']['phone']) && $settings['fields']['phone']) {
            $requiredFields[] = 'phone';
        }
        if (isset($settings['fields']['group']) && $settings['fields']['group']) {
            $requiredFields[] = 'group_id';
        }
        if (isset($settings['fields']['language']) && $settings['fields']['language']) {
            $requiredFields[] = 'language';
        }
        
        foreach ($guests as $guest) {
            $guestMissing = [];
            
            foreach ($requiredFields as $field) {
                if ($field === 'group_id') {
                    if (empty($guest->group_id)) {
                        $guestMissing[] = 'group';
                    }
                } else {
                    if (empty($guest->$field)) {
                        $guestMissing[] = $field;
                    }
                }
            }
            
            if (!empty($guestMissing)) {
                $missing[] = [
                    'guest_id' => $guest->id,
                    'guest_name' => $guest->name,
                    'missing_fields' => $guestMissing
                ];
            }
        }
        
        return $missing;
    }
    
    /**
     * Find phone numbers that don't start with a country code
     */
    private function findInvalidPhoneFormats(Collection $guests): array
    {
        $invalid = [];
        
        foreach ($guests as $guest) {
            if (!empty($guest->phone)) {
                $phone = trim($guest->phone);
                
                // Only flag as invalid if the phone number doesn't start with +
                // and has a reasonable length (more than 7 digits)
                if (!preg_match('/^\+/', $phone) && strlen(preg_replace('/[^\d]/', '', $phone)) >= 7) {
                    $invalid[] = [
                        'guest_id' => $guest->id,
                        'guest_name' => $guest->name,
                        'phone' => $phone,
                        'suggestion' => $this->suggestCountryCode($phone)
                    ];
                }
            }
        }
        
        return $invalid;
    }
    
    /**
     * Suggest a country code for a phone number
     */
    private function suggestCountryCode(string $phone): string
    {
        // Clean the phone number - remove all non-digits
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);
        
        // Common country codes
        $countryCodes = [
            '1' => 'US/Canada',
            '44' => 'UK',
            '33' => 'France',
            '49' => 'Germany',
            '39' => 'Italy',
            '34' => 'Spain',
            '31' => 'Netherlands',
            '32' => 'Belgium',
            '41' => 'Switzerland',
            '43' => 'Austria',
            '46' => 'Sweden',
            '47' => 'Norway',
            '45' => 'Denmark',
            '358' => 'Finland',
            '48' => 'Poland',
            '420' => 'Czech Republic',
            '36' => 'Hungary',
            '40' => 'Romania',
            '30' => 'Greece',
            '351' => 'Portugal',
            '380' => 'Ukraine',
            '375' => 'Belarus',
            '371' => 'Latvia',
            '372' => 'Estonia',
            '370' => 'Lithuania',
            '90' => 'Turkey',
            '971' => 'UAE',
            '966' => 'Saudi Arabia',
            '973' => 'Bahrain',
            '974' => 'Qatar',
            '975' => 'Bhutan',
            '976' => 'Mongolia',
            '977' => 'Nepal',
            '20' => 'Egypt',
            '27' => 'South Africa',
            '91' => 'India',
            '86' => 'China',
            '81' => 'Japan',
            '82' => 'South Korea',
            '65' => 'Singapore',
            '60' => 'Malaysia',
            '66' => 'Thailand',
            '84' => 'Vietnam',
            '62' => 'Indonesia',
            '63' => 'Philippines',
            '61' => 'Australia',
            '64' => 'New Zealand',
            '52' => 'Mexico',
            '55' => 'Brazil',
            '54' => 'Argentina',
            '56' => 'Chile',
            '57' => 'Colombia',
            '58' => 'Venezuela',
            '51' => 'Peru',
            '593' => 'Ecuador',
            '595' => 'Paraguay',
            '598' => 'Uruguay',
            '591' => 'Bolivia',
            '503' => 'El Salvador',
            '504' => 'Honduras',
            '505' => 'Nicaragua',
            '506' => 'Costa Rica',
            '507' => 'Panama',
            '502' => 'Guatemala',
            '501' => 'Belize'
        ];
        
        $length = strlen($cleanPhone);
        
        // Try to suggest based on length and common patterns
        if ($length === 10) {
            return "+1 " . substr($cleanPhone, 0, 3) . " " . substr($cleanPhone, 3, 3) . " " . substr($cleanPhone, 6); // US/Canada format
        } elseif ($length === 11 && substr($cleanPhone, 0, 1) === '1') {
            return "+" . substr($cleanPhone, 0, 1) . " " . substr($cleanPhone, 1, 3) . " " . substr($cleanPhone, 4, 3) . " " . substr($cleanPhone, 7); // US/Canada with country code
        } elseif ($length === 10 && substr($cleanPhone, 0, 1) === '0') {
            return "+44 " . substr($cleanPhone, 1, 4) . " " . substr($cleanPhone, 5, 3) . " " . substr($cleanPhone, 8); // UK format
        } elseif ($length === 9) {
            return "+33 " . substr($cleanPhone, 0, 1) . " " . substr($cleanPhone, 1, 2) . " " . substr($cleanPhone, 3, 2) . " " . substr($cleanPhone, 5, 2) . " " . substr($cleanPhone, 7); // France format
        } elseif ($length >= 7 && $length <= 15) {
            // For other lengths, provide a generic suggestion
            return "+[Country Code] " . $cleanPhone;
        }
        
        return "+[Country Code] " . $cleanPhone;
    }
    
    /**
     * Get error summary for display
     */
    public function getErrorSummary(GuestList $guestList): array
    {
        $errors = $this->getValidationErrors($guestList);
        
        $summary = [
            'total_errors' => 0,
            'total_warnings' => 0,
            'error_types' => []
        ];
        
        foreach ($errors as $error) {
            if ($error['severity'] === 'error') {
                $summary['total_errors'] += $error['count'];
            } else {
                $summary['total_warnings'] += $error['count'];
            }
            
            $summary['error_types'][] = $error['type'];
        }
        
        return $summary;
    }
} 