<?php

namespace App\Http\Controllers;

use App\Shared\Models\User;
use App\Shared\Models\OTP;
use App\PasswordResetToken;
use App\Mail\PasswordResetMail;
use App\Mail\OTPMail;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Redirect based on user role
            $user = Auth::user();
            return redirect()->intended($this->getDashboardRoute($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'credential' => 'required|string'
        ]);

        $idToken = $request->credential;
        $payload = $this->verifyGoogleToken($idToken);

        if ($payload && isset($payload['email'])) {
            $email = $payload['email'];
            $name = $payload['name'] ?? $email;

            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(bin2hex(random_bytes(8))),
                    'role' => 'organizer' // Default role for new users
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            $redirectUrl = $this->getDashboardRoute($user);
            
            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $redirectUrl
                ]);
            }

            return redirect()->intended($redirectUrl);
        }

        // Handle AJAX requests for errors
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in failed. Please try again.'
            ], 422);
        }

        return back()->withErrors([
            'google' => 'Google sign-in failed.'
        ]);
    }

    public function googleRedirect(Request $request)
    {
        if ($request->has('guest_list_id')) {
            session(['google_guest_list_id' => $request->guest_list_id]);
        }
        // Store intended modal (contacts or sheets)
        if ($request->has('modal')) {
            session(['google_import_modal' => $request->modal]);
        }
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/contacts.readonly',
                'https://www.googleapis.com/auth/drive.readonly',
                'https://www.googleapis.com/auth/spreadsheets.readonly'
            ])
            ->redirect();
    }

    public function googleCallback(Request $request)
    {
        $user = Socialite::driver('google')->user();
        session(['google_token' => $user->token]);

        $guestListId = session('google_guest_list_id');
        $modal = session('google_import_modal');
        session()->forget(['google_guest_list_id', 'google_import_modal']);

        if ($guestListId) {
            $params = ['guestList' => $guestListId];
            if ($modal === 'contacts') {
                $params['import_google_contacts'] = 1;
            } elseif ($modal === 'sheets') {
                $params['import_google_sheets'] = 1;
            }
            return redirect()->route('organizer.guest-lists.edit', $params)
                ->with('success', 'Google account connected!');
        }

        return redirect()->route('organizer.guest-lists.index')
            ->with('success', 'Google account connected!');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', new StrongPassword]
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Store registration data in session for OTP verification
        session([
            'registration_data' => [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]
        ]);

        // Redirect to OTP verification
        return redirect()->route('register.verify-otp');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get the appropriate dashboard route based on user role
     */
    private function getDashboardRoute(User $user)
    {
        switch ($user->role) {
            case 'admin':
                return '/admin';
            case 'organizer':
                return '/organizer';
            case 'scanner':
                return '/scanner';
            default:
                return '/scanner'; // Default fallback
        }
    }

    /**
     * Show the forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.'])->withInput();
        }

        try {
            // Generate reset token
            $token = PasswordResetToken::generateToken($email);
            
            // Create reset URL
            $resetUrl = route('password.reset', ['token' => $token]);
            
            // Send email
            Mail::to($email)->send(new PasswordResetMail(
                $resetUrl,
                $user->name,
                now()->addHours(24)->format('F j, Y \a\t g:i A')
            ));
            
            return back()->with('status', 'Password reset link sent to your email address!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send password reset link. Please try again.'])->withInput();
        }
    }

    /**
     * Show the reset password form
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', compact('token'));
    }

    /**
     * Reset the user's password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'string', 'confirmed', new StrongPassword]
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $token = $request->token;
        $password = $request->password;

        // Validate token
        if (!PasswordResetToken::validateToken($email, $token)) {
            return back()->withErrors(['token' => 'Invalid or expired password reset link.'])->withInput();
        }

        // Update password
        $user = User::where('email', $email)->first();
        $user->update([
            'password' => Hash::make($password)
        ]);

        // Delete the used token
        PasswordResetToken::deleteToken($email);

        return redirect()->route('login')->with('status', 'Password updated successfully! You can now log in with your new password.');
    }

    /**
     * Send OTP for registration
     */
    public function sendRegistrationOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255'
        ], [
            'email.unique' => 'This email address is already registered. Please use a different email or try logging in instead.'
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
        $name = $request->name;

        try {
            // Generate OTP for registration (using a temporary user ID of 0)
            $otpCode = OTP::generateOTP(0, 'registration', $email);
            
            // Send OTP via email
            Mail::to($email)->send(new OTPMail($otpCode, $name, 'email'));
            
            // Store email in session for verification
            session(['registration_email' => $email, 'registration_name' => $name]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent to your email address! Please check your inbox.'
                ]);
            }
            
            return back()->with('status', 'OTP sent to your email address! Please check your inbox.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ], 500);
            }
            
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again.'])->withInput();
        }
    }

    /**
     * Show OTP verification form
     */
    public function showVerifyOTP()
    {
        // Check if we have registration data in session
        if (!session('registration_data') && !session('registration_email')) {
            return redirect()->route('register')->withErrors(['error' => 'Please complete registration first.']);
        }

        $email = session('registration_email') ?? session('registration_data.email');
        
        return view('auth.verify-otp', compact('email'));
    }

    /**
     * Verify OTP and complete registration
     */
    public function verifyRegistrationOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp_code' => 'required|string|size:6',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'password' => ['required', 'string', 'confirmed', new StrongPassword]
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
        $name = $request->name;
        $password = $request->password;

        // Check if email already exists (in case it was registered between OTP send and verification)
        if (User::where('email', $email)->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email address is already registered. Please use a different email or try logging in instead.'
                ], 422);
            }
            return back()->withErrors(['email' => 'This email address is already registered. Please use a different email or try logging in instead.'])->withInput();
        }

        // Validate OTP
        if (!OTP::validateOTP(0, 'registration', $email, $otpCode)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP code.'
                ], 422);
            }
            return back()->withErrors(['otp_code' => 'Invalid or expired OTP code.'])->withInput();
        }

        try {
            // Create user account
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'organizer', // Default role for new users
                'email_verified_at' => now() // Mark email as verified
            ]);

            // Clear any session data
            session()->forget(['registration_data', 'registration_email', 'registration_name']);

            // Login the user
            Auth::login($user);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account created successfully! Welcome to Invaro!',
                    'redirect_url' => $this->getDashboardRoute($user)
                ]);
            }

            return redirect($this->getDashboardRoute($user))->with('success', 'Account created successfully! Welcome to Invaro!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create account. Please try again.'
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to create account. Please try again.'])->withInput();
        }
    }

    private function verifyGoogleToken($idToken)
    {
        try {
            $clientId = config('services.google.client_id');
            $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
            
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                \Log::error('Google token verification failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
            
            $payload = $response->json();
            
            if (isset($payload['aud']) && $payload['aud'] === $clientId && isset($payload['email'])) {
                return $payload;
            }
            
            \Log::warning('Google token verification failed - invalid payload', [
                'aud' => $payload['aud'] ?? 'missing',
                'client_id' => $clientId,
                'has_email' => isset($payload['email'])
            ]);
            
            return false;
        } catch (\Exception $e) {
            \Log::error('Google token verification exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
