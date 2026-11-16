<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Attendance,
    User 
};
use Illuminate\Support\Facades\{
    Log,
    Validator
};

class AttendanceController extends Controller
{
   public function index(Request $request){
        $attendances = User::with([
                'designation:id,name',
                'attendances' => function ($query) {
                    $query->select('id','user_id','latitude','longitude','time','image')
                        ->whereDate('date', date('Y-m-d'));
                }
            ])
            ->select('id', 'name', 'profile_photo', 'designation_id')
            ->where('status', 1);
            if($request->user()->role == "Agent"){
                $attendances = $attendances->where('role', 'Sub Agent')
                    ->where('company_id', $request->user()->id);
            }
            if($request->user()->role == "Superadmin"){
                $attendances = $attendances->where('role', 'Agent');
            }
            $attendances = $attendances->get()
            ->map(function ($user) {
                $attendance = $user->attendances->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->profile_photo_url,
                    'role' => $user->designation->name ?? $user->role,
                    'status' => $attendance ? 'present' : 'absent',
                    'checkInTime' => $attendance ? date('h:i A', strtotime($attendance->time)) : null,
                    'location' => $attendance ? ($attendance->latitude . ', ' . $attendance->longitude) : null,
                    'selfie' => $attendance->image_url ?? null,
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Attendance Data Fetched Successfully!', 'data'=>$attendances], 200);
    }

    public function store(Request $request){
        $validated = Validator::make($request->all(), [
            'image' => 'nullable|mimes:jpg,jpeg,png,webp',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'errors' => 'Validation Error!'], 422);
        }

        try {
            $exist = Attendance::where('user_id', $request->user()->id)
                ->where('date', date('Y-m-d'))
                ->first();
            if($exist){
                return response()->json(['status'=>false, 'message'=>'Attendance with the same date already exists!', 'error'=>'Duplcate Entry!'], 422);
            }
            $image = upload($request->file('image'), 'attendances');
            Attendance::create([
                'user_id' => $request->user()->id,
                'image' => $image ?? null,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'date' => date('Y-m-d'),
                'time' => date('H:i:s'),
            ]);
            return response()->json(['status'=>true, 'message'=>'Attendance Created Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Attendance Creation Failed: ', [
                'exceptioin' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Somthing went Wrong'], 500);
        }
    }
}
