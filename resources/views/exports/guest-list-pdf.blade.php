<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $guestList->name }} - Guest List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 20px;
        }
        .header p {
            margin: 3px 0;
            color: #666;
            font-size: 11px;
        }
        .stats {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .stats-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .stat {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            display: block;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $guestList->name }}</h1>
        <p>{{ $guestList->description }}</p>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="stats">
        <div class="stats-row">
            <div class="stat">
                <span class="stat-number">{{ $guests->count() }}</span>
                <span class="stat-label">Total Guests</span>
            </div>
            @if($guestList->settings['fields']['email'] ?? false)
            <div class="stat">
                <span class="stat-number">{{ $guests->whereNotNull('email')->count() }}</span>
                <span class="stat-label">With Email</span>
            </div>
            @endif
            @if($guestList->settings['fields']['phone'] ?? false)
            <div class="stat">
                <span class="stat-number">{{ $guests->whereNotNull('phone')->count() }}</span>
                <span class="stat-label">With Phone</span>
            </div>
            @endif
            @if($guestList->settings['fields']['group'] ?? false)
            <div class="stat">
                <span class="stat-number">{{ $guests->pluck('group_id')->unique()->count() }}</span>
                <span class="stat-label">Groups</span>
            </div>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                @if($guestList->settings['fields']['email'] ?? false)
                    <th>Email</th>
                @endif
                @if($guestList->settings['fields']['phone'] ?? false)
                    <th>Phone</th>
                @endif
                @if($guestList->settings['fields']['language'] ?? false)
                    <th>Language</th>
                @endif
                @if($guestList->settings['fields']['group'] ?? false)
                    <th>Group</th>
                @endif
                @if($guestList->settings['fields']['notes'] ?? false)
                    <th>Notes</th>
                @endif
                <th>Created Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guests as $guest)
                <tr>
                    <td>{{ $guest->name }}</td>
                    @if($guestList->settings['fields']['email'] ?? false)
                        <td>{{ $guest->email ?? '-' }}</td>
                    @endif
                    @if($guestList->settings['fields']['phone'] ?? false)
                        <td>{{ $guest->phone ?? '-' }}</td>
                    @endif
                    @if($guestList->settings['fields']['language'] ?? false)
                        <td>{{ $guest->language ?? '-' }}</td>
                    @endif
                    @if($guestList->settings['fields']['group'] ?? false)
                        <td>{{ $guest->group ? $guest->group->name : 'No Group' }}</td>
                    @endif
                    @if($guestList->settings['fields']['notes'] ?? false)
                        <td>{{ $guest->notes ?? '-' }}</td>
                    @endif
                    <td>{{ $guest->created_at ? $guest->created_at->format('M j, Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Invaro - {{ config('app.name') }}</p>
        <p>This report was generated automatically. For questions, please contact the event organizer.</p>
    </div>
</body>
</html> 