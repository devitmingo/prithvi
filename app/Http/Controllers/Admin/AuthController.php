<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Masters\State;
use App\Models\Masters\City;

class AuthController extends Controller
{
    /**
     * Show admin login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (in_array(Auth::user()->role, ['Superadmin', 'Agent'])) {
                return redirect()->route('admin.dashboard');
            }
        }

        // Ensure captcha code is generated in session
        if (!session()->has('admin_login_captcha')) {
            $code = $this->generateCaptchaCode();
            session(['admin_login_captcha' => $code]);
        }

        return view('admin.auth.login');
    }

    /**
     * Generate visual SVG captcha image.
     */
    public function getCaptcha(Request $request)
    {
        $code = $this->generateCaptchaCode();
        session(['admin_login_captcha' => $code]);

        $svg = $this->renderCaptchaSvg($code);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }

    /**
     * Refresh captcha code via AJAX or direct request.
     */
    public function refreshCaptcha(Request $request)
    {
        $code = $this->generateCaptchaCode();
        session(['admin_login_captcha' => $code]);

        return response()->json([
            'status' => 'success',
            'timestamp' => time(),
        ]);
    }

    /**
     * Handle login submission with Rate Limiting, Captcha Verification, and Remember Me.
     */
    public function login(Request $request)
    {
        $loginInput = trim((string) $request->input('login', ''));
        $throttleKey = 'admin_login_lockout|' . $request->ip() . '|' . Str::lower($loginInput);

        // 1. Brute Force & Rate Limit Protection (5 attempts per minute)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => "Too many failed sign-in attempts. Account access is locked for {$seconds} seconds for security.",
            ]);
        }

        // 2. Strict Input Validation
        $request->validate([
            'login' => 'required|string|max:191',
            'password' => 'required|string|max:255',
            'captcha' => 'required|string|max:10',
        ], [
            'login.required' => 'Please enter your registered email address or mobile number.',
            'password.required' => 'Please enter your password.',
            'captcha.required' => 'Please enter the security verification captcha.',
        ]);

        // 3. Captcha Security Verification
        $expectedCaptcha = (string) session('admin_login_captcha');
        $enteredCaptcha = trim((string) $request->input('captcha'));

        if (empty($expectedCaptcha) || strcasecmp($enteredCaptcha, $expectedCaptcha) !== 0) {
            RateLimiter::hit($throttleKey, 60);
            // Regenerate captcha on failure to prevent replay
            $newCode = $this->generateCaptchaCode();
            session(['admin_login_captcha' => $newCode]);

            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'captcha' => 'Invalid security captcha code. Please enter the new code displayed.',
            ]);
        }

        // 4. Safe Credential Identification
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        $credentials = [
            $isEmail ? 'email' : 'mobile' => $loginInput,
            'password' => (string) $request->input('password'),
        ];

        // Parameterized Eloquent Query (Immune to SQL Injection)
        $user = User::where($isEmail ? 'email' : 'mobile', $loginInput)->first();

        if (!$user) {
            RateLimiter::hit($throttleKey, 60);
            session(['admin_login_captcha' => $this->generateCaptchaCode()]);
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'No account found matching these credentials.',
            ]);
        }

        // 5. Role Authorization Check (Superadmin Only)
        if ($user->role !== 'Superadmin') {
            RateLimiter::hit($throttleKey, 60);
            session(['admin_login_captcha' => $this->generateCaptchaCode()]);
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'Access denied. This portal is strictly restricted to Super Administrators only.',
            ]);
        }

        // 6. Account Status Verifications
        if ((int)$user->status === 3) {
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'Your account has been deactivated. Please contact the administrator.',
            ]);
        }

        if ((int)$user->status === 0) {
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'Your account is pending approval by the administrator.',
            ]);
        }

        if ((int)$user->status === 2) {
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'Your account application has been rejected: ' . ($user->rejected_reason ?? 'Contact support.'),
            ]);
        }

        // 7. Remember Me Check
        $remember = $request->boolean('remember');

        // 8. Attempt Authentication
        if (Auth::attempt($credentials, $remember)) {
            // Clear rate limiting on successful login
            RateLimiter::clear($throttleKey);

            // Invalidate single-use captcha
            session()->forget('admin_login_captcha');

            // Regenerate session ID to prevent Session Fixation attacks
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Password failed
        RateLimiter::hit($throttleKey, 60);
        session(['admin_login_captcha' => $this->generateCaptchaCode()]);

        return back()->withInput($request->only('login', 'remember'))->withErrors([
            'password' => 'The provided password does not match our records.',
        ]);
    }

    /**
     * Generate 5-character high-contrast alphanumeric captcha string.
     */
    protected function generateCaptchaCode(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';
        $len = strlen($chars);
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, $len - 1)];
        }
        return $code;
    }

    /**
     * Render SVG captcha image in memory.
     */
    protected function renderCaptchaSvg(string $code): string
    {
        $width = 160;
        $height = 46;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg .= '<rect width="100%" height="100%" fill="#0a0f1d" rx="10"/>';

        // Background security grid lines
        for ($x = 12; $x < $width; $x += 18) {
            $svg .= '<line x1="' . $x . '" y1="0" x2="' . $x . '" y2="' . $height . '" stroke="#1e293b" stroke-width="1" stroke-opacity="0.6"/>';
        }
        for ($y = 8; $y < $height; $y += 12) {
            $svg .= '<line x1="0" y1="' . $y . '" x2="' . $width . '" y2="' . $y . '" stroke="#1e293b" stroke-width="1" stroke-opacity="0.6"/>';
        }

        // Curved security splines
        $svg .= '<path d="M 8 24 Q 45 6 85 24 T 152 20" fill="none" stroke="#38bdf8" stroke-width="1.6" stroke-opacity="0.45"/>';
        $svg .= '<path d="M 10 34 Q 55 44 95 18 T 150 32" fill="none" stroke="#34d399" stroke-width="1.6" stroke-opacity="0.4"/>';

        // Noise dots
        for ($d = 0; $d < 25; $d++) {
            $dx = random_int(5, $width - 5);
            $dy = random_int(5, $height - 5);
            $r = random_int(1, 2);
            $svg .= '<circle cx="' . $dx . '" cy="' . $dy . '" r="' . $r . '" fill="#64748b" opacity="0.35"/>';
        }

        // Render characters with slight rotation & vibrant colors
        $colors = ['#38bdf8', '#34d399', '#fbbf24', '#f472b6', '#a78bfa', '#60a5fa'];
        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $char = $code[$i];
            $charX = 20 + ($i * 26);
            $charY = 32 + random_int(-2, 2);
            $rot = random_int(-14, 14);
            $col = $colors[$i % count($colors)];
            $svg .= '<text x="' . $charX . '" y="' . $charY . '" fill="' . $col . '" font-family="Courier, monospace, sans-serif" font-size="23" font-weight="900" transform="rotate(' . $rot . ', ' . $charX . ', ' . $charY . ')">' . $char . '</text>';
        }

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been successfully logged out.');
    }

    /**
     * Show profile page.
     */
    public function profile()
    {
        $user = Auth::user()->load(['state', 'city', 'designation']);
        $states = State::where('status', 1)->orderBy('name')->get();
        $cities = City::where('status', 1)->orderBy('name')->get();

        return view('admin.profile', compact('user', 'states', 'cities'));
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:70',
            'email' => 'required|email|max:70|unique:users,email,' . $user->id,
            'mobile' => 'required|digits:10|unique:users,mobile,' . $user->id,
            'address' => 'nullable|string',
            'pin_code' => 'nullable|digits:6',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'company_name' => 'nullable|string|max:255',
            'gst_no' => 'nullable|string|max:15',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $data = $request->only([
            'name', 'email', 'mobile', 'address', 'pin_code', 'state_id', 'city_id',
            'company_name', 'gst_no'
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                deleteFile($user->profile_photo, 'users/');
            }
            $data['profile_photo'] = upload($request->file('profile_photo'), 'users/');
        }

        if ($request->hasFile('company_logo')) {
            if ($user->company_logo) {
                deleteFile($user->company_logo, 'users/');
            }
            $data['company_logo'] = upload($request->file('company_logo'), 'users/');
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
