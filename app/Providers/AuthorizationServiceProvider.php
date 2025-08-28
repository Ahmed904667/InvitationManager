<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Shared\Models\User;
use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use App\Shared\Models\Event;
use App\Policies\EventPolicy;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Event::class => EventPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Admin Gates
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-user', function (User $user, User $targetUser) {
            return $user->isAdmin();
        });

        Gate::define('update-user', function (User $user, User $targetUser) {
            return $user->isAdmin();
        });

        Gate::define('delete-user', function (User $user, User $targetUser) {
            return $user->isAdmin() && $user->id !== $targetUser->id;
        });

        Gate::define('system-settings', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-reports', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('export-reports', function (User $user) {
            return $user->isAdmin();
        });

        // Organizer Gates
        Gate::define('organizer-access', function (User $user) {
            return $user->isOrganizer();
        });

        Gate::define('view-guest-lists', function (User $user) {
            return $user->isOrganizer();
        });

        Gate::define('create-guest-list', function (User $user) {
            return $user->isOrganizer();
        });

        Gate::define('view-guest-list', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('update-guest-list', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('delete-guest-list', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('add-guest', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('update-guest', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('delete-guest', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('import-guests', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('export-guests', function (User $user, GuestList $guestList) {
            return $user->isOrganizer() && $guestList->user_id === $user->id;
        });

        Gate::define('view-organizer-reports', function (User $user) {
            return $user->isOrganizer();
        });

        // Event Gates
        Gate::define('view-events', function (User $user) {
            return $user->isOrganizer();
        });

        Gate::define('create-event', function (User $user) {
            return $user->isOrganizer();
        });

        Gate::define('view-event', function (User $user, Event $event) {
            return $user->isOrganizer() && $event->user_id === $user->id;
        });

        Gate::define('update-event', function (User $user, Event $event) {
            return $user->isOrganizer() && $event->user_id === $user->id;
        });

        Gate::define('delete-event', function (User $user, Event $event) {
            return $user->isOrganizer() && $event->user_id === $user->id;
        });

        // Scanner Gates
        Gate::define('scanner-access', function (User $user) {
            return $user->isScanner();
        });

        Gate::define('view-events', function (User $user) {
            return $user->isScanner();
        });

        Gate::define('scan-event', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('scan-guest', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('manual-checkin', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('search-guest', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('view-guest-details', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('view-checkin-history', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('export-checkins', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('bulk-checkin', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('undo-checkin', function (User $user, GuestList $guestList) {
            return $user->isScanner();
        });

        Gate::define('scanner-settings', function (User $user) {
            return $user->isScanner();
        });

        Gate::define('offline-mode', function (User $user) {
            return $user->isScanner();
        });

        Gate::define('sync-offline', function (User $user) {
            return $user->isScanner();
        });
    }
}
