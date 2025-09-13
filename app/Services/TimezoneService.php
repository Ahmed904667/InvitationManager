<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TimezoneService
{
    /**
     * Detect user timezone from request (browser only)
     */
    public static function detectTimezone(Request $request): string
    {
        // Priority order for timezone detection:
        // 1. Browser timezone from JavaScript (primary)
        // 2. Explicit timezone parameter (fallback)
        // 3. Default to UTC

        // 1. Check for browser timezone from JavaScript (primary method)
        if ($request->has('browser_timezone') && $request->browser_timezone) {
            $timezone = $request->browser_timezone;
            // Skip invalid string values like "undefined" or "null"
            if ($timezone !== 'undefined' && $timezone !== 'null' && self::isValidTimezone($timezone)) {
                return $timezone;
            }
        }

        // 2. Check for timezone in headers (from JavaScript)
        $timezoneHeader = $request->header('X-Timezone');
        if ($timezoneHeader && self::isValidTimezone($timezoneHeader)) {
            return $timezoneHeader;
        }

        // 3. Check for explicit timezone parameter (fallback)
        if ($request->has('timezone') && $request->timezone) {
            $timezone = $request->timezone;
            // Skip invalid string values like "undefined" or "null"
            if ($timezone !== 'undefined' && $timezone !== 'null' && self::isValidTimezone($timezone)) {
                return $timezone;
            }
        }

        // 4. Default to UTC
        return 'UTC';
    }


    /**
     * Check if timezone is valid
     */
    public static function isValidTimezone(string $timezone): bool
    {
        try {
            $dt = new \DateTime('now', new \DateTimeZone($timezone));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Get common timezones for dropdowns
     */
    public static function getCommonTimezones(): array
    {
        return [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'America/New_York' => 'Eastern Time (ET)',
            'America/Chicago' => 'Central Time (CT)',
            'America/Denver' => 'Mountain Time (MT)',
            'America/Los_Angeles' => 'Pacific Time (PT)',
            'Europe/London' => 'London (GMT/BST)',
            'Europe/Paris' => 'Paris (CET/CEST)',
            'Europe/Berlin' => 'Berlin (CET/CEST)',
            'Asia/Tokyo' => 'Tokyo (JST)',
            'Asia/Shanghai' => 'Shanghai (CST)',
            'Asia/Singapore' => 'Singapore (SGT)',
            'Asia/Kolkata' => 'Mumbai/Delhi (IST)',
            'Australia/Sydney' => 'Sydney (AEST/AEDT)',
            'Australia/Melbourne' => 'Melbourne (AEST/AEDT)',
            'Pacific/Auckland' => 'Auckland (NZST/NZDT)',
        ];
    }

    /**
     * Format timezone for display
     */
    public static function formatTimezoneForDisplay(string $timezone): string
    {
        $common = self::getCommonTimezones();
        return $common[$timezone] ?? $timezone;
    }
}
