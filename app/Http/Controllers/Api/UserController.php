<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{
    User,
    Theme,
    Contact,
    ContactCallHistory,
    Attendance,
    LeadAssign
};
use Illuminate\Support\Facades\{
    Log,
    Validator,
    DB,
};
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function agents(Request $request){
        $records = User::with('state:id,name', 'city:id,name')
            ->select(
                'id',
                'user_id',
                'name',
                'email',
                'mobile',
                'state_id',
                'city_id',
                'pin_code',
                'address',
                'status',
                'rejected_reason',
                'aadhar_no',
                'pan_no',
                'gst_no',
                'profile_photo',
                'aadhar_front_photo',
                'aadhar_back_photo',
                'company_logo',
                'pan_photo',
                'approved_date',
                'company_name',
                'created_at'
            )
            ->where('role', 'Agent');
            if($request->status != 'null'){
                $records = $records->where('status', $request->status);
            }
            $records = $records->get()
            ->map(function ($item) use ($request) {

                $statusMap = [
                    '0' => 'Pending',
                    '1' => 'Approved',
                    '2' => 'Rejected',
                    '3' => 'Declined', // or 'Deactivated' depending on your system
                ];

                $subAgentIds = User::where('company_id', $item->id)->pluck('id')->toarray();
                $userIds = array_merge($subAgentIds, [$item->id]);
                $totalLeads = Contact::whereIn('user_id', $userIds)->where('status', 'interested')->count();
                $dealClosed = Contact::whereIn('user_id', $userIds)->where('status','booking_done')->count();

                // ===== Attendance History (Always Array) =====
                $today = now()->startOfDay();
                $sixMonthsAgo = $today->copy()->subMonths(6);

                // First attendance date
                $firstDate = $item->approved_date;
                //Attendance::where('user_id', $item->id)->min('date');

                // If no attendance → return empty array
                if (!$firstDate) {
                    $attendanceHistory = [];
                } else {

                    $start = max(strtotime($firstDate), strtotime($sixMonthsAgo));

                    // Fetch attendance records
                    $records = Attendance::where('user_id', $item->id)
                        ->whereBetween('date', [date('Y-m-d', $start), $today])
                        ->get()
                        ->keyBy('date');

                    // Build daily history
                    $attendanceHistory = [];
                    for ($d = $start; $d <= $today->timestamp; $d += 86400) {
                        $date = date('Y-m-d', $d);
                        $a = $records->get($date);

                        $attendanceHistory[] = [
                            'date' => $date,
                            'status' => $a ? 'present' : 'absent',
                            'checkIn' => $a ? date('h:i A', strtotime($a->time)) : null,
                        ];
                    }
                }
                // ===== End Attendance History =====

                $data = [
                    'user_id' => $item->user_id,
                    'id' => $item->id,
                    'name' => $item->name,
                    'companyName' => $item->company_name ?? 'N/A',
                    'avatar' => $item->profile_photo_url,
                    'status' => $statusMap[$item->status] ?? 'Unknown',
                    'registrationDate' => formatDateTodmY($item->created_at),
                    'email' => $item->email,
                    'phone' => $item->mobile,
                    'address' => $item->address,
                    'city' => $item->city->name ?? 'N/A',
                    'state' => $item->state->name ?? 'N/A',
                    'pin_code' => $item->pin_code ?? 'N/A',
                    'aadharNumber' => $item->aadhar_no ?? 'N/A',
                    'panNumber' => $item->pan_no ?? 'N/A',
                    'gstNumber' => $item->gst_no?? 'N/A',
                    'profile_photo' => $item->profile_photo_url ?? null,
                    'companyLogo' => $item->company_logo_url,
                    'aadharFront' => $item->aadhar_front_photo_url ?? null,
                    'aadharBack' => $item->aadhar_back_photo_url ?? null,
                    'panPhoto' => $item->pan_photo_url ?? null,
                    'stats' => [
                        'staff' => User::where('company_id', $item->id)->count(),
                        'leads' => $totalLeads,
                        'deals' => $dealClosed,
                    ],
                    'aadhar_back_photo_storage' => asset('storage/public/uploads/users/' . $item->aadhar_back_photo),
                    'aadhar_back_photo_app'=> storage_path('app/public/uploads/users/' . $item->aadhar_back_photo),
                    'attendanceHistory' => $attendanceHistory,
                    'request_date' => formatDateToDMY($item->created_at),
                    'request_time' => formatDateToHIA($item->created_at),
                ];

                // 👉 Add rejectedReason only if status == 2 and value is not empty
                if ($item->status == 2 && !empty($item->rejected_reason)) {
                    $data['rejectedReason'] = $item->rejected_reason;
                }

                return $data;
            });

        return response()->json([
            'status' => true,
            'message' => 'Agents fetched successfully!',
            'data' => $records
        ], 200);
    }

    public function agentsStatusUpdate(Request $request){ 
        $validated = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'status' => 'required|in:0,1,2,3',
            'rejected_reason' => 'required_if:status,2|string|max:255',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        try {
            User::where('id', $request->id)->update([
                'status'=>$request->status,
                'approved_date'=>$request->status == 1 ? date('Y-m-d') : null,
                'rejected_reason'=>$request->rejected_reason ?? null,
            ]);

            // agent and sub agent deactivate
            if($request->status == 3){
                User::where('id', $request->id)
                    ->orWhere('company_id', $request->id)
                    ->where('status', '!=', 3)
                    ->update([
                        'status'=>$request->status,
                        'deactivated_agent_subagent_by_superadmin_id' => $request->user()->id,
                    ]);
            }
            if($request->status == 1){
                User::where('id', $request->id)
                    ->orWhere('company_id', $request->id)
                    ->whereNotNull('deactivated_agent_subagent_by_superadmin_id')
                    ->update([
                        'status'=>$request->status,
                    ]);
            }

            return response()->json(['status'=>true, 'message'=>'Agent Status Updated Successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Agent Status Updating Error: ', [
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went wrong!'], 500);
        }
    }

    public function themeStoreUpdate(Request $request){
        $validated = Validator::make($request->all(), [
            'theme' => 'required|string|max:10',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        try {
            Theme::updateOrCreate(
                ['user_id' => $request->user()->id],
                ['theme' => $request->theme]
            );
            return response()->json(['status'=>true, 'message'=>'Theme Updated Successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Theme Updating Error: ', [
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went wrong!'], 500);
        }
    }

    public function staffs(Request $request){
        try {
            $staff = User::with(['state:id,name', 'city:id,name', 'designation:id,name'])
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
                    'rejected_reason',
                    'created_at'
                )
                ->where('company_id', $request->user()->id)
                ->whereIn('role', ['Sub Agent'])
                ->get()
                ->map(function ($user) use ($request) {

                    $statusMap = [
                        '0' => 'Pending',
                        '1' => 'Approved',
                        '2' => 'Rejected',
                        '3' => 'Declined',
                    ];

                    // ===== Attendance History (Always Array) =====
                    $today = now()->startOfDay();
                    $sixMonthsAgo = $today->copy()->subMonths(6);

                    // First attendance date
                    $firstDate = $user->approved_date;
                    //Attendance::where('user_id', $user->id)->min('date');

                    // If no attendance → return empty array
                    if (!$firstDate) {
                        $attendanceHistory = [];
                    } else {

                        $start = max(strtotime($firstDate), strtotime($sixMonthsAgo));

                        // Fetch attendance records
                        $records = Attendance::where('user_id', $user->id)
                            ->whereBetween('date', [date('Y-m-d', $start), $today])
                            ->get()
                            ->keyBy('date');

                        // Build daily history
                        $attendanceHistory = [];
                        for ($d = $start; $d <= $today->timestamp; $d += 86400) {
                            $date = date('Y-m-d', $d);
                            $a = $records->get($date);

                            $attendanceHistory[] = [
                                'date' => $date,
                                'status' => $a ? 'present' : 'absent',
                                'checkIn' => $a ? date('h:i A', strtotime($a->time)) : null,
                            ];
                        }
                    }
                    // ===== End Attendance History =====

                    // basic user structure
                    $data = [
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
                        'request_date' => formatDateToDMY($user->created_at),
                        'request_time' => formatDateToHIA($user->created_at),
                    ];

                    // optional additional info if available
                    if ($user->address || $user->aadhar_no || $user->pan_no) {
                        $data['address'] = $user->address ?? '';
                        $data['city'] = $user->city->name ?? '';
                        $data['state'] = $user->state->name ?? '';
                        $data['pin_code'] = $user->pin_code ?? '';
                        $data['aadharNumber'] = $user->aadhar_no ?? '';
                        $data['aadharFront'] = $user->aadhar_front_photo_url;
                        $data['aadharBack'] = $user->aadhar_back_photo_url;
                        $data['panNumber'] = $user->pan_no ?? '';
                        $data['panPhoto'] = $user->pan_photo_url;
                    }

                    // include rejected reason if applicable
                    if ($user->status == 2 && !empty($user->rejected_reason)) {
                        $data['rejectedReason'] = $user->rejected_reason;
                    }

                    return $data;
                });

            return response()->json([
                'status' => true,
                'message' => 'Staff fetched successfully!',
                'data' => $staff,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Staff List Fetching Error: ', [
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went wrong!'], 500);
        }
    }

    public function staffsShow(Request $request){ 
        try {
            $staff = User::with(['state:id,name', 'city:id,name', 'designation:id,name'])
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
                ->where('id', $request->id)
                ->where('role', 'Sub Agent')
                ->first();

            if (!$staff) {
                // Handle user not found case
                return null; // or throw exception, or return error response
            }

            $statusMap = [
                '0' => 'Pending',
                '1' => 'Approved',
                '2' => 'Rejected',
                '3' => 'Declined',
            ];

            // Attendance History (Always Array)
            $today = now()->startOfDay();
            $sixMonthsAgo = $today->copy()->subMonths(6);
            $firstDate = $staff->approved_date;

            if (!$firstDate) {
                $attendanceHistory = [];
            } else {
                $start = max(strtotime($firstDate), strtotime($sixMonthsAgo));
                $records = Attendance::where('user_id', $staff->id)
                    ->whereBetween('date', [date('Y-m-d', $start), $today->toDateString()])
                    ->get()
                    ->keyBy('date');

                $attendanceHistory = [];
                for ($d = $start; $d <= $today->timestamp; $d += 86400) {
                    $date = date('Y-m-d', $d);
                    $a = $records->get($date);

                    $attendanceHistory[] = [
                        'date' => $date,
                        'status' => $a ? 'present' : 'absent',
                        'checkIn' => $a ? date('h:i A', strtotime($a->time)) : null,
                    ];
                }
            }

            // Prepare final user data array
            $data = [
                'id' => $staff->id,
                'user_id' => $staff->user_id,
                'name' => $staff->name,
                'role' => $staff->designation->name ?? 'Sub Agent',
                'email' => $staff->email,
                'phone' => '+91 ' . $staff->mobile,
                'dob' => formatDateTodmY($staff->dob),
                'joiningDate' => formatDateTodmY($staff->approved_date),
                'avatar' => $staff->profile_photo_url,
                'status' => $statusMap[$staff->status] ?? 'Unknown',
                'stats' => [
                    'visits' => ContactCallHistory::where('user_id', $staff->id)->where('visited', 1)->count(),
                    'calls' => ContactCallHistory::where('user_id', $staff->id)->count(),
                    'deals' => Contact::where('user_id', $staff->id)->where('status', 'booking_done')->count(),
                ],
                'recentLeads' => Contact::whereIn('status', ['interested', 'follow_up', 'booking_done', 'site_visit_done'])
                    ->where('user_id', $staff->id)
                    ->orderBy('updated_at', 'desc')
                    ->take(4)
                    ->get()
                    ->map(function ($lead) {
                        return [
                            'id' => $lead->id,
                            'name' => $lead->name,
                            'status' => $lead->status,
                        ];
                    })
                    ->toArray(),
                'attendanceHistory' => $attendanceHistory,
            ];

            // Optional additional info if available
            if ($staff->address || $staff->aadhar_no || $staff->pan_no) {
                $data['address'] = $staff->address ?? '';
                $data['city'] = $staff->city->name ?? '';
                $data['state'] = $staff->state->name ?? '';
                $data['pin_code'] = $staff->pin_code ?? '';
                $data['aadharNumber'] = $staff->aadhar_no ?? '';
                $data['aadharFront'] = $staff->aadhar_front_photo_url;
                $data['aadharBack'] = $staff->aadhar_back_photo_url;
                $data['panNumber'] = $staff->pan_no ?? '';
                $data['panPhoto'] = $staff->pan_photo_url;
            }

            // Include rejected reason if applicable
            if ($staff->status == 2 && !empty($staff->rejected_reason)) {
                $data['rejectedReason'] = $staff->rejected_reason;
            }

            return response()->json([
                'status' => true,
                'message' => 'Staff fetched successfully!',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Staff List Fetching Error: ', [
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went wrong!'], 500);
        }
    }

    public function staffsStatusUpdate(Request $request){ 
        $validated = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'status' => 'required|in:0,1,2,3',
            'rejected_reason' => 'required_if:status,2|string|max:255',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        try {
            $role = User::where('id', $request->user()->id)->first()->role;

            if ($role == 'Superadmin' && ($request->status == 2 || $request->status == 3)) {
                User::where('id', $request->id)
                    ->update([
                        'status' => $request->status,
                        'rejected_reason' => $request->rejected_reason ?? null,
                        'deactivated_by_superadmin_id' => $request->user()->id,
                    ]);
            } elseif ($role == 'Agent') {
                $rejectedBysuperagent = User::where('id', $request->id)
                    ->whereNotNull('deactivated_by_superadmin_id')
                    ->exists();

                if ($rejectedBysuperagent) {
                    return response()->json(['status' => false, 'message' => 'You cannot update the status of this staff deactivated by superadmin!'], 200);
                }

                User::where('id', $request->id)
                    ->where('company_id', $request->user()->id)
                    ->update([
                        'status' => $request->status,
                        'approved_date' => $request->status == 1 ? date('Y-m-d') : DB::raw('approved_date'), // Keep old value if status is 3
                        'rejected_reason' => $request->rejected_reason ?? null,
                    ]);
            } else { // superadmin case
                User::where('id', $request->id)
                    ->update([
                        'status' => $request->status,
                        'approved_date' => $request->status == 1 ? date('Y-m-d') : DB::raw('approved_date'), // Keep old value if status is 3
                        'rejected_reason' => $request->rejected_reason ?? null,
                    ]);
            }

            return response()->json(['status'=>true, 'message'=>'Staff Status Updated Successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Staff Status Updating Error: ', [
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went wrong!'], 500);
        }
    }

    public function activeSubAgents(Request $request){
        $activeSubagents = User::select('id','name','role')
            ->where('role','Sub Agent')
            ->where('status',1)
            ->get();
        return response()->json(['status'=>true, 'message'=>'Active Projects Fetched Successfully!', 'data'=>$activeSubagents], 200);
    }

    public function leadassignStore(Request $request){
        $validated = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'contact_id' => 'required|exists:contacts,id',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'error'=>'Validation Error!'], 422);
        }

        try {
            $exist = LeadAssign::where('contact_id', $request->contact_id)
                ->where('other_assign', 0)
                ->where('status', 0)
                ->first();
            if($exist){
                LeadAssign::where('contact_id', $request->contact_id)
                    ->where('status', 0)
                    ->update([
                        'other_assign'=>1,
                    ]);
            }
            LeadAssign::create([
                'user_id'=>$request->user_id,
                'contact_id'=>$request->contact_id,
            ]);
            
            return response()->json(['status'=>true, 'message'=>'Lead Assign Successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Lead Assigning Error: ', [
                'exception' => $e->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went wrong!'], 500);
        }
    }

    public function activeAgentWiseSubAgents(Request $request){
        $records = User::select('id', 'name', 'company_name', 'profile_photo')
            ->where('role', 'Agent')
            ->where('status', 1)
            ->with([
                'subAgents' => function ($query) {
                    $query->select('id', 'name', 'approved_date', 'designation_id','company_id')
                        ->with('designation:id,name'); 
                }
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'companyName' => $item->company_name,
                    'avatar' => $item->profile_photo_url,
                    'totalContacts' => Contact::where('user_id', $item->id)->count(),
                    'totalLeads' => Contact::where('user_id', $item->id)->where('status', 'interested')->count(),
                    'sub_agents' => $item->subAgents->map(function ($sub_agent) {
                        return [
                            'id' => $sub_agent->id,
                            'name' => $sub_agent->name,
                            'designation' => $sub_agent->designation->name ?? '', 
                            'joining_date' => formatDateTodmY($sub_agent->approved_date), 
                            'totalContacts' => Contact::where('user_id', $sub_agent->id)->count(),
                            'totalLeads' => Contact::where('user_id', $sub_agent->id)->where('status', 'interested')->count(),
                        ];
                    })
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Active Sub Agent Data Fetched Successfully!', 'data'=>$records]);
    }
}
