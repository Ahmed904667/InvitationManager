<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Shared\Models\Notification;

class TwilioWebhookController extends Controller
{
    public function handleStatusCallback(Request $request)
    {
        Log::info('Twilio webhook received', ['data' => $request->all()]);

        $messageSid = $request->input('MessageSid');
        $messageStatus = $request->input('MessageStatus');
        $errorCode = $request->input('ErrorCode');
        $errorMessage = $request->input('ErrorMessage');

        if (!$messageSid) {
            Log::warning('Twilio webhook missing MessageSid');
            return response('OK', 200);
        }

        $notification = Notification::where('external_id', $messageSid)->first();

        if (!$notification) {
            Log::warning('Notification not found for message_sid', [
                'message_sid' => $messageSid,
                'status' => $messageStatus
            ]);
            return response('OK', 200);
        }

        // Log the notification found and status update
        Log::info('Notification found for webhook', [
            'notification_id' => $notification->id,
            'current_status' => $notification->status,
            'twilio_status' => $messageStatus,
            'message_sid' => $messageSid
        ]);
        
        $this->updateNotificationStatus($notification, $messageStatus, $errorCode, $errorMessage);

        return response('OK', 200);
    }

    private function updateNotificationStatus($notification, $status, $errorCode = null, $errorMessage = null)
    {
        $deliveryDetails = [
            'twilio_status' => $status,
            'updated_at' => now()->toISOString()
        ];

        // Log the status update for debugging
        Log::info('Updating notification status', [
            'notification_id' => $notification->id,
            'message_sid' => $notification->external_id,
            'old_status' => $notification->status,
            'new_twilio_status' => $status,
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ]);

        switch (strtolower($status)) {
            case 'queued':
            case 'sending':
            case 'accepted':
            case 'scheduled':
            case 'partially_delivered':
            case 'receiving':
            case 'received':
                // Map intermediate statuses to 'queued'
                $notification->markAsQueued($deliveryDetails);
                break;
            case 'sent':
                // Map 'sent' to 'delivered' since it means message was accepted and sent
                $notification->markAsDelivered($notification->external_id, $deliveryDetails);
                break;
                
            case 'delivered':
                // Message delivered to recipient
                $notification->markAsDelivered($notification->external_id, $deliveryDetails);
                break;
                
            case 'read':
                // Message delivered and read by recipient (only for WhatsApp)
                if ($notification->channel === 'whatsapp') {
                    $notification->markAsRead($deliveryDetails);
                } else {
                    // For non-WhatsApp channels (like email), just update delivery details
                    $notification->updateDeliveryDetails($deliveryDetails);
                }
                break;
                
            case 'failed':
            case 'undelivered':
            case 'canceled':
            case 'bounced':
                // Map all failure statuses to 'failed'
                $errorDetails = $errorMessage ?: 'Message delivery failed';
                if ($errorCode) {
                    $errorDetails .= " (Code: {$errorCode})";
                }
                $notification->markAsFailed($errorDetails, $deliveryDetails);
                break;
                
            default:
                // Handle any other statuses by mapping to 'queued'
                Log::info('Unknown Twilio status received, mapping to queued', [
                    'status' => $status,
                    'notification_id' => $notification->id
                ]);
                $notification->markAsQueued($deliveryDetails);
                break;
        }
        
        // Log the final notification status
        Log::info('Notification status updated', [
            'notification_id' => $notification->id,
            'final_status' => $notification->status,
            'twilio_status' => $status
        ]);
    }
}
