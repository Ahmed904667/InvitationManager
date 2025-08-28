@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card">
            <div class="p-6 border-b border-primary">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-primary">{{ $guestList->name }}</h2>
                        <p class="text-secondary">{{ $guests->count() }} guests</p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('guest-lists.export', $guestList) }}" 
                           class="btn-success">
                            Export
                        </a>
                        <a href="{{ route('guest-lists.edit', $guestList) }}" 
                           class="btn-primary">
                            Edit List
                        </a>
                    </div>
                </div>

                <!-- Import Section -->
                <div class="mb-6 p-4 bg-secondary rounded-lg">
                    <h3 class="text-lg font-semibold mb-2 text-primary">Import Guests</h3>
                    <form action="{{ route('guest-lists.import', $guestList) }}" method="POST" enctype="multipart/form-data" class="flex space-x-2">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" 
                               class="form-input flex-1" required>
                        <button type="submit" 
                                class="btn-primary">
                            Import
                        </button>
                    </form>
                    <p class="text-sm text-secondary mt-2">
                        Supported formats: Excel (.xlsx, .xls) and CSV. Columns: Name, Email, Phone, Language, Group
                    </p>
                </div>

                <!-- Add Guest Form -->
                <div class="mb-6 p-4 bg-secondary rounded-lg">
                    <h3 class="text-lg font-semibold mb-2 text-primary">Add New Guest</h3>
                    <form action="{{ route('guests.store', $guestList) }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @csrf
                        <input type="text" name="name" placeholder="Name" required 
                               class="form-input">
                        <input type="email" name="email" placeholder="Email" 
                               class="form-input">
                        <input type="text" name="phone" placeholder="Phone" 
                               class="form-input">
                        <input type="text" name="language" placeholder="Language" 
                               class="form-input">
                        <button type="submit" 
                                class="btn-primary">
                            Add Guest
                        </button>
                    </form>
                </div>

                <!-- Guests Table -->
                @if($guests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-primary">
                            <thead class="bg-secondary">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                        Phone
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                        Language
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                        Group
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-primary divide-y divide-primary">
                                @foreach($guests as $guest)
                                    <tr class="hover:bg-secondary transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                            {{ $guest->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                            {{ $guest->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                            {{ $guest->phone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                            {{ $guest->language }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                            {{ $guest->group ? $guest->group->name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="editGuest({{ $guest->id }})" 
                                                    class="text-primary-600 hover:text-primary-900 mr-3 transition-colors duration-200">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('guests.destroy', [$guestList, $guest]) }}" 
                                                  class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-tertiary mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-primary mb-2">No guests yet</h3>
                        <p class="text-secondary">Add your first guest using the form above or import from a file.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Guest Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md card">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-primary mb-4">Edit Guest</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <input type="text" name="name" id="edit_name" placeholder="Name" required 
                           class="form-input w-full">
                    <input type="email" name="email" id="edit_email" placeholder="Email" 
                           class="form-input w-full">
                    <input type="text" name="phone" id="edit_phone" placeholder="Phone" 
                           class="form-input w-full">
                    <input type="text" name="language" id="edit_language" placeholder="Language" 
                           class="form-input w-full">
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeEditModal()" 
                            class="btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="btn-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editGuest(guestId) {
    // This would typically fetch guest data via AJAX
    // For now, we'll show a simple modal
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection 