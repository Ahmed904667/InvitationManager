<?php

use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\AdminController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'userManagement'])->name('users.index');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // System Settings
    Route::get('/settings', [AdminController::class, 'systemSettings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    
    // URL Management
    Route::get('/url-settings', [AdminController::class, 'getUrlSettings'])->name('url-settings');
    Route::put('/url-settings', [AdminController::class, 'updateUrlSettings'])->name('url-settings.update');
    Route::post('/switch-to-localhost', [AdminController::class, 'switchToLocalhost'])->name('switch-to-localhost');
    Route::post('/switch-to-ngrok', [AdminController::class, 'switchToNgrok'])->name('switch-to-ngrok');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [AdminController::class, 'exportReport'])->name('reports.export');
    
    // Trial Submissions
    Route::get('/trials', [AdminController::class, 'trials'])->name('trials.index');
    Route::get('/trials/{trial}', [AdminController::class, 'showTrial'])->name('trials.show');
    Route::patch('/trials/{trial}/status', [AdminController::class, 'updateTrialStatus'])->name('trials.update-status');
}); 