<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Report - {{ $event['name'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #3b82f6;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .header p {
            color: #6b7280;
            margin: 5px 0;
        }
        
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #f9fafb;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .card-header h3 {
            color: #3b82f6;
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .grid-2 {
            display: table;
            width: 100%;
        }
        
        .grid-2 > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .grid-2 > div:last-child {
            padding-right: 0;
        }
        
        .field-group {
            margin-bottom: 15px;
        }
        
        .field-label {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 5px;
            display: block;
        }
        
        .field-value {
            color: #3b82f6;
            font-size: 12px;
            font-weight: bold;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        
        .stat-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 10px;
            color: #6b7280;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .table th {
            background-color: #f9fafb;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-green { color: #059669; }
        .text-blue { color: #3b82f6; }
        .text-purple { color: #8b5cf6; }
        .text-yellow { color: #d97706; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Event Report</h1>
        <p><strong>{{ $event['name'] }}</strong></p>
        <p>Generated on {{ $generated_at }}</p>
    </div>

    <!-- Event Information -->
    <div class="card">
        <div class="card-header">
            <h3>Event Information</h3>
        </div>
        <div class="card-body">
            <div class="grid-2">
                <div>
                    <h4 style="color: #3b82f6; font-size: 14px; margin-bottom: 15px;">Basic Details</h4>
                    <div class="field-group">
                        <span class="field-label">Event Name:</span>
                        <div class="field-value">{{ $event['name'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Description:</span>
                        <div class="field-value">{{ $event['description'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Start Date:</span>
                        <div class="field-value">{{ $event['start_date'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">End Date:</span>
                        <div class="field-value">{{ $event['end_date'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Location:</span>
                        <div class="field-value">{{ $event['location'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Venue Name:</span>
                        <div class="field-value">{{ $event['venue_name'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Venue Address:</span>
                        <div class="field-value">{{ $event['venue_address'] }}</div>
                    </div>
                </div>
                <div>
                    <h4 style="color: #3b82f6; font-size: 14px; margin-bottom: 15px;">Event Settings</h4>
                    <div class="field-group">
                        <span class="field-label">RSVP Enabled:</span>
                        <div class="field-value">{{ $settings['rsvp_enabled'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Check-in Enabled:</span>
                        <div class="field-value">{{ $settings['checkin_enabled'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Invitation Platforms:</span>
                        <div class="field-value">{{ $settings['invitation_platforms'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Guest Lists:</span>
                        <div class="field-value">{{ $settings['guest_list_count'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Scanners:</span>
                        <div class="field-value">{{ $settings['scanner_count'] }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Created:</span>
                        <div class="field-value">{{ $event['created_at'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Overview -->
    <div class="card">
        <div class="card-header">
            <h3>Event Overview</h3>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">{{ $overview['total_guests'] }}</div>
                    <div class="stat-label">Total Guests</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number text-green">{{ $overview['checked_in_guests'] }}</div>
                    <div class="stat-label">Checked In</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number text-blue">{{ $overview['checkin_rate'] }}%</div>
                    <div class="stat-label">Check-in Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number text-purple">{{ $overview['rsvp_yes'] }}</div>
                    <div class="stat-label">RSVP Yes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number text-yellow">{{ $overview['rsvp_maybe'] }}</div>
                    <div class="stat-label">RSVP Maybe</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number text-red">{{ $overview['rsvp_no'] }}</div>
                    <div class="stat-label">RSVP No</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="grid-2">
        <!-- Invitation Statistics -->
        <div class="card">
            <div class="card-header">
                <h3>Invitation Statistics</h3>
            </div>
            <div class="card-body">
                <div class="field-group">
                    <span class="field-label">Total Invitations:</span>
                    <div class="field-value">{{ $invitation_stats['total_invitations'] ?? 0 }}</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Unique Guests Invited:</span>
                    <div class="field-value text-blue">{{ $invitation_stats['total_unique_guests_invited'] ?? 0 }}</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Sent Successfully:</span>
                    <div class="field-value text-green">{{ $invitation_stats['sent_invitations'] ?? 0 }}</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Delivery Rate:</span>
                    <div class="field-value text-blue">{{ $invitation_stats['delivery_rate'] ?? 0 }}%</div>
                </div>
                <div class="field-group">
                    <span class="field-label">RSVP Response Rate:</span>
                    <div class="field-value text-purple">{{ $invitation_stats['rsvp_response_rate'] ?? 0 }}%</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Pending Responses:</span>
                    <div class="field-value text-yellow">{{ $invitation_stats['rsvp_breakdown']['pending'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Check-in Statistics -->
        <div class="card">
            <div class="card-header">
                <h3>Check-in Statistics</h3>
            </div>
            <div class="card-body">
                <div class="field-group">
                    <span class="field-label">First Check-in:</span>
                    <div class="field-value">{{ $checkin_stats['first_checkin'] }}</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Last Check-in:</span>
                    <div class="field-value">{{ $checkin_stats['last_checkin'] }}</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Average Time:</span>
                    <div class="field-value text-blue">{{ $checkin_stats['average_checkin_time'] ?? 'N/A' }}</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Not Checked In:</span>
                    <div class="field-value text-red">{{ $checkin_stats['not_checked_in'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="card">
        <div class="card-header">
            <h3>Guest Engagement Metrics</h3>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number text-green">{{ $engagement_metrics['overall_engagement_rate'] ?? 0 }}%</div>
                    <div class="stat-label">Overall Engagement</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number text-blue">{{ $engagement_metrics['rsvp_engagement_rate'] ?? 0 }}%</div>
                    <div class="stat-label">RSVP Engagement</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number text-purple">{{ $engagement_metrics['checkin_engagement_rate'] ?? 0 }}%</div>
                    <div class="stat-label">Check-in Engagement</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Usage -->
    @if(!empty($scanner_usage))
    <div class="card">
        <div class="card-header">
            <h3>Scanner Usage</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Scanner Name</th>
                        <th>Check-ins</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scanner_usage as $scanner)
                    <tr>
                        <td>{{ $scanner['scanner_name'] }}</td>
                        <td>{{ $scanner['checkins_count'] }}</td>
                        <td>{{ $scanner['is_active'] ? 'Active' : 'Inactive' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Guest List Details -->
    @if(!empty($guests))
    <div class="page-break"></div>
    <div class="card">
        <div class="card-header">
            <h3>Guest List Details</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>RSVP Status</th>
                        <th>RSVP Date</th>
                        <th>Check-in Time</th>
                        <th>Check-in Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($guests as $guest)
                    <tr>
                        <td>{{ $guest['name'] }}</td>
                        <td>{{ $guest['email'] }}</td>
                        <td>{{ $guest['rsvp_status'] ?? 'N/A' }}</td>
                        <td>{{ $guest['rsvp_date'] ?? 'N/A' }}</td>
                        <td>{{ $guest['checkin_time'] ?? 'Not checked in' }}</td>
                        <td>{{ $guest['checkin_method'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div style="margin-top: 40px; text-align: center; color: #6b7280; font-size: 10px;">
        <p>This report was generated automatically by the Guest Manager System</p>
        <p>For questions or support, please contact your system administrator</p>
    </div>
</body>
</html>
