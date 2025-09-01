# Webhook Setup Guide

## Overview

The application uses Twilio webhooks to automatically update notification statuses. However, Twilio doesn't accept `localhost` URLs for webhooks, so different approaches are needed for different environments.

## Environment Setup

### Production Environment

1. **Set up a public webhook URL** in your `.env` file:
   ```
   TWILIO_WEBHOOK_URL=https://yourdomain.com/webhooks/twilio/status
   ```

2. **Ensure your domain is publicly accessible** and the webhook endpoint is reachable.

### Local Development

Since Twilio doesn't accept `localhost` URLs, webhooks are automatically disabled in local development. The system will:

1. **Send messages without webhooks** (messages will still be sent successfully)
2. **Create notification records** with initial status
3. **Require manual status updates** for testing

## Manual Status Updates (Local Development)

### List Notifications
```bash
php artisan notifications:list
```

### Update Notification Status
```bash
php artisan notifications:update-status {notification_id} {status}
```

Example:
```bash
php artisan notifications:update-status 123 delivered
```

### Available Statuses
- `queued` - Message is queued for delivery
- `sent` - Message was sent to Twilio
- `delivered` - Message was delivered to recipient
- `read` - Message was read by recipient (WhatsApp only)
- `failed` - Message delivery failed
- `undelivered` - Message could not be delivered
- `canceled` - Message was canceled

## Testing Webhooks

### Using ngrok (Recommended for Local Testing)

1. **Install ngrok** and expose your local server:
   ```bash
   ngrok http 8000
   ```

2. **Set the webhook URL** in your `.env`:
   ```
   TWILIO_WEBHOOK_URL=https://your-ngrok-url.ngrok.io/webhooks/twilio/status
   ```

3. **Test webhooks** by sending messages and checking if statuses update automatically.

### Using the Test Route

The application includes a test route for simulating webhook calls:

```
GET /test/webhook/{notification_id}/{status}
```

Example:
```
GET /test/webhook/123/delivered
```

This will simulate a Twilio webhook and update the notification status.

## Troubleshooting

### Common Issues

1. **"StatusCallback URL is not a valid URL"**
   - **Cause**: Using localhost URL
   - **Solution**: Use ngrok or set `TWILIO_WEBHOOK_URL` to a public URL

2. **Webhooks not updating statuses**
   - **Cause**: Webhook endpoint not accessible
   - **Solution**: Check if the webhook URL is publicly accessible

3. **Notifications stuck in "queued" status**
   - **Cause**: Webhooks disabled in local development
   - **Solution**: Use manual status updates or set up ngrok

### Debugging

1. **Check webhook logs** in `storage/logs/laravel.log`
2. **List notifications** with `php artisan notifications:list`
3. **Test webhook manually** with the test route
4. **Check Twilio console** for webhook delivery status

## Commands Reference

| Command | Description |
|---------|-------------|
| `notifications:list` | List all notifications with their statuses |
| `notifications:update-status {id} {status}` | Manually update notification status |
| `notifications:refresh` | Refresh all notification statuses from Twilio |

## Configuration

### Environment Variables

```env
# Twilio Configuration
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_WHATSAPP_FROM=whatsapp:+1234567890

# Webhook URL (optional - auto-detected if not set)
TWILIO_WEBHOOK_URL=https://yourdomain.com/webhooks/twilio/status
```

### Automatic Detection

The system automatically detects localhost environments and disables webhooks to prevent Twilio errors. This allows development to continue without webhook issues.
