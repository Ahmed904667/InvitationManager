<?php

namespace App\Admin\Services;

use App\Shared\Models\User;
use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminService
{
    public function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_organizers' => User::where('role', 'organizer')->count(),
            'total_scanners' => User::where('role', 'scanner')->count(),
            'total_guest_lists' => GuestList::count(),
            'total_guests' => Guest::count(),
            'active_users_today' => User::where('last_login_at', '>=', Carbon::today())->count(),
            'recent_activity' => $this->getRecentActivity(),
        ];
    }

    public function getAllUsers()
    {
        return User::with('guestLists')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getUserStats(User $user): array
    {
        return [
            'guest_lists_count' => $user->guestLists()->count(),
            'total_guests' => $user->guestLists()->withCount('guests')->get()->sum('guests_count'),
            'last_login' => $user->last_login_at,
            'account_age' => $user->created_at->diffForHumans(),
            'recent_activity' => $this->getUserRecentActivity($user),
        ];
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        
        // Log the change (commented out until activity log is properly set up)
        // activity()
        //     ->performedOn($user)
        //     ->log('User updated by admin');
            
        return $user;
    }

    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => bcrypt($data['password']),
            'is_active' => true,
        ]);
        
        // Log the creation (commented out until activity log is properly set up)
        // activity()
        //     ->performedOn($user)
        //     ->log('User created by admin');
            
        return $user;
    }

    public function deleteUser(User $user): bool
    {
        // Log before deletion (commented out until activity log is properly set up)
        // activity()
        //     ->performedOn($user)
        //     ->log('User deleted by admin');
            
        return $user->delete();
    }

    public function getSystemSettings(): array
    {
        return [
            'app_name' => config('app.name'),
            'max_guests_per_list' => config('guest-manager.max_guests_per_list', 1000),
            'allow_guest_import' => config('guest-manager.allow_guest_import', true),
            'require_guest_approval' => config('guest-manager.require_guest_approval', false),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];
    }

    public function updateSystemSettings(array $settings): void
    {
        // Update configuration (you might want to store these in database)
        foreach ($settings as $key => $value) {
            // Implementation depends on how you store settings
            // Could be database, cache, or config files
        }
        
        // Clear cache after settings update
        cache()->flush();
    }

    public function getReports(): array
    {
        return [
            'user_registration' => $this->getUserRegistrationReport(),
            'guest_list_creation' => $this->getGuestListCreationReport(),
            'guest_imports' => $this->getGuestImportReport(),
            'system_usage' => $this->getSystemUsageReport(),
        ];
    }

    public function exportReport(string $type, string $dateRange)
    {
        switch ($type) {
            case 'users':
                return $this->exportUsersReport($dateRange);
            case 'guest_lists':
                return $this->exportGuestListsReport($dateRange);
            case 'guests':
                return $this->exportGuestsReport($dateRange);
            default:
                abort(400, 'Invalid report type');
        }
    }

    private function getRecentActivity(): array
    {
        // Check if activity_log table exists before querying
        if (!DB::getSchemaBuilder()->hasTable('activity_log')) {
            return [];
        }
        
        try {
            return DB::table('activity_log')
                ->latest()
                ->limit(10)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserRecentActivity(User $user): array
    {
        // Check if activity_log table exists before querying
        if (!DB::getSchemaBuilder()->hasTable('activity_log')) {
            return [];
        }
        
        try {
            return DB::table('activity_log')
                ->where('causer_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserRegistrationReport(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        
        return [
            'total' => User::count(),
            'this_month' => User::where('created_at', '>=', $lastMonth)->count(),
            'by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role')
                ->toArray(),
        ];
    }

    private function getGuestListCreationReport(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        
        return [
            'total' => GuestList::count(),
            'this_month' => GuestList::where('created_at', '>=', $lastMonth)->count(),
            'by_organizer' => GuestList::with('user')
                ->select('user_id', DB::raw('count(*) as count'))
                ->groupBy('user_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->user->name ?? 'Unknown' => $item->count];
                })
                ->toArray(),
        ];
    }

    private function getGuestImportReport(): array
    {
        return [
            'total_guests' => Guest::count(),
            'checked_in' => Guest::where('checked_in', true)->count(),
            'not_checked_in' => Guest::where('checked_in', false)->count(),
            'by_group' => Guest::with('group')
                ->select('group_id', DB::raw('count(*) as count'))
                ->groupBy('group_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->group->name ?? 'No Group' => $item->count];
                })
                ->toArray(),
        ];
    }

    private function getSystemUsageReport(): array
    {
        return [
            'total_users' => User::count(),
            'active_users_today' => User::where('last_login_at', '>=', Carbon::today())->count(),
            'total_guest_lists' => GuestList::count(),
            'total_guests' => Guest::count(),
            'check_ins_today' => Guest::where('checked_in', true)
                ->whereDate('checked_in_at', Carbon::today())
                ->count(),
        ];
    }

    private function exportUsersReport(string $dateRange): array
    {
        // Implementation for exporting users report
        return [];
    }

    private function exportGuestListsReport(string $dateRange): array
    {
        // Implementation for exporting guest lists report
        return [];
    }

    private function exportGuestsReport(string $dateRange): array
    {
        // Implementation for exporting guests report
        return [];
    }
} 