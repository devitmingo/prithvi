<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AccountDeletionController extends Controller
{
    /**
     * Handle user account deletion (via Web View, Form POST, or direct GET/API).
     * Meets Google Play Store Data Safety and Account Deletion Policy requirements.
     *
     * Supports:
     * - Browser View: GET /delete-account (shows policy & interactive deletion form)
     * - Direct GET / Param: GET /delete-account?id=... or GET /delete-account/{id} or ?mobile=...
     * - Form POST: POST /delete-account
     * - API JSON: when request expects JSON or format=json
     */
    public function deleteAccount(Request $request, $id = null)
    {
        // Check if an identifier is provided via route param, query string, or POST input
        $identifier = $id 
            ?? $request->input('id') 
            ?? $request->input('user_id') 
            ?? $request->input('mobile') 
            ?? $request->input('email');

        // If no identifier provided, display the Google Play compliant deletion page
        if (empty($identifier)) {
            return view('delete-account');
        }

        // Search user by Database ID, user_id (string like AGT.../STF...), Mobile, or Email
        $user = User::where(function ($query) use ($identifier) {
            $query->where('id', $identifier)
                ->orWhere('user_id', $identifier)
                ->orWhere('mobile', $identifier)
                ->orWhere('email', $identifier);
        })->first();

        $wantsJson = $request->wantsJson() 
            || $request->is('api/*') 
            || $request->input('format') === 'json'
            || $request->ajax();

        if (!$user) {
            if ($wantsJson) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found with identifier: ' . $identifier,
                    'error' => 'User not found'
                ], 404);
            }

            return back()->with('error', 'User not found with provided ID or mobile number: ' . $identifier)
                ->withInput();
        }

        // If password is provided, verify it
        $password = $request->input('password');
        if (!empty($password) && !Hash::check($password, $user->password)) {
            if ($wantsJson) {
                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect password provided.',
                    'error' => 'Invalid password'
                ], 422);
            }

            return back()->with('error', 'Incorrect password. Account deletion aborted.')
                ->withInput();
        }

        try {
            $deletedUserInfo = [
                'id' => $user->id,
                'user_id' => $user->user_id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'role' => $user->role,
                'deleted_at' => Carbon::now()->toDateTimeString(),
            ];

            // Action type: request (90-day grace) or permanent (instant delete)
            $action = $request->input('action', 'permanent');

            if ($action === 'request') {
                // Set deletion request timestamp (90 days cooling period)
                $user->deletion_requested_at = Carbon::now();
                $user->tokens()->delete();
                $user->save();

                $scheduledDate = Carbon::now()->addDays(90)->format('d-m-Y');
                $message = "Account deletion requested successfully for {$user->name} ({$user->mobile}). All records will be permanently purged after 90 days ({$scheduledDate}).";

                if ($wantsJson) {
                    return response()->json([
                        'status' => true,
                        'message' => $message,
                        'data' => [
                            'user_id' => $user->user_id,
                            'deletion_requested_at' => $user->deletion_requested_at,
                            'scheduled_deletion_date' => $scheduledDate,
                        ]
                    ], 200);
                }

                return view('delete-account', [
                    'successMessage' => $message,
                    'deletedUser' => $deletedUserInfo,
                    'isRequest' => true,
                ]);
            }

            // Permanent Deletion: wipe user and all associated photos, KYC documents, attendances, call histories & records
            $user->deleteWithAllRecords();

            Log::info('User deleted via /delete-account URL: ', $deletedUserInfo);

            $message = "User '{$deletedUserInfo['name']}' (ID: {$deletedUserInfo['user_id']}, Mobile: {$deletedUserInfo['mobile']}) and all associated files/data have been permanently deleted successfully.";

            if ($wantsJson) {
                return response()->json([
                    'status' => true,
                    'message' => $message,
                    'data' => $deletedUserInfo,
                ], 200);
            }

            return view('delete-account', [
                'successMessage' => $message,
                'deletedUser' => $deletedUserInfo,
                'isRequest' => false,
            ]);

        } catch (\Exception $ex) {
            Log::error('Error in /delete-account handler: ', [
                'exception' => $ex->getMessage(),
                'line' => $ex->getLine(),
                'file' => $ex->getFile(),
            ]);

            if ($wantsJson) {
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while processing account deletion: ' . $ex->getMessage(),
                    'error' => 'Server Error'
                ], 500);
            }

            return back()->with('error', 'An error occurred while processing deletion: ' . $ex->getMessage());
        }
    }

    /**
     * API endpoint handler for account deletion.
     */
    public function deleteAccountApi(Request $request)
    {
        return $this->deleteAccount($request);
    }
}
