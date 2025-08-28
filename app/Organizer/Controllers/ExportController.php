<?php

/**
 * ExportController
 * 
 * This controller handles export operations for guest lists.
 * It manages the export of guest data to various formats including
 * CSV, Excel, and PDF formats.
 * 
 * Responsibilities:
 * - Handle export requests and responses
 * - Manage export authorization
 * - Return export files and statistics
 */

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\GuestList;
use App\Organizer\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export guests to CSV format
     */
    public function exportToCsv(Request $request, GuestList $guestList)
    {
        Gate::authorize('export-guests', $guestList);

        $result = $this->exportService->exportToCsv($request, $guestList);
        
        return response()->stream($result['callback'], 200, $result['headers']);
    }

    /**
     * Export guests to Excel format
     */
    public function exportToExcel(Request $request, GuestList $guestList)
    {
        Gate::authorize('export-guests', $guestList);

        $result = $this->exportService->exportToExcel($request, $guestList);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($result['spreadsheet']);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $result['filename'] . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export guests to PDF format
     */
    public function exportToPdf(Request $request, GuestList $guestList)
    {
        Gate::authorize('export-guests', $guestList);

        $result = $this->exportService->exportToPdf($request, $guestList);
        
        return $result['pdf']->download($result['filename']);
    }

    /**
     * Get export statistics
     */
    public function getExportStats(Request $request, GuestList $guestList)
    {
        Gate::authorize('export-guests', $guestList);

        $stats = $this->exportService->getExportStats($request, $guestList);
        
        return response()->json($stats);
    }
} 