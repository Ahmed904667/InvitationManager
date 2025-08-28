<div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900">📋 RSVP Statistics</h3>
            <p class="text-gray-600">Real-time guest responses</p>
        </div>
        <div class="flex items-center space-x-2">
            <div id="rsvp-last-updated" class="text-sm text-gray-500">
                Last updated: <span class="font-medium">Just now</span>
            </div>
            <button id="refresh-rsvp" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- RSVP Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Guests -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">Total Guests</p>
                    <p id="total-guests" class="text-2xl font-bold text-blue-900">0</p>
                </div>
                <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Confirmed -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600">Confirmed</p>
                    <p id="rsvp-yes" class="text-2xl font-bold text-green-900">0</p>
                </div>
                <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Maybe -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-600">Maybe</p>
                    <p id="rsvp-maybe" class="text-2xl font-bold text-yellow-900">0</p>
                </div>
                <div class="w-10 h-10 bg-yellow-200 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Declined -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-600">Declined</p>
                    <p id="rsvp-no" class="text-2xl font-bold text-red-900">0</p>
                </div>
                <div class="w-10 h-10 bg-red-200 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Response Rate Progress -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Response Rate</span>
            <span id="response-rate" class="text-sm font-bold text-blue-600">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div id="response-rate-bar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>
    </div>

    <!-- Attendance Rate Progress -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Expected Attendance</span>
            <span id="attendance-rate" class="text-sm font-bold text-green-600">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div id="attendance-rate-bar" class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>
    </div>

    <!-- No Response Count -->
    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">No Response Yet</p>
                <p id="rsvp-no-response" class="text-lg font-bold text-gray-900">0</p>
            </div>
            <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- RSVP Details Table -->
<div class="bg-white rounded-2xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900">📝 RSVP Details</h3>
            <p class="text-gray-600">Individual guest responses</p>
        </div>
        <button id="refresh-rsvp-details" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Refresh
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Guest</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700">List</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Response Time</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Notes</th>
                </tr>
            </thead>
            <tbody id="rsvp-details-table">
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-500">
                        <div class="flex items-center justify-center">
                            <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Loading RSVP details...
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = {{ $event->id }};
    let refreshInterval;

    // Function to update RSVP stats
    function updateRsvpStats() {
        fetch(`/organizer/events/${eventId}/rsvp/stats`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    
                    // Update numbers
                    document.getElementById('total-guests').textContent = stats.total_guests;
                    document.getElementById('rsvp-yes').textContent = stats.rsvp_responses.yes;
                    document.getElementById('rsvp-maybe').textContent = stats.rsvp_responses.maybe;
                    document.getElementById('rsvp-no').textContent = stats.rsvp_responses.no;
                    document.getElementById('rsvp-no-response').textContent = stats.rsvp_responses.no_response;
                    
                    // Update rates
                    document.getElementById('response-rate').textContent = stats.response_rate + '%';
                    document.getElementById('attendance-rate').textContent = stats.attendance_rate + '%';
                    
                    // Update progress bars
                    document.getElementById('response-rate-bar').style.width = stats.response_rate + '%';
                    document.getElementById('attendance-rate-bar').style.width = stats.attendance_rate + '%';
                    
                    // Update last updated time
                    const lastUpdated = new Date(data.last_updated);
                    document.getElementById('rsvp-last-updated').innerHTML = 
                        `Last updated: <span class="font-medium">${lastUpdated.toLocaleTimeString()}</span>`;
                }
            })
            .catch(error => {
                console.error('Error fetching RSVP stats:', error);
            });
    }

    // Function to update RSVP details
    function updateRsvpDetails() {
        fetch(`/organizer/events/${eventId}/rsvp/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const details = data.rsvp_details;
                    const tbody = document.getElementById('rsvp-details-table');
                    
                    if (details.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-500">
                                    No RSVP responses yet
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    tbody.innerHTML = details.map(guest => {
                        const statusColors = {
                            'yes': 'bg-green-100 text-green-800',
                            'no': 'bg-red-100 text-red-800',
                            'maybe': 'bg-yellow-100 text-yellow-800',
                            'no_response': 'bg-gray-100 text-gray-800'
                        };
                        
                        const statusText = {
                            'yes': 'Confirmed',
                            'no': 'Declined',
                            'maybe': 'Maybe',
                            'no_response': 'No Response'
                        };
                        
                        const statusClass = statusColors[guest.rsvp_status] || 'bg-gray-100 text-gray-800';
                        const statusLabel = statusText[guest.rsvp_status] || 'Unknown';
                        
                        const responseTime = guest.rsvp_at ? new Date(guest.rsvp_at).toLocaleString() : '-';
                        const notes = guest.rsvp_note ? `<div class="text-sm text-gray-600">${guest.rsvp_note}</div>` : '';
                        
                        return `
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div>
                                        <div class="font-medium text-gray-900">${guest.guest_name}</div>
                                        <div class="text-sm text-gray-500">${guest.guest_email || ''}</div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-700">${guest.guest_list_name}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
                                        ${statusLabel}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-700">${responseTime}</td>
                                <td class="py-3 px-4">
                                    ${notes}
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(error => {
                console.error('Error fetching RSVP details:', error);
            });
    }

    // Initial load
    updateRsvpStats();
    updateRsvpDetails();

    // Set up auto-refresh every 30 seconds
    refreshInterval = setInterval(() => {
        updateRsvpStats();
        updateRsvpDetails();
    }, 30000);

    // Manual refresh buttons
    document.getElementById('refresh-rsvp').addEventListener('click', updateRsvpStats);
    document.getElementById('refresh-rsvp-details').addEventListener('click', updateRsvpDetails);

    // Clean up interval on page unload
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
});
</script>



