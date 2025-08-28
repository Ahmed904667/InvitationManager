<?php

namespace App\Http\Controllers;

use App\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        if (Auth::attempt($credentials)) {
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
                    'role' => 'scanner' // Default role for new users
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended($this->getDashboardRoute($user));
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
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'scanner' // Default role for new users
        ]);

        Auth::login($user);

        return redirect($this->getDashboardRoute($user));
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

    private function verifyGoogleToken($idToken)
    {
        $clientId = config('services.google.client_id');
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
        
        $response = @file_get_contents($url);
        if ($response === false) return false;
        
        $payload = json_decode($response, true);
        
        if (isset($payload['aud']) && $payload['aud'] === $clientId && isset($payload['email'])) {
            return $payload;
        }
        
        return false;
    }
}
