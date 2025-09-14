<?php

namespace App\Organizer\Services;

use App\Shared\Models\GuestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    /**
     * Export guests to CSV format
     */
    public function exportToCsv(Request $request, GuestList $guestList): array
    {
        $guests = $this->getFilteredGuests($request, $guestList);
        
        $filename = $this->generateFilename($guestList, 'csv');
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($guests, $guestList) {
            $file = fopen('php://output', 'w');
            
            // Write headers based on guest list settings
            $headers = $this->getExportHeaders($guestList);
            fputcsv($file, $headers);
            
            // Write data
            foreach ($guests as $guest) {
                $row = $this->formatGuestForExport($guest, $guestList);
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return [
            'callback' => $callback,
            'headers' => $headers
        ];
    }

    /**
     * Export guests to Excel format
     */
    public function exportToExcel(Request $request, GuestList $guestList): array
    {
        $guests = $this->getFilteredGuests($request, $guestList);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = $this->getExportHeaders($guestList);
        $col = 'A';
        $phoneColumnIndex = null;
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($col . '1', $header);
            if ($header === 'Phone') {
                $phoneColumnIndex = $col;
            }
            $col++;
        }
        
        // Set phone column format to text to prevent Excel from treating phone numbers as numbers
        if ($phoneColumnIndex) {
            $sheet->getStyle($phoneColumnIndex . ':' . $phoneColumnIndex)->getNumberFormat()->setFormatCode('@');
        }
        
        // Set data
        $row = 2;
        $headers = $this->getExportHeaders($guestList);
        foreach ($guests as $guest) {
            $guestData = $this->formatGuestForExport($guest, $guestList);
            $col = 'A';
            foreach ($guestData as $index => $value) {
                $headerName = $headers[$index] ?? '';
                
                // Format phone numbers as strings to prevent Excel from treating them as numbers
                if ($headerName === 'Phone' && !empty($value)) {
                    $sheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($col . $row, $value);
                }
                $col++;
            }
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', $col) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $filename = $this->generateFilename($guestList, 'xlsx');
        
        return [
            'spreadsheet' => $spreadsheet,
            'filename' => $filename
        ];
    }

    /**
     * Export guests to PDF format
     */
    public function exportToPdf(Request $request, GuestList $guestList): array
    {
        $guests = $this->getFilteredGuests($request, $guestList);
        
        $data = [
            'guestList' => $guestList,
            'guests' => $guests,
            'headers' => $this->getExportHeaders($guestList),
            'stats' => [
                'total' => $guests->count(),
                'checked_in' => $guests->where('checked_in', true)->count(),
                'pending' => $guests->where('checked_in', false)->count(),
            ]
        ];
        
        $pdf = Pdf::loadView('exports.guest-list-pdf', $data);
        
        $filename = $this->generateFilename($guestList, 'pdf');
        
        return [
            'pdf' => $pdf,
            'filename' => $filename
        ];
    }

    /**
     * Get export statistics
     */
    public function getExportStats(Request $request, GuestList $guestList): array
    {
        $guests = $this->getFilteredGuests($request, $guestList);
        
        return [
            'total_guests' => $guests->count(),
            'checked_in_guests' => $guests->where('checked_in', true)->count(),
            'pending_guests' => $guests->where('checked_in', false)->count(),
            'groups_count' => $guests->groupBy('group_id')->count(),
            'export_columns' => $this->getExportHeaders($guestList),
        ];
    }

    /**
     * Get filtered guests based on request parameters
     */
    protected function getFilteredGuests(Request $request, GuestList $guestList)
    {
        $query = $guestList->guests()->with('group');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('language')) {
            if ($request->language === 'no_language') {
                $query->whereNull('language');
            } else {
                $query->where('language', $request->language);
            }
        }

        if ($request->filled('group')) {
            if ($request->group === 'no_group') {
                $query->whereNull('group_id');
            } else {
                $query->where('group_id', $request->group);
            }
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get export headers based on guest list settings
     */
    protected function getExportHeaders(GuestList $guestList): array
    {
        $headers = ['Name'];
        
        $settings = $guestList->settings ?? [];
        $fields = $settings['fields'] ?? [];
        
        if ($fields['email'] ?? false) {
            $headers[] = 'Email';
        }
        if ($fields['phone'] ?? false) {
            $headers[] = 'Phone';
        }
        if ($fields['language'] ?? false) {
            $headers[] = 'Language';
        }
        if ($fields['group'] ?? false) {
            $headers[] = 'Group';
        }
        if ($fields['notes'] ?? false) {
            $headers[] = 'Notes';
        }
        
        // Always include created date for tracking
        $headers[] = 'Created Date';
        
        return $headers;
    }

    /**
     * Format guest data for export
     */
    protected function formatGuestForExport($guest, GuestList $guestList): array
    {
        $settings = $guestList->settings ?? [];
        $fields = $settings['fields'] ?? [];
        
        $row = [$guest->name];
        
        if ($fields['email'] ?? false) {
            $row[] = $guest->email ?? '';
        }
        if ($fields['phone'] ?? false) {
            $phone = $guest->phone ?? '';
            // Simple approach: add a leading space to force string treatment
            if (!empty($phone)) {
                $phone = ' ' . $phone;
            }
            $row[] = $phone;
        }
        if ($fields['language'] ?? false) {
            $row[] = $guest->language ?? '';
        }
        if ($fields['group'] ?? false) {
            $row[] = $guest->group ? $guest->group->name : '';
        }
        if ($fields['notes'] ?? false) {
            $row[] = $guest->notes ?? '';
        }
        
        // Always include created date for tracking
        $row[] = $guest->created_at ? $guest->created_at->format('Y-m-d H:i:s') : '';
        
        return $row;
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(GuestList $guestList, string $extension): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9\s-]/', '', $guestList->name);
        $safeName = str_replace(' ', '-', $safeName);
        
        return $safeName . '-Guests-List-' . $guestList->id . '.' . $extension;
    }
} 