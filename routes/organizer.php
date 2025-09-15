<?php

use Illuminate\Support\Facades\Route;
use App\Organizer\Controllers\DashboardController;
use App\Organizer\Controllers\GuestListController;
use App\Organizer\Controllers\GuestController;
use App\Organizer\Controllers\ImportController;
use App\Organizer\Controllers\ExportController;
use App\Organizer\Controllers\GoogleController;
use App\Organizer\Controllers\ProfileController;
use App\Organizer\Controllers\OrganizerSettingsController;

Route::middleware(['auth', 'organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    // -------------------- Dashboard --------------------
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::get('/stats', [DashboardController::class, 'stats'])->name('organizer.stats');
    Route::get('/guest-lists/json', [DashboardController::class, 'guestListsJson'])->name('guest-lists.json');
    Route::get('/event-report/{eventId}', [DashboardController::class, 'getEventReport'])->name('event-report');
    Route::get('/event-report/{eventId}/pdf', [DashboardController::class, 'downloadEventReportPdf'])->name('event-report-pdf');

    // -------------------- Profile Management --------------------
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email');
    Route::post('/profile/email/verify', [ProfileController::class, 'verifyEmailOTP'])->name('profile.email.verify');
    Route::put('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhoneOTP'])->name('profile.phone.verify');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/password/reset-link', [ProfileController::class, 'sendPasswordResetLink'])->name('profile.password.reset-link');
    Route::get('/profile/password/reset/{token}', [ProfileController::class, 'showPasswordResetForm'])->name('profile.password.reset');
    Route::post('/profile/password/reset', [ProfileController::class, 'resetPassword'])->name('profile.password.reset.submit');
    Route::put('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');

    // -------------------- Guest Lists Management --------------------
    Route::get('/guest-lists', [GuestListController::class, 'index'])->name('guest-lists.index');
    Route::get('/guest-lists/create', [GuestListController::class, 'create'])->name('guest-lists.create');
    Route::post('/guest-lists', [GuestListController::class, 'store'])->name('guest-lists.store');
    Route::get('/guest-lists/{guestList}', [GuestListController::class, 'display'])->name('guest-lists.display');
    Route::get('/guest-lists/{guestList}/edit', [GuestListController::class, 'edit'])->name('guest-lists.edit');
    Route::put('/guest-lists/{guestList}', [GuestListController::class, 'update'])->name('guest-lists.update');
    Route::post('/guest-lists/{guestList}/settings', [GuestListController::class, 'updateSettings'])->name('guest-lists.updateSettings');
    Route::get('/guest-lists/{guestList}/guests', [GuestListController::class, 'getGuests'])->name('guest-lists.guests');

    // -------------------- Groups --------------------
Route::get('/guest-lists/{guestList}/groups', [GuestListController::class, 'getGuestGroups'])->name('guest-lists.groups');
Route::post('/guest-lists/{guestList}/groups', [GuestListController::class, 'addGroup'])->name('guest-lists.groups.store');
Route::put('/guest-lists/{guestList}/groups/{group}', [GuestListController::class, 'updateGroup'])->name('guest-lists.groups.update');
Route::delete('/guest-lists/{guestList}/groups/{group}', [GuestListController::class, 'deleteGroup'])->name('guest-lists.groups.delete');
    Route::post('/guest-lists/{guestList}/change-group', [GuestListController::class, 'bulkChangeGroup'])->name('guest-lists.change-group');

    // -------------------- Validation --------------------
    Route::get('/guest-lists/{guestList}/validation-errors', [GuestListController::class, 'getValidationErrors'])->name('guest-lists.validation-errors');
    Route::get('/guest-lists/{guestList}/health', [GuestListController::class, 'getHealth'])->name('guest-lists.health');

    // -------------------- Guest Management --------------------
    Route::post('/guest-lists/{guestList}/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::put('/guest-lists/{guestList}/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guest-lists/{guestList}/guests/{guest}', [GuestController::class, 'destroy'])
        ->where('guest', '[0-9]+')
        ->name('guests.delete');
    // Add bulk delete endpoint
    Route::delete('/guest-lists/{guestList}/guests', [GuestController::class, 'bulkDeleteGuests'])
        ->name('guests.bulk-delete');

    // -------------------- Guest List Delete (moved after guest routes) --------------------
    Route::delete('/guest-lists/{guestList}', [GuestListController::class, 'destroy'])->name('guest-lists.delete');

    // -------------------- Import/Export --------------------
    Route::get('/guest-lists/{guestList}/google-contacts', [GoogleController::class, 'getGoogleContacts'])->name('google.contacts');
    Route::get('/guest-lists/{guestList}/google-sheets', [GoogleController::class, 'listValidGoogleSheets'])->name('guest-lists.google-sheets');
    
    Route::post('/guest-lists/{guestList}/import', [ImportController::class, 'import'])->name('guest-lists.import');
    
    // File-based import (CSV, Excel)
    Route::post('/guest-lists/{guestList}/import-guests', [ImportController::class, 'import'])->name('guest-lists.import-guests');
    
    // Google Contacts import
    Route::post('/guest-lists/{guestList}/import-google-contacts', [ImportController::class, 'importFromGoogleContacts'])->name('guest-lists.import-google-contacts');
    
    // Google Sheets import
    Route::post('/guest-lists/{guestList}/import-google-sheet', [ImportController::class, 'importFromGoogleSheet'])->name('guest-lists.import-google-sheet');
    
    // -------------------- Export --------------------
    Route::get('/guest-lists/{guestList}/export/csv', [ExportController::class, 'exportToCsv'])->name('guest-lists.export.csv');
    Route::get('/guest-lists/{guestList}/export/excel', [ExportController::class, 'exportToExcel'])->name('guest-lists.export.excel');
    Route::get('/guest-lists/{guestList}/export/pdf', [ExportController::class, 'exportToPdf'])->name('guest-lists.export.pdf');
    Route::get('/guest-lists/{guestList}/export/stats', [ExportController::class, 'getExportStats'])->name('guest-lists.export.stats');

    // -------------------- Events Management --------------------
    Route::get('/events', [\App\Organizer\Controllers\EventController::class, 'index'])->name('events.index');
    Route::get('/events/completed', [\App\Organizer\Controllers\EventController::class, 'completed'])->name('events.completed');
    Route::post('/events/{event}/complete', [\App\Organizer\Controllers\EventController::class, 'markAsCompleted'])->name('events.mark-completed');
    Route::post('/events/{event}/start', [\App\Organizer\Controllers\EventController::class, 'markAsRunning'])->name('events.mark-running');
    
    // Scanner management routes
    Route::get('/events/{event}/scanners', [\App\Organizer\Controllers\EventController::class, 'getScanners'])->name('events.scanners');
    Route::post('/events/{event}/scanners', [\App\Organizer\Controllers\EventController::class, 'createScanner'])->name('events.scanners.create');
    
    // Multi-step Event Creation Flow
    Route::get('/events/create', [\App\Organizer\Controllers\EventController::class, 'create'])->name('events.create');
    Route::get('/events/create/step1', [\App\Organizer\Controllers\EventController::class, 'createStep1'])->name('events.create.step1');
    Route::post('/events/create/step1', [\App\Organizer\Controllers\EventController::class, 'processStep1'])->name('events.create.step1.process');
    Route::get('/events/create/step2', [\App\Organizer\Controllers\EventController::class, 'createStep2'])->name('events.create.step2');
    Route::post('/events/create/step2', [\App\Organizer\Controllers\EventController::class, 'processStep2'])->name('events.create.step2.process');
    Route::get('/events/create/step3', [\App\Organizer\Controllers\EventController::class, 'createStep3'])->name('events.create.step3');
    Route::post('/events/create/step3', [\App\Organizer\Controllers\EventController::class, 'processStep3'])->name('events.create.step3.process');
    Route::get('/events/create/step4', [\App\Organizer\Controllers\EventController::class, 'createStep4'])->name('events.create.step4');
    Route::post('/events/create/step4', [\App\Organizer\Controllers\EventController::class, 'processStep4'])->name('events.create.step4.process');
    
    // AI Message Generation for Step 3
    Route::post('/events/create/generate-ai-message', [\App\Organizer\Controllers\EventController::class, 'generateAIMessage'])->name('events.create.generate-ai-message');
    Route::post('/events/create/chat', [\App\Organizer\Controllers\EventController::class, 'chat'])->name('events.create.chat');
    Route::post('/events/create/auto-fix-guest-lists', [\App\Organizer\Controllers\EventController::class, 'autoFixGuestLists'])->name('events.create.auto-fix-guest-lists');
    
    // Auto-save functionality
    Route::post('/events/create/auto-save', [\App\Organizer\Controllers\EventController::class, 'autoSave'])->name('events.create.auto-save');
    Route::post('/events/create/clear-session', [\App\Organizer\Controllers\EventController::class, 'clearSession'])->name('events.create.clear-session');
    Route::get('/events/create/new', [\App\Organizer\Controllers\EventController::class, 'createNew'])->name('events.create.new');
    
    // Duplicate guest management
    Route::post('/events/create/remove-duplicates', [\App\Organizer\Controllers\EventController::class, 'removeDuplicateGuests'])->name('events.create.remove-duplicates');
    Route::get('/events/{event}/continue', [\App\Organizer\Controllers\EventController::class, 'continueEditing'])->name('events.continue');
    
    // Legacy single-step create (kept for backward compatibility)
    Route::post('/events', [\App\Organizer\Controllers\EventController::class, 'store'])->name('events.store');
    
    Route::get('/events/{event}', [\App\Organizer\Controllers\EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [\App\Organizer\Controllers\EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [\App\Organizer\Controllers\EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [\App\Organizer\Controllers\EventController::class, 'destroy'])->name('events.delete');
    Route::post('/events/{event}/cancel', [\App\Organizer\Controllers\EventController::class, 'cancel'])->name('events.cancel');
    Route::get('/events/{event}/preview', [\App\Organizer\Controllers\EventController::class, 'preview'])->name('events.preview');
    Route::post('/events/{event}/send-invitations', [\App\Organizer\Controllers\EventController::class, 'sendInvitations'])->name('events.send-invitations');
    
    // -------------------- Sent Event Updates --------------------
    Route::get('/events/{event}/update-sent', [\App\Organizer\Controllers\EventController::class, 'updateSentEvent'])->name('events.update-sent');
    Route::put('/events/{event}/update-sent/basic', [\App\Organizer\Controllers\EventController::class, 'updateSentEventBasic'])->name('events.update-sent.basic');
    Route::post('/events/{event}/update-sent/add-guest-list', [\App\Organizer\Controllers\EventController::class, 'addGuestListToSentEvent'])->name('events.update-sent.add-guest-list');
    Route::post('/events/{event}/update-sent/add-guest', [\App\Organizer\Controllers\EventController::class, 'addGuestToSentEvent'])->name('events.update-sent.add-guest');
    Route::post('/events/{event}/update-sent/remove-guest', [\App\Organizer\Controllers\EventController::class, 'removeGuestFromSentEvent'])->name('events.update-sent.remove-guest');
Route::get('/events/{event}/notifications', [\App\Organizer\Controllers\EventController::class, 'viewNotifications'])->name('events.notifications');
Route::post('/events/{event}/notifications/refresh', [\App\Organizer\Controllers\EventController::class, 'refreshNotificationStatuses'])->name('events.notifications.refresh');
Route::get('/events/{event}/notifications/stats', [\App\Organizer\Controllers\EventController::class, 'getNotificationStats'])->name('events.notifications.stats');
    Route::get('/events/{event}/notifications/list', [\App\Organizer\Controllers\EventController::class, 'getNotificationList'])->name('events.notifications.list');
    Route::post('/events/{event}/notifications/send', [\App\Organizer\Controllers\EventController::class, 'sendNotificationToAllGuests'])->name('events.notifications.send');
    Route::get('/events/{event}/notifications/{notificationId}/check-status', [\App\Organizer\Controllers\EventController::class, 'checkNotificationStatus'])->name('events.notifications.check-status');
    
    // Test route for debugging
    Route::get('/events/{event}/notifications/test', function(\App\Shared\Models\Event $event) {
        return response()->json([
            'success' => true,
            'message' => 'Test route working',
            'event_id' => $event->id,
            'event_name' => $event->name,
            'user_id' => auth()->id()
        ]);
    })->name('events.notifications.test');
    
    // -------------------- Invitation Management --------------------
    Route::post('/events/{event}/invitations/refresh', [\App\Organizer\Controllers\EventController::class, 'refreshInvitationStatuses'])->name('events.invitations.refresh');

    Route::post('/events/{event}/update-sent/remove-guest-list', [\App\Organizer\Controllers\EventController::class, 'removeGuestListFromSentEvent'])->name('events.update-sent.remove-guest-list');
    Route::post('/events/{event}/update-sent/generate-messages', [\App\Organizer\Controllers\EventController::class, 'generateMessagesForNewGuests'])->name('events.update-sent.generate-messages');
    Route::post('/events/{event}/update-sent/save-message', [\App\Organizer\Controllers\EventController::class, 'saveGuestMessage'])->name('events.update-sent.save-message');
    Route::post('/events/{event}/update-sent/notify-guests', [\App\Organizer\Controllers\EventController::class, 'notifyGuestsOfUpdates'])->name('events.update-sent.notify-guests');
    Route::post('/events/{event}/update-sent/send-new-invitations', [\App\Organizer\Controllers\EventController::class, 'sendNewGuestInvitations'])->name('events.update-sent.send-new-invitations');
    Route::put('/events/{event}/update-sent/settings', [\App\Organizer\Controllers\EventController::class, 'updateSentEventSettings'])->name('events.update-sent.settings');
    Route::get('/events/{event}/individual-messages', [\App\Organizer\Controllers\EventController::class, 'getIndividualMessages'])->name('events.individual-messages');
    
    // -------------------- Scheduled Messages --------------------
    Route::get('/scheduled-messages', [\App\Organizer\Controllers\EventController::class, 'scheduledMessagesView'])->name('scheduled-messages.view');
    Route::get('/scheduled-messages/api', [\App\Organizer\Controllers\EventController::class, 'getScheduledMessages'])->name('scheduled-messages.index');
    Route::get('/scheduled-messages/filtered', [\App\Organizer\Controllers\EventController::class, 'getScheduledMessagesFiltered'])->name('scheduled-messages.filtered');
    
    // -------------------- RSVP Management --------------------
    Route::get('/events/{event}/rsvp/stats', [App\Http\Controllers\RsvpController::class, 'getStats'])->name('events.rsvp.stats');
    Route::get('/events/{event}/rsvp/details', [App\Http\Controllers\RsvpController::class, 'getDetails'])->name('events.rsvp.details');
    
    // -------------------- Guest Management --------------------
    Route::get('/events/{event}/guests/{guest}', [App\Http\Controllers\GuestController::class, 'show'])->name('events.guests.show');
    Route::get('/events/{event}/guests/{guest}/rsvp-history', [App\Http\Controllers\GuestController::class, 'getRsvpHistory'])->name('events.guests.rsvp-history');
    Route::get('/events/{event}/guests/{guest}/check-in-status', [App\Http\Controllers\GuestController::class, 'getCheckInStatus'])->name('events.guests.check-in-status');
    
    // -------------------- Settings --------------------
    Route::get('/settings', [OrganizerSettingsController::class, 'index'])->name('settings');
    Route::put('/settings/notifications', [OrganizerSettingsController::class, 'updateNotifications'])->name('settings.notifications');
}); 