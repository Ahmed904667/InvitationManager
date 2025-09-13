@extends('layouts.organizer')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Page Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-primary mb-2">
            @if(request()->has('mode') && request()->get('mode') === 'update')
                Update Event
            @else
                Create New Event
            @endif
        </h1>
        <p class="text-lg text-secondary">
            @if(request()->has('mode') && request()->get('mode') === 'update')
                Let's update the basic details of your event
            @else
                Let's start with the basic details of your event
            @endif
        </p>
    </div>



    <form id="event-form-1" action="{{ route('organizer.events.create.step1.process') }}" method="POST">
        @csrf
        
        {{-- Preserve mode parameter --}}
        @if(request()->has('mode'))
            <input type="hidden" name="mode" value="{{ request()->get('mode') }}">
        @endif
        
        {{-- Step Navigation --}}
        @include('organizer.events.partials.step-navigation', ['currentStep' => 1])

        {{-- Step 1 Content --}}
        <div class="card">
            <div class="card-header text-center">
                <h2 class="text-2xl font-semibold text-primary mb-2">
                    <i class="fas fa-calendar-alt text-primary-500 mr-2"></i> 
                    @if(request()->has('mode') && request()->get('mode') === 'update')
                        Update Event Details
                    @else
                        Event Details
                    @endif
                </h2>
                <p class="text-secondary">
                    @if(request()->has('mode') && request()->get('mode') === 'update')
                        Update the essential information about your event
                    @else
                        Provide the essential information about your event
                    @endif
                </p>
            </div>

            <div class="card-body space-y-8">
                {{-- Event Information --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-xl font-semibold text-primary mb-4">Event Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="form-label">
                                Event Title <span class="text-danger-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-input @error('name') border-danger-500 @enderror"
                                value="{{ old('name', $mergedData['name'] ?? $data['name'] ?? $allStepsData['name'] ?? '') }}"
                                placeholder="e.g., Annual Company Gala, Wedding Reception, Birthday Party"
                                required
                            >
                            @error('name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="form-label">Event Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                class="form-input @error('description') border-danger-500 @enderror"
                                rows="3"
                                placeholder="Describe your event in a few sentences..."
                            >{{ old('description', $mergedData['description'] ?? $data['description'] ?? $allStepsData['description'] ?? '') }}</textarea>
                            @error('description')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <small class="text-secondary text-sm">This will help generate better AI-powered invitations later.</small>
                        </div>
                    </div>
                </div>

                {{-- Location --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-xl font-semibold text-primary mb-4">Location</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="venue_name" class="form-label">Venue Name</label>
                            <input
                                type="text"
                                id="venue_name"
                                name="venue_name"
                                class="form-input @error('venue_name') border-danger-500 @enderror"
                                value="{{ old('venue_name', $mergedData['venue_name'] ?? $data['venue_name'] ?? $allStepsData['venue_name'] ?? '') }}"
                                placeholder="e.g., Grand Hall, City Park"
                            >
                            @error('venue_name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="location" class="form-label">General Location</label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                class="form-input @error('location') border-danger-500 @enderror"
                                value="{{ old('location', $mergedData['location'] ?? $data['location'] ?? $allStepsData['location'] ?? '') }}"
                                placeholder="City / Area (optional)"
                            >
                            @error('location')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="venue_address" class="form-label">Address</label>
                        <input
                            type="text"
                            id="venue_address"
                            name="venue_address"
                            class="form-input @error('venue_address') border-danger-500 @enderror"
                                                            value="{{ old('venue_address', $mergedData['venue_address'] ?? $data['venue_address'] ?? $allStepsData['venue_address'] ?? '') }}"
                            placeholder="Street, City, Country"
                        >
                        @error('venue_address')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <small class="text-secondary text-sm">You can also select a point on the map.</small>
                    </div>

                    <div class="mt-4">
                        <div id="map" class="w-full rounded-lg border border-gray-200" style="height: 320px;"></div>
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $mergedData['latitude'] ?? $data['latitude'] ?? $allStepsData['latitude'] ?? '') }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $mergedData['longitude'] ?? $data['longitude'] ?? $allStepsData['longitude'] ?? '') }}">
                        @error('latitude')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        @error('longitude')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Date & Time --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-xl font-semibold text-primary mb-4">Date & Time</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="form-label">
                                Start Date & Time <span class="text-danger-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="start_date" 
                                name="start_date" 
                                class="form-input @error('start_date') border-danger-500 @enderror"
                                value="{{ old('start_date', $mergedData['start_date'] ?? $data['start_date'] ?? $allStepsData['start_date'] ?? '') }}"
                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                required
                            >
                            @error('start_date')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="end_date" class="form-label">End Date & Time</label>
                            <input 
                                type="datetime-local" 
                                id="end_date" 
                                name="end_date" 
                                class="form-input @error('end_date') border-danger-500 @enderror"
                                value="{{ old('end_date', $mergedData['end_date'] ?? $data['end_date'] ?? $allStepsData['end_date'] ?? '') }}"
                            >
                            @error('end_date')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <small class="text-secondary text-sm">Optional - helps guests plan their schedule</small>
                        </div>
                    </div>
                </div>

                {{-- Additional Information --}}
                <div>
                    <h3 class="text-xl font-semibold text-primary mb-4">Additional Information</h3>
                    
                    <div>
                        <label for="additional_information" class="form-label">
                            Notes & Special Instructions
                        </label>
                        <textarea 
                            id="additional_information" 
                            name="additional_information" 
                            class="form-input @error('additional_information') border-danger-500 @enderror"
                            rows="4"
                            placeholder="Any special instructions, dress code, what to bring, parking information, etc."
                        >{{ old('additional_information', $mergedData['additional_information'] ?? $data['additional_information'] ?? $allStepsData['additional_information'] ?? '') }}</textarea>
                        @error('additional_information')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <small class="text-secondary text-sm">This information will be included in your invitations.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex justify-between items-center mt-8">
            <div class="text-sm text-secondary">
                <i class="fas fa-info-circle mr-1"></i>
                All fields marked with <span class="text-danger-500">*</span> are required
            </div>
            <div class="flex space-x-4">
                <button type="button" onclick="window.history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-arrow-right mr-2"></i> Next Step
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Time Validation Modal --}}
<x-confirmation-modal 
    id="timeValidationModal"
    title="Invalid End Time"
    message="The end time you selected is too early. End time must be at least {{ config('app.min_event_duration', 15) }} minutes after the start time."
    warning="true"
    confirmText="OK"
    cancelText="Cancel"
    :showWarningBox="false"
/>

<script>
// Global confirmation modal state
window.confirmationModalState = {
    currentModalId: null,
    onConfirm: null,
    onCancel: null
};

function showConfirmationModal(modalId, onConfirm, onCancel = null) {
    window.confirmationModalState.currentModalId = modalId;
    window.confirmationModalState.onConfirm = onConfirm;
    window.confirmationModalState.onCancel = onCancel;
    
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('show');
    }
}

function hideConfirmationModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.classList.add('hidden');
    }
    
    // Reset state
    window.confirmationModalState.currentModalId = null;
    window.confirmationModalState.onConfirm = null;
    window.confirmationModalState.onCancel = null;
}

function executeConfirmedAction(modalId) {
    if (window.confirmationModalState.onConfirm && window.confirmationModalState.currentModalId === modalId) {
        window.confirmationModalState.onConfirm();
    }
    hideConfirmationModal(modalId);
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Set minimum date to now
    const now = new Date();
    const minDateTime = now.toISOString().slice(0, 16);
    startDateInput.min = minDateTime;
    
    // When start date changes, enable end date and set minimum time
    startDateInput.addEventListener('change', function() {
        if (this.value) {
            // Enable end date input
            endDateInput.disabled = false;
            
            // Calculate minimum end time (15 minutes after start time - configurable)
            const minDurationMinutes = {{ config('app.min_event_duration', 15) }};
            const startDate = new Date(this.value);
            const minEndDate = new Date(startDate.getTime() + (minDurationMinutes * 60 * 1000)); // minimum duration later
            const minEndDateTime = minEndDate.toISOString().slice(0, 16);
            
            endDateInput.min = minEndDateTime;
            
            // Clear end date if it's before the new minimum
            if (endDateInput.value && endDateInput.value < minEndDateTime) {
                endDateInput.value = '';
            }
        } else {
            // Disable end date if no start date
            endDateInput.disabled = true;
            endDateInput.value = '';
        }
    });
    
    // Validate end date when it changes
    endDateInput.addEventListener('change', function() {
        if (this.value && startDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(this.value);
            const minDurationMinutes = {{ config('app.min_event_duration', 15) }};
            const minEndDate = new Date(startDate.getTime() + (minDurationMinutes * 60 * 1000)); // minimum duration later
            
            if (endDate < minEndDate) {
                // Show confirmation modal instead of alert
                const onConfirm = () => {
                    endDateInput.value = '';
                    endDateInput.placeholder = 'Select end date & time';
                };
                
                const onCancel = () => {
                    endDateInput.value = '';
                    endDateInput.placeholder = 'Select end date & time';
                };
                
                // Reset the field immediately when showing modal
                endDateInput.value = '';
                endDateInput.placeholder = 'Select end date & time';
                
                showConfirmationModal('timeValidationModal', onConfirm, onCancel);
                return;
            }
        }
    });
    
    // Initialize end date state
    if (!startDateInput.value) {
        endDateInput.disabled = true;
    }
});
</script>

{{-- Google Maps for location selection --}}
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&language=en&region=MY&callback=initMap"></script>
<script>
function initMap() {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('venue_address');
    const venueNameInput = document.getElementById('venue_name');

    // Default to Kuala Lumpur if we don't have a user location
    const defaultLat = 3.139003;   // Kuala Lumpur
    const defaultLng = 101.686855;
    const initialLat = parseFloat(latInput.value);
    const initialLng = parseFloat(lngInput.value);
    const hasInitial = Number.isFinite(initialLat) && Number.isFinite(initialLng);

    const map = new google.maps.Map(document.getElementById('map'), {
        center: hasInitial ? { lat: initialLat, lng: initialLng } : { lat: defaultLat, lng: defaultLng },
        zoom: hasInitial ? 14 : 12,
        mapTypeControl: false,
        streetViewControl: false
    });

    let marker = new google.maps.Marker({
        position: hasInitial ? { lat: initialLat, lng: initialLng } : { lat: defaultLat, lng: defaultLng },
        map,
        draggable: true
    });
    if (!hasInitial) marker.setVisible(false);

    const geocoder = new google.maps.Geocoder();
    const placesService = new google.maps.places.PlacesService(map);

    function reverseGeocodeAndSet(loc) {
        geocoder.geocode({ location: loc }, (results, status) => {
            if (status === 'OK' && results && results.length) {
                const best = results[0];
                if (best.formatted_address) {
                    addressInput.value = best.formatted_address;
                }
                if (best.place_id) {
                    placesService.getDetails({ placeId: best.place_id, fields: ['name'] }, (place, s) => {
                        if (s === 'OK' && place && place.name) {
                            venueNameInput.value = place.name;
                        } else {
                            // Fallback: use first segment of address as a rough venue name
                            if (best.formatted_address) {
                                venueNameInput.value = best.formatted_address.split(',')[0];
                            }
                        }
                        // Trigger auto-save after reverse geocoding updates
                        immediateAutoSave();
                    });
                } else if (best.formatted_address) {
                    venueNameInput.value = best.formatted_address.split(',')[0];
                    // Trigger auto-save after reverse geocoding updates
                    immediateAutoSave();
                }
            }
        });
    }

    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
        fields: ['geometry', 'formatted_address', 'name'],
        componentRestrictions: { country: ['my'] }
    });
    autocomplete.bindTo('bounds', map);

    // Autocomplete for venue name (establishments)
    const venueAutocomplete = new google.maps.places.Autocomplete(venueNameInput, {
        fields: ['geometry', 'formatted_address', 'name'],
        types: ['establishment'],
        componentRestrictions: { country: ['my'] }
    });
    venueAutocomplete.bindTo('bounds', map);

    function setAutocompleteBoundsFrom(latLng) {
        const circle = new google.maps.Circle({ center: latLng, radius: 5000 }); // 5km bias
        const bounds = circle.getBounds();
        autocomplete.setBounds(bounds);
        autocomplete.setOptions({ strictBounds: false }); // bias, not restrict
        venueAutocomplete.setBounds(bounds);
        venueAutocomplete.setOptions({ strictBounds: false });
    }

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;
        const loc = place.geometry.location;
        map.setCenter(loc);
        map.setZoom(15);
        marker.setPosition(loc);
        marker.setVisible(true);
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        if (place.formatted_address) {
            addressInput.value = place.formatted_address;
        }
        if (place.name) {
            venueNameInput.value = place.name;
        }
        setAutocompleteBoundsFrom(loc);
        
        // Trigger auto-save when location is updated from Google Maps
        immediateAutoSave();
    });

    venueAutocomplete.addListener('place_changed', () => {
        const place = venueAutocomplete.getPlace();
        if (!place.geometry) return;
        const loc = place.geometry.location;
        map.setCenter(loc);
        map.setZoom(15);
        marker.setPosition(loc);
        marker.setVisible(true);
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        if (place.name) {
            venueNameInput.value = place.name;
        }
        if (place.formatted_address) {
            addressInput.value = place.formatted_address;
        }
        setAutocompleteBoundsFrom(loc);
        
        // Trigger auto-save when location is updated from Google Maps
        immediateAutoSave();
    });

    map.addListener('click', (e) => {
        const loc = e.latLng;
        marker.setPosition(loc);
        marker.setVisible(true);
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        setAutocompleteBoundsFrom(loc);
        reverseGeocodeAndSet(loc);
        
        // Trigger auto-save when location is updated from map click
        immediateAutoSave();
    });

    marker.addListener('dragend', (e) => {
        const loc = e.latLng;
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        setAutocompleteBoundsFrom(loc);
        reverseGeocodeAndSet(loc);
        
        // Trigger auto-save when location is updated from marker drag
        immediateAutoSave();
    });

    // Try to use user's current location if no initial coordinates provided
    if (!hasInitial && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const loc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                map.setCenter(loc);
                map.setZoom(14);
                marker.setPosition(loc);
                marker.setVisible(true);
                latInput.value = loc.lat.toFixed(6);
                lngInput.value = loc.lng.toFixed(6);
                setAutocompleteBoundsFrom(loc);
                reverseGeocodeAndSet(loc);
                
                // Trigger auto-save when location is updated from geolocation
                immediateAutoSave();
            },
            () => {
                // If denied or failed, we keep default KL
                map.setCenter({ lat: defaultLat, lng: defaultLng });
                map.setZoom(12);
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }
}

// Auto-save functionality
let autoSaveTimeout;
let lastSavedData = '';
let isAutoSaving = false;

// Timezone-aware date validation
function updateStartDateMin() {
    const startDateInput = document.getElementById('start_date');
    if (!startDateInput) return;
    
    // Get current date and time in user's timezone
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    // Format as datetime-local input expects (YYYY-MM-DDTHH:MM)
    const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Update the min attribute
    startDateInput.min = minDateTime;
    
    // Validate current value is in the future
    validateStartDate();
}

// Validate start date is in the future (both date and time)
function validateStartDate() {
    const startDateInput = document.getElementById('start_date');
    if (!startDateInput || !startDateInput.value) return true;
    
    const selectedDate = new Date(startDateInput.value);
    const now = new Date();
    
    // Add a small buffer (1 minute) to account for time differences
    const bufferTime = 60 * 1000; // 1 minute in milliseconds
    const minAllowedTime = new Date(now.getTime() + bufferTime);
    
    if (selectedDate <= minAllowedTime) {
        // Clear the invalid date
        startDateInput.value = '';
        // Trigger auto-save to clear the value
        immediateAutoSave();
        
        // Show user-friendly message
        showStartDateError('Please select a future date and time for your event.');
        return false;
    }
    
    return true;
}

// Show start date error message using existing notification system
function showStartDateError(message) {
    // Use the existing GuestManager.showNotification function from app.js
    if (window.GuestManager?.showNotification) {
        window.GuestManager.showNotification(message, 'error');
    } else {
        // Fallback to console log if notification function is not available
        console.error('Start date validation error:', message);
    }
}

// Update minimum date every minute to ensure it stays current
function startDateValidation() {
    updateStartDateMin();
    // Update every minute
    setInterval(updateStartDateMin, 60000);
}

// Validate end date is after start date
function validateEndDate() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (!startDateInput || !endDateInput) return;
    
    if (startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        // Minimum duration: 15 minutes
        const minDuration = 15 * 60 * 1000; // 15 minutes in milliseconds
        const minEndDate = new Date(startDate.getTime() + minDuration);
        
        if (endDate <= startDate) {
            // Show validation modal
            showConfirmationModal('timeValidationModal', () => {
                endDateInput.value = '';
                immediateAutoSave();
            });
            return false;
        }
        
        if (endDate < minEndDate) {
            // Show validation modal for minimum duration
            showConfirmationModal('timeValidationModal', () => {
                endDateInput.value = '';
                immediateAutoSave();
            });
            return false;
        }
    }
    
    return true;
}



function autoSave() {
    if (isAutoSaving) return;
    
    const formData = new FormData(document.getElementById('event-form-1'));
    const currentData = JSON.stringify(Object.fromEntries(formData));
    
    if (currentData === lastSavedData) return;
    
    isAutoSaving = true;
    lastSavedData = currentData;
    
    fetch('{{ route("organizer.events.create.auto-save") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
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

// Initialize auto-save on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize timezone-aware date validation
    startDateValidation();
    
    const autoSaveInputs = document.querySelectorAll('input, textarea, select');
    
    autoSaveInputs.forEach(input => {
        if (input.name === 'name' || input.name === 'start_date' || input.name === 'description') {
            input.addEventListener('input', immediateAutoSave);
            input.addEventListener('change', immediateAutoSave);
        } else if (input.name === 'venue_name' || input.name === 'venue_address' || input.name === 'location' || input.name === 'latitude' || input.name === 'longitude') {
            // Location fields should trigger immediate auto-save
            input.addEventListener('input', immediateAutoSave);
            input.addEventListener('change', immediateAutoSave);
        } else {
            input.addEventListener('input', debounceAutoSave);
            input.addEventListener('change', debounceAutoSave);
        }
    });
    
    // Add date validation listeners
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            // Validate start date is in the future
            if (!validateStartDate()) {
                return; // Stop processing if validation failed
            }
            
            // Update end date min when start date changes
            if (endDateInput && this.value) {
                const startDate = new Date(this.value);
                const minEndDate = new Date(startDate.getTime() + (15 * 60 * 1000)); // 15 minutes later
                const year = minEndDate.getFullYear();
                const month = String(minEndDate.getMonth() + 1).padStart(2, '0');
                const day = String(minEndDate.getDate()).padStart(2, '0');
                const hours = String(minEndDate.getHours()).padStart(2, '0');
                const minutes = String(minEndDate.getMinutes()).padStart(2, '0');
                endDateInput.min = `${year}-${month}-${day}T${hours}:${minutes}`;
                
                // Clear end date if it's now invalid
                if (endDateInput.value && new Date(endDateInput.value) <= startDate) {
                    endDateInput.value = '';
                    immediateAutoSave();
                }
            }
        });
        
        // Also validate on input event for real-time feedback
        startDateInput.addEventListener('input', function() {
            // Only validate if there's a complete datetime value
            if (this.value && this.value.length >= 16) {
                validateStartDate();
            }
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', validateEndDate);
    }
    
    // Handle form submission - allow normal form submission for the submit button
    document.getElementById('event-form-1').addEventListener('submit', function(e) {
        // Validate start date before submission
        if (!validateStartDate()) {
            e.preventDefault();
            return false;
        }
        
        // Only prevent default if it's not a submit button click
        const submitter = e.submitter;
        if (submitter && submitter.type === 'submit') {
            // This is a real form submission, allow it to proceed normally
            clearTimeout(autoSaveTimeout);
            return true; // Allow normal form submission
        }
        
        // For other cases (like auto-save), prevent default and use AJAX
        e.preventDefault();
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
</script>
@endsection