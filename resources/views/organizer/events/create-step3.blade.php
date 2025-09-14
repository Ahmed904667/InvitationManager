@extends('layouts.organizer')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Page Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-[var(--primary-600)] mb-2">Message Customization</h1>
        <p class="text-lg text-[var(--text-secondary)]">Customize your invitation messages with AI Assistant</p>
    </div>



    <form action="{{ Session::has('editing_draft_event_id') ? route('organizer.events.create.auto-save') : route('organizer.events.create.step3.process') }}" method="POST" id="event-form-3">
        @csrf
        
        {{-- Preserve mode parameter --}}
        @if(request()->has('mode'))
            <input type="hidden" name="mode" value="{{ request()->get('mode') }}">
        @endif
        
        {{-- Preserve step 2 data --}}
        @if(isset($allData['guest_list_ids']) && is_array($allData['guest_list_ids']))
            @foreach($allData['guest_list_ids'] as $guestListId)
                <input type="hidden" name="guest_list_ids[]" value="{{ $guestListId }}">
            @endforeach
        @endif
        
        @if(isset($allData['invitation_platforms']) && is_array($allData['invitation_platforms']))
            @foreach($allData['invitation_platforms'] as $platform)
                <input type="hidden" name="invitation_platforms[]" value="{{ $platform }}">
            @endforeach
        @endif
        
        {{-- Removed hidden QR/RSVP fields to prevent Step 3 from overriding Step 2 toggles on Next/Back --}}
        
        {{-- Step Navigation --}}
        @include('organizer.events.partials.step-navigation', ['currentStep' => 3])

        {{-- Message Mode removed --}}

        {{-- Step 3 Content --}}
        <div class="card">
            <div class="card-header text-center">
                <h2 class="text-2xl font-semibold text-[var(--primary-600)] mb-2">
                    <i class="fas fa-robot text-[var(--primary-500)] mr-2"></i> AI Assistant & Message Customization
                </h2>
                <p class="text-[var(--text-secondary)]">Chat with our AI assistant to customize your invitation messages</p>
            </div>

            <div class="card-body">

                {{-- Event Summary --}}
                <div class="bg-gradient-to-r from-[var(--primary-500)] to-[var(--primary-700)] rounded-xl p-6 text-white mb-8">
                    <h4 class="text-lg font-semibold mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Event Summary
                    </h4>
                    <div class="bg-[var(--bg-secondary)] text-[var(--text-primary)] rounded-lg p-4 backdrop-blur-sm">
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <strong class="text-lg">{{ $allData['name'] ?? '' }}</strong>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                {{ isset($allData['start_date']) ? \Carbon\Carbon::parse($allData['start_date'], 'UTC')->setTimezone(Auth::user()->timezone ?? 'UTC')->format('l, F j, Y \a\t g:i A') : '' }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Platforms: {{ isset($allData['invitation_platforms']) ? implode(', ', array_map('ucfirst', $allData['invitation_platforms'])) : '' }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users mr-2"></i>
                                {{ count($organizedGuestData ?? []) }} Guest List(s) Selected
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Warning for Fallback Data --}}
                @if(isset($allData['using_fallback_data']) && $allData['using_fallback_data'])
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6 mb-8">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-yellow-800 mb-2">⚠️ Using Default Event Information</h3>
                            <p class="text-yellow-700 mb-4">I notice you're using default event information instead of your actual event details. The AI assistant will still work, but your messages will be more personalized with your real event information.</p>
                            <div class="flex space-x-3">
                                <a href="{{ route('organizer.events.create.step1') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit mr-2"></i> Complete Step 1
                                </a>
                                <button type="button" onclick="dismissWarning()" class="inline-flex items-center px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-sm font-medium rounded-lg transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i> Dismiss
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Guest Lists with Groups and Guests --}}
                <div class="space-y-8">
                    @if(isset($organizedGuestData) && count($organizedGuestData) > 0)
                        @foreach($organizedGuestData as $listId => $listData)
                            <div class="bg-[var(--bg-primary)] border-2 border-[var(--border-primary)] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="bg-gradient-to-r from-[var(--bg-secondary)] to-[var(--bg-primary)] px-8 py-6 border-b border-[var(--border-primary)]">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-2xl font-bold text-[var(--primary-600)] flex items-center">
                                                <i class="fas fa-list text-[var(--primary-600)] mr-3 text-xl"></i> {{ $listData['list_name'] }}
                                            </h3>
                                            <div class="flex items-center mt-2 space-x-4">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] border border-[var(--border-primary)]">
                                                    <i class="fas fa-layer-group mr-2"></i>{{ count($listData['groups']) }} groups
                                                </span>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] border border-[var(--border-primary)]">
                                                    <i class="fas fa-user-friends mr-2"></i>{{ count($listData['ungrouped_guests']) }} ungrouped
                                                </span>
                                                @php
                                                    $languages = collect();
                                                    foreach($listData['groups'] as $group) {
                                                        foreach($group['guests'] as $guest) {
                                                            if($guest->language) {
                                                                $languages->push($guest->language);
                                                            }
                                                        }
                                                    }
                                                    foreach($listData['ungrouped_guests'] as $guest) {
                                                        if($guest->language) {
                                                            $languages->push($guest->language);
                                                        }
                                                    }
                                                    $uniqueLanguages = $languages->unique()->values();
                                                @endphp
                                                @if($uniqueLanguages->count() > 0)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[var(--info-100)] text-[var(--info-700)] border border-[var(--info-200)]">
                                                        <i class="fas fa-globe mr-2"></i>{{ $uniqueLanguages->count() }} language(s)
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-8 space-y-8">
                                    {{-- Groups Section --}}
                                    @if(count($listData['groups']) > 0)
                                        <div class="space-y-6">
                                            <div class="flex items-center space-x-3 mb-6">
                                                <div class="w-1 h-8 bg-gradient-to-b from-[var(--primary-500)] to-[var(--primary-600)] rounded-full"></div>
                                                <h4 class="text-xl font-bold text-[var(--primary-600)] flex items-center">
                                                    <i class="fas fa-layer-group text-[var(--primary-600)] mr-3"></i> Groups
                                                </h4>
                                            </div>
                                            
                                            @foreach($listData['groups'] as $groupId => $groupData)
                                                <div class="bg-gradient-to-br from-[var(--bg-secondary)] to-[var(--bg-primary)] border border-[var(--border-primary)] rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                                                    <div class="bg-[var(--bg-primary)] px-6 py-4 border-b border-[var(--border-primary)]">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center">
                                                                @if($groupData['group_color'])
                                                                    <div class="w-5 h-5 rounded-full mr-4 shadow-sm" style="background-color: {{ $groupData['group_color'] }}"></div>
                                                                @endif
                                                                <div>
                                                                    <h5 class="text-lg font-bold text-[var(--primary-600)]">{{ $groupData['group_name'] }}</h5>
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] border border-[var(--border-primary)]">
                                                                        <i class="fas fa-users mr-1"></i>{{ count($groupData['guests']) }} guests
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center space-x-3">
                                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-[var(--border-secondary)] rounded-lg text-sm font-medium text-[var(--text-primary)] bg-[var(--bg-primary)] hover:bg-[var(--bg-secondary)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary-500)] transition-all duration-200 hidden" id="template_btn_{{ $listId }}_{{ $groupId }}" onclick="toggleGroupTemplate({{ $listId }}, {{ $groupId }})">
                                                                    <i class="fas fa-chevron-down mr-2" id="group_template_icon_{{ $listId }}_{{ $groupId }}"></i>Template
                                                                </button>
                                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-[var(--border-secondary)] rounded-lg text-sm font-medium text-[var(--primary-600)] bg-[var(--bg-primary)] hover:bg-[var(--bg-secondary)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary-500)] transition-all duration-200" onclick="toggleGroup({{ $listId }}, {{ $groupId }})">
                                                                    <img src="{{ asset('images/right-arrow.svg') }}" alt="Toggle" class="w-4 h-4" id="icon_{{ $listId }}_{{ $groupId }}">
                                                                </button>
                                                            </div>
                                                        </div>
                                                        @if($groupData['group_description'])
                                                            <p class="text-sm text-[var(--text-secondary)] mt-3 italic">{{ $groupData['group_description'] }}</p>
                                                        @endif
                                                    </div>

                                                    <div class="group-content" id="group_content_{{ $listId }}_{{ $groupId }}" style="display: none;">
                                                        <div class="p-6 space-y-6 bg-gradient-to-br from-[var(--bg-secondary)] to-[var(--bg-primary)]">
                                                            {{-- Group Template --}}
                                                            <div class="bg-[var(--bg-primary)] rounded-xl p-6 border border-[var(--border-primary)] shadow-sm">
                                                                <label class="block text-sm font-semibold text-[var(--text-primary)] mb-3">Group Invitation Template</label>
                                                                <div class="group-template-content" id="group_template_content_{{ $listId }}_{{ $groupId }}" style="display: none;">
                                                                <div class="bg-gradient-to-r from-[var(--bg-secondary)] to-[var(--bg-primary)] rounded-xl p-4 mb-4 border border-[var(--border-primary)]">
                                                                    <div class="text-sm font-medium text-[var(--text-primary)] mb-3 flex items-center">
                                                                        <i class="fas fa-lightbulb text-[var(--text-secondary)] mr-2"></i>Available placeholders:
                                                                    </div>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] transition-all duration-200 border border-[var(--border-primary)]" onclick="insertPlaceholder('group_template_{{ $listId }}_{{ $groupId }}', '{name}')">
                                                                            <i class="fas fa-user mr-1"></i>{name}
                                                                        </button>
                                                                        <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] transition-all duration-200 border border-[var(--border-primary)]" onclick="insertPlaceholder('group_template_{{ $listId }}_{{ $groupId }}', '{event_name}')">
                                                                            <i class="fas fa-calendar mr-1"></i>{event_name}
                                                                        </button>
                                                                        <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] transition-all duration-200 border border-[var(--border-primary)]" onclick="insertPlaceholder('group_template_{{ $listId }}_{{ $groupId }}', '{date}')">
                                                                            <i class="fas fa-clock mr-1"></i>{date}
                                                                        </button>
                                                                        <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] transition-all duration-200 border border-[var(--border-primary)]" onclick="insertPlaceholder('group_template_{{ $listId }}_{{ $groupId }}', '{group_name}')">
                                                                            <i class="fas fa-layer-group mr-1"></i>{group_name}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <textarea 
                                                                    id="group_template_{{ $listId }}_{{ $groupId }}"
                                                                    name="group_messages[{{ $listId }}][{{ $groupId }}]" 
                                                                    data-list-id="{{ $listId }}" 
                                                                    data-group-id="{{ $groupId }}"
                                                                    class="w-full min-h-[120px] px-4 py-3 bg-[var(--bg-primary)] text-[var(--text-primary)] border-2 border-[var(--border-primary)] rounded-xl focus:ring-2 focus:ring-[var(--primary-500)] focus:border-[var(--primary-500)] transition-all duration-200 resize-y"
                                                                    placeholder="Write your group invitation template here... You can use placeholders like {name}, {event_name}, {date}, {group_name}."
                                                                >{{ old('group_messages.' . $listId . '.' . $groupId, $data['group_messages'][$listId][$groupId] ?? '') }}</textarea>
                                                                
                                                                {{-- Apply to all guests in group checkbox --}}
                                                                <div class="mt-4 p-4 bg-gradient-to-r from-[var(--info-50)] to-[var(--info-100)] rounded-lg border border-[var(--info-200)] transition-all duration-300" id="apply_group_container_{{ $listId }}_{{ $groupId }}">
                                                                    <label class="flex items-center cursor-pointer">
                                                                        <input 
                                                                            type="checkbox" 
                                                                            id="apply_group_{{ $listId }}_{{ $groupId }}"
                                                                            class="w-4 h-4 text-[var(--info-600)] bg-[var(--bg-secondary)] border-[var(--border-secondary)] rounded focus:ring-[var(--info-500)] focus:ring-2 mr-3"
                                                                            onchange="toggleApplyToGroup({{ $listId }}, {{ $groupId }}, this.checked)"
                                                                        >
                                                                        <div>
                                                                            <span class="text-[var(--text-primary)] font-medium">Apply this template to all guests in this group</span>
                                                                            <p class="text-sm text-[var(--text-secondary)] mt-1">When checked, any changes to this template will automatically apply to all {{ count($groupData['guests']) }} guests in this group.</p>
                                                                        </div>
                                                                    </label>
                                                                    <div class="mt-2 text-xs text-[var(--info-600)] hidden" id="apply_group_status_{{ $listId }}_{{ $groupId }}">
                                                                        <i class="fas fa-check-circle mr-1"></i>Auto-apply enabled for this group
                                                                    </div>
                                                                </div>
                                                                </div>
                                                            </div>

                                                            {{-- Guests in this group --}}
                                                            <div class="bg-[var(--bg-primary)] rounded-xl p-6 border border-[var(--border-primary)] shadow-sm">
                                                                <h6 class="font-semibold text-[var(--text-primary)] mb-4 flex items-center">
                                                                    <i class="fas fa-users text-[var(--info-600)] mr-2"></i>Guests in this group
                                                                </h6>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    @foreach($groupData['guests'] as $guest)
                                                                        <div class="bg-gradient-to-br from-[var(--bg-secondary)] to-[var(--bg-primary)] rounded-xl p-4 border border-[var(--border-primary)] hover:shadow-md transition-all duration-300">
                                                                            <div class="flex justify-between items-start mb-3">
                                                                                <div class="flex-1">
                                                                                    <div class="flex items-center mb-2">
                                                                                        <div class="w-2 h-2 bg-[var(--success-500)] rounded-full mr-2"></div>
                                                                                        <strong class="text-[var(--text-primary)] font-semibold">{{ $guest->name }}</strong>
                                                                                    </div>
                                                                                    @if($guest->email)
                                                                                        <div class="text-sm text-[var(--text-secondary)] flex items-center">
                                                                                            <i class="fas fa-envelope text-[var(--text-tertiary)] mr-2 w-4"></i>{{ $guest->email }}
                                                                                        </div>
                                                                                    @elseif($guest->phone)
                                                                                        <div class="text-sm text-[var(--text-secondary)] flex items-center">
                                                                                            <i class="fas fa-phone text-[var(--text-tertiary)] mr-2 w-4"></i>{{ $guest->phone }}
                                                                                        </div>
                                                                                    @endif
                                                                                    @if($guest->language)
                                                                                        <div class="text-sm text-[var(--info-600)] flex items-center">
                                                                                            <i class="fas fa-globe text-[var(--info-500)] mr-2 w-4"></i>{{ strtoupper($guest->language) }}
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <button type="button" class="inline-flex items-center px-3 py-1.5 border border-[var(--border-secondary)] rounded-lg text-xs font-medium text-[var(--text-primary)] bg-[var(--bg-primary)] hover:bg-[var(--bg-secondary)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary-500)] transition-all duration-200 ml-3" onclick="toggleGuestMessage({{ $guest->id }})">
                                                                                    <i class="fas fa-chevron-down mr-1" id="guest_icon_{{ $guest->id }}"></i>Message
                                                                                </button>
                                                                            </div>
                                                                            <div class="guest-message-content" id="guest_message_content_{{ $guest->id }}" style="display: none;">
                                                                                <textarea 
                                                                                    id="guest_message_{{ $guest->id }}"
                                                                                    data-list-id="{{ $listId }}" 
                                                                                    data-group-id="{{ $groupId }}"
                                                                                    data-guest-id="{{ $guest->id }}"
                                                                                    data-guest-name="{{ $guest->name }}"
                                                                                    data-group-name="{{ $groupData['group_name'] }}"
                                                                                                                                                        data-event-name="{{ $allData['name'] ?? '' }}"
                                                                    data-event-date="{{ isset($allData['start_date']) ? \Carbon\Carbon::parse($allData['start_date'], 'UTC')->setTimezone(Auth::user()->timezone ?? 'UTC')->format('l, F j, Y \a\t g:i A') : '' }}"
                                                                    data-guest-language="{{ $guest->language ?? 'en' }}"
                                                                    name="per_guest_messages[{{ $guest->id }}]" 
                                                                    class="w-full min-h-[80px] text-sm px-3 py-2 bg-[var(--bg-primary)] text-[var(--text-primary)] border-2 border-[var(--border-primary)] rounded-lg focus:ring-2 focus:ring-[var(--primary-500)] focus:border-[var(--primary-500)] transition-all duration-200 resize-y"
                                                                    placeholder="Personal message for {{ $guest->name }}..."
                                                                    data-original-placeholder="Personal message for {{ $guest->id }}..."
                                                                >{{ old('per_guest_messages.' . $guest->id, $data['per_guest_messages'][$guest->id] ?? '') }}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                @endif

                                    {{-- Ungrouped Guests Section --}}
                                    @if(count($listData['ungrouped_guests']) > 0)
                                        <div class="space-y-6">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-1 h-8 bg-gradient-to-b from-[var(--warning-500)] to-[var(--warning-600)] rounded-full"></div>
                                                    <h4 class="text-xl font-bold text-[var(--primary-600)] flex items-center">
                                                        <i class="fas fa-user-friends text-[var(--warning-600)] mr-3"></i> Ungrouped Guests
                                                    </h4>
                                                </div>
                                                <button type="button" class="inline-flex items-center px-4 py-2 border border-[var(--border-secondary)] rounded-lg text-sm font-medium text-[var(--text-primary)] bg-[var(--bg-primary)] hover:bg-[var(--bg-secondary)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary-500)] transition-all duration-200" onclick="toggleUngrouped({{ $listId }})">
                                                    <i class="fas fa-chevron-down mr-2" id="ungrouped_icon_{{ $listId }}"></i>Toggle
                                                </button>
                                            </div>

                                            <div class="ungrouped-content" id="ungrouped_content_{{ $listId }}" style="display: none;">
                                                <div class="bg-[var(--bg-primary)] rounded-xl p-6 border border-[var(--border-primary)] shadow-sm">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        @foreach($listData['ungrouped_guests'] as $guest)
                                                            <div class="bg-gradient-to-br from-[var(--warning-50)] to-[var(--bg-primary)] rounded-xl p-4 border border-[var(--warning-200)] hover:shadow-md transition-all duration-300">
                                                                <div class="flex justify-between items-start mb-3">
                                                                    <div class="flex-1">
                                                                        <div class="flex items-center mb-2">
                                                                            <div class="w-2 h-2 bg-[var(--warning-500)] rounded-full mr-2"></div>
                                                                            <strong class="text-[var(--text-primary)] font-semibold">{{ $guest->name }}</strong>
                                                                        </div>
                                                                        @if($guest->email)
                                                                            <div class="text-sm text-[var(--text-secondary)] flex items-center">
                                                                                <i class="fas fa-envelope text-[var(--text-tertiary)] mr-2 w-4"></i>{{ $guest->email }}
                                                                            </div>
                                                                        @elseif($guest->phone)
                                                                            <div class="text-sm text-[var(--text-secondary)] flex items-center">
                                                                                <i class="fas fa-phone text-[var(--text-tertiary)] mr-2 w-4"></i>{{ $guest->phone }}
                                                                            </div>
                                                                        @endif
                                                                        @if($guest->language)
                                                                            <div class="text-sm text-[var(--info-600)] flex items-center">
                                                                                <i class="fas fa-globe text-[var(--info-500)] mr-2 w-4"></i>{{ strtoupper($guest->language) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <button type="button" class="inline-flex items-center px-3 py-1.5 border border-[var(--warning-300)] rounded-lg text-xs font-medium text-[var(--warning-700)] bg-[var(--bg-primary)] hover:bg-[var(--warning-50)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--warning-500)] transition-all duration-200 ml-3" onclick="toggleGuestMessage({{ $guest->id }})">
                                                                        <i class="fas fa-chevron-down mr-1" id="guest_icon_{{ $guest->id }}"></i>Message
                                                                    </button>
                                                                </div>
                                                                <div class="guest-message-content" id="guest_message_content_{{ $guest->id }}" style="display: none;">
                                                                    <textarea 
                                                                        id="guest_message_{{ $guest->id }}"
                                                                        name="per_guest_messages[{{ $guest->id }}]" 
                                                                        data-list-id="{{ $listId }}"
                                                                        data-guest-id="{{ $guest->id }}"
                                                                        data-guest-language="{{ $guest->language ?? 'en' }}"
                                class="w-full min-h-[100px] px-3 py-2 border-2 border-[var(--warning-200)] rounded-lg focus:ring-2 focus:ring-[var(--warning-500)] focus:border-[var(--warning-500)] transition-all duration-200 resize-y"
                                                                        placeholder="Personal message for {{ $guest->name }}..."
                                                                        data-original-placeholder="Personal message for {{ $guest->name }}..."
                                                                    >{{ old('per_guest_messages.' . $guest->id, $data['per_guest_messages'][$guest->id] ?? '') }}</textarea>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-users text-[var(--text-tertiary)] text-4xl mb-4"></i>
                            <p class="text-[var(--text-secondary)]">No guest lists selected. Please go back to step 2 to select guest lists.</p>
                            <div class="mt-4">
                                <a href="{{ route('organizer.events.create.step2') }}" class="btn-primary">
                                    <i class="fas fa-arrow-left mr-2"></i> Go Back to Step 2
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- General Message Template (Optional) --}}
                <div class="border-t-2 border-[var(--border-primary)] pt-8 mt-8">
                    <div class="bg-gradient-to-r from-[var(--bg-secondary)] to-[var(--bg-primary)] rounded-2xl p-8 border border-[var(--border-primary)]">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-1 h-8 bg-gradient-to-b from-[var(--primary-500)] to-[var(--primary-600)] rounded-full"></div>
                                <h3 class="text-2xl font-bold text-[var(--primary-600)] flex items-center">
                                    <i class="fas fa-edit text-[var(--primary-600)] mr-3"></i> General Message Template
                                </h3>
                            </div>
                            <button type="button" class="inline-flex items-center px-4 py-2 border border-[var(--border-secondary)] rounded-lg text-sm font-medium text-[var(--primary-700)] bg-[var(--bg-primary)] hover:bg-[var(--bg-secondary)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary-500)] transition-all duration-200" onclick="clearGeneralMessage()">
                                <i class="fas fa-trash mr-2"></i> Clear Template
                            </button>
                        </div>
                        <p class="text-[var(--text-secondary)] text-sm mb-6 italic">This template is for manual editing only. Use the AI assistant above to generate messages for specific guests or groups.</p>
                        
                        <div class="bg-[var(--bg-primary)] rounded-xl p-6 border border-[var(--border-primary)] shadow-sm">
                            <div class="mb-6">
                                <div class="text-sm font-medium text-[var(--text-primary)] mb-3 flex items-center">
                                    <i class="fas fa-lightbulb text-[var(--text-secondary)] mr-2"></i>Available placeholders:
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] transition-all duration-200 border border-[var(--border-primary)]" onclick="insertPlaceholder('general_message', '{name}')">
                                        <i class="fas fa-user mr-1"></i>{name}
                                    </button>
                                    <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] transition-all duration-200 border border-[var(--border-primary)]" onclick="insertPlaceholder('general_message', '{event_name}')">
                                        <i class="fas fa-calendar mr-1"></i>{event_name}
                                    </button>
                                    <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-[var(--bg-secondary)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] transition-all duration-200 border border-[var(--border-primary)]" onclick="insertPlaceholder('general_message', '{date}')">
                                        <i class="fas fa-clock mr-1"></i>{date}
                                    </button>
                                </div>
                            </div>
                            <textarea 
                                id="general_message" 
                                name="general_message" 
                                data-scope="general"
                                class="w-full min-h-[150px] px-4 py-3 bg-[var(--bg-primary)] text-[var(--text-primary)] border-2 border-[var(--border-primary)] rounded-xl focus:ring-2 focus:ring-[var(--primary-500)] focus:border-[var(--primary-500)] transition-all duration-200 resize-y"
                                placeholder="Write a general invitation message template here manually... You can use placeholders like {name} for personalization. This template will be used as a base for all guests."
                                data-original-placeholder="Write a general invitation message template here manually... You can use placeholders like {name} for personalization. This template will be used as a base for all guests."
                            >{{ old('general_message', $data['general_message'] ?? '') }}</textarea>
                            <div class="flex justify-between items-center mt-3">
                                <div class="text-sm text-[var(--primary-600)]">
                                    <i class="fas fa-info-circle mr-1"></i>This template will be used as a base for all guests
                                </div>
                                <div class="text-sm font-medium text-[var(--primary-700)] bg-[var(--primary-100)] px-3 py-1 rounded-full">
                                    <span id="general-count">0</span> characters
                                </div>
                            </div>
                            
                            {{-- Apply to all guests checkbox --}}
                            <div class="mt-4 p-4 bg-gradient-to-r from-[var(--success-50)] to-[var(--success-100)] rounded-lg border border-[var(--success-200)] transition-all duration-300" id="apply_general_container">
                                <label class="flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        id="apply_general"
                                        class="w-4 h-4 text-[var(--success-600)] bg-[var(--bg-secondary)] border-[var(--border-secondary)] rounded focus:ring-[var(--success-500)] focus:ring-2 mr-3"
                                        onchange="toggleApplyGeneral(this.checked)"
                                    >
                                                                            <div>
                                            <span class="text-[var(--text-primary)] font-medium">Apply this template to all guests</span>
                                            <p class="text-sm text-[var(--text-secondary)] mt-1">When checked, any changes to this general template will automatically apply to ALL guests directly, regardless of group settings.</p>
                                        </div>
                                </label>
                                <div class="mt-2 text-xs text-[var(--success-600)] hidden" id="apply_general_status">
                                    <i class="fas fa-check-circle mr-1"></i>Auto-apply enabled for all guests
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AI Settings --}}
                                <div class="bg-gradient-to-r from-[var(--success-50)] to-[var(--success-100)] border-2 border-[var(--success-200)] rounded-2xl p-8 mt-8">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-1 h-8 bg-gradient-to-b from-[var(--success-500)] to-[var(--success-600)] rounded-full"></div>
                        <h3 class="text-xl font-bold text-[var(--primary-600)] flex items-center">
                            <i class="fas fa-robot text-[var(--success-600)] mr-3"></i> AI Transparency Settings
                        </h3>
                    </div>
                    <p class="text-[var(--text-secondary)] text-sm mb-6">Help your guests understand how their invitation messages were created.</p>
                    
                    <label class="flex items-center cursor-pointer bg-[var(--bg-primary)] rounded-xl p-6 border border-[var(--success-200)] shadow-sm hover:shadow-md transition-all duration-300">
                        <input 
                            type="checkbox" 
                            name="ai_generated" 
                            value="1"
                            class="w-5 h-5 text-[var(--success-600)] bg-[var(--bg-secondary)] border-[var(--border-secondary)] rounded focus:ring-[var(--success-500)] focus:ring-2 mr-4"
                            {{ old('ai_generated', $data['ai_generated'] ?? false) ? 'checked' : '' }}
                        >
                        <div>
                            <span class="text-[var(--text-primary)] font-semibold">Mark messages as AI-generated for transparency</span>
                            <p class="text-sm text-[var(--text-secondary)] mt-1">This will add a small note to let guests know AI assistance was used in creating their personalized messages.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </form>
    
    {{-- Modern Chatbot Widget --}}
    <div class="fixed bottom-6 right-6 z-50">
        {{-- Chat Widget Container --}}
        <div id="chatWidget" class="hidden mb-4 bg-[var(--bg-primary)] rounded-2xl shadow-2xl border-0 w-96 h-[600px] flex flex-col overflow-hidden">
            {{-- Chat Header --}}
            <div class="bg-gradient-to-r from-[var(--primary-600)] to-[var(--primary-700)] text-white p-4 rounded-t-2xl relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-[var(--bg-primary)]/20 backdrop-blur-sm rounded-full flex items-center justify-center mr-3 overflow-hidden">
                            <img src="{{ asset('images/invaroAI.png') }}" alt="AI Assistant" class="h-full w-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-base font-semibold">InvaroAI</h3>
                            <p class="text-xs text-white/80">Usually responds in seconds</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button type="button" class="text-white/80 hover:text-white transition-colors p-2 rounded-full hover:bg-[var(--bg-primary)]/10" onclick="clearChat()" title="Clear Chat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        <button type="button" class="text-white/80 hover:text-white transition-colors p-2 rounded-full hover:bg-[var(--bg-primary)]/10" onclick="showQuickActions()" title="Quick Actions">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </button>
                        <button type="button" class="text-white/80 hover:text-white transition-colors p-2 rounded-full hover:bg-[var(--bg-primary)]/10" onclick="closeAIChat()" title="Close Chat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Chat Messages Container --}}
            <div id="chatMessages" class="flex-1 bg-gradient-to-b from-[var(--bg-secondary)] to-[var(--bg-tertiary)] p-4 overflow-y-auto space-y-4">
                @if(isset($allData['using_fallback_data']) && $allData['using_fallback_data'])
                <div class="chat-message assistant">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 max-w-xs shadow-sm">
                            <p class="text-yellow-800 text-sm leading-relaxed font-medium">⚠️ <strong>Event Information Notice</strong></p>
                            <p class="text-yellow-700 text-xs leading-relaxed mt-1">I'm using default event information because Step 1 hasn't been completed with your actual event details. For personalized messages, please go back to Step 1 and fill in your real event information.</p>
                            <div class="mt-3">
                                <a href="{{ route('organizer.events.create.step1') }}" class="block w-full text-center text-xs bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl px-3 py-2 transition-all duration-200">
                                    📝 Complete Step 1
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="chat-message assistant">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden">
                            <img src="{{ asset('images/invaroAI.png') }}" alt="AI Assistant" class="h-full w-full object-cover">
                        </div>
                        <div class="bg-[var(--bg-primary)] rounded-2xl p-4 max-w-xs shadow-sm border border-[var(--border-primary)]">
                            <p class="text-[var(--text-primary)] text-sm leading-relaxed">Hello! I'm your AI assistant. I can help you customize invitation messages for your event in multiple languages based on your guests' preferred languages. What would you like to do?</p>
                            <div class="mt-3 space-y-2">
                                <button type="button" class="block w-full text-left text-xs bg-gradient-to-r from-[var(--primary-50)] to-[var(--primary-100)] text-[var(--primary-700)] hover:from-[var(--primary-100)] hover:to-[var(--primary-200)] rounded-xl px-3 py-2 transition-all duration-200 border border-[var(--border-primary)]" onclick="sendQuickMessage('Generate formal messages for all guests in their preferred languages')">
                                    🌍 Generate formal messages for all guests
                                </button>
                                <button type="button" class="block w-full text-left text-xs bg-gradient-to-r from-[var(--primary-50)] to-[var(--primary-100)] text-[var(--primary-700)] hover:from-[var(--primary-100)] hover:to-[var(--primary-200)] rounded-xl px-3 py-2 transition-all duration-200 border border-[var(--border-primary)]" onclick="sendQuickMessage('Make the messages more friendly')">
                                    😊 Make the messages more friendly
                                </button>
                                <button type="button" class="block w-full text-left text-xs bg-gradient-to-r from-[var(--primary-50)] to-[var(--primary-100)] text-[var(--primary-700)] hover:from-[var(--primary-100)] hover:to-[var(--primary-200)] rounded-xl px-3 py-2 transition-all duration-200 border border-[var(--border-primary)]" onclick="sendQuickMessage('Show me all available placeholders')">
                                    📝 Show me all available placeholders
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Chat Input --}}
            <div class="border-t border-[var(--border-primary)] p-4 bg-[var(--bg-primary)] rounded-b-2xl">
                <div class="flex space-x-3">
                    <div class="flex-1 relative">
                        <input 
                            type="text" 
                            id="chatInput" 
                            class="w-full border-2 border-[var(--border-primary)] rounded-2xl px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-tertiary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-500)] focus:border-[var(--primary-500)] pr-12 transition-all duration-200"
                            placeholder="Type your message here..."
                            onkeypress="handleChatKeyPress(event)"
                        >
                        <button 
                            type="button" 
                            id="sendButton"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-[var(--primary-500)] to-[var(--primary-600)] hover:from-[var(--primary-600)] hover:to-[var(--primary-700)] text-white p-2.5 rounded-xl transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md"
                            onclick="sendChatMessage()"
                            title="Send Message"
                        >
                            <svg class="w-4 h-4 send-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <svg class="w-4 h-4 loading-icon hidden animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Floating AI Assistant Button --}}
        <button 
            type="button" 
            id="aiAssistantBtn"
            class="bg-gradient-to-r from-[var(--primary-500)] to-[var(--primary-600)] hover:from-[var(--primary-600)] hover:to-[var(--primary-700)] text-white rounded-full w-16 h-16 shadow-2xl hover:shadow-3xl transition-all duration-300 flex items-center justify-center group"
            onclick="toggleAIChat()"
            title="AI Assistant"
        >
            <div class="relative">
                <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-[var(--success-400)] rounded-full animate-pulse"></div>
            </div>
        </button>
    </div>
</div>

{{-- Quick Actions Modal --}}
<div id="quickActionsModal" class="modal">
    <div class="modal-content max-w-2xl">
        <div class="modal-header">
            <h3 class="modal-title">Quick Actions</h3>
            <button type="button" class="modal-close" onclick="hideQuickActionsModal()"></button>
        </div>
        
        <div class="modal-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <h4 class="font-semibold text-[var(--primary-600)]">Generate for All Guests</h4>
                    <button type="button" class="w-full text-left p-3 bg-[var(--bg-secondary)] hover:bg-[var(--bg-tertiary)] rounded-lg transition-colors" onclick="sendQuickMessage('Generate formal messages for all guests in their preferred languages')">
                        <div class="font-medium">🌍 Formal Messages</div>
                        <div class="text-sm text-[var(--text-secondary)]">Generate professional formal messages in each guest's language</div>
                    </button>
                    <button type="button" class="w-full text-left p-3 bg-[var(--bg-secondary)] hover:bg-[var(--bg-tertiary)] rounded-lg transition-colors" onclick="sendQuickMessage('Generate friendly messages for all guests in their preferred languages')">
                        <div class="font-medium">😊 Friendly Messages</div>
                        <div class="text-sm text-[var(--text-secondary)]">Generate warm casual messages in each guest's language</div>
                    </button>
                    <button type="button" class="w-full text-left p-3 bg-[var(--bg-secondary)] hover:bg-[var(--bg-tertiary)] rounded-lg transition-colors" onclick="sendQuickMessage('Generate professional messages for all guests in their preferred languages')">
                        <div class="font-medium">📋 Professional Messages</div>
                        <div class="text-sm text-[var(--text-secondary)]">Generate business-style messages in each guest's language</div>
                    </button>
        </div>
        
                <div class="space-y-3">
                    <h4 class="font-semibold text-[var(--primary-600)]">Special Instructions</h4>
                    <button type="button" class="w-full text-left p-3 bg-[var(--bg-secondary)] hover:bg-[var(--bg-tertiary)] rounded-lg transition-colors" onclick="sendQuickMessage('Write friendly messages for all guests in their preferred languages and mention they can bring their kids')">
                        <div class="font-medium">👨‍👩‍👧‍👦 Kid-Friendly</div>
                        <div class="text-sm text-[var(--text-secondary)]">Mention guests can bring their children</div>
                    </button>
                    <button type="button" class="w-full text-left p-3 bg-[var(--bg-secondary)] hover:bg-[var(--bg-tertiary)] rounded-lg transition-colors" onclick="sendQuickMessage('Write formal messages for all guests in their preferred languages and include dress code information')">
                        <div class="font-medium">👔 Dress Code</div>
                        <div class="text-sm text-[var(--text-secondary)]">Include dress code requirements</div>
            </button>
                    <button type="button" class="w-full text-left p-3 bg-[var(--bg-secondary)] hover:bg-[var(--bg-tertiary)] rounded-lg transition-colors" onclick="sendQuickMessage('Write friendly messages for all guests in their preferred languages and mention food will be provided')">
                        <div class="font-medium">🍽️ Food Included</div>
                        <div class="text-sm text-[var(--text-secondary)]">Mention that food will be provided</div>
            </button>
        </div>
    </div>
        </div>
    </div>
</div>



<script>
let chatHistory = [];
let isProcessing = false;
let disableAutoSave = false; // Flag to temporarily disable auto-save during chat updates

// Global apply settings
const applyToGroupSettings = new Set(); // keys: `listId:groupId`
let applyGeneralSetting = false;

// Update the character counter for a textarea (currently used for general message)
function updateCharacterCount(textareaId) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById('general-count');
    if (textarea && counter) {
        counter.textContent = textarea.value.length;
    }
}

function clearGeneralMessage() {
    document.getElementById('general_message').value = '';
    updateCharacterCount('general_message');
    // Persist the clear immediately to avoid stale session restoring old text on refresh
    if (typeof immediateAutoSave === 'function') {
        immediateAutoSave();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Character counting for general message
    const generalTextarea = document.getElementById('general_message');
    if (generalTextarea) {
        const counter = document.getElementById('general-count');
        generalTextarea.addEventListener('input', function() {
            counter.textContent = this.value.length;
        });
        // Initialize count
        counter.textContent = generalTextarea.value.length;
    }

    // Helper to build keys
    const groupKey = (listId, groupId) => `${listId}:${groupId}`;

    // General template changes - only apply when checkbox is checked
    const general = document.getElementById('general_message');
    if (general) {
        general.addEventListener('input', () => {
            const value = general.value;
            
            // Only apply if the general apply checkbox is checked
            if (applyGeneralSetting) {
                // Apply to all group templates
                document.querySelectorAll('textarea[id^="group_template_"]').forEach(t => {
                    const listId = t.getAttribute('data-list-id');
                    const groupId = t.getAttribute('data-group-id');
                    t.value = value;
                    
                    // Apply to all guests in this group (regardless of group checkbox)
                    document.querySelectorAll(`textarea[data-list-id="${listId}"][data-group-id="${groupId}"][data-guest-id]`).forEach(guestTA => {
                        guestTA.value = applyPlaceholders(value, guestTA);
                    });
                });
                
                // Apply to all ungrouped guests
                document.querySelectorAll('textarea[data-guest-id]:not([data-group-id])').forEach(guestTA => {
                    guestTA.value = applyPlaceholders(value, guestTA);
                });
            }
        });
    }

    // Group template changes - only apply when checkbox is checked
    document.querySelectorAll('textarea[id^="group_template_"]').forEach(groupTA => {
        const listId = groupTA.getAttribute('data-list-id');
        const groupId = groupTA.getAttribute('data-group-id');
        const key = groupKey(listId, groupId);

        groupTA.addEventListener('input', () => {
            const value = groupTA.value;
            
            // Only apply if the group apply checkbox is checked
            if (applyToGroupSettings.has(key)) {
                document.querySelectorAll(`textarea[data-list-id="${listId}"][data-group-id="${groupId}"][data-guest-id]`).forEach(guestTA => {
                    guestTA.value = applyPlaceholders(value, guestTA);
                });
            }
        });
    });

    // Guest message changes - no special handling needed
    // Users can edit individual guest messages freely
});

// Ensure pending edits are saved when leaving or refreshing the page
window.addEventListener('beforeunload', function() {
    // Try to persist changes reliably during unload
    try {
        const form = document.getElementById('event-form-3');
        if (!form) return;
        const formData = new FormData(form);
        const formDataObj = {};
        for (let [key, value] of formData.entries()) {
            if (key.includes('[') && key.includes(']')) {
                setNestedValue(formDataObj, key, value);
            } else {
                formDataObj[key] = value;
            }
        }
        // Ensure CSRF token present for JSON body
        const tokenEl = document.querySelector('meta[name="csrf-token"]');
        if (tokenEl) {
            formDataObj['_token'] = tokenEl.getAttribute('content');
        }
        const url = form.getAttribute('action') || '{{ route("organizer.events.create.auto-save") }}';
        const payload = JSON.stringify(formDataObj);
        const beaconSupported = typeof navigator.sendBeacon === 'function';
        if (beaconSupported) {
            const blob = new Blob([payload], { type: 'application/json' });
            navigator.sendBeacon(url, blob);
        } else if (typeof fetch === 'function') {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': tokenEl ? tokenEl.getAttribute('content') : ''
                },
                body: payload,
                keepalive: true
            });
        }
    } catch (e) {
        // swallow
    }
});

// Toggle group content
function toggleGroup(listId, groupId) {
    const content = document.getElementById(`group_content_${listId}_${groupId}`);
    const icon = document.getElementById(`icon_${listId}_${groupId}`);
    const templateBtn = document.getElementById(`template_btn_${listId}_${groupId}`);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.src = '{{ asset("images/bottom-arrow.svg") }}';
        templateBtn.classList.remove('hidden');
    } else {
        content.style.display = 'none';
        icon.src = '{{ asset("images/right-arrow.svg") }}';
        templateBtn.classList.add('hidden');
    }
}

// Toggle group template content
function toggleGroupTemplate(listId, groupId) {
    const content = document.getElementById(`group_template_content_${listId}_${groupId}`);
    const icon = document.getElementById(`group_template_icon_${listId}_${groupId}`);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
    }
}

// Toggle ungrouped content
function toggleUngrouped(listId) {
    const content = document.getElementById(`ungrouped_content_${listId}`);
    const icon = document.getElementById(`ungrouped_icon_${listId}`);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
    }
}

// Toggle guest message content
function toggleGuestMessage(guestId) {
    const content = document.getElementById(`guest_message_content_${guestId}`);
    const icon = document.getElementById(`guest_icon_${guestId}`);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
    }
}

// Ask assistant directly
function askAssistant(message) {
    // Show loading on relevant text boxes based on the message
    if (message.toLowerCase().includes('generate') || message.toLowerCase().includes('write') || message.toLowerCase().includes('create')) {
        // Show loading on all text boxes since we don't know which ones will be updated
        const allTextareas = document.querySelectorAll('textarea');
        allTextareas.forEach(textarea => {
            if (textarea.id) {
                showTextboxLoading(textarea.id);
            }
        });
    }
    sendChatMessage(message);
}

// Send quick message
function sendQuickMessage(message) {
    document.getElementById('chatInput').value = message;
    sendChatMessage();
    hideQuickActionsModal();
}

// Show quick actions modal
function showQuickActions() {
    document.getElementById('quickActionsModal').classList.add('show');
}

// Hide quick actions modal
function hideQuickActionsModal() {
    document.getElementById('quickActionsModal').classList.remove('show');
}

// Handle chat key press
function handleChatKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendChatMessage();
    }
}

// Send chat message
async function sendChatMessage(customMessage = null) {
    const input = document.getElementById('chatInput');
    const message = customMessage || input.value.trim();
    
    if (!message || isProcessing) return;
    
    isProcessing = true;
    const sendButton = document.getElementById('sendButton');
    const sendIcon = sendButton.querySelector('.send-icon');
    const loadingIcon = sendButton.querySelector('.loading-icon');
    
    // Show loading state
    sendButton.disabled = true;
    sendIcon.classList.add('hidden');
    loadingIcon.classList.remove('hidden');
    
    // Add user message to chat
    addChatMessage('user', message);
    input.value = '';
    
    try {
        // Capture current messages to enable precise edit operations
        const currentMessages = collectCurrentMessages();

        const response = await fetch('{{ route("organizer.events.create.chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                history: chatHistory,
                current_messages: currentMessages
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Add assistant response to chat
            addChatMessage('assistant', result.response);
            
            // Apply any actions from the assistant
            if (result.actions) {
                applyAssistantActions(result.actions);
            }
        } else {
            addChatMessage('assistant', result.response || 'Sorry, I encountered an error. Please try again.');
        }
    } catch (error) {
        console.error('Chat error:', error);
        
        // Check if it's a JSON parsing error
        if (error.name === 'SyntaxError' && error.message.includes('Unexpected token')) {
            addChatMessage('assistant', 'Sorry, I encountered a server error. Please go back to Step 2 and ensure guest lists are selected, then try again.');
        } else {
            addChatMessage('assistant', 'Sorry, I encountered an error. Please try again.');
        }
    } finally {
        // Hide loading state
        isProcessing = false;
        sendButton.disabled = false;
        sendIcon.classList.remove('hidden');
        loadingIcon.classList.add('hidden');
    }
}

// Gather current message contents from the page so the AI can apply precise edits
function collectCurrentMessages() {
    const snapshot = {
        general: '',
        group_templates: {},
        per_guest_messages: {}
    };

    const general = document.getElementById('general_message');
    if (general) snapshot.general = general.value || '';

    // Group templates
    document.querySelectorAll('textarea[id^="group_template_"]').forEach(t => {
        const listId = t.getAttribute('data-list-id');
        const groupId = t.getAttribute('data-group-id');
        if (listId && groupId) {
            snapshot.group_templates[`${listId}_${groupId}`] = t.value || '';
        }
    });

    // Per-guest messages
    document.querySelectorAll('textarea[data-guest-id]').forEach(t => {
        const guestId = t.getAttribute('data-guest-id');
        if (guestId) snapshot.per_guest_messages[guestId] = t.value || '';
    });

    return snapshot;
}

// Convert Markdown to HTML
function convertMarkdownToHtml(text) {
    if (!text) return '';
    
    return text
        // Convert **bold** to <strong>bold</strong>
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Convert \n to <br> for line breaks
        .replace(/\\n/g, '<br>')
        // Convert actual newlines to <br>
        .replace(/\n/g, '<br>');
}

// Add message to chat
function addChatMessage(sender, message) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${sender}`;
    
    const bgColor = sender === 'user' ? 'bg-[var(--primary-500)]' : 'bg-[var(--bg-primary)]';
    const textColor = sender === 'user' ? 'text-white' : 'text-[var(--text-primary)]';
    const borderColor = sender === 'user' ? '' : 'border border-[var(--border-primary)]';
    
    // Convert markdown in the message
    const formattedMessage = convertMarkdownToHtml(message);
    
    messageDiv.innerHTML = `
        <div class="flex items-start space-x-3 ${sender === 'user' ? 'justify-end' : ''}">
            ${sender === 'assistant' ? `<div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden">
                <img src="{{ asset('images/invaroAI.png') }}" alt="AI Assistant" class="h-full w-full object-cover">
            </div>` : ''}
            <div class="${bgColor} rounded-2xl p-4 max-w-xs shadow-sm ${borderColor}">
                <div class="${textColor} text-sm leading-relaxed">${formattedMessage}</div>
            </div>
            ${sender === 'user' ? `<div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden">
                @if(Auth::user()->profile_photo_url ?? false)
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[var(--primary-500)] to-[var(--primary-600)] flex items-center justify-center">
                        <span class="text-white font-medium text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                @endif
            </div>` : ''}
        </div>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Add to history
    chatHistory.push({ sender, message });
}

// Show loading on text box
function showTextboxLoading(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (textarea) {
        textarea.disabled = true;
        textarea.placeholder = 'AI is generating message...';
        textarea.classList.add('opacity-50');
    }
}

// Hide loading on text box
function hideTextboxLoading(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (textarea) {
        textarea.disabled = false;
        textarea.placeholder = textarea.getAttribute('data-original-placeholder') || 'Personal message...';
        textarea.classList.remove('opacity-50');
    }
}

// Apply assistant actions
function applyAssistantActions(actions) {
    
    
    // Temporarily disable auto-save while updating textareas
    disableAutoSave = true;
    
    actions.forEach(action => {
        
        switch (action.type) {
            case 'update_group_template':
                
                const groupTextarea = document.getElementById(`group_template_${action.listId}_${action.groupId}`);
                
                
                if (groupTextarea) {
                    // Convert markdown to plain text for textarea and handle line breaks
                    let plainContent = action.content
                        .replace(/<strong>(.*?)<\/strong>/g, '**$1**')
                        .replace(/<br>/g, '\n')
                        .replace(/<div>/g, '')
                        .replace(/<\/div>/g, '\n')
                        .replace(/\\n/g, '\n'); // Convert literal \n to actual line breaks
                    groupTextarea.value = plainContent;
                    hideTextboxLoading(`group_template_${action.listId}_${action.groupId}`);
                    
                    
                    // Show language info if available
                    if (action.language && action.language !== 'en') {
                        const languageName = getLanguageName(action.language);
                        showLanguageNotification(`Generated group message in ${languageName}`);
                    }
                } else {
                    
                }
                break;
                
            case 'update_guest_message':
                
                const guestTextarea = document.querySelector(`textarea[name="per_guest_messages[${action.guestId}]"]`);
                
                if (guestTextarea) {
                    // Convert markdown to plain text for textarea and handle line breaks
                    let plainContent = action.content
                        .replace(/<strong>(.*?)<\/strong>/g, '**$1**')
                        .replace(/<br>/g, '\n')
                        .replace(/<div>/g, '')
                        .replace(/<\/div>/g, '\n')
                        .replace(/\\n/g, '\n'); // Convert literal \n to actual line breaks
                    guestTextarea.value = plainContent;
                    hideTextboxLoading(guestTextarea.id);
                    
                    
                    // Show language info if available
                    if (action.language && action.language !== 'en') {
                        const languageName = getLanguageName(action.language);
                        showLanguageNotification(`Generated message for ${guestTextarea.getAttribute('data-guest-name')} in ${languageName}`);
                    }
                } else {
                    
                    // Try to find all textareas with per_guest_messages to debug
                    const allGuestTextareas = document.querySelectorAll('textarea[name^="per_guest_messages["]');
                    
                    allGuestTextareas.forEach((ta, index) => {
                        
                    });
                }
                break;
                
            case 'update_general_message':
                const generalTextarea = document.getElementById('general_message');
                if (generalTextarea) {
                    // Convert markdown to plain text for textarea and handle line breaks
                    let plainContent = action.content
                        .replace(/<strong>(.*?)<\/strong>/g, '**$1**')
                        .replace(/<br>/g, '\n')
                        .replace(/<div>/g, '')
                        .replace(/<\/div>/g, '\n')
                        .replace(/\\n/g, '\n'); // Convert literal \n to actual line breaks
                    generalTextarea.value = plainContent;
                    document.getElementById('general-count').textContent = plainContent.length;
                    hideTextboxLoading('general_message');
                    
                    // Show language info if available
                    if (action.language && action.language !== 'en') {
                        const languageName = getLanguageName(action.language);
                        showLanguageNotification(`Generated general message in ${languageName}`);
                    }
                }
                break;
        }
    });
    
    // Re-enable auto-save after a short delay, then trigger an immediate auto-save
    setTimeout(() => {
        disableAutoSave = false;
        if (typeof immediateAutoSave === 'function') {
            // Extra small delay to ensure textareas have final content
            setTimeout(() => {
                immediateAutoSave();
            }, 150);
        }
    }, 1000); // 1 second delay
}


// Get language name from language code
function getLanguageName(languageCode) {
    const languages = {
        'en': 'English',
        'es': 'Spanish',
        'fr': 'French',
        'de': 'German',
        'it': 'Italian',
        'pt': 'Portuguese',
        'ru': 'Russian',
        'zh': 'Chinese',
        'ja': 'Japanese',
        'ko': 'Korean',
        'ar': 'Arabic',
        'hi': 'Hindi',
        'nl': 'Dutch',
        'sv': 'Swedish',
        'no': 'Norwegian',
        'da': 'Danish',
        'fi': 'Finnish',
        'pl': 'Polish',
        'tr': 'Turkish',
        'he': 'Hebrew'
    };
    
    return languages[languageCode] || languageCode.toUpperCase();
}

// Show language notification
function showLanguageNotification(message) {
    // Create a temporary notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-[var(--info-500)] text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-globe mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Clear chat
function clearChat() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML = `
        <div class="chat-message assistant">
            <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-[var(--primary-500)] to-[var(--primary-600)] rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="bg-[var(--bg-primary)] rounded-2xl p-4 max-w-xs shadow-sm border border-[var(--border-primary)]">
            <p class="text-[var(--text-primary)] text-sm leading-relaxed">Chat cleared! How can I help you customize your messages?</p>
        </div>
            </div>
        </div>
    `;
    chatHistory = [];
}

function insertPlaceholder(textareaId, placeholder) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    
    const cursorPos = textarea.selectionStart;
    const textBefore = textarea.value.substring(0, cursorPos);
    const textAfter = textarea.value.substring(textarea.selectionEnd);
    
    textarea.value = textBefore + placeholder + textAfter;
    textarea.focus();
    textarea.setSelectionRange(cursorPos + placeholder.length, cursorPos + placeholder.length);
    
    // Update character count if applicable
    if (textareaId === 'general_message') {
        document.getElementById('general-count').textContent = textarea.value.length;
    }

    // If a group template was updated via placeholder button, propagate to its guests if apply is enabled
    if (textareaId.startsWith('group_template_')) {
        const t = textarea;
        const listId = t.getAttribute('data-list-id');
        const groupId = t.getAttribute('data-group-id');
        if (listId && groupId) {
            const key = `${listId}:${groupId}`;
            if (applyToGroupSettings.has(key)) {
                document.querySelectorAll(`textarea[data-list-id="${listId}"][data-group-id="${groupId}"][data-guest-id]`).forEach(guestTA => {
                    guestTA.value = applyPlaceholders(t.value, guestTA);
                });
            }
        }
    }
}

// Replace placeholders like {name}, {group_name}, {event_name}, {date}
function applyPlaceholders(template, guestTextarea) {
    if (!template) return '';
    const name = guestTextarea.getAttribute('data-guest-name') || '';
    const groupName = guestTextarea.getAttribute('data-group-name') || '';
    const eventName = guestTextarea.getAttribute('data-event-name') || '';
    const date = guestTextarea.getAttribute('data-event-date') || '';
    return template
        .replaceAll('{name}', name)
        .replaceAll('{group_name}', groupName)
        .replaceAll('{event_name}', eventName)
        .replaceAll('{date}', date);
}

// Toggle AI Chat Widget
function toggleAIChat() {
    const chatWidget = document.getElementById('chatWidget');
    const aiAssistantBtn = document.getElementById('aiAssistantBtn');
    
    if (chatWidget.classList.contains('hidden')) {
        chatWidget.classList.remove('hidden');
        aiAssistantBtn.classList.add('hidden');
        document.getElementById('chatInput').focus();
    } else {
        chatWidget.classList.add('hidden');
        aiAssistantBtn.classList.remove('hidden');
    }
}

// Close AI Chat Widget
function closeAIChat() {
    const chatWidget = document.getElementById('chatWidget');
    const aiAssistantBtn = document.getElementById('aiAssistantBtn');
    
    chatWidget.classList.add('hidden');
    aiAssistantBtn.classList.remove('hidden');
}

// Toggle apply to group functionality
function toggleApplyToGroup(listId, groupId, isChecked) {
    const key = `${listId}:${groupId}`;
    const statusElement = document.getElementById(`apply_group_status_${listId}_${groupId}`);
    const containerElement = document.getElementById(`apply_group_container_${listId}_${groupId}`);
    
    if (isChecked) {
        applyToGroupSettings.add(key);
        // Show status indicator
        if (statusElement) statusElement.classList.remove('hidden');
        if (containerElement) {
            containerElement.classList.remove('from-[var(--info-50)]', 'to-[var(--info-100)]', 'border-[var(--info-200)]');
            containerElement.classList.add('from-[var(--info-100)]', 'to-[var(--info-200)]', 'border-[var(--info-300)]');
        }
        
        // Apply current template to all guests in this group
        const groupTemplate = document.getElementById(`group_template_${listId}_${groupId}`);
        if (groupTemplate && groupTemplate.value) {
            document.querySelectorAll(`textarea[data-list-id="${listId}"][data-group-id="${groupId}"][data-guest-id]`).forEach(guestTA => {
                guestTA.value = applyPlaceholders(groupTemplate.value, guestTA);
            });
        }
    } else {
        applyToGroupSettings.delete(key);
        // Hide status indicator
        if (statusElement) statusElement.classList.add('hidden');
        if (containerElement) {
            containerElement.classList.remove('from-[var(--info-100)]', 'to-[var(--info-200)]', 'border-[var(--info-300)]');
            containerElement.classList.add('from-[var(--info-50)]', 'to-[var(--info-100)]', 'border-[var(--info-200)]');
        }
    }
}

// Toggle apply general functionality
function toggleApplyGeneral(isChecked) {
    applyGeneralSetting = isChecked;
    const statusElement = document.getElementById('apply_general_status');
    const containerElement = document.getElementById('apply_general_container');
    
    if (isChecked) {
        // Show status indicator
        if (statusElement) statusElement.classList.remove('hidden');
        if (containerElement) {
            containerElement.classList.remove('from-[var(--success-50)]', 'to-[var(--success-100)]', 'border-[var(--success-200)]');
            containerElement.classList.add('from-[var(--success-100)]', 'to-[var(--success-200)]', 'border-[var(--success-300)]');
        }
        
        // Apply current general template to all guests
        const generalTemplate = document.getElementById('general_message');
        if (generalTemplate && generalTemplate.value) {
            // Apply to all group templates
            document.querySelectorAll('textarea[id^="group_template_"]').forEach(t => {
                const listId = t.getAttribute('data-list-id');
                const groupId = t.getAttribute('data-group-id');
                t.value = generalTemplate.value;
                
                // Apply to all guests in this group (regardless of group checkbox)
                document.querySelectorAll(`textarea[data-list-id="${listId}"][data-group-id="${groupId}"][data-guest-id]`).forEach(guestTA => {
                    guestTA.value = applyPlaceholders(generalTemplate.value, guestTA);
                });
            });
            
            // Apply to all ungrouped guests
            document.querySelectorAll('textarea[data-guest-id]:not([data-group-id])').forEach(guestTA => {
                guestTA.value = applyPlaceholders(generalTemplate.value, guestTA);
            });
        }
    } else {
        // Hide status indicator
        if (statusElement) statusElement.classList.add('hidden');
        if (containerElement) {
            containerElement.classList.remove('from-[var(--success-100)]', 'to-[var(--success-200)]', 'border-[var(--success-300)]');
            containerElement.classList.add('from-[var(--success-50)]', 'to-[var(--success-100)]', 'border-[var(--success-200)]');
        }
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const quickActionsModal = document.getElementById('quickActionsModal');
    
    if (event.target === quickActionsModal) {
        hideQuickActionsModal();
    }
}

// Auto-save functionality
let autoSaveTimeout;
let lastSavedData = '';
let isAutoSaving = false;

// Test function to verify JavaScript is working
function testAutoSave() {

}

// Helper function to set nested values in an object based on form field names
function setNestedValue(obj, key, value) {
    // Parse field names like group_messages[1][2] or per_guest_messages[123]
    const matches = key.match(/^([^\[]+)(\[.+\])$/);
    if (!matches) {
        obj[key] = value;
        return;
    }
    
    const baseKey = matches[1];
    const indices = matches[2].match(/\[([^\]]*)\]/g);
    
    if (!obj[baseKey]) {
        obj[baseKey] = {};
    }
    
    let current = obj[baseKey];
    for (let i = 0; i < indices.length - 1; i++) {
        const index = indices[i].slice(1, -1); // Remove [ and ]
        if (!current[index]) {
            current[index] = {};
        }
        current = current[index];
    }
    
    const lastIndex = indices[indices.length - 1].slice(1, -1);
    current[lastIndex] = value;
}

function autoSave() {
    if (isAutoSaving || disableAutoSave) return;
    
    const formData = new FormData(document.getElementById('event-form-3'));
    
    // Convert FormData to proper object with nested arrays
    const formDataObj = {};
    for (let [key, value] of formData.entries()) {
        if (key.includes('[') && key.includes(']')) {
            // Handle complex field names like group_messages[1][2] or per_guest_messages[123]
            setNestedValue(formDataObj, key, value);
        } else {
            // Handle simple fields
            formDataObj[key] = value;
        }
    }
    
    const currentData = JSON.stringify(formDataObj);
    

    
    if (currentData === lastSavedData) return;
    
    isAutoSaving = true;
    lastSavedData = currentData;
    
    fetch('{{ route("organizer.events.create.auto-save") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formDataObj)
    })
    .then(response => response.json())
    .then(data => {
        // Silent auto-save
    })
    .catch(error => {
        // Silent error handling
    })
    .finally(() => {
        isAutoSaving = false;
    });
}



// Immediate save for important fields
function immediateAutoSave() {

    clearTimeout(autoSaveTimeout);
    autoSave();
}

function debounceAutoSave() {

    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(autoSave, 500); // Save after 0.5 seconds of inactivity for faster response
}

// Initialize auto-save on page load
document.addEventListener('DOMContentLoaded', function() {
    const autoSaveInputs = document.querySelectorAll('input, textarea, select');
    
    autoSaveInputs.forEach(input => {
        
        // Use immediate save for important fields
        if (input.name === 'general_message' || 
            input.name === 'ai_generated' || 
            input.name.startsWith('group_messages[') || 
            input.name.startsWith('per_guest_messages[')) {
            input.addEventListener('input', immediateAutoSave);
            input.addEventListener('change', immediateAutoSave);
        } else {
            input.addEventListener('input', debounceAutoSave);
            input.addEventListener('change', debounceAutoSave);
        }
    });
    
    // Handle form submission via AJAX to prevent page refresh
    document.getElementById('event-form-3').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        
        clearTimeout(autoSaveTimeout);
        
        // Submit form data via AJAX
        const formData = new FormData(this);
        const currentData = JSON.stringify(Object.fromEntries(formData));
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Form-Submission': 'true', // Mark this as a form submission
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            }
        })
        .catch(error => {
            // Silent error handling
        });
    });
});

// Dismiss warning function
function dismissWarning() {
    const warningElement = document.querySelector('.bg-yellow-50');
    if (warningElement) {
        warningElement.style.display = 'none';
    }
}
</script>
@endsection








