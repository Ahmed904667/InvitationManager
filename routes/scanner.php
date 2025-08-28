<?php

use Illuminate\Support\Facades\Route;
use App\Scanner\Controllers\ScannerController;

Route::middleware(['auth', 'scanner'])->prefix('scanner')->name('scanner.')->group(function () {
    
    // Scanner Dashboard
    Route::get('/', [ScannerController::class, 'dashboard'])->name('dashboard');
    
    // Available Events
    Route::get('/events', [ScannerController::class, 'availableEvents'])->name('events.index');
    Route::get('/events/{guestList}/scan', [ScannerController::class, 'scanEvent'])->name('events.scan');
    
    // Scanning Operations
    Route::post('/events/{guestList}/scan', [ScannerController::class, 'scanGuest'])->name('events.scan-guest');
    Route::post('/events/{guestList}/checkin', [ScannerController::class, 'manualCheckIn'])->name('events.checkin');
    Route::post('/events/{guestList}/search', [ScannerController::class, 'searchGuest'])->name('events.search');
    
    // Guest Details
    Route::get('/events/{guestList}/guests/{guest}', [ScannerController::class, 'guestDetails'])->name('guests.details');
    
    // Check-in History
    Route::get('/events/{guestList}/history', [ScannerController::class, 'checkInHistory'])->name('events.history');
    Route::get('/events/{guestList}/export', [ScannerController::class, 'exportCheckIns'])->name('events.export');
    
    // Bulk Operations
    Route::post('/events/{guestList}/bulk-checkin', [ScannerController::class, 'bulkCheckIn'])->name('events.bulk-checkin');
    Route::post('/events/{guestList}/guests/{guest}/undo', [ScannerController::class, 'undoCheckIn'])->name('events.undo-checkin');
    
    // Scanner Settings
    Route::get('/settings', [ScannerController::class, 'scannerSettings'])->name('settings');
    Route::put('/settings', [ScannerController::class, 'updateScannerSettings'])->name('settings.update');
    
    // Offline Mode
    Route::get('/offline', [ScannerController::class, 'offlineMode'])->name('offline');
    Route::post('/offline/sync', [ScannerController::class, 'syncOfflineData'])->name('offline.sync');
}); 