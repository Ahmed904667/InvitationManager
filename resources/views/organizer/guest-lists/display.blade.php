@extends('layouts.organizer')

@section('title', $guestList->name)

@push('styles')
<style>
/* --- Main Content Responsive Styles --- */

/* Main content width rules */
.main-content-1149 {
  max-width: 773px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 2rem;
  padding-right: 2rem;
}
@media (min-width: 1024px) and (max-width: 1148.98px) {
  .main-content-1149 {
    max-width: 768px !important;
  }
}

/* --- Custom Styles --- */









@media (max-width: 768px) {
  .main-content-1149 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  /* Make tables responsive on mobile */
  #guestsTableContainer table {
    font-size: 14px;
  }
  
  #guestsTableContainer th,
  #guestsTableContainer td {
    padding: 8px 4px;
  }
}
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 main-content-1149">
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('organizer.guest-lists.index') }}" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 id="guestListName" class="text-3xl font-bold text-primary">{{ $guestList->name }}</h1>
                    <p id="guestListDescription" class="text-gray-600 mt-1">{{ $guestList->description }}</p>
                </div>
            </div>
            @if($guestList->event_date)
            <div class="mt-4 flex items-center space-x-6 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ \Carbon\Carbon::parse($guestList->event_date)->format('F j, Y') }}
                    @if($guestList->event_time)
                        at {{ \Carbon\Carbon::parse($guestList->event_time)->format('g:i A') }}
                    @endif
                </div>
                @if($guestList->event_location)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $guestList->event_location }}
                </div>
                @endif
            </div>
            @endif
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('organizer.guest-lists.edit', $guestList) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit List
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--primary-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $allGuests->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--success-500);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #fff;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Groups Count</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $guestGroups->count() }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg shadow-sm border p-6 cursor-pointer hover:shadow-md transition-shadow" style="background: var(--bg-primary); border-color: var(--border-primary);" onclick="showHealthModal()">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--{{ $guestList->health['color'] ?? 'gray' }}-100);">
                    @if($guestList->health && $guestList->health['status'] === 'not valid')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--{{ $guestList->health['color'] ?? 'gray' }}-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--{{ $guestList->health['color'] ?? 'gray' }}-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">List Health</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">
                        @if($guestList->health)
                            {{ ucfirst($guestList->health['status']) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="ml-auto">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="rounded-lg shadow-sm border p-6 mb-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Search Guests</label>
                <input type="text" id="searchInput" placeholder="Search by name or email..." class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Health Issues</label>
                <select id="healthFilter" class="form-select">
                    <option value="">All Guests</option>
                    <option value="missing_email">Missing Email</option>
                    <option value="missing_phone">Missing Phone</option>
                    <option value="missing_group">Missing Group</option>
                    <option value="missing_language">Missing Language</option>
                    <option value="duplicate_email">Duplicate Email</option>
                    <option value="duplicate_phone">Duplicate Phone</option>
                    <option value="invalid_phone">Invalid Phone Format</option>
                </select>
            </div>
            @if($guestList->settings['fields']['group'] ?? false)
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Group</label>
                <select id="groupFilter" class="form-select">
                    <option value="">All Groups</option>
                    @foreach($guestGroups as $group)
                        <option value="{{ $group->name }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex items-end">
                <button class="btn-secondary w-full" onclick="clearFilters()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Guests List -->
    <div id="guestsTableContainer">
        @if($allGuests->count() > 0)
            @if($guestList->settings['fields']['group'] ?? false)
                @php
                    // Group guests by group_id
                    $grouped = [];
                    $ungrouped = [];
                    
                    foreach($guestGroups as $group) {
                        $grouped[$group->id] = [];
                    }
                    
                    foreach($allGuests as $guest) {
                        if ($guest->group_id && isset($grouped[$guest->group_id])) {
                            $grouped[$guest->group_id][] = $guest;
                        } else {
                            $ungrouped[] = $guest;
                        }
                    }
                @endphp
                
                <!-- Render tables for each group -->
                @foreach($guestGroups as $group)
                    @php $groupGuests = $grouped[$group->id] ?? []; @endphp
                    <h2 class="text-xl font-bold mt-8 mb-2">{{ $group->name }}</h2>
                    <div class="rounded-lg shadow-sm border overflow-hidden mb-8" style="background: var(--bg-primary); border-color: var(--border-primary);">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50" style="background: var(--bg-primary); border-color: var(--border-primary);">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    @if($guestList->settings['fields']['email'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    @endif
                                    @if($guestList->settings['fields']['phone'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>
                                    @endif
                                    @if($guestList->settings['fields']['language'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Language</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="background: var(--bg-primary); color: var(--text-primary);">
                                @if(count($groupGuests) > 0)
                                    @foreach($groupGuests as $guest)
                                        @php
                                            $healthIssues = [];
                                            if (($guestList->settings['fields']['email'] ?? false) && empty($guest->email)) {
                                                $healthIssues[] = 'missing_email';
                                            }
                                            if (($guestList->settings['fields']['phone'] ?? false) && empty($guest->phone)) {
                                                $healthIssues[] = 'missing_phone';
                                            }
                                            if (($guestList->settings['fields']['group'] ?? false) && empty($guest->group_id)) {
                                                $healthIssues[] = 'missing_group';
                                            }
                                            if (($guestList->settings['fields']['language'] ?? false) && empty($guest->language)) {
                                                $healthIssues[] = 'missing_language';
                                            }
                                            $healthData = implode(',', $healthIssues);
                                        @endphp
                                        <tr class="hover:bg-gray-100 transition" data-name="{{ strtolower($guest->name) }}" data-email="{{ strtolower($guest->email ?? '') }}" data-health="{{ $healthData }}" data-group="{{ $guest->group ? $guest->group->name : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $guest->name }}</td>
                                            @if($guestList->settings['fields']['email'] ?? false)
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $guest->email ?? '' }}</td>
                                            @endif
                                            @if($guestList->settings['fields']['phone'] ?? false)
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $guest->phone ?? '' }}</td>
                                            @endif
                                            @if($guestList->settings['fields']['language'] ?? false)
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $guest->language ?? '' }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="100%" class="px-6 py-4 text-center text-gray-400">No guests in this group.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endforeach
                
                <!-- Render table for ungrouped guests -->
                @if(count($ungrouped) > 0)
                    <h2 class="text-xl font-bold mt-8 mb-2">No Group</h2>
                    <div class="rounded-lg shadow-sm border overflow-hidden mb-8" style="background: var(--bg-primary); border-color: var(--border-primary);">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50" style="background: var(--bg-primary); border-color: var(--border-primary);">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    @if($guestList->settings['fields']['email'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    @endif
                                    @if($guestList->settings['fields']['phone'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>
                                    @endif
                                    @if($guestList->settings['fields']['language'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Language</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="background: var(--bg-primary); color: var(--text-primary);">
                                @foreach($ungrouped as $guest)
                                    @php
                                        $healthIssues = [];
                                        if (($guestList->settings['fields']['email'] ?? false) && empty($guest->email)) {
                                            $healthIssues[] = 'missing_email';
                                        }
                                        if (($guestList->settings['fields']['phone'] ?? false) && empty($guest->phone)) {
                                            $healthIssues[] = 'missing_phone';
                                        }
                                        if (($guestList->settings['fields']['group'] ?? false) && empty($guest->group_id)) {
                                            $healthIssues[] = 'missing_group';
                                        }
                                        if (($guestList->settings['fields']['language'] ?? false) && empty($guest->language)) {
                                            $healthIssues[] = 'missing_language';
                                        }
                                        $healthData = implode(',', $healthIssues);
                                    @endphp
                                    <tr class="hover:bg-gray-100 transition" data-name="{{ strtolower($guest->name) }}" data-email="{{ strtolower($guest->email ?? '') }}" data-health="{{ $healthData }}" data-group="{{ $guest->group ? $guest->group->name : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $guest->name }}</td>
                                        @if($guestList->settings['fields']['email'] ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $guest->email ?? '' }}</td>
                                        @endif
                                        @if($guestList->settings['fields']['phone'] ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $guest->phone ?? '' }}</td>
                                        @endif
                                        @if($guestList->settings['fields']['language'] ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $guest->language ?? '' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <!-- Groups not enabled - display all guests in a single flat table -->
                <div class="rounded-lg shadow-sm border overflow-hidden mb-8" style="background: var(--bg-primary); border-color: var(--border-primary);">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50" style="background: var(--bg-primary); border-color: var(--border-primary);">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                @if($guestList->settings['fields']['email'] ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                @endif
                                @if($guestList->settings['fields']['phone'] ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>
                                @endif
                                @if($guestList->settings['fields']['language'] ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Language</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody style="background: var(--bg-primary); color: var(--text-primary);">
                            @foreach($allGuests as $guest)
                                @php
                                    $healthIssues = [];
                                    if (($guestList->settings['fields']['email'] ?? false) && empty($guest->email)) {
                                        $healthIssues[] = 'missing_email';
                                    }
                                    if (($guestList->settings['fields']['phone'] ?? false) && empty($guest->phone)) {
                                        $healthIssues[] = 'missing_phone';
                                    }
                                    if (($guestList->settings['fields']['group'] ?? false) && empty($guest->group_id)) {
                                        $healthIssues[] = 'missing_group';
                                    }
                                    if (($guestList->settings['fields']['language'] ?? false) && empty($guest->language)) {
                                        $healthIssues[] = 'missing_language';
                                    }
                                    $healthData = implode(',', $healthIssues);
                                @endphp
                                <tr class="hover:bg-gray-100 transition" data-name="{{ strtolower($guest->name) }}" data-email="{{ strtolower($guest->email ?? '') }}" data-health="{{ $healthData }}" data-group="{{ $guest->group ? $guest->group->name : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $guest->name }}</td>
                                    @if($guestList->settings['fields']['email'] ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $guest->email ?? '' }}</td>
                                    @endif
                                    @if($guestList->settings['fields']['phone'] ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $guest->phone ?? '' }}</td>
                                    @endif
                                    @if($guestList->settings['fields']['language'] ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $guest->language ?? '' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="text-center py-16 px-6">
                <div class="mx-auto mb-6">
                    <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No guests found</h3>
                <p class="text-gray-500 mb-6">This guest list is empty. 
                    <a href="{{ route('organizer.guest-lists.edit', $guestList) }}" class="text-blue-600 hover:text-blue-800 font-medium underline">
                        Add some guests to get started
                    </a>.
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Health Modal -->
<div id="healthModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">List Health Details</h3>
            <button type="button" class="modal-close" onclick="closeHealthModal()"></button>
        </div>
        
        <div class="modal-body">
            <div class="mb-6">
                <div class="flex items-center mb-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium badge-{{ $guestList->health['color'] ?? 'gray' }}">
                        {{ ucfirst($guestList->health['status'] ?? 'Unknown') }}
                    </span>
                </div>
                <p class="text-sm" style="color: var(--text-secondary);">{{ $guestList->health['message'] ?? 'No health information available.' }}</p>
            </div>
            
            @if($guestList->health && isset($guestList->health['issues']) && count($guestList->health['issues']) > 0)
                <div class="mb-6">
                    <h4 class="text-sm font-medium mb-3" style="color: var(--text-primary);">Issues Found:</h4>
                    <ul class="space-y-2">
                        @foreach($guestList->health['issues'] as $issue)
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span style="color: var(--text-secondary);">{{ $issue }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="text-sm" style="color: var(--text-tertiary);">
                <p class="mb-1">Total Guests: {{ $guestList->health['total_guests'] ?? 0 }}</p>
                <p>Total Issues: {{ $guestList->health['total_issues'] ?? 0 }}</p>
            </div>
        </div>
        
        <div class="modal-footer">
            @if($guestList->health && $guestList->health['status'] === 'not valid')
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeHealthModal()">Close</button>
                <a href="{{ route('organizer.guest-lists.edit', $guestList) }}" class="modal-btn modal-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit List
                </a>
            @else
                <div></div>
                <button type="button" class="modal-btn modal-btn-primary" onclick="closeHealthModal()">Close</button>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/organizerJs/guest-list-table.js') }}"></script>
<script>
// Filter functionality
function filterGuests() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const health = document.getElementById('healthFilter').value;
    const group = document.getElementById('groupFilter').value;
    
    const guestRows = document.querySelectorAll('#guestsTableContainer tbody tr');
    
    guestRows.forEach(row => {
        const name = row.dataset.name;
        const email = row.dataset.email;
        const guestHealth = row.dataset.health;
        const guestGroup = row.dataset.group;
        
        const matchesSearch = name.includes(search) || email.includes(search);
        const matchesHealth = !health || guestHealth.includes(health);
        const matchesGroup = !group || guestGroup === group;
        
        if (matchesSearch && matchesHealth && matchesGroup) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Hide empty tables
    const tables = document.querySelectorAll('#guestsTableContainer table');
    tables.forEach(table => {
        const visibleRows = table.querySelectorAll('tbody tr[style="display: table-row;"]');
        const tableWrapper = table.closest('.rounded-lg');
        const groupHeading = tableWrapper.previousElementSibling;
        
        if (visibleRows.length === 0) {
            tableWrapper.style.display = 'none';
            if (groupHeading && groupHeading.tagName === 'H2') {
                groupHeading.style.display = 'none';
            }
        } else {
            tableWrapper.style.display = 'block';
            if (groupHeading && groupHeading.tagName === 'H2') {
                groupHeading.style.display = 'block';
            }
        }
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('healthFilter').value = '';
    document.getElementById('groupFilter').value = '';
    filterGuests();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const healthFilter = document.getElementById('healthFilter');
    const groupFilter = document.getElementById('groupFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterGuests, 300));
    }
    
    if (healthFilter) {
        healthFilter.addEventListener('change', filterGuests);
    }
    
    if (groupFilter) {
        groupFilter.addEventListener('change', filterGuests);
    }
});

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Health Modal Functions
function showHealthModal() {
    const modal = document.getElementById('healthModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeHealthModal() {
    const modal = document.getElementById('healthModal');
    modal.classList.remove('show');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('healthModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeHealthModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeHealthModal();
        }
    });
});
</script>
@endpush 