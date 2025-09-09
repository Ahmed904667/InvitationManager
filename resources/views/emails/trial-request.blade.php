<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'You\'re Invited!' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px 0;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background-color: #4f46e5;
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header .subtitle {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 25px;
        }
        
        .invitation-content {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #4f46e5;
        }
        
        .invitation-text {
            font-size: 16px;
            color: #374151;
            line-height: 1.7;
            white-space: pre-line;
        }
        
        .event-details {
            background-color: #ffffff;
            border-radius: 6px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid #e5e7eb;
        }
        
        .event-details h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            text-align: center;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-icon {
            width: 24px;
            height: 24px;
            margin-right: 15px;
            color: #4f46e5;
            font-size: 16px;
        }
        
        .detail-text {
            flex: 1;
            color: #4b5563;
            font-size: 15px;
        }
        
        .detail-text strong {
            color: #1f2937;
            font-weight: 600;
        }
        
        .cta-section {
            background-color: #4f46e5;
            color: white;
            padding: 25px;
            border-radius: 6px;
            text-align: center;
            margin: 30px 0;
        }
        
        .cta-section h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .cta-section p {
            margin: 0;
            opacity: 0.9;
            font-size: 15px;
        }
        
        .quote-section {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
            border-left: 3px solid #4f46e5;
        }
        
        .quote-text {
            margin: 0;
            color: #4b5563;
            font-style: italic;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer .signature {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .footer .tagline {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .social-links {
            margin: 20px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .footer-note {
            margin-top: 20px;
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.4;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-container {
                border-radius: 6px;
            }
            
            .header, .content, .footer {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .invitation-content {
                padding: 20px;
            }
            
            .event-details {
                padding: 20px;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
            
            .detail-icon {
                margin-bottom: 5px;
                margin-right: 0;
            }
            
            .cta-section {
                padding: 20px;
            }
            
            .social-links a {
                display: block;
                margin: 8px 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 {{ $subject ?? 'You\'re Invited!' }}</h1>
            <p class="subtitle">A special invitation just for you</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Dear {{ $name }},</div>
            
            @if($invitationMessage)
                <div class="invitation-content">
                    <div class="invitation-text">{{ $invitationMessage }}</div>
                </div>
            @else
                <div class="invitation-content">
                    <div class="invitation-text">
                        You're cordially invited to join us for a wonderful {{ $eventType }} event! 

We're excited to have you as our special guest and can't wait to share this memorable occasion with you.

Please save the date and join us for an evening of celebration, great company, and unforgettable moments.
                    </div>
                </div>
            @endif

            <div class="event-details">
                <h3>📅 Event Details</h3>
                <div class="detail-row">
                    <div class="detail-icon">📅</div>
                    <div class="detail-text">Date: <strong>To be confirmed</strong></div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon">🕒</div>
                    <div class="detail-text">Time: <strong>To be confirmed</strong></div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon">📍</div>
                    <div class="detail-text">Location: <strong>To be confirmed</strong></div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon">👔</div>
                    <div class="detail-text">Dress Code: <strong>Smart Casual</strong></div>
                </div>
            </div>

            @if($inviteUrl)
            <div class="cta-section">
                <h3>🔗 View Your Invitation</h3>
                <p>Click the link below to see your personalized invitation with all the details:</p>
                <div style="margin: 20px 0;">
                    <a href="{{ $inviteUrl }}" style="display: inline-block; background-color: #ffffff; color: #4f46e5; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; border: 2px solid #ffffff;">View Invitation</a>
                </div>
            </div>
            @endif

            <div class="cta-section">
                <h3>💌 RSVP</h3>
                <p>Please confirm your attendance by replying to this email or contacting us directly. We look forward to hearing from you!</p>
            </div>

            <div class="quote-section">
                <p class="quote-text">
                    The best parties are those where everyone feels welcome and included. We can't wait to celebrate with you!
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="signature">Warm regards,</div>
            <div class="tagline">Your Host</div>
            
            <div class="social-links">
                <a href="#">📧 Contact</a> |
                <a href="#">📱 Call</a> |
                <a href="#">💬 Message</a>
            </div>
            
            <div class="footer-note">
                This invitation was sent to {{ $contact }} for the {{ $eventType }} event.<br>
                If you have any questions or need to make changes, please don't hesitate to reach out.
            </div>
        </div>
    </div>
</body>
</html> 