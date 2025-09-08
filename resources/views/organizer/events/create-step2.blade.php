@extends('layouts.organizer')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Page Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-primary mb-2">Event Settings</h1>
        <p class="text-lg text-secondary">Configure how your event will work and who will receive invitations</p>
    </div>

    <form id="event-form-2" action="{{ route('organizer.events.create.step2.process') }}" method="POST">
        @csrf
        
        {{-- Preserve mode parameter --}}
        @if(request()->has('mode'))
            <input type="hidden" name="mode" value="{{ request()->get('mode') }}">
        @endif
        
        {{-- Preserve step 1 data --}}
        @if(isset($step1Data['name']))
            <input type="hidden" name="name" value="{{ $step1Data['name'] }}">
        @endif
        
        @if(isset($step1Data['description']))
            <input type="hidden" name="description" value="{{ $step1Data['description'] }}">
        @endif
        
        @if(isset($step1Data['start_date']))
            <input type="hidden" name="start_date" value="{{ $step1Data['start_date'] }}">
        @endif
        
        @if(isset($step1Data['end_date']))
            <input type="hidden" name="end_date" value="{{ $step1Data['end_date'] }}">
        @endif
        
        @if(isset($step1Data['additional_information']))
            <input type="hidden" name="additional_information" value="{{ $step1Data['additional_information'] }}">
        @endif
        
        @if(isset($step1Data['location']))
            <input type="hidden" name="location" value="{{ $step1Data['location'] }}">
        @endif
        
        @if(isset($step1Data['venue_name']))
            <input type="hidden" name="venue_name" value="{{ $step1Data['venue_name'] }}">
        @endif
        
        @if(isset($step1Data['venue_address']))
            <input type="hidden" name="venue_address" value="{{ $step1Data['venue_address'] }}">
        @endif
        
        @if(isset($step1Data['latitude']))
            <input type="hidden" name="latitude" value="{{ $step1Data['latitude'] }}">
        @endif
        
        @if(isset($step1Data['longitude']))
            <input type="hidden" name="longitude" value="{{ $step1Data['longitude'] }}">
        @endif
        
        {{-- Step Navigation --}}
        @include('organizer.events.partials.step-navigation', ['currentStep' => 2])

        {{-- Step 2 Content --}}
        <div class="card">
            <div class="card-header text-center">
                <h2 class="text-2xl font-semibold text-primary mb-2">
                    <i class="fas fa-cogs text-primary-500 mr-2"></i> Event Settings
                </h2>
                <p class="text-secondary">Set up event features and select your guest lists</p>
            </div>

            <div class="card-body space-y-8">
                {{-- Event Preview --}}
                @if(!empty($step1Data))
                <div class="bg-tertiary-50 border-2 border-dashed border-gray-300 rounded-xl p-6 mb-8">
                    <h4 class="text-lg font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-eye text-primary-500 mr-2"></i> Event Preview
                    </h4>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-xl font-semibold text-primary mb-2">{{ $step1Data['name'] }}</h5>
                            <p class="text-info-500 font-medium mb-2 flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                {{ \Carbon\Carbon::parse($step1Data['start_date'])->format('l, F j, Y \a\t g:i A') }}
                            </p>
                            @if(!empty($step1Data['description']))
                                <p class="text-secondary">{{ $step1Data['description'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Event Features --}}
                @php
                    $qrOn = isset($data['qr_checkin_enabled'])
                        ? (bool)$data['qr_checkin_enabled']
                        : (isset($mergedData['qr_checkin_enabled'])
                            ? (bool)$mergedData['qr_checkin_enabled']
                            : (bool)($allStepsData['qr_checkin_enabled'] ?? false));
                    $rsvpOn = isset($data['rsvp_enabled'])
                        ? (bool)$data['rsvp_enabled']
                        : (isset($mergedData['rsvp_enabled'])
                            ? (bool)$mergedData['rsvp_enabled']
                            : (bool)($allStepsData['rsvp_enabled'] ?? false));
                @endphp
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-tools text-primary-500 mr-2"></i> Event Features
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-6 bg-tertiary-50 rounded-xl border-2 border-primary hover:border-gray-300 transition-all duration-300">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-primary mb-2">QR Code Check-In</h4>
                                <p class="text-secondary">Generate unique QR codes for each guest to streamline check-in at your event venue</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="qr_checkin_enabled" 
                                    value="1"
                                    class="sr-only toggle-input"
                                    {{ $qrOn ? 'checked' : '' }}
                                >
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="flex justify-between items-center p-6 bg-tertiary-50 rounded-xl border-2 border-primary hover:border-gray-300 transition-all duration-300">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-primary mb-2">RSVP Required</h4>
                                <p class="text-secondary">Guests will receive a response form to confirm their attendance</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="rsvp_enabled" 
                                    value="1"
                                    class="sr-only toggle-input"
                                    {{ $rsvpOn ? 'checked' : '' }}
                                >
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Invitation Platforms --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-paper-plane text-primary-500 mr-2"></i> Invitation Platforms
                    </h3>
                    <p class="text-secondary mb-6">Select how you want to send invitations to your guests</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <input 
                                type="checkbox" 
                                id="platform_email" 
                                name="invitation_platforms[]" 
                                value="email"
                                class="sr-only platform-input"
                                {{ (in_array('email', old('invitation_platforms', $mergedData['invitation_platforms'] ?? $data['invitation_platforms'] ?? $allStepsData['invitation_platforms'] ?? []))) ? 'checked' : '' }}
                            >
                            <label for="platform_email" class="platform-label block p-6 border-2 border-gray-200 rounded-xl bg-white cursor-pointer transition-all duration-300 hover:border-gray-300">
                                <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fas fa-envelope text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-primary mb-2">Email</h4>
                                    <p class="text-secondary mb-4">Professional and detailed invitations with full formatting support</p>
                                    <ul class="space-y-1">
                                        <li class="text-sm text-secondary flex items-center">
                                            <span class="text-success-500 mr-2">✓</span> Rich text formatting
                                        </li>
                                        <li class="text-sm text-secondary flex items-center">
                                            <span class="text-success-500 mr-2">✓</span> Attachments support
                                        </li>
                                        <li class="text-sm text-secondary flex items-center">
                                            <span class="text-success-500 mr-2">✓</span> Professional appearance
                                        </li>
                                    </ul>
                                </div>
                            </label>
                        </div>

                        <div class="relative">
                            <input 
                                type="checkbox" 
                                id="platform_whatsapp" 
                                name="invitation_platforms[]" 
                                value="whatsapp"
                                class="sr-only platform-input"
                                {{ (in_array('whatsapp', old('invitation_platforms', $mergedData['invitation_platforms'] ?? $data['invitation_platforms'] ?? $allStepsData['invitation_platforms'] ?? []))) ? 'checked' : '' }}
                            >
                            <label for="platform_whatsapp" class="platform-label block p-6 border-2 border-gray-200 rounded-xl bg-white cursor-pointer transition-all duration-300 hover:border-gray-300">
                                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fab fa-whatsapp text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-primary mb-2">WhatsApp</h4>
                                    <p class="text-secondary mb-4">Direct and personal messaging for instant communication</p>
                                    <ul class="space-y-1">
                                        <li class="text-sm text-secondary flex items-center">
                                            <span class="text-success-500 mr-2">✓</span> Instant delivery
                                        </li>
                                        <li class="text-sm text-secondary flex items-center">
                                            <span class="text-success-500 mr-2">✓</span> High read rates
                                        </li>
                                        <li class="text-sm text-secondary flex items-center">
                                            <span class="text-success-500 mr-2">✓</span> Personal touch
                                        </li>
                                    </ul>
                                </div>
                            </label>
                        </div>
                    </div>

                    @error('invitation_platforms')
                        <div class="text-danger-500 text-sm mt-2 p-2 bg-danger-50 rounded border-l-4 border-danger-500">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Guest Lists Selection --}}
                <div>
                    <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-users text-primary-500 mr-2"></i> Guest Lists
                    </h3>
                    <p class="text-secondary mb-6">Select which guest lists to invite to this event. Only lists with excellent health status are shown.</p>
                    
                    @if($guestLists->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {{-- Create New List Card --}}
                            <div class="relative">
                                <button 
                                    type="button"
                                    onclick="openCreateListModal()"
                                    class="w-full p-6 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-300 group"
                                    style="border-color: var(--border-primary); background: var(--bg-primary);"
                                    onmouseover="this.style.borderColor='var(--primary-500)'; this.style.background='var(--primary-50)'"
                                    onmouseout="this.style.borderColor='var(--border-primary)'; this.style.background='var(--bg-primary)'"
                                >
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-200 transition-colors">
                                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <h4 class="text-lg font-semibold text-primary mb-2">Create New List</h4>
                                        <p class="text-secondary text-sm">Create a new guest list for this event</p>
                                    </div>
                                </button>
                            </div>
                            @foreach($guestLists as $guestList)
                                <div class="relative">
                                    <input 
                                        type="checkbox" 
                                        id="guest_list_{{ $guestList->id }}" 
                                        name="guest_list_ids[]" 
                                        value="{{ $guestList->id }}"
                                        class="sr-only guest-list-input"
                                        {{ (in_array($guestList->id, old('guest_list_ids', $mergedData['guest_list_ids'] ?? $data['guest_list_ids'] ?? $allStepsData['guest_list_ids'] ?? []))) ? 'checked' : '' }}
                                    >
                                    <label for="guest_list_{{ $guestList->id }}" class="guest-list-label block p-6 border-2 rounded-xl cursor-pointer transition-all duration-300 {{ (in_array($guestList->id, old('guest_list_ids', $mergedData['guest_list_ids'] ?? $data['guest_list_ids'] ?? $allStepsData['guest_list_ids'] ?? []))) ? 'border-primary-500 bg-primary-50' : '' }}" style="{{ !in_array($guestList->id, old('guest_list_ids', $mergedData['guest_list_ids'] ?? $data['guest_list_ids'] ?? $allStepsData['guest_list_ids'] ?? [])) ? 'border-color: var(--border-primary); background: var(--bg-primary);' : '' }}">
                                        <div class="flex justify-between items-start mb-3">
                                            <h4 class="text-lg font-semibold text-primary">{{ $guestList->name }}</h4>
                                            <span class="bg-primary-500 text-white px-3 py-1 rounded-full text-xs font-semibold">{{ $guestList->guests->count() }} guests</span>
                                        </div>
                                        @if($guestList->description)
                                            <p class="text-secondary text-sm mb-4">{{ $guestList->description }}</p>
                                        @endif
                                        <div class="flex gap-4 text-xs text-gray-500">
                                            <div class="flex items-center">
                                                <i class="fas fa-layer-group mr-1"></i>
                                                <span>{{ $guestList->guestGroups->count() }} groups</span>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-clock mr-1"></i>
                                                <span>{{ $guestList->updated_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-users-slash text-6xl text-gray-300 mb-4"></i>
                            <h4 class="text-xl font-semibold text-primary mb-2">No Valid Guest Lists Found</h4>
                            <p class="text-secondary mb-6">You need guest lists with excellent health status to create an event. Create a new list or fix existing ones.</p>
                            <button onclick="openCreateListModal()" class="btn-primary">
                                <i class="fas fa-plus mr-2"></i> Create Guest List
                            </button>
                        </div>
                    @endif

                    @error('guest_list_ids')
                        <div class="text-danger-500 text-sm mt-2 p-2 bg-danger-50 rounded border-l-4 border-danger-500">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* Toggle Button Styles */
.toggle-slider {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    background-color: var(--border-primary);
    border-radius: 12px;
    transition: background-color 0.3s ease;
    cursor: pointer;
}

.toggle-slider::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggle-input:checked + .toggle-slider {
    background-color: var(--primary-500);
}

.toggle-input:checked + .toggle-slider::after {
    transform: translateX(20px);
}

/* Platform Selection Styles */
.platform-input:checked + .platform-label {
    border-color: var(--primary-500) !important;
    background-color: var(--primary-50) !important;
}

.platform-input:not(:checked) + .platform-label {
    border-color: var(--border-primary) !important;
    background-color: var(--bg-primary) !important;
}

/* Guest List Selection Styles */
.guest-list-input:checked + .guest-list-label {
    border-color: var(--primary-500) !important;
    background-color: var(--primary-50) !important;
}

.guest-list-input:not(:checked) + .guest-list-label {
    border-color: var(--border-primary) !important;
    background-color: var(--bg-primary) !important;
}
</style>

<script>
// Function to open create list modal in new window
function openCreateListModal() {
    const guestListsUrl = '{{ route("organizer.guest-lists.index") }}?create_modal=true';
    const newWindow = window.open(guestListsUrl, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    
    // Focus the new window
    if (newWindow) {
        newWindow.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const platformInputs = document.querySelectorAll('input[name="invitation_platforms[]"]');
    const guestListInputs = document.querySelectorAll('input[name="guest_list_ids[]"]');
    
    function validateForm() {
        const submitBtn = document.querySelector('button[type="submit"]');
        const platformSelected = Array.from(platformInputs).some(input => input.checked);
        const guestListSelected = Array.from(guestListInputs).some(input => input.checked);
        
        if (submitBtn) {
            submitBtn.disabled = !platformSelected || !guestListSelected;
        }
    }
    
    platformInputs.forEach(input => {
        const label = input.closest('.relative').querySelector('.platform-label');
        
        function updatePlatformStyle() {
            if (input.checked) {
                label.style.borderColor = 'var(--primary-500)';
                label.style.backgroundColor = 'var(--primary-50)';
            } else {
                label.style.borderColor = 'var(--border-primary)';
                label.style.backgroundColor = 'var(--bg-primary)';
            }
        }
        
        updatePlatformStyle();
        
        input.addEventListener('change', function() {
            updatePlatformStyle();
            validateForm();
        });
    });
    
    guestListInputs.forEach(input => {
        const label = input.closest('.relative').querySelector('.guest-list-label');
        
        function updateLabelStyle() {
            if (input.checked) {
                label.style.borderColor = 'var(--primary-500)';
                label.style.backgroundColor = 'var(--primary-50)';
            } else {
                label.style.borderColor = 'var(--border-primary)';
                label.style.backgroundColor = 'var(--bg-primary)';
            }
        }
        
        // Initial state
        updateLabelStyle();
        
        // Listen for changes
        input.addEventListener('change', function() {
            updateLabelStyle();
            validateForm();
        });
    });
    
    // Initial validation
    validateForm();
});

    let autoSaveTimeout;
    let lastSavedData = '';
    let isAutoSaving = false;



function autoSave() {
    if (isAutoSaving) return;
    
    const formData = new FormData(document.getElementById('event-form-2'));
    const currentData = JSON.stringify(Object.fromEntries(formData));
    

    
    // Only save if data has changed
    if (currentData === lastSavedData) return;
    
    isAutoSaving = true;
    lastSavedData = currentData;
    

    
    // Convert FormData to proper object with arrays and explicit booleans for toggles
    const formDataObj = {};
    for (let [key, value] of formData.entries()) {
        if (key.endsWith('[]')) {
            const arrayKey = key.slice(0, -2);
            if (!formDataObj[arrayKey]) formDataObj[arrayKey] = [];
            formDataObj[arrayKey].push(value);
        } else {
            formDataObj[key] = value;
        }
    }
    // Ensure toggle fields are explicitly included as 1/0 regardless of checked state
    const qrToggle = document.querySelector('input[name="qr_checkin_enabled"]');
    const rsvpToggle = document.querySelector('input[name="rsvp_enabled"]');
    if (qrToggle) formDataObj['qr_checkin_enabled'] = qrToggle.checked ? '1' : '0';
    if (rsvpToggle) formDataObj['rsvp_enabled'] = rsvpToggle.checked ? '1' : '0';
    

    
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



function immediateAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSave();
}

function debounceAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(autoSave, 500);
}

document.addEventListener('DOMContentLoaded', function() {
    const autoSaveInputs = document.querySelectorAll('input, textarea, select');
    
    autoSaveInputs.forEach(input => {
        if (input.name === 'qr_checkin_enabled' || input.name === 'rsvp_enabled' || input.name === 'guest_list_ids[]' || input.name === 'invitation_platforms[]') {
            input.addEventListener('input', immediateAutoSave);
            input.addEventListener('change', immediateAutoSave);
        } else {
            input.addEventListener('input', debounceAutoSave);
            input.addEventListener('change', debounceAutoSave);
        }
    });
    
    document.getElementById('event-form-2').addEventListener('submit', function(e) {
        e.preventDefault();
        clearTimeout(autoSaveTimeout);
        
        console.log('Form submission started...');
        
        // Submit form data via AJAX
        const formData = new FormData(this);
        
        // Convert FormData to proper object with arrays and explicit booleans for toggles
        const formDataObj = {};
        for (let [key, value] of formData.entries()) {
            if (key.endsWith('[]')) {
                const arrayKey = key.slice(0, -2);
                if (!formDataObj[arrayKey]) formDataObj[arrayKey] = [];
                formDataObj[arrayKey].push(value);
            } else {
                formDataObj[key] = value;
            }
        }
        const qrToggle = document.querySelector('input[name="qr_checkin_enabled"]');
        const rsvpToggle = document.querySelector('input[name="rsvp_enabled"]');
        if (qrToggle) formDataObj['qr_checkin_enabled'] = qrToggle.checked ? '1' : '0';
        if (rsvpToggle) formDataObj['rsvp_enabled'] = rsvpToggle.checked ? '1' : '0';
        
        console.log('Form data to submit:', formDataObj);
        
        fetch('{{ route("organizer.events.create.step2.process") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Form-Submission': 'true',
            },
            body: JSON.stringify(formDataObj)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success && data.redirect) {
                console.log('Redirecting to:', data.redirect);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else if (data.errors) {
                // Handle validation errors
                console.error('Validation errors:', data.errors);
                alert('Please fix the errors and try again.');
            } else {
                console.error('Unexpected response:', data);
                alert('An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            alert('An error occurred while submitting the form. Please try again.');
        });
    });
});
</script>
@endsection