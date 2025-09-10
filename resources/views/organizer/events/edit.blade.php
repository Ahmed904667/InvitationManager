@extends('layouts.organizer')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-primary">
                    @if($event->status === 'sent')
                        Update Event
                    @else
                        Edit Event
                    @endif
                </h1>
                <p class="text-gray-600 mt-2">
                    @if($event->status === 'sent')
                        Update event information and notify guests of changes
                    @else
                        Edit your event details
                    @endif
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="badge badge-{{ $event->status === 'draft' ? 'draft' : ($event->status === 'scheduled' ? 'scheduled' : 'sent') }}">
                    {{ ucfirst($event->status) }}
                </span>
                <a href="{{ route('organizer.events.show', $event) }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Event
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('organizer.events.update', $event) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Event Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $event->name) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date & Time *</label>
                    <input type="datetime-local" id="start_date" name="start_date" 
                           value="{{ old('start_date', $event->start_date ? date('Y-m-d\TH:i', strtotime($event->start_date)) : '') }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date & Time</label>
                    <input type="datetime-local" id="end_date" name="end_date" 
                           value="{{ old('end_date', $event->end_date ? date('Y-m-d\TH:i', strtotime($event->end_date)) : '') }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <small class="text-gray-500 text-sm">Optional - must be at least 15 minutes after start time</small>
                </div>
                
                <div>
                    <label for="venue_name" class="block text-sm font-medium text-gray-700 mb-2">Venue Name</label>
                    <input type="text" id="venue_name" name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Event Description</label>
                <textarea id="description" name="description" rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $event->description) }}</textarea>
            </div>
        </div>

        <!-- Location with Map -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">Location</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label for="venue_address" class="block text-sm font-medium text-gray-700 mb-2">Venue Address</label>
                    <input type="text" id="venue_address" name="venue_address" 
                           value="{{ old('venue_address', $event->venue_address) }}" 
                           placeholder="Street, City, Country"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <small class="text-gray-500 text-sm">You can also select a point on the map below.</small>
                </div>
                
            </div>
            
            <div class="mt-4">
                <div id="map" class="w-full rounded-lg border border-gray-200" style="height: 320px;"></div>
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $event->latitude ?? '') }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $event->longitude ?? '') }}">
            </div>
        </div>

        <!-- Invitation Settings -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">Invitation Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="invitation_title" class="block text-sm font-medium text-gray-700 mb-2">Invitation Title</label>
                    <input type="text" id="invitation_title" name="invitation_title" 
                           value="{{ old('invitation_title', $event->invitation_title ?? "You're Invited!") }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
            </div>
            
        </div>

        <!-- RSVP Settings -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">RSVP Settings</h2>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" id="rsvp_enabled" name="rsvp_enabled" value="1" 
                           {{ old('rsvp_enabled', $event->rsvp_enabled) ? 'checked' : '' }} 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="rsvp_enabled" class="ml-2 block text-sm text-gray-900">Enable RSVP</label>
                </div>
                
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                </div>
            </div>
        </div>

        <!-- QR Code Settings -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">QR Code Settings</h2>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" id="qr_checkin_enabled" name="qr_checkin_enabled" value="1" 
                           {{ old('qr_checkin_enabled', $event->qr_checkin_enabled) ? 'checked' : '' }} 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="qr_checkin_enabled" class="ml-2 block text-sm text-gray-900">Enable QR Code Check-in</label>
                </div>
                
            </div>
        </div>

        <!-- Guest Lists -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">Guest Lists</h2>
            <div class="space-y-3">
                @foreach($guestLists as $guestList)
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="guest_list_ids[]" value="{{ $guestList->id }}"
                               {{ in_array($guestList->id, old('guest_list_ids', $event->guestLists->pluck('id')->toArray())) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ $guestList->name }}</div>
                            <div class="text-sm text-gray-500">{{ $guestList->guests->count() }} guests</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Status & Notifications -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">Status & Notifications</h2>
            
            @if($event->status === 'sent')
                <!-- For sent events -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="notify_guests" name="notify_guests" value="1" checked
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="notify_guests" class="ml-2 block text-sm text-gray-900">Notify guests of changes</label>
                    </div>
                    
                    <div>
                        <label for="update_message" class="block text-sm font-medium text-gray-700 mb-2">Update Message (Optional)</label>
                        <textarea id="update_message" name="update_message" rows="3" 
                                  placeholder="Add a custom message to include with the update notification..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('update_message') }}</textarea>
                    </div>
                </div>
            @else
                <!-- For draft/scheduled events -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Event Status</label>
                        <select id="status" name="status" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="scheduled" {{ old('status', $event->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="send_type" class="block text-sm font-medium text-gray-700 mb-2">Send Type</label>
                        <select id="send_type" name="send_type" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="now" {{ old('send_type', $event->send_type ?? 'now') === 'now' ? 'selected' : '' }}>Send Now</option>
                            <option value="scheduled" {{ old('send_type', $event->send_type ?? 'now') === 'scheduled' ? 'selected' : '' }}>Schedule for Later</option>
                        </select>
                    </div>
                </div>
                
                <div id="scheduled_at_group" class="mt-6" style="display: none;">
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-2">Schedule Date & Time</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" 
                           value="{{ old('scheduled_at', $event->scheduled_at ? date('Y-m-d\TH:i', strtotime($event->scheduled_at)) : '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            @endif
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('organizer.events.show', $event) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                @if($event->status === 'sent')
                    Update Event
                @else
                    Save Changes
                @endif
            </button>
        </div>
    </form>
</div>

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
                    });
                } else if (best.formatted_address) {
                    venueNameInput.value = best.formatted_address.split(',')[0];
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
    });

    map.addListener('click', (e) => {
        const loc = e.latLng;
        marker.setPosition(loc);
        marker.setVisible(true);
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        setAutocompleteBoundsFrom(loc);
        reverseGeocodeAndSet(loc);
    });

    marker.addListener('dragend', (e) => {
        const loc = e.latLng;
        latInput.value = loc.lat().toFixed(6);
        lngInput.value = loc.lng().toFixed(6);
        setAutocompleteBoundsFrom(loc);
        reverseGeocodeAndSet(loc);
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

document.addEventListener('DOMContentLoaded', function() {
    // Handle send type change
    const sendTypeSelect = document.getElementById('send_type');
    const scheduledAtGroup = document.getElementById('scheduled_at_group');
    
    if (sendTypeSelect && scheduledAtGroup) {
        sendTypeSelect.addEventListener('change', function() {
            if (this.value === 'scheduled') {
                scheduledAtGroup.style.display = 'block';
            } else {
                scheduledAtGroup.style.display = 'none';
            }
        });
        
        // Initialize on page load
        if (sendTypeSelect.value === 'scheduled') {
            scheduledAtGroup.style.display = 'block';
        }
    }
    
    // Time validation
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
    startDateInput.addEventListener('change', function() {
        if (this.value) {
                const minDurationMinutes = 15;
            const startDate = new Date(this.value);
                const minEndDate = new Date(startDate.getTime() + (minDurationMinutes * 60 * 1000));
            const minEndDateTime = minEndDate.toISOString().slice(0, 16);
            
            endDateInput.min = minEndDateTime;
            
            if (endDateInput.value && endDateInput.value < minEndDateTime) {
                endDateInput.value = '';
            }
        }
    });
    
    endDateInput.addEventListener('change', function() {
        if (this.value && startDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(this.value);
                const minDurationMinutes = 15;
                const minEndDate = new Date(startDate.getTime() + (minDurationMinutes * 60 * 1000));
                
                if (endDate < minEndDate) {
                    alert('End time must be at least 15 minutes after start time.');
                    this.value = '';
                return;
            }
        }
    });
    
    // Initialize validation if start date is already set
    if (startDateInput.value) {
        startDateInput.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endsection 