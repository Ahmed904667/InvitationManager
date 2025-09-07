<?php

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\OTP;
use App\PasswordResetToken;
use App\Services\TwilioService;
use App\Mail\OTPMail;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected TwilioService $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function show()
    {
        $user = auth()->user();
        return view('organizer.profile.show', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('organizer.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->update($request->only(['name', 'bio']));

        return redirect()->route('organizer.profile.show')->with('success', 'Profile updated successfully!');
    }

    public function updateEmail(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $newEmail = $request->email;
        
        // Check if email is different from current
        if ($newEmail === $user->email) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'New email must be different from current email'
                ], 422);
            }
            return back()->withErrors(['email' => 'New email must be different from current email'])->withInput();
        }

        // Clean up any existing OTPs for this user and email type
        OTP::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('identifier', $newEmail)
            ->delete();

        try {
            // Generate OTP
            $otpCode = OTP::generateOTP($user->id, 'email', $newEmail);
            
            // Send OTP via email
            Mail::to($newEmail)->send(new OTPMail($otpCode, $user->name, 'email'));
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent to your email successfully!',
                    'requires_verification' => true
                ]);
            }

            return back()->with('success', 'OTP sent to your email. Please check and verify.');
        } catch (\Exception $e) {
            // If email fails, delete the OTP
            OTP::where('user_id', $user->id)
                ->where('type', 'email')
                ->where('identifier', $newEmail)
                ->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ], 500);
            }

            return back()->withErrors(['email' => 'Failed to send OTP. Please try again.'])->withInput();
        }
    }

    public function verifyEmailOTP(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $otpCode = $request->otp_code;

        // Validate OTP
        if (OTP::validateOTP($user->id, 'email', $email, $otpCode)) {
            // Update email
            $user->update(['email' => $email]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email updated successfully!'
                ]);
            }
            
            return redirect()->route('organizer.profile.show')->with('success', 'Email updated successfully!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code'
            ], 422);
        }

        return back()->withErrors(['otp_code' => 'Invalid or expired OTP code'])->withInput();
    }

    public function updatePhone(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:30'],
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $newPhone = $request->phone;
        
        // Check if phone is different from current
        if ($newPhone === $user->phone) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'New phone must be different from current phone'
                ], 422);
            }
            return back()->withErrors(['phone' => 'New phone must be different from current phone'])->withInput();
        }

        // Clean up any existing OTPs for this user and phone type
        OTP::where('user_id', $user->id)
            ->where('type', 'phone')
            ->where('identifier', $newPhone)
            ->delete();

        try {
            // Generate OTP
            $otpCode = OTP::generateOTP($user->id, 'phone', $newPhone);
            
            // Send OTP via WhatsApp
            $message = "🔐 *Invaro Verification Code*\n\nHello {$user->name},\n\nYour verification code is: *{$otpCode}*\n\nThis code is valid for 10 minutes.\n\nIf you didn't request this code, please ignore this message.\n\nBest regards,\nInvaro Team";
            
            $result = $this->twilioService->sendWhatsAppMessage($newPhone, $message);
            
            if ($result['success']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'OTP sent to your WhatsApp successfully!',
                        'requires_verification' => true
                    ]);
                }

                return back()->with('success', 'OTP sent to your WhatsApp. Please check and verify.');
            } else {
                // If WhatsApp fails, delete the OTP
                OTP::where('user_id', $user->id)
                    ->where('type', 'phone')
                    ->where('identifier', $newPhone)
                    ->delete();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to send OTP via WhatsApp: ' . ($result['error'] ?? 'Unknown error')
                    ], 500);
                }

                return back()->withErrors(['phone' => 'Failed to send OTP via WhatsApp. Please try again.'])->withInput();
            }
        } catch (\Exception $e) {
            // If WhatsApp fails, delete the OTP
            OTP::where('user_id', $user->id)
                ->where('type', 'phone')
                ->where('identifier', $newPhone)
                ->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ], 500);
            }

            return back()->withErrors(['phone' => 'Failed to send OTP. Please try again.'])->withInput();
        }
    }

    public function verifyPhoneOTP(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:30'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $phone = $request->phone;
        $otpCode = $request->otp_code;

        // Validate OTP
        if (OTP::validateOTP($user->id, 'phone', $phone, $otpCode)) {
            // Update phone
            $user->update(['phone' => $phone]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phone updated successfully!'
                ]);
            }
            
            return redirect()->route('organizer.profile.show')->with('success', 'Phone updated successfully!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code'
            ], 422);
        }

        return back()->withErrors(['otp_code' => 'Invalid or expired OTP code'])->withInput();
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('organizer.profile.show')->with('success', 'Password updated successfully!');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $user = auth()->user();
        
        try {
            // Generate reset token
            $token = PasswordResetToken::generateToken($user->email);
            
            // Create reset URL
            $resetUrl = route('organizer.profile.password.reset', ['token' => $token]);
            
            // Send email
            Mail::to($user->email)->send(new PasswordResetMail(
                $resetUrl,
                $user->name,
                now()->addHours(24)->format('F j, Y \a\t g:i A')
            ));
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset link sent to your email successfully!'
                ]);
            }
            
            return back()->with('success', 'Password reset link sent to your email successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send password reset link. Please try again.'
                ], 500);
            }
            
            return back()->withErrors(['password' => 'Failed to send password reset link. Please try again.'])->withInput();
        }
    }

    public function showPasswordResetForm($token)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        
        // Validate token
        if (!PasswordResetToken::validateToken($user->email, $token)) {
            return redirect()->route('organizer.profile.edit')->withErrors(['password' => 'Invalid or expired password reset link.']);
        }
        
        return view('organizer.profile.password-reset', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate token
        if (!PasswordResetToken::validateToken($user->email, $request->token)) {
            return back()->withErrors(['password' => 'Invalid or expired password reset link.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete the used token
        PasswordResetToken::deleteToken($user->email);

        return redirect()->route('organizer.profile.show')->with('success', 'Password updated successfully!');
    }

    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // Increased max size for cropped images
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $user = auth()->user();

            // Delete old photo if exists
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            
            $user->update(['profile_photo_path' => $path]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile photo updated successfully!',
                    'photo_url' => $user->fresh()->profile_photo_url
                ]);
            }

            return redirect()->route('organizer.profile.show')->with('success', 'Profile photo updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error uploading photo: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['profile_photo' => 'Error uploading photo. Please try again.'])->withInput();
        }
    }

    public function deletePhoto()
    {
        $user = auth()->user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->update(['profile_photo_path' => null]);

        return redirect()->route('organizer.profile.show')->with('success', 'Profile photo removed successfully!');
    }
}
