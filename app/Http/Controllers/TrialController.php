<?php

namespace App\Http\Controllers;

use App\Mail\TrialRequestMail;
use App\Trial;
use App\Services\InvitationMessageService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TrialController extends Controller
{
    public function store(Request $request)
    {
        // Robust server-side validation
        $validator = Validator::make($request->all(), [
            'contact' => ['required', 'string', 'max:255', function($attribute, $value, $fail) {
                if (strpos($value, '@') !== false) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Please enter a valid email address.');
                    }
                } else {
                    $clean = preg_replace('/[\s\-\(\)]/', '', $value);
                    if (!preg_match('/^\+\d{10,15}$/', $clean)) {
                        $fail('Please enter a valid phone number with country code (e.g., +1234567890).');
                    }
                }
            }],
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}\s\-\'\.]+$/u'],
            'event_type' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[\p{L}\s\-\'\.\d]+$/u'],
        ], [
            'name.regex' => 'Name contains invalid characters.',
            'event_type.regex' => 'Event type contains invalid characters.',
        ]);

        if ($validator->fails()) {
            $allErrors = $validator->errors()->all();
            $firstError = $allErrors[0] ?? 'Validation error.';
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => [$firstError]
            ], 422);
        }

        // Limit: 3 trials per IP per day
        $ip = $request->ip();
        $today = now()->startOfDay();
        $trialCount = \App\Trial::where('ip_address', $ip)
            ->where('created_at', '>=', $today)
            ->where('status', 'pending') // or whatever status means 'success' in your app
            ->count();

        Log::info('Trial count for IP', ['ip' => $ip, 'count' => $trialCount]);
        
        if ($trialCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum number of trial requests 😔. You can try again tomorrow 😁.',
                'errors' => ['You have reached the maximum number of trial requests 😔.']
            ], 429);
        }

        try {
            $invitationMessageService = new InvitationMessageService();
            $result = $invitationMessageService->generateInvitationMessage(
                $request->contact,
                $request->name,
                $request->event_type
            );

            if (!$result['success']) {
                return response()->json($result, 422);
            }

            $trial = Trial::create([
                'contact' => $request->contact,
                'name' => $request->name,
                'event_type' => $request->event_type,
                'contact_method' => $result['contact_method'],
                'invitation_message' => $result['invitation_message'],
                'status' => 'pending',
                'ip_address' => $ip,
                'user_agent' => $request->userAgent() 
            ]);

            if ($result['contact_method'] === 'email') {
                Mail::to($request->contact)->send(new TrialRequestMail(
                    $request->name,
                    $request->event_type,
                    $request->contact,
                    $result['invitation_message'],
                    $result['subject'] ?? null
                ));
                // Update status to message_sent after email sent
                $trial->status = 'message_sent';
                $trial->save();
            } else {
                $twilioService = new TwilioService();
                $whatsappSent = $twilioService->sendWhatsAppMessage(
                    $request->contact,
                    $result['invitation_message']
                );
                if ($whatsappSent) {
                    // Update status to message_sent after WhatsApp sent
                    $trial->status = 'message_sent';
                    $trial->save();
                } else {
                    Log::error('Failed to send WhatsApp message', [
                        'contact' => $request->contact,
                        'message' => $result['invitation_message']
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Your trial request has been sent successfully!',
                'contact_method' => $result['contact_method'],
                'ai_service' => $result['ai_service']
            ]);

        } catch (\Exception $e) {
            Log::error('Trial request failed', [
                'error' => $e->getMessage(),
                'contact' => $request->contact ?? null
            ]);

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred. Please try again.']
            ], 500);
        }
    }
} 