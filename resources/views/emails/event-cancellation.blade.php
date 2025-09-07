<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Cancellation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .event-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .apology-message {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Event Cancellation Notice</h1>
    </div>
    
    <div class="content">
        <p>Dear Guest,</p>
        
        <p>We regret to inform you that the following event has been cancelled:</p>
        
        <div class="event-details">
            <h3>{{ $event->name }}</h3>
            @if($event->start_date)
                <p><strong>Date:</strong> {{ $event->start_date->format('l, F j, Y') }}</p>
                <p><strong>Time:</strong> {{ $event->start_date->format('g:i A') }}</p>
            @endif
            @if($event->venue_name || $event->venue_address)
                <p><strong>Location:</strong> {{ $event->venue_name ?: $event->venue_address }}</p>
            @endif
        </div>
        
        <div class="apology-message">
            <h4>Message from the Organizer:</h4>
            <p>{{ $apologyMessage }}</p>
        </div>
        
        <p>We sincerely apologize for any inconvenience this may cause. We will notify you of any future events.</p>
        
        <p>Thank you for your understanding.</p>
        
        <p>Best regards,<br>
        {{ $event->user->name ?? 'Event Organizer' }}</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>
