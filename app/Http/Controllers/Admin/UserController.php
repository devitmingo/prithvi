<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Masters\State;
use App\Models\Masters\City;
use App\Models\Masters\Designation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $query = User::with(['state', 'city', 'designation', 'company'])->withCount('subAgents');

        if (!$isSuperAdmin) {
            // Agent sees only their sub-agents
            $query->where('company_id', $currentUser->id);
        } else {
            // Superadmin can filter by agent/company
            if ($request->filled('agent_id')) {
                $query->where(function ($q) use ($request) {
                    $q->where('id', $request->agent_id)
                      ->orWhere('company_id', $request->agent_id);
                });
            }
        }

        // Tab / Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('agent_code', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Count badges for tabs
        $baseCountQuery = User::query();
        if (!$isSuperAdmin) {
            $baseCountQuery->where('company_id', $currentUser->id);
        }

        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'superadmin' => $isSuperAdmin ? (clone $baseCountQuery)->where('role', 'Superadmin')->count() : 0,
            'agent' => $isSuperAdmin ? (clone $baseCountQuery)->where('role', 'Agent')->count() : 0,
            'sub_agent' => (clone $baseCountQuery)->where('role', 'Sub Agent')->count(),
            'pending' => (clone $baseCountQuery)->where('status', 0)->count(),
            'rejected' => (clone $baseCountQuery)->where('status', 2)->count(),
            'deactivated' => (clone $baseCountQuery)->where('status', 3)->count(),
        ];

        $allAgents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.users.index', compact('users', 'counts', 'allAgents', 'isSuperAdmin'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $states = State::where('status', 1)->orderBy('name')->get();
        $cities = City::where('status', 1)->orderBy('name')->get();
        $designations = Designation::where('status', 1)->orderBy('name')->get();
        $agents = $isSuperAdmin ? User::where('role', 'Agent')->where('status', 1)->orderBy('name')->get() : collect();

        return view('admin.users.create', compact('states', 'cities', 'designations', 'agents', 'isSuperAdmin'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $allowedRoles = $isSuperAdmin ? ['Superadmin', 'Agent', 'Sub Agent'] : ['Sub Agent'];

        $request->validate([
            'name' => 'required|string|max:70',
            'email' => 'required|email|max:70|unique:users,email',
            'mobile' => 'required|digits:10|unique:users,mobile',
            'password' => 'required|string|min:6',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'pin_code' => 'nullable|digits:6',
            'address' => 'nullable|string',
            'aadhar_no' => 'nullable|digits:12',
            'pan_no' => 'nullable|string|max:10',
            'company_name' => 'nullable|string|max:255',
            'gst_no' => 'nullable|string|max:15',
            'company_id' => 'nullable|exists:users,id',
            'designation_id' => 'nullable|exists:designations,id',
            'dob' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1,2,3',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'aadhar_front_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'aadhar_back_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'pan_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $role = $isSuperAdmin ? $request->role : 'Sub Agent';
        $companyId = $isSuperAdmin ? $request->company_id : $currentUser->id;

        // Generate standard prefix user_id
        $prefix = match($role) {
            'Superadmin' => 'SUP',
            'Agent' => 'AGT',
            default => 'STF',
        };
        $userId = $prefix . date('ymdHis') . rand(100, 999);

        // Generate agent_code if Sub Agent or Agent
        $agentCode = null;
        if ($role === 'Sub Agent' && $companyId) {
            $parentAgent = User::find($companyId);
            $agentCode = $parentAgent->agent_code ?? ('AGT' . date('ymdHis') . rand(100, 999));
        } elseif ($role === 'Agent') {
            $agentCode = 'AGT' . date('ymdHis') . rand(100, 999);
        }

        $data = [
            'user_id' => $userId,
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'role' => $role,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'pin_code' => $request->pin_code,
            'address' => $request->address,
            'aadhar_no' => $request->aadhar_no,
            'pan_no' => $request->pan_no,
            'company_name' => $request->company_name,
            'gst_no' => $request->gst_no,
            'company_id' => $companyId,
            'agent_code' => $agentCode,
            'designation_id' => $request->designation_id,
            'salary' => $request->salary ?? 0,
            'dob' => $request->dob,
            'status' => $request->status,
            'approved_date' => $request->status == 1 ? now()->toDateString() : null,
        ];

        // File uploads
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = upload($request->file('profile_photo'), 'users/');
        }
        if ($request->hasFile('aadhar_front_photo')) {
            $data['aadhar_front_photo'] = upload($request->file('aadhar_front_photo'), 'users/');
        }
        if ($request->hasFile('aadhar_back_photo')) {
            $data['aadhar_back_photo'] = upload($request->file('aadhar_back_photo'), 'users/');
        }
        if ($request->hasFile('pan_photo')) {
            $data['pan_photo'] = upload($request->file('pan_photo'), 'users/');
        }
        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = upload($request->file('company_logo'), 'users/');
        }

        $user = User::create($data);

        return redirect()->route('admin.users.show', $user->id)->with('success', "User {$user->name} created successfully.");
    }

    /**
     * Display user details.
     */
    public function show(User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin() && $user->company_id != $currentUser->id && $user->id != $currentUser->id) {
            abort(403, 'Unauthorized access.');
        }

        $user->load(['state', 'city', 'designation', 'company', 'subAgents']);

        // Load associated counts
        $leadsCount = $user->contacts()->count();
        $attendancesCount = $user->attendances()->count();
        $callsCount = $user->contactCallHistories()->count();
        $recentAttendances = $user->attendances()->orderBy('date', 'desc')->take(7)->get();
        $recentLeads = $user->contacts()->with('project')->orderBy('id', 'desc')->take(5)->get();

        return view('admin.users.show', compact('user', 'leadsCount', 'attendancesCount', 'callsCount', 'recentAttendances', 'recentLeads'));
    }

    /**
     * Show edit form.
     */
    public function edit(User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin() && $user->company_id != $currentUser->id && $user->id != $currentUser->id) {
            abort(403, 'Unauthorized access.');
        }

        $isSuperAdmin = $currentUser->isSuperAdmin();
        $states = State::where('status', 1)->orderBy('name')->get();
        $cities = City::where('status', 1)->orderBy('name')->get();
        $designations = Designation::where('status', 1)->orderBy('name')->get();
        $agents = $isSuperAdmin ? User::where('role', 'Agent')->where('status', 1)->orderBy('name')->get() : collect();

        return view('admin.users.edit', compact('user', 'states', 'cities', 'designations', 'agents', 'isSuperAdmin'));
    }

    /**
     * Update user in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin() && $user->company_id != $currentUser->id && $user->id != $currentUser->id) {
            abort(403, 'Unauthorized access.');
        }

        $isSuperAdmin = $currentUser->isSuperAdmin();

        $request->validate([
            'name' => 'required|string|max:70',
            'email' => 'required|email|max:70|unique:users,email,' . $user->id,
            'mobile' => 'required|digits:10|unique:users,mobile,' . $user->id,
            'password' => 'nullable|string|min:6',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'pin_code' => 'nullable|digits:6',
            'address' => 'nullable|string',
            'aadhar_no' => 'nullable|digits:12',
            'pan_no' => 'nullable|string|max:10',
            'company_name' => 'nullable|string|max:255',
            'gst_no' => 'nullable|string|max:15',
            'company_id' => 'nullable|exists:users,id',
            'designation_id' => 'nullable|exists:designations,id',
            'dob' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1,2,3',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'aadhar_front_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'aadhar_back_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'pan_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'pin_code' => $request->pin_code,
            'address' => $request->address,
            'aadhar_no' => $request->aadhar_no,
            'pan_no' => $request->pan_no,
            'company_name' => $request->company_name,
            'gst_no' => $request->gst_no,
            'designation_id' => $request->designation_id,
            'salary' => $request->salary ?? 0,
            'dob' => $request->dob,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($isSuperAdmin) {
            if ($request->filled('role')) {
                $data['role'] = $request->role;
            }
            if ($request->has('company_id')) {
                $data['company_id'] = $request->company_id;
            }
        }

        if ($request->status == 1 && !$user->approved_date) {
            $data['approved_date'] = now()->toDateString();
        }

        // Handle uploaded media
        if ($request->hasFile('profile_photo')) {
            deleteFile($user->profile_photo, 'users/');
            $data['profile_photo'] = upload($request->file('profile_photo'), 'users/');
        }
        if ($request->hasFile('aadhar_front_photo')) {
            deleteFile($user->aadhar_front_photo, 'users/');
            $data['aadhar_front_photo'] = upload($request->file('aadhar_front_photo'), 'users/');
        }
        if ($request->hasFile('aadhar_back_photo')) {
            deleteFile($user->aadhar_back_photo, 'users/');
            $data['aadhar_back_photo'] = upload($request->file('aadhar_back_photo'), 'users/');
        }
        if ($request->hasFile('pan_photo')) {
            deleteFile($user->pan_photo, 'users/');
            $data['pan_photo'] = upload($request->file('pan_photo'), 'users/');
        }
        if ($request->hasFile('company_logo')) {
            deleteFile($user->company_logo, 'users/');
            $data['company_logo'] = upload($request->file('company_logo'), 'users/');
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user->id)->with('success', 'User profile updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin() && $user->company_id != $currentUser->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }

        $name = $user->name;
        $user->deleteWithAllRecords();

        return redirect()->route('admin.users.index')->with('success', "User '{$name}' has been deleted.");
    }

    /**
     * Update user status (Approve, Reject, Activate, Deactivate).
     */
    public function updateStatus(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin() && $user->company_id != $currentUser->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:0,1,2,3',
            'rejected_reason' => 'nullable|string|max:255',
        ]);

        $status = (int)$request->status;
        $updateData = ['status' => $status];

        if ($status === 1) { // Approved / Active
            $updateData['approved_date'] = now()->toDateString();
            $updateData['rejected_reason'] = null;
            $updateData['deactivated_by_superadmin_id'] = null;
        } elseif ($status === 2) { // Rejected
            $updateData['rejected_reason'] = $request->rejected_reason ?? 'Rejected by Administrator';
        } elseif ($status === 3) { // Deactivated
            $updateData['deactivated_by_superadmin_id'] = $currentUser->id;
        }

        $user->update($updateData);

        return back()->with('success', "User status updated to '{$user->status_label}'.");
    }

    /**
     * Update staff member base salary.
     */
    public function updateSalary(Request $request, User $user)
    {
        $request->validate([
            'salary' => 'required|numeric|min:0',
        ]);

        $user->salary = $request->salary;
        $user->save();

        return back()->with('success', "Monthly base salary for {$user->name} updated to ₹" . number_format($user->salary, 2));
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $query = User::with(['state', 'city', 'designation', 'company'])->withCount('subAgents');

        if (!$isSuperAdmin) {
            $query->where('company_id', $currentUser->id);
        } else {
            if ($request->filled('agent_id')) {
                $query->where(function ($q) use ($request) {
                    $q->where('id', $request->agent_id)
                      ->orWhere('company_id', $request->agent_id);
                });
            }
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('id', 'desc')->get();

        $filename = 'users_export_' . date('Y_m_d_His') . '.csv';

        return new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'User ID', 'Name', 'Role', 'Mobile', 'Email', 'Company', 'Agent Code',
                'Designation', 'State', 'City', 'Status', 'Approved Date', 'Registered At'
            ]);

            foreach ($users as $u) {
                fputcsv($handle, [
                    $u->id,
                    $u->user_id,
                    $u->name,
                    $u->role,
                    $u->mobile,
                    $u->email,
                    $u->company_name ?? ($u->company->company_name ?? 'N/A'),
                    $u->agent_code ?? 'N/A',
                    $u->designation->name ?? 'N/A',
                    $u->state->name ?? 'N/A',
                    $u->city->name ?? 'N/A',
                    $u->status_label,
                    $u->approved_date ?? 'N/A',
                    $u->created_at ? $u->created_at->format('Y-m-d H:i') : 'N/A',
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
