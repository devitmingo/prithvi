<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\UserForgotPassword;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /**
     * Display password reset requests.
     */
    public function index(Request $request)
    {
        $query = UserForgotPassword::with(['user.designation', 'user.company', 'user.city']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to showing pending first
            $query->orderByRaw('CASE WHEN status = 1 THEN 0 ELSE 1 END');
        }

        $requests = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $pendingCount = UserForgotPassword::where('status', 1)->count();
        $approvedCount = UserForgotPassword::where('status', 2)->count();
        $rejectedCount = UserForgotPassword::where('status', 3)->count();

        return view('admin.forgot_passwords.index', compact('requests', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    /**
     * Reset user password and approve request.
     */
    public function resetPassword(Request $request, UserForgotPassword $forgotPassword)
    {
        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user = $forgotPassword->user;
        if (!$user) {
            return back()->with('error', 'Associated user not found.');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $forgotPassword->update([
            'status' => 2, // Approved / Reset
        ]);

        return back()->with('success', "Password for {$user->name} ({$user->mobile}) has been reset successfully to '{$request->new_password}'.");
    }

    /**
     * Reject request.
     */
    public function reject(UserForgotPassword $forgotPassword)
    {
        $forgotPassword->update([
            'status' => 3, // Rejected
        ]);

        return back()->with('success', 'Password reset request has been rejected.');
    }
}
