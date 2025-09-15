<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\ScannerController;

/*
|--------------------------------------------------------------------------
| Mobile Scanner Routes
|--------------------------------------------------------------------------
|
| These routes are for mobile scanner interface access via unique tokens.
| No authentication required as tokens provide access control.
|
*/

Route::prefix('scanner')->name('mobile.scanner.')->group(function () {
    
    // Scanner Access
    Route::get('/{token}', [ScannerController::class, 'access'])->name('access');
    Route::get('/{token}/profiles', [ScannerController::class, 'profiles'])->name('profiles');
    Route::post('/{token}/profiles', [ScannerController::class, 'createProfile'])->name('profiles.create');
    Route::get('/{token}/switch/{newToken}', [ScannerController::class, 'switchProfile'])->name('switch');
    
    // Scanner Interface
    Route::get('/{token}/scan', [ScannerController::class, 'scan'])->name('scan');
    Route::get('/{token}/guests', [ScannerController::class, 'guests'])->name('guests');
    Route::get('/{token}/profile', [ScannerController::class, 'profile'])->name('profile');
    
    // Scanner Actions (AJAX)
    Route::post('/{token}/qr', [ScannerController::class, 'processQR'])->name('process-qr');
    Route::post('/{token}/checkin', [ScannerController::class, 'checkIn'])->name('checkin');
    
    // Scanner Data APIs
    Route::get('/{token}/stats', [ScannerController::class, 'getStats'])->name('stats');
    Route::get('/{token}/scan-stats', [ScannerController::class, 'getScanStats'])->name('scan-stats');
    Route::get('/{token}/performance', [ScannerController::class, 'getPerformanceData'])->name('performance');
    Route::get('/{token}/analytics', [ScannerController::class, 'getAnalytics'])->name('analytics');
    Route::get('/{token}/chart-data', [ScannerController::class, 'getChartData'])->name('chart-data');
Route::post('/{token}/detect-timezone', [ScannerController::class, 'detectTimezone'])->name('detect-timezone');
    Route::get('/{token}/recent-activity', [ScannerController::class, 'getRecentActivity'])->name('recent-activity');
    Route::get('/{token}/profiles', [ScannerController::class, 'getProfiles'])->name('api-profiles');
    Route::post('/{token}/settings', [ScannerController::class, 'saveSettings'])->name('save-settings');
    Route::get('/{token}/checkins', [ScannerController::class, 'getMoreCheckIns'])->name('more-checkins');
    Route::get('/{token}/export', [ScannerController::class, 'exportData'])->name('export-data');
    
});
