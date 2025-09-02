<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #8b2bfa;
            margin-bottom: 10px;
        }
        .title {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 30px;
        }
        .otp-container {
            background: linear-gradient(135deg, #8b2bfa 0%, #7c3aed 100%);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        .otp-label {
            color: #e0e7ff;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .content {
            margin: 30px 0;
            color: #374151;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #8b2bfa 0%, #7c3aed 100%);
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Invaro</div>
            <div class="title">Verify Your {{ $type === 'email' ? 'Email Address' : 'Phone Number' }}</div>
            <div class="subtitle">Enter the code below to complete your verification</div>
        </div>

        <div class="otp-container">
            <div class="otp-label">Your Verification Code</div>
            <div class="otp-code">{{ $otpCode }}</div>
            <div class="otp-label">Valid for 10 minutes</div>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $userName }}</strong>,</p>
            
            <p>We received a request to update your {{ $type === 'email' ? 'email address' : 'phone number' }} on your Invaro account. To proceed with this change, please use the verification code above.</p>
            
            <p>If you didn't request this change, please ignore this email and your account will remain secure.</p>
        </div>

        <div class="warning">
            <strong>Security Notice:</strong> Never share this code with anyone. Invaro staff will never ask for your verification code.
        </div>

        <div class="footer">
            <p>This is an automated message from Invaro. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Invaro. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
