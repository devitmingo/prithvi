<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{
    Offer,
    Project
};
use Illuminate\Support\Facades\{
    Log,
    Validator,
};
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request){
        $offers = Offer::with('project:id,name,image')
            ->select('id', 'project_id', 'from_date', 'to_date', 'description');
        if($request->user()->role != "Superadmin"){
            $offers = $offers->wheredate('from_date', '<=', date('Y-m-d'))
                ->wheredate('to_date', '>=', date('Y-m-d'));
        }
        $offers = $offers->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'projectId' => $item->project->id ?? null,
                    'projectName' => $item->project->name ?? null,
                    'fromDate' => $item->from_date,
                    'toDate' => $item->to_date,
                    'description' => $item->description,
                    'image' => $item->project->image_url ?? null,
                ];
            });

        return response()->json(['status' => true, 'message' => 'Offers fetched successfully!', 'data' => $offers], 200);
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'description' => 'required|string',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'errors' => 'Validation Error!'], 422);
        }

        try {
            $exist = Offer::where('project_id', $request->project_id)
                ->wheredate('from_date', formatDateToYmd($request->from_date))
                ->wheredate('to_date', formatDateToYmd($request->to_date))
                ->first();
            if($exist){
                return response()->json(['status'=>false, 'message'=>'Offer with the same dates already exists/!', 'error'=>'Duplcate Entry!'], 422);
            }

            Offer::create([
                'user_id' => $request->user()->id,
                'project_id' => $request->project_id,
                'from_date' => formatDateToYmd($request->from_date),
                'to_date' => formatDateToYmd($request->to_date),
                'description' => $request->description,
            ]);
            return response()->json(['status'=>true, 'message'=>'Offer Created Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Offer Creation Failed: ', [
                'exceptioin' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Somthing went Wrong'], 500);
        }
    }

     public function update(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required|exists:offers,id',
            'project_id' => 'required|exists:projects,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'description' => 'required|string',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'errors' => 'Validation Error!'], 422);
        }

        try {
            $exist = Offer::where('project_id', $request->project_id)
                ->wheredate('from_date', formatDateToYmd($request->from_date))
                ->wheredate('to_date', formatDateToYmd($request->to_date))
                ->where('id', $request->id)
                ->first();
            if($exist){
                return response()->json(['status'=>false, 'message'=>'Offer with the same dates already exists/!', 'error'=>'Duplcate Entry!'], 422);
            }

            Offer::find($request->id)->update([
                'user_id' => $request->user()->id,
                'project_id' => $request->project_id,
                'from_date' => formatDateToYmd($request->from_date),
                'to_date' => formatDateToYmd($request->to_date),
                'description' => $request->description,
            ]);
            return response()->json(['status'=>true, 'message'=>'Offer Updated Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Offer Updating Failed: ', [
                'exceptioin' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Somthing went Wrong'], 500);
        }
    }
}