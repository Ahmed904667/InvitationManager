<?php

namespace App\Organizer\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class EventReportPdfService
{
    /**
     * Generate PDF for event report
     */
    public function generateEventReportPdf(array $eventData): \Barryvdh\DomPDF\PDF
    {
        try {
            // Prepare data for PDF view
            $pdfData = $this->preparePdfData($eventData);
            
            // Generate PDF using a dedicated view
            $pdf = PDF::loadView('organizer.pdf.event-report', $pdfData);
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Set options for better rendering
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial',
                'dpi' => 150,
                'defaultMediaType' => 'screen',
                'isFontSubsettingEnabled' => true,
            ]);
            
            return $pdf;
        } catch (\Exception $e) {
            \Log::error('PDF generation error: ' . $e->getMessage());
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Prepare data for PDF generation
     */
    private function preparePdfData(array $eventData): array
    {
        try {
            // Format dates for better display
            $startDate = $eventData['start_date'] ? date('F j, Y \a\t g:i A', strtotime($eventData['start_date'])) : 'N/A';
            $endDate = $eventData['end_date'] ? date('F j, Y \a\t g:i A', strtotime($eventData['end_date'])) : 'N/A';
            $createdDate = $eventData['created_at'] ? date('F j, Y \a\t g:i A', strtotime($eventData['created_at'])) : 'N/A';
            
            // Get invitation platforms
            $platforms = 'None';
            if (isset($eventData['invitation_stats']['channel_breakdown'])) {
                $channelBreakdown = $eventData['invitation_stats']['channel_breakdown'];
                
                // Convert Collection to array if needed
                if (is_object($channelBreakdown) && method_exists($channelBreakdown, 'toArray')) {
                    $channelBreakdown = $channelBreakdown->toArray();
                }
                
                if (is_array($channelBreakdown) && !empty($channelBreakdown)) {
                    $channelNames = array_keys($channelBreakdown);
                    $platforms = implode(', ', array_map(function($channel) {
                        return ucfirst($channel);
                    }, $channelNames));
                }
            }
            
            // Format check-in times
            $firstCheckin = 'No check-ins';
            $lastCheckin = 'No check-ins';
            if (isset($eventData['checkin_stats']['first_checkin']) && $eventData['checkin_stats']['first_checkin']) {
                $firstCheckin = date('F j, Y \a\t g:i A', strtotime($eventData['checkin_stats']['first_checkin']));
            }
            if (isset($eventData['checkin_stats']['last_checkin']) && $eventData['checkin_stats']['last_checkin']) {
                $lastCheckin = date('F j, Y \a\t g:i A', strtotime($eventData['checkin_stats']['last_checkin']));
            }
            
            return [
                'event' => [
                    'name' => $eventData['event_name'] ?? 'Unnamed Event',
                    'description' => $eventData['description'] ?? 'No description',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'location' => $eventData['location'] ?? 'No location specified',
                    'venue_name' => $eventData['venue_name'] ?? 'No venue specified',
                    'venue_address' => $eventData['venue_address'] ?? 'No address specified',
                    'created_at' => $createdDate,
            ],
                'settings' => [
                    'rsvp_enabled' => $eventData['rsvp_enabled'] ? 'Yes' : 'No',
                    'checkin_enabled' => $eventData['checkin_enabled'] ? 'Yes' : 'No',
                    'invitation_platforms' => $platforms,
                    'guest_list_count' => $eventData['event_info']['guest_list_count'] ?? 0,
                    'scanner_count' => $eventData['event_info']['scanner_count'] ?? 0,
                ],
                'overview' => [
                    'total_guests' => $eventData['total_guests'] ?? 0,
                    'checked_in_guests' => $eventData['checked_in_guests'] ?? 0,
                    'checkin_rate' => $eventData['checkin_rate'] ?? 0,
                    'rsvp_yes' => $eventData['rsvp_yes'] ?? 0,
                    'rsvp_maybe' => $eventData['rsvp_maybe'] ?? 0,
                    'rsvp_no' => $eventData['rsvp_no'] ?? 0,
                ],
                'invitation_stats' => $eventData['invitation_stats'] ?? [],
                'checkin_stats' => array_merge($eventData['checkin_stats'] ?? [], [
                    'first_checkin' => $firstCheckin,
                    'last_checkin' => $lastCheckin,
                ]),
                'engagement_metrics' => $eventData['engagement_metrics'] ?? [],
                'scanner_usage' => $eventData['checkin_stats']['scanner_usage'] ?? [],
                'guests' => $eventData['guests'] ?? [],
                'generated_at' => now()->format('F j, Y \a\t g:i A'),
            ];
        } catch (\Exception $e) {
            \Log::error('PDF data preparation error: ' . $e->getMessage());
            throw new \Exception('Failed to prepare PDF data: ' . $e->getMessage());
        }
    }
}
