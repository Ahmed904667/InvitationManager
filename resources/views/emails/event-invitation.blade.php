<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Invitation</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .header .subtitle {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .event-details {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 25px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .event-details h2 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 22px;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .detail-text {
            flex: 1;
        }
        
        .message-content {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            font-size: 16px;
            line-height: 1.7;
            white-space: pre-line;
        }
        
        .cta-section {
            text-align: center;
            margin: 35px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .features {
            margin: 30px 0;
        }
        
        .features h3 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
        }
        
        .feature-list li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
            font-size: 16px;
        }
        
        .footer {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 30px;
            text-align: center;
            font-size: 14px;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .footer a {
            color: #3498db;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        /* Mobile responsiveness */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                box-shadow: none;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .event-details {
                padding: 20px;
            }
            
            .message-content {
                padding: 20px;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .detail-icon {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $event->name }}</h1>
            <p class="subtitle">You're Invited!</p>
        </div>
        
        <!-- Content -->
        <div class="content">

                    <!-- Personal Message -->
            @if($personalMessage)
            <div class="message-content">
                {{ $personalMessage }}
            </div>
            @endif
            
            <!-- Event Details -->
            <div class="event-details">
                <h2>Event Details</h2>
                
                <div class="detail-row">
                    <div class="detail-icon">📅</div>
                    <div class="detail-text">
                        <strong>Date:</strong> {{ $eventDate }}
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-icon">🕐</div>
                    <div class="detail-text">
                        <strong>Time:</strong> {{ $eventTime }}
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-icon">📍</div>
                    <div class="detail-text">
                        <strong>Location:</strong> {{ $eventLocation }}
                    </div>
                </div>
                
                @if($eventDescription)
                <div class="detail-row">
                    <div class="detail-icon">📝</div>
                    <div class="detail-text">
                        <strong>Description:</strong> {{ $eventDescription }}
                    </div>
                </div>
                @endif
                
                <div class="detail-row">
                    <div class="detail-icon">👤</div>
                    <div class="detail-text">
                        <strong>Hosted by:</strong> {{ $organizerName }}
                    </div>
                </div>
            </div>
            

            
            <!-- Call to Action -->
            <div class="cta-section">
                <a href="{{ $inviteUrl }}" class="cta-button" style="color: white;">View Invitation</a>
            </div>
            
            <!-- Event Features -->
            <div class="features">
                <h3>What to Expect</h3>
                <ul class="feature-list">
                    @if($hasRsvp)
                    <li>Easy RSVP response system</li>
                    @endif
                    @if($hasQrCheckin)
                    <li>Quick QR code check-in process</li>
                    @endif
                    <li>Event updates and notifications</li>
                    <li>Digital invitation management</li>
                </ul>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $organizerName }}</strong></p>
            <p>This invitation was sent using Invaro</p>
            <p>If you have any questions, please contact the event organizer directly:</p>
            
            <!-- Organizer Contact Information -->
            <div style="margin: 15px 0; padding: 15px; background-color: rgba(255,255,255,0.1); border-radius: 8px;">
                <p style="margin: 5px 0;"><strong>📧 Email:</strong> <a href="mailto:{{ $organizerEmail }}" style="color: #3498db;">{{ $organizerEmail }}</a></p>
                @if($organizerPhone)
                <p style="margin: 5px 0;"><strong>📞 Phone:</strong> <a href="tel:{{ $organizerPhone }}" style="color: #3498db;">{{ $organizerPhone }}</a></p>
                @endif
            </div>
            
            <p style="margin-top: 20px; font-size: 12px; opacity: 0.8;">
                <a href="{{ $inviteUrl }}">View Online Invitation</a> | 
                <a href="{{ $inviteUrl }}?action=decline">Decline Invitation</a>
            </p>
        </div>
    </div>
</body>
</html>
