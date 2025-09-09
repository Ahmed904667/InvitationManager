<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deletion Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
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
            color: #dc2626;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .content {
            margin-bottom: 30px;
        }
        .warning-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-icon {
            color: #dc2626;
            font-size: 20px;
            margin-right: 10px;
        }
        .button {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .button:hover {
            background: #b91c1c;
        }
        .button-secondary {
            background: #6b7280;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 10px 0;
            display: inline-block;
        }
        .button-secondary:hover {
            background: #4b5563;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .expiry-notice {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Invaro</div>
            <h1 class="title">Account Deletion Confirmation</h1>
        </div>

        <div class="content">
            <p>Hello {{ $deletionRequest->user->name }},</p>
            
            <p>We received a request to delete your Invaro account. This action is <strong>permanent and cannot be undone</strong>.</p>

            <div class="warning-box">
                <p><span class="warning-icon">⚠️</span><strong>Warning:</strong> Deleting your account will permanently remove:</p>
                <ul>
                    <li>All your event data and guest lists</li>
                    <li>Your profile information and settings</li>
                    <li>All associated data and history</li>
                    <li>Access to all Invaro services</li>
                </ul>
            </div>

            <p>If you want to proceed with deleting your account, click the button below:</p>

            <div style="text-align: center;">
                <a href="{{ route('account.delete.confirm', $deletionRequest->token) }}" class="button">
                    Confirm Account Deletion
                </a>
            </div>

            <div class="expiry-notice">
                <strong>⏰ Important:</strong> This confirmation link will expire in 24 hours for security reasons.
            </div>

            <p>If you did not request this account deletion, please:</p>
            <ul>
                <li>Ignore this email</li>
                <li>Consider changing your password if you suspect unauthorized access</li>
                <li>Contact our support team if you have concerns</li>
            </ul>

            <p>If you change your mind and want to keep your account, simply ignore this email and your account will remain active.</p>
        </div>

        <div class="footer">
            <p>This email was sent to {{ $deletionRequest->user->email }}</p>
            <p>If you have any questions, please contact us at <a href="mailto:support@invaro.com">support@invaro.com</a></p>
            <p>&copy; {{ date('Y') }} Invaro. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
