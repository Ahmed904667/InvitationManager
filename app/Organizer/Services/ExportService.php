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
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Set data
        $row = 2;
        foreach ($guests as $guest) {
            $guestData = $this->formatGuestForExport($guest, $guestList);
            $col = 'A';
            foreach ($guestData as $value) {
                $sheet->setCellValue($col . $row, $value);
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

        if ($request->filled('status')) {
            if ($request->status === 'checked_in') {
                $query->where('checked_in', true);
            } elseif ($request->status === 'pending') {
                $query->where('checked_in', false);
            }
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
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
        
        if ($settings['enable_email'] ?? false) {
            $headers[] = 'Email';
        }
        if ($settings['enable_phone'] ?? false) {
            $headers[] = 'Phone';
        }
        if ($settings['enable_language'] ?? false) {
            $headers[] = 'Language';
        }
        if ($settings['enable_group'] ?? false) {
            $headers[] = 'Group';
        }
        
        return $headers;
    }

    /**
     * Format guest data for export
     */
    protected function formatGuestForExport($guest, GuestList $guestList): array
    {
        $settings = $guestList->settings ?? [];
        
        $row = [$guest->name];
        
        if ($settings['enable_email'] ?? false) {
            $row[] = $guest->email ?? '';
        }
        if ($settings['enable_phone'] ?? false) {
            $row[] = $guest->phone ?? '';
        }
        if ($settings['enable_language'] ?? false) {
            $row[] = $guest->language ?? '';
        }
        if ($settings['enable_group'] ?? false) {
            $row[] = $guest->group ? $guest->group->name : '';
        }
        
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