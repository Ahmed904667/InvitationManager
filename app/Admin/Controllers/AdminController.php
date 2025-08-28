<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\User;
use App\Shared\Models\GuestList;
use App\Trial;
use App\Admin\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function dashboard()
    {
        Gate::authorize('admin-access');

        $stats = $this->adminService->getDashboardStats();
        
        return view('admin.dashboard', compact('stats'));
    }

    public function userManagement()
    {
        Gate::authorize('manage-users');

        $users = $this->adminService->getAllUsers();
        
        return view('admin.user-management', compact('users'));
    }

    public function storeUser(Request $request)
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,organizer,scanner',
            'password' => 'required|string|min:8'
        ]);

        $user = $this->adminService->createUser($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully!');
    }

    public function showUser(User $user)
    {
        Gate::authorize('view-user', $user);

        $userStats = $this->adminService->getUserStats($user);
        
        return view('admin.user-show', compact('user', 'userStats'));
    }

    public function updateUser(Request $request, User $user)
    {
        Gate::authorize('update-user', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,organizer,scanner',
            'is_active' => 'boolean'
        ]);

        $this->adminService->updateUser($user, $validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    public function deleteUser(User $user)
    {
        Gate::authorize('delete-user', $user);

        $this->adminService->deleteUser($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function systemSettings()
    {
        Gate::authorize('system-settings');

        $settings = $this->adminService->getSystemSettings();
        
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        Gate::authorize('system-settings');

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'max_guests_per_list' => 'required|integer|min:1|max:10000',
            'allow_guest_import' => 'boolean',
            'require_guest_approval' => 'boolean'
        ]);

        $this->adminService->updateSystemSettings($validated);

        return redirect()->route('admin.settings')
            ->with('success', 'System settings updated successfully!');
    }

    public function reports()
    {
        Gate::authorize('view-reports');

        $reports = $this->adminService->getReports();
        
        return view('admin.reports', compact('reports'));
    }

    public function exportReport(Request $request)
    {
        Gate::authorize('export-reports');

        $type = $request->get('type', 'users');
        $dateRange = $request->get('date_range', 'month');

        return $this->adminService->exportReport($type, $dateRange);
    }

    /**
     * Trial Management Methods
     */
    public function trials()
    {
        Gate::authorize('admin-access');

        $trials = Trial::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.trials.index', compact('trials'));
    }

    public function showTrial(Trial $trial)
    {
        Gate::authorize('admin-access');

        return view('admin.trials.show', compact('trial'));
    }

    public function updateTrialStatus(Request $request, Trial $trial)
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'status' => 'required|in:pending,contacted,converted,rejected'
        ]);

        $trial->update(['status' => $validated['status']]);

        return redirect()->route('admin.trials.show', $trial)
            ->with('success', 'Trial status updated successfully!');
    }

    /**
     * URL Management Methods
     */
    public function getUrlSettings()
    {
        Gate::authorize('system-settings');

        $contactService = app(\App\Services\ContactService::class);
        return response()->json($contactService->getUrlSettings());
    }

    public function updateUrlSettings(Request $request)
    {
        Gate::authorize('system-settings');

        $validated = $request->validate([
            'use_localhost' => 'required|boolean',
            'ngrok_url' => 'nullable|url',
        ]);

        $this->updateEnvironmentFile($validated);

        $contactService = app(\App\Services\ContactService::class);
        return response()->json([
            'success' => true,
            'message' => 'URL settings updated successfully!',
            'current_active_url' => $contactService->getCurrentActiveUrl(),
        ]);
    }

    public function switchToLocalhost()
    {
        Gate::authorize('system-settings');

        $this->updateEnvironmentFile(['use_localhost' => true]);

        $contactService = app(\App\Services\ContactService::class);
        return response()->json([
            'success' => true,
            'message' => 'Switched to localhost URL',
            'active_url' => $contactService->getCurrentActiveUrl(),
        ]);
    }

    public function switchToNgrok()
    {
        Gate::authorize('system-settings');

        $this->updateEnvironmentFile(['use_localhost' => false]);

        $contactService = app(\App\Services\ContactService::class);
        return response()->json([
            'success' => true,
            'message' => 'Switched to ngrok URL',
            'active_url' => $contactService->getCurrentActiveUrl(),
        ]);
    }

    private function getCurrentActiveUrl(): string
    {
        if (env('USE_LOCALHOST', 'false') === 'true') {
            return 'http://localhost:8000';
        }
        return env('APP_URL', 'http://localhost:8000');
    }

    private function updateEnvironmentFile(array $settings): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Update USE_LOCALHOST
        if (isset($settings['use_localhost'])) {
            $value = $settings['use_localhost'] ? 'true' : 'false';
            if (strpos($envContent, 'USE_LOCALHOST=') !== false) {
                $envContent = preg_replace('/USE_LOCALHOST=.*/', "USE_LOCALHOST={$value}", $envContent);
            } else {
                $envContent .= "\nUSE_LOCALHOST={$value}";
            }
        }

        // Update NGROK_URL
        if (isset($settings['ngrok_url']) && !empty($settings['ngrok_url'])) {
            if (strpos($envContent, 'NGROK_URL=') !== false) {
                $envContent = preg_replace('/NGROK_URL=.*/', "NGROK_URL={$settings['ngrok_url']}", $envContent);
            } else {
                $envContent .= "\nNGROK_URL={$settings['ngrok_url']}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
} 