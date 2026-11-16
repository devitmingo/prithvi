<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\{
    Log,
    Validator
};
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = Project::with('state:id,name', 'city:id,name')
            ->select('id', 'name', 'short_description', 'detailed_description', 'state_id', 'city_id', 'latitude', 'longitude', 'price', 'rera_no', 'image', 'document', 'availability_document', 'project_status', 'status', 'updated_at')
            ->get();
            if($request->user()->role != 'Superadmin'){
                $projects = $projects->where('status', 1);
            }
            $projects = $projects->values()->map(function($project){
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'short_description' => $project->short_description,
                    'detailed_description' => $project->detailed_description,
                    'state_id' => $project->state ? $project->state->id : null,
                    'state' => $project->state ? $project->state->name : null,
                    'city_id' => $project->city ? $project->city->id : null,
                    'city' => $project->city ? $project->city->name : null,
                    'price' => $project->price,
                    'rera_no' => $project->rera_no ?? null,
                    'image_url' => $project->image_url,
                    'document_url' => $project->document_url,
                    'availability_document_url' => $project->availability_document_url,
                    'project_status' => $project->project_status,
                    'status' => $project->status,
                    'updated_at_date' => $project->availability_document_url ? formatDateTodmY($project->updated_at) : null,
                    'updated_at_time' => $project->availability_document_url ? formatDateTohiA($project->updated_at) : null,
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Project Fetched Successfully!', 'data' => $projects], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'detailed_description' => 'required|string',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'price' => 'nullable|numeric',
            'rera_no' => 'nullable|string|max:50',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp',
            'document' => 'nullable|mimes:pdf',
            'availability_document' => 'nullable|mimes:pdf',
            'project_status' => 'required|in:Ongoing,Upcoming,Completed',
            'status' => 'nullable|boolean',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'errors' => 'Validation Error!'], 422);
        }

        try {
            $exist = Project::where('city_id', $request->city_id)
                ->where('name', $request->name)
                ->first();
            if($exist){
                return response()->json(['status'=>false, 'message'=>'Project with the same name already exists in this city for the user!', 'error'=>'Duplcate Entry!'], 422);
            }
            $image = upload($request->file('image'), 'projects/images');
            $document = upload($request->file('document'), 'projects/documents');
            if(isset($request->availability_document)){
                $availabilityDocument = upload($request->file('availability_document'), 'projects/availability_documents');
            }else{
                $availabilityDocument = null;
            }

            Project::create([
                'user_id' => $request->user()->id,
                'name' => $request->name,
                'short_description' => $request->short_description ?? null,
                'detailed_description' => $request->detailed_description ?? null,
                'state_id' => $request->state_id ?? null,
                'city_id' => $request->city_id ?? null,
                'price' => $request->price ?? null,
                'rera_no' => $request->rera_no ?? null,
                'image' => $image ?? null,
                'document' => $document ?? null,
                'availability_document' => $availabilityDocument,
                'project_status' => $request->project_status,
                'status' => $request->status ?? true,
            ]);
            return response()->json(['status'=>true, 'message'=>'Project Created Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Project Creation Failed: ', [
                'exceptioin' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Somthing went Wrong'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function projectsUpdate(Request $request)
    { 
        $validated = Validator::make($request->all(), [
            'id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'detailed_description' => 'required|string',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'price' => 'nullable|numeric',
            'rera_no' => 'nullable|string|max:50',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp',
            'document' => 'nullable|mimes:pdf',
            'availability_document' => 'nullable|mimes:pdf',
            'project_status' => 'required|in:Ongoing,Upcoming,Completed',
            'status' => 'nullable|boolean',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'errors' => 'Validation Error!'], 422);
        }

        try {
            $exist = Project::where('city_id', $request->city_id)
                ->where('name', $request->name)
                ->where('id', '!=', $request->id)
                ->first();
            if($exist){
                return response()->json(['status'=>false, 'message'=>'Project with the same name already exists in this city for the user!', 'error'=>'Duplcate Entry!'], 422);
            }

            $project = Project::where('id', $request->id)
                ->firstOrFail();

            if(!empty($request->image)){
                $image = upload($request->file('image'), 'projects/images');
                deleteFile($project->image, 'projects/images');
            }

            if(!empty($request->document)){
                $document = upload($request->file('document'), 'projects/documents');
                deleteFile($project->document, 'projects/documents');
            }

            if(!empty($request->availability_document)){
                $availabilityDocument = upload($request->file('availability_document'), 'projects/availability_documents');
                deleteFile($project->availability_document, 'projects/availability_documents');
            }
            
            Project::find($request->id)->update([
                'name' => $request->name,
                'short_description' => $request->short_description ?? null,
                'detailed_description' => $request->detailed_description ?? null,
                'state_id' => $request->state_id ?? null,
                'city_id' => $request->city_id ?? null,
                'price' => $request->price ?? null,
                'rera_no' => $request->rera_no ?? null,
                'image' => $image ?? $project->image,
                'document' => $document ?? $project->document,
                'availability_document' => $availabilityDocument ?? $project->availability_document,
                'project_status' => $request->project_status,
                'status' => $request->status,
            ]);
            return response()->json(['status'=>true, 'message'=>'Project Updated Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Project Updating Failed: ', [
                'exceptioin' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Somthing went Wrong'], 500);
        }
    }
}
