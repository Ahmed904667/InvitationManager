@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-primary">User Management</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage system users and permissions</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="btn-primary" onclick="openCreateUserModal()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add User
                    </button>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        👑 Admin
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg leading-6 font-medium text-primary">System Users</h3>
                    <div class="flex space-x-2">
                        <input type="text" id="searchUsers" placeholder="Search users..." class="form-input w-64">
                        <select id="roleFilter" class="form-input w-32">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="organizer">Organizer</option>
                            <option value="scanner">Scanner</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-header">
                            <tr>
                                <th class="table-header-cell">User</th>
                                <th class="table-header-cell">Role</th>
                                <th class="table-header-cell">Status</th>
                                <th class="table-header-cell">Last Login</th>
                                <th class="table-header-cell">Created</th>
                                <th class="table-header-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse($users as $user)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-primary">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <span class="badge badge-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'organizer' ? 'warning' : 'info') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="table-cell text-sm text-gray-500">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="table-cell text-sm text-gray-500">
                                    {{ $user->created_at->format('M j, Y') }}
                                </td>
                                <td class="table-cell">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">View</a>
                                        <button onclick="editUser({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</button>
                                        @if($user->id !== auth()->id())
                                        <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="table-cell text-center py-8 text-gray-500">
                                    No users found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-primary mb-4">Create New User</h3>
            <form id="createUserForm" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-input" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="organizer">Organizer</option>
                            <option value="scanner">Scanner</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeCreateUserModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openCreateUserModal() {
    document.getElementById('createUserModal').classList.remove('hidden');
}

function closeCreateUserModal() {
    document.getElementById('createUserModal').classList.add('hidden');
}

function editUser(userId) {
    // Implement edit user functionality
    console.log('Edit user:', userId);
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
}

// Search and filter functionality
document.getElementById('searchUsers').addEventListener('input', filterUsers);
document.getElementById('roleFilter').addEventListener('change', filterUsers);

function filterUsers() {
    const searchTerm = document.getElementById('searchUsers').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        const email = row.querySelector('td:first-child div:last-child').textContent.toLowerCase();
        const role = row.querySelector('td:nth-child(2) span').textContent.toLowerCase();

        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        const matchesRole = !roleFilter || role.includes(roleFilter);

        row.style.display = matchesSearch && matchesRole ? '' : 'none';
    });
}
</script>
@endpush 