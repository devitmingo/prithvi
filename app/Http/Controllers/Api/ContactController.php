<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{
    Contact,
    ContactCallHistory,
    LeadAssign,
    User,
};
use Illuminate\Support\Facades\{
    Log,
    Validator
};
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request){
        $contacts = Contact::with([
                'user:id,name,role',
                'project:id,name',
                'contactCallHistories' => function ($query) {
                    $query->select('id','date','remark','status','contact_id','latitude','longitude','image')
                        ->orderBy('id', 'desc'); // 👈 order by date descending
                }
            ])
            ->select('id','user_id','name','mobile','project_id','remark','date','time','status');
            if($request->user()->role == 'Sub Agent'){
                $contacts = $contacts->where('user_id', $request->user()->id);
            }
            if($request->user()->role == 'Agent'){
                $subAgentIds = User::where('company_id', $request->user()->id)->pluck('id')->toarray();
                $userIds = array_merge($subAgentIds, [$request->user()->id]);
                $contacts = $contacts->whereIn('user_id', $userIds);
            }
            if($request->status != 'null'){
                $contacts = $contacts->where('status', $request->status);
            }
            if($request->schedule == 1){
                $contacts = $contacts->whereNotNull('date')
                    ->whereNotNull('time')
                    ->where('date', '>=', date('Y-m-d'));
            }
            $contacts = $contacts->latest('updated_at')
            ->get()
            ->map(function($item) use ($request) {
                $leadAssign = LeadAssign::with('user:id,name')
                    ->where('contact_id',$item->id)
                    ->latest()
                    ->get()
                    ->map(function($assign){
                        return [
                            'id' => $assign->id,
                            'name' => $assign->user->name ?? null,
                            'role' => 'Sub Agent',
                            'date' => formatDateTodmY($assign->created_at)
                        ];
                    });
                $item->assign_to = $leadAssign;

                if ($item->contactCallHistories) {
                    foreach ($item->contactCallHistories as $history) {
                        $history->image_url = $history->image_url; // or any processing
                    }
                }
                return $item;
            });
        return response()->json(['status'=>true, 'message'=>'Contacts Fatched Successfully', 'data'=>$contacts], 200);
    }

    public function leadassigns(Request $request){ 
        $contacts = Contact::with([
                'project:id,name',
                'contactCallHistories' => function ($query) {
                    $query->select('id','date','remark','status','contact_id')
                        ->orderBy('id', 'desc'); // 👈 order by date descending
                }
            ])
            ->select('id','name','mobile','project_id','remark','date','time','status');
           if($request->user()->role == 'Sub Agent'){ // not result expected multiple whereRelation
                $contacts = $contacts->whereRelation('leadAssigns', function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id)
                    ->where('other_assign', 0);
                }); // Sub Agent
           }
            if(isset($request->status)){
                $contacts = $contacts->where('status', $request->status);
            }
            $contacts = $contacts->latest('updated_at')
            ->get()
            ->map(function($item) use ($request){
                $leadAssign = LeadAssign::with('user:id,name')
                    ->where('contact_id',$item->id);
                    if($request->user()->role == 'Sub Agent'){
                        $leadAssign = $leadAssign->where('user_id', $request->user()->id)
                            ->where('other_assign', 0); // Sub Agent
                    }
                    $leadAssign = $leadAssign->latest()
                    ->get()
                    ->map(function($assign){
                        return [
                            'id' => $assign->id,
                            'name' => $assign->user->name ?? null,
                            'role' => 'Sub Agent',
                            'date' => formatDateTodmY($assign->created_at)
                        ];
                    });
                $item->assign_to = $leadAssign;
                return $item;
            });
        return response()->json(['status'=>true, 'message'=>'Contacts Fatched Successfully', 'data'=>$contacts], 200);
    }

    public function store(Request $request){
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:70',
            'mobile' => 'required|numeric|digits:10',
            'project_id' => 'required|exists:projects,id',
            'remark' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'time' => 'nullable',
            'status' => 'required|string|max:50',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'errors' => 'Validation Error!'], 422);
        }

        try {
            $exist = Contact::where('mobile', $request->mobile)
                ->where('project_id', $request->project_id)
                ->where('status', '!=', 'booking_done')
                ->where('updated_at', '>=', now()->subDays(25))
                ->latest('id')
                ->first();
            if(!$exist){
                $contact = Contact::create([
                    'user_id' => $request->user()->id,
                    'name' => $request->name,
                    'mobile' => $request->mobile,
                    'project_id' => $request->project_id,
                    'remark' => $request->remark,
                    'date' => formatDateToYmd($request->date),
                    'time' => $request->time ? date('H:i:s', strtotime($request->time)) : null,
                    'status' => $request->status,
                ]);
            }else{
                Contact::find($exist->id)->update([
                    'user_id' => $request->user()->id,
                    'name' => $request->name,
                    'mobile' => $request->mobile,
                    'project_id' => $request->project_id,
                    'remark' => $request->remark,
                    'date' => formatDateToYmd($request->date),
                    'time' => $request->time ? date('H:i:s', strtotime($request->time)) : null,
                    'status' => $request->status,
                ]);
                $contact = Contact::find($exist->id);
            }

            if(isset($request->image)){
                $image = upload($request->file('image'), 'visits/');
            }
            $contact->contactCallHistories()->create([
                'user_id' => $contact->user_id,
                'date' => date('Y-m-d'),
                'remark' => $contact->remark,
                'latitude' => $request->latitude ?? null,
                'longitude' => $request->longitude ?? null,
                'image' => $image ?? null,
                'visited' => $contact->status == 'site_visit_done' ? 1 : 0,
                'status' => $contact->status,
            ]);
            return response()->json(['status'=>true, 'message'=>'Contact Created Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Contact Creation Failed: ', [
                'exceptioin' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went Wrong'], 500);
        }
    }

    public function update(Request $request){
        $validated = Validator::make($request->all(), [
            'id' => 'required|exists:contacts,id',
            'name' => 'required|string|max:70',
            'mobile' => 'required|numeric|digits:10',
            'project_id' => 'required|exists:projects,id',
            'remark' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'time' => 'nullable',
            'status' => 'required|string|max:50',
        ]);
        if($validated->fails()){
            return response()->json(['status'=>false, 'message'=>$validated->errors()->first(), 'errors' => 'Validation Error!'], 422);
        }

        try {
            Contact::find($request->id)->update([
                'user_id' => $request->user()->id,
                'name' => $request->name,
                'mobile' => $request->mobile,
                'project_id' => $request->project_id,
                'remark' => $request->remark,
                'date' => formatDateToYmd($request->date),
                'time' => $request->time ? date('H:i:s', strtotime($request->time)) : null,
                'status' => $request->status,
            ]);
            if(isset($request->image)){
                $image = upload($request->file('image'), 'visits/');
            }
            $contact = Contact::find($request->id);
            $contact->contactCallHistories()->create([
                'user_id' => $contact->user_id,
                'date' => date('Y-m-d'),
                'remark' => $contact->remark,
                'latitude' => $request->latitude ?? null,
                'longitude' => $request->longitude ?? null,
                'image' => $image ?? null,
                'visited' => $contact->status == 'site_visit_done' ? 1 : 0,
                'status' => $contact->status,
            ]);
            return response()->json(['status'=>true, 'message'=>'Contact Updated Successfully!'], 200);
        } catch (\Exception $ex) {
            Log::error('Contact Updating Failed: ', [
                'exceptioin' => $ex->getMessage(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return response()->json(['status'=>false, 'message'=>'Something went wrong!', 'error'=>'Something went Wrong'], 500);
        }
    }

    public function show(Request $request){
        $contact = Contact::with([
            'project:id,name',
            'contactCallHistories' => function ($query) {
                $query->select('id','date','remark','status','contact_id')
                    ->orderBy('id', 'desc'); // 👈 order by date descending
            }
        ])
        ->select('id','name','mobile','project_id','remark','date','time','status')
        ->where('user_id', $request->user()->id)
            ->where('id', $request->id)
            ->latest('updated_at')
            ->first();
        if($contact){
            $leadAssign = LeadAssign::with('user:id,name')
                ->where('contact_id',$contact->id)
                ->latest()
                ->get()
                ->map(function($assign){
                    return [
                        'id' => $assign->id,
                        'name' => $assign->user->name ?? null,
                        'role' => 'Sub Agent',
                        'date' => formatDateTodmY($assign->created_at)
                    ];
                });
                $contact->assign_to = $leadAssign;
            return response()->json(['status'=>true, 'message'=>'Contact Fatched Successfully', 'data'=>$contact], 200);
        }else{
            return response()->json(['status'=>false, 'message'=>'Data Not Found!', 'error'=>'Something went Wrong'], 500);
        } 
    }

    public function bookingDone(Request $request){
        $contacts = Contact::with('project:id,name', 'user:id,company_name,name,company_logo,profile_photo')
            ->select('id','name','project_id','user_id','updated_at')
            ->where('status', 'booking_done');
            if($request->user()->role != 'Superadmin'){
                $contacts = $contacts->where('user_id', $request->user()->id);
            }
            $contacts =$contacts->get()
            ->map(function($item){
                return [
                    'id' => $item->id,
                    'agent_avatar' => $item->user->profile_photo_url,
                    'agent_name' => $item->user->name ?? '',
                    'agent_company_name' => $item->user->company_name ?? '',
                    'project_name' => $item->project->name ?? '',
                    'client_name' => $item->name,
                    'date' => formatdateTodmY($item->updated_at),
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Contact Booking Done data Fetched Successfully!', 'data'=>$contacts], 200);
    }

    public function activeLeads(Request $request){
        
        $contacts = User::with([
                'contacts' => function($query) {
                    $query->select('id','name','mobile','project_id','user_id')
                        ->with('project:id,name')
                        ->where('status', 'interested')
                        ->latest();
                }
            ])
            ->select('id','name','company_name','company_logo')
            ->where('role', 'Agent')
            //->where('status', 1)
            ->get()
            ->map(function($item){
                $subAgentIds = User::where('company_id', $item->id)->pluck('id')->toArray();

                // Get leads for the agent
                $agentLeads = $item->contacts->map(function($lead) {
                    return [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'mobile' => $lead->mobile,
                        'project_name' => $lead->project->name ?? '',
                    ];
                });

                $subAgentLeads = Contact::select('id','name','mobile','project_id','user_id')
                        ->with('project:id,name')
                        ->where('status', 'interested')
                        ->whereIn('user_id', $subAgentIds)
                        ->latest()
                        ->get()
                        ->map(function($subLead) {
                            return [
                                'id' => $subLead->id,
                                'name' => $subLead->name,
                                'mobile' => $subLead->mobile,
                                'project_name' => $subLead->project->name ?? '',
                            ];
                        });
                $allLeads = $agentLeads->isEmpty()
                    ? $subAgentLeads
                    : $agentLeads->merge($subAgentLeads);

                return [
                    'id' => $item->id,
                    'avatar' => $item->company_logo_url,
                    'name' => $item->name ?? '',
                    'company_name' => $item->company_name ?? '',
                    'leads' => $allLeads,
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Contact Booking Done data Fetched Successfully!', 'data'=>$contacts], 200);
    }

    public function agentVisit(Request $request){
        $agents = User::select('id','name','profile_photo','company_name')
            ->where('role', 'Agent');
            $agents = $agents->latest('updated_at')
            ->get()
            ->map(function($item){
                $subAgentIds = User::where('company_id', $item->id)->where('role', 'Sub Agent')->pluck('id')->toArray();
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'avatar' => $item->profile_photo_url,
                    'company_name' => $item->company_name,
                    'self_visits_count' => ContactCallHistory::where('user_id', $item->id)->where('visited', 1)->count(),
                    'team_visits_count' => ContactCallHistory::whereIn('user_id', $subAgentIds)->where('visited', 1)->count(),
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Contacts Agent Visit Fatched Successfully', 'data'=>$agents], 200);
    }

    public function agentTeamVisit(Request $request){
        $agents = ContactCallHistory::with('contact:id,name,project_id', 'contact.project:id,name')
            ->select('id','user_id','contact_id','date','latitude','longitude','image','created_at')
            ->where('visited', 1);
            if(isset($request->agent_id)){
                $agents = $agents->where('user_id', $request->agent_id);
            }
            $agents = $agents->latest('updated_at')
            ->get()
            ->map(function($item){
                return [
                    'id' => $item->id, 
                    'user_id' => $item->user_id, 
                    'visitorName' => $item->contact->name ?? '', 
                    'project' => $item->contact->project->name ?? '', 
                    'date' => $item->date, 
                    'time' => formatDateTohiA($item->created_at), 
                    'image' => $item->image_url, 
                    'latitude' => $item->latitude, 
                    'longitude' => $item->longitude
                ];
            });

        $subAgentIds = User::where('company_id', $request->agent_id)->pluck('id')->toArray();
        $subAgents = User::with([
                'contactCallHistories' => function ($q) {
                    $q->select('id','user_id','contact_id','date','latitude','longitude','image','created_at')
                    ->where('visited', 1)
                    ->latest('updated_at');
                },
                'designation:id,name'
            ])
            ->select('id','user_id','contact_id','date','latitude','longitude','image','created_at')
            ->select('id','name','designation_id','user_id')
            ->whereIn('id', $subAgentIds)
            ->get()
             ->map(function($item){
                return [
                    'staffId' => $item->user_id,
                    'staffName' => $item->name,
                    'designation' => $item->designation->name ?? '',
                    'visits' => $item->contactCallHistories->map(function ($history) {
                        return [
                            'id' => $history->id, 
                            'visitorName' => $history->contact->name ?? '', 
                            'project' => $history->contact->project->name ?? '', 
                            'date' => $history->date, 
                            'time' => formatDateTohiA($history->created_at), 
                            'image' => $history->image_url, 
                            'latitude' => $history->latitude, 
                            'longitude' => $history->longitude
                        ];
                    })
                ];
            });;
        
        $data = [
            'self_visits' => $agents,
            'team_visits' => $subAgents
        ];
        return response()->json(['status'=>true, 'message'=>'Contacts Agent Visit Fatched Successfully', 'data'=>$data], 200);
    }

}
