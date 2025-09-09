<?php

namespace App\Http\Controllers;

use App\Services\PlatformStatsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LandingController extends Controller
{
    protected $platformStatsService;

    public function __construct(PlatformStatsService $platformStatsService)
    {
        $this->platformStatsService = $platformStatsService;
    }

    /**
     * Show the landing page with platform statistics
     */
    public function index()
    {
        // Redirect authenticated users to their dashboard
        if (auth()->check()) {
            $user = auth()->user();
            
            return match($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'organizer' => redirect()->route('organizer.dashboard'),
                'scanner' => redirect()->route('scanner.dashboard'),
                default => redirect()->route('organizer.dashboard')
            };
        }
        
        $stats = $this->platformStatsService->getFormattedStats();
        
        return view('landing', compact('stats'));
    }

    /**
     * Get platform statistics as JSON (for AJAX requests)
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->platformStatsService->getFormattedStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get detailed platform statistics
     */
    public function getDetailedStats(): JsonResponse
    {
        $stats = $this->platformStatsService->getPlatformStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get statistics for a specific time period
     */
    public function getStatsForPeriod(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $stats = $this->platformStatsService->getStatsForPeriod($period);
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'period' => $period
        ]);
    }
}
