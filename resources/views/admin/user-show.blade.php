@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-primary">User Details</h1>
                    <p class="mt-1 text-sm text-gray-500">View and manage user information</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Users
                    </a>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        👑 Admin
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- User Profile -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center mb-6">
                            <div class="flex-shrink-0 h-16 w-16">
                                <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-xl font-medium text-gray-700">{{ substr($user->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="ml-6">
                                <h3 class="text-2xl font-bold text-primary">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                <div class="flex items-center mt-2 space-x-2">
                                    <span class="badge badge-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'organizer' ? 'warning' : 'info') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" value="{{ $user->name }}" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="{{ $user->email }}" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-input" required>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="organizer" {{ $user->role === 'organizer' ? 'selected' : '' }}>Organizer</option>
                                        <option value="scanner" {{ $user->role === 'scanner' ? 'selected' : '' }}>Scanner</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-input">
                                        <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                                <button type="submit" class="btn-primary">Update User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- User Stats -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-primary mb-4">User Statistics</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Member Since</span>
                                <span class="text-sm font-medium text-primary">{{ $user->created_at->format('M j, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Last Login</span>
                                <span class="text-sm font-medium text-primary">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Login Count</span>
                                <span class="text-sm font-medium text-primary">{{ $userStats['login_count'] ?? 0 }}</span>
                            </div>
                            @if($user->role === 'organizer')
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Guest Lists</span>
                                <span class="text-sm font-medium text-primary">{{ $userStats['guest_lists_count'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Total Guests</span>
                                <span class="text-sm font-medium text-primary">{{ $userStats['total_guests'] ?? 0 }}</span>
                            </div>
                            @endif
                            @if($user->role === 'scanner')
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Check-ins Today</span>
                                <span class="text-sm font-medium text-primary">{{ $userStats['checkins_today'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Total Check-ins</span>
                                <span class="text-sm font-medium text-primary">{{ $userStats['total_checkins'] ?? 0 }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                @if($user->id !== auth()->id())
                <div class="bg-white shadow rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-red-900 mb-4">Danger Zone</h3>
                        <p class="text-sm text-gray-500 mb-4">Once you delete a user, there is no going back. Please be certain.</p>
                        <button onclick="deleteUser({{ $user->id }})" class="btn-danger">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete User
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(response => {
            if (response.ok) {
                window.location.href = '{{ route("admin.users.index") }}';
            }
        });
    }
}
</script>
@endpush 