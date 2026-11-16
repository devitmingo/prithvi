<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        return view('admin.auth.login');
    }

    /**
     * Handle login submission.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('login');
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        $credentials = [
            $isEmail ? 'email' : 'mobile' => $loginInput,
            'password' => $request->input('password'),
        ];

        $user = User::where($isEmail ? 'email' : 'mobile', $loginInput)->first();

        if (!$user) {
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'No account found matching these credentials.',
            ]);
        }

        // Only Superadmin and Agent can access the admin panel
        if (!in_array($user->role, ['Superadmin', 'Agent'])) {
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'Access denied. You do not have administrator permissions.',
            ]);
        }

        // Account status check
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

        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withInput($request->only('login', 'remember'))->withErrors([
            'password' => 'The provided password does not match our records.',
        ]);
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
