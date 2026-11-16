<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\{
    User,
    Theme,
    Attendance,
    Contact,
    ContactCallHistory,
    LeadAssign,
    Project,
    UserForgotPassword,
    Offer,
};
use App\Models\Masters\{
    State,
    City,
    Designation,
};
use Illuminate\Support\Facades\{
    Hash,
    Log,
    Validator,
};
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request){
        $exist = User::where('mobile', $request->mobile)->first();
        if($exist && $exist->status == 2){
            User::find($exist->id)->delete();
        }

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:70',
            'mobile' => 'required|numeric|digits:10|unique:users',
            'email' => 'required|email|max:70|unique:users',
            'password' => 'required|string|min:6|max:20',
            'profile_photo' => 'nullable|mimes:jpg,jpeg,png,webp',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'pin_code' => 'required|numeric|digits:6',
            'address' => 'required|string',
            'aadhar_no' => 'required|numeric|digits:12',
            'aadhar_front_photo' => 'nullable|mimes:jpg,jpeg,png,webp',
            'aadhar_back_photo' => 'nullable|mimes:jpg,jpeg,png,webp',
            // 'aadhar_front_photo' => 'nullable|mimes:jpg,jpeg,png,webp',
            // 'aadhar_back_photo' => 'nullable|mimes:jpg,jpeg,png,webp',
            'pan_no' => 'nullable|string|min:10|max:10',
            'pan_photo' => 'nullable|mimes:jpg,jpeg,png,webp',

            // Agent
            'company_name' => 'nullable|string|max:255',
            'gst_no' => 'nullable|string|min:15|max:15',
            'company_logo' => 'nullable|mimes:jpg,jpeg,png,webp',

            // Sub Agent
            'company_id' => 'nullable',
            'agent_code' => 'nullable|string|exists:users,user_id',
            'designation_id' => 'nullable',
            'dob' => 'nullable|date',
        ]);

        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        try {
            // OTP send
            $otp = rand(1111, 9999);

            if($request->file('profile_photo')){
                $profilePhoto = upload($request->profile_photo, 'users/');
            }
            if($request->file('aadhar_front_photo')){
                $aadharFrontPhoto = upload($request->aadhar_front_photo, 'users/');
            }
            if($request->file('aadhar_back_photo')){
                $aadharBackPhoto = upload($request->aadhar_back_photo, 'users/');
            }
            if($request->file('pan_photo')){
                $panPhoto = upload($request->pan_photo, 'users/');
            }
            if($request->file('company_logo')){
                $companyLogo = upload($request->company_logo, 'users/');
            }

            $companyId = User::where('user_id', $request->agent_code)->pluck('id')->first();

            User::create([
                'user_id' =>  $request->agent_code 
                    ? 'STF' . date('ymdHis') . rand(100, 999) 
                    : 'AGT' . date('ymdHis') . rand(100, 999),
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'otp' => $otp,
                'role' => $request->agent_code ? 'Sub Agent' : 'Agent',
                'profile_photo' => $profilePhoto ?? null,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'pin_code' => $request->pin_code,
                'address' => $request->address,
                'aadhar_no' => $request->aadhar_no,
                'aadhar_front_photo' => $aadharFrontPhoto ?? null,
                'aadhar_back_photo' => $aadharBackPhoto ?? null,
                'pan_no' => $request->pan_no,
                'pan_photo' => $panPhoto ?? null,

                'company_name' => $request->company_name,
                'gst_no' => $request->gst_no,
                'company_logo' => $companyLogo ?? null,

                'company_id' => $companyId ?? null,
                'agent_code' => $request->agent_code ?? null,
                'designation_id' => $request->agent_code ? $request->designation_id : null,
                
                'dob' => formatDateToYmd($request->dob),
            ]);
            return response()->json(['status'=>true, 'message'=>'Register Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Register Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'Something Went Wrong!'], 422);
        }
    }

    public function login(Request $request){
        $validated = Validator::make($request->all(), [
            'mobile' => 'required|numeric|digits:10|exists:users',
            'password' => 'required|string|min:6|max:20',
        ]);

        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        try {
            $user = User::where('mobile', $request->mobile)->first();
            if(!Hash::check($request->password, $user->password)){
                return response()->json(['status'=>false, 'message'=>'Password Does Not Match!', 'error'=>'Password Does Not Match!'], 422);
            }elseif($user->status == '0'){
                return response()->json(['status'=>false, 'message' => 'Under Process!', 'error' => 'Account is under process'], 422);
            }elseif($user->status == '2'){
                return response()->json(['status'=>false, 'message' => 'Account Rejected Please Re-Register!','error' => 'Account Rejected Please Re-Register'], 422);
            }elseif($user->status == '3'){
                return response()->json(['status'=>false, 'message' => 'Account Deactivated!','error' => 'Account is deactivated'], 422);
            }else{
                $deletionRestored = false;
                if (!empty($user->deletion_requested_at)) {
                    $requestedAt = Carbon::parse($user->deletion_requested_at);
                    if ($requestedAt->addDays(90)->isPast()) {
                        // 90 days have expired without login - wipe all user records immediately
                        $user->deleteWithAllRecords();
                        return response()->json(['status'=>false, 'message'=>'Account has been permanently deleted as 90 days have expired since deletion request.', 'error'=>'Account permanently deleted'], 404);
                    } else {
                        // User logged in within 90 days: cancel the deletion request and restore account
                        $user->deletion_requested_at = null;
                        $user->save();
                        $deletionRestored = true;
                    }
                }

                $user->token = $user->createToken($user->role)->plainTextToken;
                $user->theme = Theme::whereIn('user_id', [$user->id, $user->company_id])->pluck('theme')->first() ?? null;
                $user->today_attendance = Attendance::where('user_id', $user->id)
                    ->where('date', date('Y-m-d'))
                    ->exists() ? true : false;

                $message = $deletionRestored 
                    ? 'Account deletion request cancelled and account restored. Login Successfully' 
                    : 'Login Successfully';

                return response()->json([
                    'status' => true, 
                    'message' => $message, 
                    'data' => [
                        'user' => $user,
                        'deletion_cancelled' => $deletionRestored,
                    ]
                ], 200);
            }
        } catch (\Exception $ex) {
            Log::error('Login Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'Something Went Wrong!'], 500);
        }
    }

    public function dashborad(Request $request){
        $id = $request->user()->id;
        $role = $request->user()->role;

        if($role == 'Superadmin'){
            $agents = User::select('id', 'name','created_at')
                ->where('role', 'Agent')
                ->take(9)
                ->latest()
                ->get()
                ->map(function($item){
                    return [
                        'id' => 'agent_' . $item->id, 
                        'text' => "New agent registration: $item->name",
                        'date' => formatdateTodmY($item->created_at),
                        'created_date' => $item->created_at
                    ];
                });

            $dealAprrovals = Contact::with('user:id,name', 'project:id,name')
                ->select('id','name','project_id','user_id','created_at')
                ->where('status', 'booking_done')
                ->take(9)
                ->latest()
                ->get()
                ->map(function($item){
                    return [
                        'id' => 'deal_' . $item->id,
                        'text' => "Deal approved for {$item->project->name} by {$item->name}",
                        'date' => formatdateTodmY($item->created_at),
                        'created_date' => $item->created_at
                    ];
                });

            $recentActivities = $agents->merge($dealAprrovals)
                ->sortByDesc('created_date')         // Sort by latest
                ->take(9)                    // Only latest 9
                ->values();       
            
            // Performance Graph
            $labels = [];
            $contacts = [];
            $deals = [];

            for ($i = 5; $i >= 0; $i--) {

                $month = Carbon::now()->subMonths($i);

                // Label (Aug, Sep...)
                $labels[] = $month->format('M');

                // contacts data
                $contacts[] = Contact::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();

                // Deals data
                $deals[] = Contact::where('status', 'booking_done')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
            }

            $activeLeads = Contact::where('status', 'interested')->count();
            $dealClosed = Contact::where('status', 'booking_done')->count();
            $agentsIds = User::where('role', 'Agent')->pluck('id')->toArray();
            $data = [
                'todayAttendance' => Attendance::whereIn('user_id', $agentsIds)
                    ->where('date', date('Y-m-d'))
                    ->count(),
                'totalProjects' => Project::count(),
                'totalActiveProjects' => Project::where('status',1)->count(),
                'totalAgents' => User::where('role', 'Agent')->count(),
                'totalStaff' => User::where('role', 'Sub Agent')->count(),
                'activeStaff' => User::where('role', 'Sub Agent')->where('status', 1)->count(),
                'activeLeads' => $activeLeads,
                'dealsClosed' => $dealClosed,
                'pendingApprovals' => 8,
                'paymentsPending' => 4,
                'totalPendingForgotPassword' => UserForgotPassword::where('status', 1)->count(),
                'totalContacts' => Contact::count(),
                 'totalOffers' => Offer::count(),

                'agentApproval' => User::where('role', 'Agent')->where('status',0)->count(),
                'dealApproval' => $dealClosed,
                'complianceAlert' => 1,

                // Contacts Status Wise
                'interested' => $activeLeads,
                'follow_up' => Contact::where('status', 'follow_up')->count(),
                'call_later' => Contact::where('status', 'call_later')->count(),
                'site_visit_done' => Contact::where('status', 'site_visit_done')->count(),
                'no_response' => Contact::where('status', 'no_response')->count(),
                'call_not_connecting' => Contact::where('status', 'call_not_connecting')->count(),
                'no_requirement' => Contact::where('status', 'no_requirement')->count(),
                'invalid_number' => Contact::where('status', 'invalid_number')->count(),
                'budget_issue' => Contact::where('status', 'budget_issue')->count(),
                'availability_issue' => Contact::where('status', 'availability_issue')->count(),
                'plan_drop' => Contact::where('status', 'plan_drop')->count(),
                'location_issue' => Contact::where('status', 'location_issue')->count(),
                'other' => Contact::where('status', 'other')->count(),
                'booking_done' => $dealClosed,
                

                'recentActivities' => $recentActivities,
                'trendPerformance' => [
                    "labels" => $labels,
                    "datasets" => [
                        [
                            "data" => $contacts,
                        ],
                        [
                            "data" => $deals,
                        ]
                    ]
                ]
            ];
        }

        if($role == 'Agent'){ 
            $staffPerformance = User::with(['state:id,name', 'city:id,name', 'designation:id,name'])
                ->select(
                    'id',
                    'user_id',
                    'name',
                    'email',
                    'mobile',
                    'role',
                    'company_id',
                    'designation_id',
                    'status',
                    'approved_date',
                    'profile_photo',
                    'dob',
                    'pin_code',
                    'address',
                    'city_id',
                    'state_id',
                    'aadhar_no',
                    'aadhar_front_photo',
                    'aadhar_back_photo',
                    'pan_no',
                    'pan_photo',
                    'rejected_reason'
                )
                ->addSelect([
                    'latest_contact_time' => Contact::select('updated_at')
                        ->whereColumn('user_id', 'users.id')
                        ->latest('updated_at')
                        ->limit(1)
                ])
              ->where('company_id', $request->user()->id)
              ->where('status', 1)
              ->where('role', 'Sub Agent')
                        ->orderByDesc('latest_contact_time')
                ->take(3)
                ->get()
                ->map(function ($user) use ($request) {

                    $statusMap = [
                        '0' => 'Pending',
                        '1' => 'Approved',
                        '2' => 'Rejected',
                        '3' => 'Declined',
                    ];

                    // ===== Attendance History Short Logic =====
                    $today = Carbon::today();
                    $sixMonthsAgo = $today->copy()->subMonths(6);

                    $firstDate = Attendance::where('user_id', $user->id)->min('date');
                    $startDate = $firstDate
                        ? (Carbon::parse($firstDate)->gt($sixMonthsAgo) ? $firstDate : $sixMonthsAgo)
                        : $sixMonthsAgo;

                    $records = Attendance::where('user_id', $user->id)
                        ->whereBetween('date', [$startDate, $today])
                        ->get()
                        ->keyBy('date');

                    $attendanceHistory = [];
                    for ($date = Carbon::parse($startDate); $date->lte($today); $date->addDay()) {
                        $d = $date->format('Y-m-d');
                        $a = $records->get($d);
                        $attendanceHistory[] = [
                            'date' => $d,
                            'status' => $a ? 'present' : 'absent',
                            'checkIn' => $a ? date('h:i A', strtotime($a->time)) : null,
                        ];
                    }
                    // ===== End Attendance History =====

                    // basic user structure
                    $userData = [
                        'id' => $user->id,
                        'user_id' => $user->user_id,
                        'name' => $user->name,
                        'role' => $user->designation->name ?? 'Sub Agent',
                        'email' => $user->email,
                        'phone' => '+91 ' . $user->mobile,
                        'dob' => formatDateTodmY($user->dob),
                        'joiningDate' => formatDateTodmY($user->approved_date),
                        'avatar' => $user->profile_photo_url,
                        'status' => $statusMap[$user->status],
                        'stats' => [
                            'visits' => ContactCallHistory::where('user_id', $user->id)->where('visited', 1)->count(),
                            'calls' => ContactCallHistory::where('user_id', $user->id)->count(),
                            'deals' => Contact::where('user_id', $user->id)->where('status', 'booking_done')->count(),
                        ],
                        'recentLeads' => Contact::whereIn('status', ['interested', 'follow_up','booking_done','site_visit_done'])
                            ->where('user_id', $user->id)
                            ->orderBy('updated_at', 'desc')
                            ->take(4)
                            ->get()
                            ->map(function ($lead) {
                                return [
                                    'id' => $lead->id,
                                    'name' => $lead->name,
                                    'status' => $lead->status,
                                ];
                            }),
                        'attendanceHistory' => $attendanceHistory,
                    ];

                    // optional additional info if available
                    if ($user->address || $user->aadhar_no || $user->pan_no) {
                        $userData['address'] = $user->address ?? '';
                        $userData['city'] = $user->city->name ?? '';
                        $userData['state'] = $user->state->name ?? '';
                        $userData['pin_code'] = $user->pin_code ?? '';
                        $userData['aadharNumber'] = $user->aadhar_no ?? '';
                        $userData['aadharFront'] = $user->aadhar_front_photo_url;
                        $userData['aadharBack'] = $user->aadhar_back_photo_url;
                        $userData['panNumber'] = $user->pan_no ?? '';
                        $userData['panPhoto'] = $user->pan_photo_url;
                    }

                    // include rejected reason if applicable
                    if ($user->status == 2 && !empty($user->rejected_reason)) {
                        $userData['rejectedReason'] = $user->rejected_reason;
                    }

                    return $userData;
                });

            $subAgentIds = User::where('company_id', $request->user()->id)->pluck('id')->toarray();
            $userIds = array_merge($subAgentIds, [$request->user()->id]);
            $totalLeads = Contact::whereIn('user_id', $userIds)->where('status', 'interested')->count();
            $totalFollowUps = Contact::whereIn('user_id', $userIds)->where('status', 'follow_up')->count();
            $dealClosed = Contact::whereIn('user_id', $userIds)->where('status','booking_done')->count();

            $data = [
                'totalStaff' => User::where('role', 'Sub Agent')->where('company_id', $request->user()->id)->count(),
                'total_leads' => $totalLeads,
                'follow_ups' => $totalFollowUps,
                'today_visits' => ContactCallHistory::whereIn('user_id', $userIds)->where('visited', 1)->where('date', date('Y-m-d'))->count(),
                'booking_done' => $dealClosed,
                'totalContacts' => Contact::whereIn('user_id', $userIds)->count(),
                'totalOffers' => Offer::wheredate('from_date', '<=', date('Y-m-d'))->wheredate('to_date', '>=', date('Y-m-d'))->count(),

                // Contacts Status Wise
                'interested' => Contact::whereIn('user_id', $userIds)->where('status', 'interested')->count(),
                'follow_up' => Contact::whereIn('user_id', $userIds)->where('status', 'follow_up')->count(),
                'call_later' => Contact::whereIn('user_id', $userIds)->where('status', 'call_later')->count(),
                'site_visit_done' => Contact::whereIn('user_id', $userIds)->where('status', 'site_visit_done')->count(),
                'no_response' => Contact::whereIn('user_id', $userIds)->where('status', 'no_response')->count(),
                'call_not_connecting' => Contact::whereIn('user_id', $userIds)->where('status', 'call_not_connecting')->count(),
                'no_requirement' => Contact::whereIn('user_id', $userIds)->where('status', 'no_requirement')->count(),
                'invalid_number' => Contact::whereIn('user_id', $userIds)->where('status', 'invalid_number')->count(),
                'budget_issue' => Contact::whereIn('user_id', $userIds)->where('status', 'budget_issue')->count(),
                'availability_issue' => Contact::whereIn('user_id', $userIds)->where('status', 'availability_issue')->count(),
                'plan_drop' => Contact::whereIn('user_id', $userIds)->where('status', 'plan_drop')->count(),
                'location_issue' => Contact::whereIn('user_id', $userIds)->where('status', 'location_issue')->count(),
                'other' => Contact::whereIn('user_id', $userIds)->where('status', 'other')->count(),
                'booking_done' => Contact::whereIn('user_id', $userIds)->where('status', 'booking_done')->count(),
                'scheduled' => Contact::whereIn('user_id', $userIds)->whereNotNull('date')->whereNotNull('time')->where('date', '>=', date('Y-m-d'))->count(),

                'staff_performance' => $staffPerformance,
            ];
        }

        if($role == 'Sub Agent'){
            $totalLeads = Contact::where('user_id', $id)->where('status', 'interested')->count();
            $totalFollowUps = Contact::where('user_id', $id)->where('status', 'follow_up')->count();
            $data = [
                'total_leads' => $totalLeads,
                'upcomming' => Contact::where('user_id', $id)->whereNotNull('date')->whereNotNull('time')->where('date', '>=', date('Y-m-d'))->count(),
                'follow_ups' => $totalFollowUps,
                'totalOffers' => Offer::wheredate('from_date', '<=', date('Y-m-d'))->wheredate('to_date', '>=', date('Y-m-d'))->count(),

                'contacts' => Contact::where('user_id', $id)->count(),
                'lead_assignments' => LeadAssign::where('user_id', $id)->where('other_assign', 0)->count(),
                'visits' => ContactCallHistory::where('user_id', $id)->where('visited', 1)->count(),
                'shceduled_visits' => Contact::whereNotNull('date')->whereNotNull('time')->where('user_id', $id)->where('date', '>=', date('Y-m-d'))->count(),

                // Contacts Status Wise
                'interested' => $totalLeads,
                'follow_up' => $totalFollowUps,
                'call_later' => Contact::where('user_id', $id)->where('status', 'call_later')->count(),
                'site_visit_done' => Contact::where('user_id', $id)->where('status', 'site_visit_done')->count(),
                'no_response' => Contact::where('user_id', $id)->where('status', 'no_response')->count(),
                'call_not_connecting' => Contact::where('user_id', $id)->where('status', 'call_not_connecting')->count(),
                'no_requirement' => Contact::where('user_id', $id)->where('status', 'no_requirement')->count(),
                'invalid_number' => Contact::where('user_id', $id)->where('status', 'invalid_number')->count(),
                'budget_issue' => Contact::where('user_id', $id)->where('status', 'budget_issue')->count(),
                'availability_issue' => Contact::where('user_id', $id)->where('status', 'availability_issue')->count(),
                'plan_drop' => Contact::where('user_id', $id)->where('status', 'plan_drop')->count(),
                'location_issue' => Contact::where('user_id', $id)->where('status', 'location_issue')->count(),
                'other' => Contact::where('user_id', $id)->where('status', 'other')->count(),
                'booking_done' => Contact::where('user_id', $id)->where('status', 'booking_done')->count(),

                '7d' => [
                    'visits' => ContactCallHistory::where('user_id', $id)->where('visited', 1)->where('created_at', '>=', now()->subDays(7))->count(),
                    'calls' => ContactCallHistory::where('user_id', $id)->where('created_at', '>=', now()->subDays(7))->count(),
                    'deals' => Contact::where('user_id', $id)->where('status', 'booking_done')->where('created_at', '>=', now()->subDays(7))->count(),
                ],
                '1m' => [
                    'visits' => ContactCallHistory::where('user_id', $id)->where('visited', 1)->where('created_at', '>=', now()->subMonths(1))->count(),
                    'calls' => ContactCallHistory::where('user_id', $id)->where('created_at', '>=', now()->subMonths(1))->count(),
                    'deals' => Contact::where('user_id', $id)->where('status', 'booking_done')->where('created_at', '>=', now()->subMonths(1))->count(),
                ],
                'all' => [
                    'visits' => ContactCallHistory::where('user_id', $id)->where('visited', 1)->count(),
                    'calls' => ContactCallHistory::where('user_id', $id)->count(),
                    'deals' => Contact::where('user_id', $id)->where('status', 'booking_done')->count(),
                ],
            ];
        }
        
        return response()->json(['status'=>true, 'message'=>'Dashboard Data Fetched Successfully!', 'data'=>$data], 200);
    }

    public function profile(Request $request){
        $user = User::with('state','city')->find($request->user()->id);
        $user['role'] = $user->role == "Agent" ? 'Leader' : $user->role;
        $user['dob'] = formatDateToDMY($user->dob);
        $user['profile_photo_url'] =  $user->profile_photo_url;
        $user['state'] = $user->state->name ?? null;
        $user['city'] = $user->city->name ?? null;
        $user['designation'] = Designation::find($request->user()->designation_id)->name ?? null;
        $user['company_name'] = $user->company_name
            ?? User::where('id', $user->company_id)->pluck('company_name')->first();
        $user['company_logo_url'] =  $user->company_logo_url;
        return response()->json(['status' => true, 'message' => 'Profile fetched successfully', 'data' => $user], 200);
    }

    public function profileUpdate(Request $request){
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:70',
            'email' => 'required|email|max:70|unique:users,email,'.$request->user()->id,
            'mobile' => 'required|numeric|digits:10|unique:users,mobile,'.$request->user()->id,
            'address' => 'required|string',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'pin_code' => 'nullable|numeric|digits:6',

            // Agent
            'company_name' => 'nullable|string|max:255',
            'company_logo' => 'nullable|mimes:jpg,jpeg,png,webp',
        ]);

        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        try {
            $existingUser = User::find($request->user()->id);

            if($request->file('image')){
                if($existingUser->profile_photo){
                    deleteFile($existingUser->profile_photo, 'users/');
                }
                $profilePhoto = upload($request->image, 'users/');
            }

            if($request->file('company_logo')){
                if($existingUser->company_logo){
                    deleteFile($existingUser->company_logo, 'users/');
                }
                $companyLogo = upload($request->company_logo, 'users/');
            }

            User::find($request->user()->id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'profile_photo' => $profilePhoto ?? $existingUser->profile_photo,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'pin_code' => $request->pin_code,

                'company_name' => $request->company_name ?? $existingUser->company_name,
                'company_logo' => $companyLogo ?? $existingUser->company_logo,
            ]);

            $user = User::with('state','city')->find($request->user()->id);
            $user['dob'] = formatDateToDMY($user->dob);
            $user['profile_photo_url'] =  $user->profile_photo_url;
            $user['state'] = $user->city->name ?? null;
            $user['city'] = $user->city->name ?? null;
            $user['company_name'] = $user->company_name
                ?? User::where('id', $user->company_id)->pluck('company_name')->first();
            $user['company_logo_url'] =  $user->company_logo_url;
            
            return response()->json(['status' => true, 'message' => 'Profile Updated successfully', 'data' => $user], 200);
        } catch (\Exception $ex) {
            Log::error('Profile Updating Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'Something Went Wrong!'], 500);
        }
    }

    public function changePassword(Request $request){
        $validated = Validator::make($request->all(),[
            'old_password' => 'required',
            'password' => 'required|string|max:20|min:6|confirmed'
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation error!',], 422);
        }

        try {
            $user = User::where('id',$request->user()->id)->first();
            if(Hash::check($request->old_password, $user->password)){
                User::where('id',$request->user()->id)->update([
                    'password' => Hash::make($request->password)
                ]);
            }else{
                return response()->json(['status'=>false, 'message'=>'Old Password Are Not Match!', 'error'=>'Old Password Are Not Match!'], 422);
            }
            return response()->json(['status'=>true, 'message'=>'Password Change Successfully!'],200);
        } catch (\Exception $ex) {
            Log::error('Login Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'omething Went Wrong!'],500);
        }
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => true, 'message' => 'Logged out successfully'], 200);
    }

    public function forgotPasswordRequests(Request $request){
        $records = UserForgotPassword::with('user:id,name,mobile,role,state_id,city_id,company_name,designation_id,address,pin_code,profile_photo,company_id',
            'user.state:id,name',
            'user.city:id,name',
            'user.designation:id,name',
            'user.company:id,name')
            ->where('status', 1)
            ->get()
            ->map(function ($item){
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'name' => $item->user->name ?? '',
                    'mobile' => $item->user->mobile ?? '',
                    'userRole' => $item->user->role ?? '',
                    'designation' => $item->user->designation->name ?? '',
                    'company' => $item->user->company_name ?? $item->user->company->company_name ?? '',
                    'address' => $item->user->address,
                    'city' => $item->user->city->name ?? '',
                    'state' => $item->user->state->name ?? '',
                    'pincode' => $item->user->pin_code,
                    'avatar' => $item->user->profile_photo_url,
                    'request_date' => formatDateToDMY($item->created_at),
                    'request_time' => formatDateToHIA($item->created_at),
                ];
            });
        return response()->json(['status' => true, 'message' => 'Forgot Password Requests Fetched Successfully', 'data' => $records], 200);
    }

    public function forgotPassword(Request $request){
        $validated = Validator::make($request->all(), [
            'mobile' => 'required|numeric|digits:10|exists:users',
        ]);

        if($validated->fails()){
            return response()->json(['status'=>false, 'message' => $validated->errors()->first(), 'error' => 'Validation Error!'], 422);
        }

        try {
            $user = User::where('mobile', $request->mobile)
                ->where('status', 1)
                ->where('role', '!=', 'Superadmin')
                ->first();
            if(!$user){
                return response()->json(['status'=>false, 'message' => 'Verified Person Not Found!', 'error' => 'Verified Person Not Found!'], 422);
            }

            $exist = UserForgotPassword::where('user_id', $user->id)
                ->where('status', 1)
                ->exists();
            if($exist){
                return response()->json(['status'=>false, 'message' => 'Forgot Password Request Already Send!', 'error' => 'Forgot Password Request Already Send!'], 422);
            }

            UserForgotPassword::create([
                'user_id' => $user->id
            ]);
            return response()->json(['status' => true, 'message' => 'Forgot Password Request Send Successfully'], 200);
        } catch (\Exception $ex) {
            Log::error('Forgot Password Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'Something Went Wrong!'], 500);
        }
    }

    public function forgotPasswordUpdate(Request $request){
        $validated = Validator::make($request->all(),[
            'id' => 'required|exists:user_forgot_passwords,id',
            'status' => 'required|in:1,2,3', 
            'user_id' => 'required|exists:users,id',
            'password' => 'nullable|required_if:status,2|string|max:20|min:8'
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation error!',], 422);
        }

        try {   
            UserForgotPassword::find($request->id)->update([
                'status' => $request->status
            ]);
            if($request->status == 2){
                User::where('id', $request->user_id)->update([
                    'password' => Hash::make($request->password)
                ]);
            }
            return response()->json(['status' => true, 'message' => 'Forgot Password Updated Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Login Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'omething Went Wrong!'],500);
        }
    }

    /**
     * Submit an account deletion request.
     * Records deletion_requested_at and invalidates all tokens.
     * User has 90 days cooling period. If user logs in within 90 days, request is cancelled.
     */
    public function deleteAccountRequest(Request $request){
        try {
            $user = $request->user();
            $user->deletion_requested_at = Carbon::now();
            $user->save();

            // Revoke all existing API tokens
            $user->tokens()->delete();

            $scheduledDeletionDate = Carbon::now()->addDays(90);

            return response()->json([
                'status' => true,
                'message' => 'Account deletion request submitted successfully. Your account and all associated records will be permanently deleted after 90 days (' . $scheduledDeletionDate->format('d-m-Y') . '). If you log in within 90 days, your deletion request will be automatically cancelled.',
                'data' => [
                    'user_id' => $user->user_id,
                    'deletion_requested_at' => $user->deletion_requested_at->format('Y-m-d H:i:s'),
                    'scheduled_deletion_date' => $scheduledDeletionDate->format('Y-m-d H:i:s'),
                    'grace_period_days' => 90,
                ]
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Delete Account Request Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'Something Went Wrong!'], 500);
        }
    }

    /**
     * Check status of account deletion request.
     */
    public function accountDeletionStatus(Request $request){
        try {
            $user = $request->user();
            if (!$user->deletion_requested_at) {
                return response()->json([
                    'status' => true,
                    'message' => 'No active deletion request for this account.',
                    'data' => [
                        'is_deletion_pending' => false,
                    ]
                ], 200);
            }

            $requestedAt = Carbon::parse($user->deletion_requested_at);
            $expiryDate = $requestedAt->copy()->addDays(90);
            $daysRemaining = max(0, ceil(Carbon::now()->diffInDays($expiryDate, false)));

            return response()->json([
                'status' => true,
                'message' => 'Account deletion request is active.',
                'data' => [
                    'is_deletion_pending' => true,
                    'deletion_requested_at' => $requestedAt->format('Y-m-d H:i:s'),
                    'scheduled_deletion_date' => $expiryDate->format('Y-m-d H:i:s'),
                    'days_remaining' => $daysRemaining,
                ]
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Account Deletion Status Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'Something Went Wrong!'], 500);
        }
    }

    /**
     * Cancel an active account deletion request.
     */
    public function cancelAccountDeletion(Request $request){
        try {
            $user = $request->user();
            if (!$user->deletion_requested_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'No pending deletion request found to cancel.',
                    'error' => 'No pending deletion request found',
                ], 400);
            }

            $user->deletion_requested_at = null;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Account deletion request cancelled successfully. Your account is active.',
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Cancel Account Deletion Error: ',[
                'exception' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something Went Wrong!', 'error'=>'Something Went Wrong!'], 500);
        }
    }
}
