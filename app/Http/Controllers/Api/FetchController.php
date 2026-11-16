<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masters\{
    State,
    City,
    Designation
};
use App\Models\{
    Setting,
    Faq,
    User,
    Project,
};
use Illuminate\Http\Request;

class FetchController extends Controller
{
    public function states(Request $request){
        $states = State::select('id','name')->where('status',1)
            ->get()
            ->map(function($item){
                return [
                    'label' => $item->name,
                    'value' => $item->id
                ];
            });
        return response()->json(['status'=>true, 'message'=>'States Fetched Successfully!', 'data'=>$states], 200);
    }

    public function cities(Request $request){
        $cities = City::with('state:id,name')
            ->select('id','name','state_id')
            ->where('status',1)
            ->when($request->has('state_id'), function ($query) use ($request) {
                $query->where('state_id',$request->state_id);
            })
            ->get()
            ->map(function($item){
                return [
                    'label' => $item->name,
                    'value' => $item->id
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Cities Fetched Successfully!', 'data'=>$cities], 200);
    }

    public function designations(Request $request){
        $designations = Designation::select('id','name')->where('status',1)
            ->get()
            ->map(function($item){
                return [
                    'label' => $item->name,
                    'value' => $item->id
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Designations Fetched Successfully!', 'data'=>$designations], 200);
    }


    public function termsAndConditions(){
        $termsAndConditions = Setting::where('key_name','terms_conditions')->pluck('value')->first();
        return response()->json(['status'=>true, 'message'=>'Terms And Conditions Fetched Successfully!', 'data'=>$termsAndConditions], 200);
    }

    public function privacyPolicy(){
        $privacyPolicy = Setting::where('key_name','privacy_policy')->pluck('value')->first();
        return response()->json(['status'=>true, 'message'=>'Privacy Policy Fetched Successfully!', 'data'=>$privacyPolicy], 200);
    }

    public function faqs(){
        $faqs = Faq::select('id','sequence','question','answer')
            ->where('status',1)
            ->orderBy('sequence','asc')
            ->get();
        $contact = [
            'mobile' => Setting::where('key_name','mobile')->pluck('value')->first(),
            'email' => Setting::where('key_name','email')->pluck('value')->first(),
        ];
        return response()->json(['status'=>true, 'message'=>'FAQs Fetched Successfully!', 'data'=>$faqs, 'contact'=>$contact], 200);
    }

    public function userAgreement(){
        $userAgreement = Setting::where('key_name','user_agreement')->pluck('value')->first();
        return response()->json(['status'=>true, 'message'=>'User Agreement Fetched Successfully!', 'data'=>$userAgreement], 200);
    }

    public function appInfo(){
        $appInfo = [
            'company_name' => Setting::where('key_name','company_name')->pluck('value')->first(),
            'app_version' => Setting::where('key_name','app_version')->pluck('value')->first(),
            'email' => Setting::where('key_name','email')->pluck('value')->first(),
            'mobile' => Setting::where('key_name','mobile')->pluck('value')->first(),
            'address' => Setting::where('key_name','address')->pluck('value')->first(),
            'web_url' => Setting::where('key_name','web_url')->pluck('value')->first(),
            'facebook_url' => Setting::where('key_name','facebook_url')->pluck('value')->first(),
            'twitter_url' => Setting::where('key_name','twitter_url')->pluck('value')->first(),
            'instagram_url' => Setting::where('key_name','instagram_url')->pluck('value')->first(),
        ];
        return response()->json(['status'=>true, 'message'=>'App Info Fetched Successfully!', 'data'=>$appInfo], 200);
    }

    public function activeAgents(Request $request){
        $agents = User::select('id', 'name', 'company_name', 'mobile')
            ->where('role', 'Agent')
            ->where('status', 1) // Approved agents only
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->company_name 
                        . ($item->name ? ' (' . $item->name . ')' : '') 
                        . ($item->mobile ? '(' . $item->mobile . ')' : ''),
                    'value' => $item->id,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Agents fetched successfully!',
            'data' => $agents
        ], 200);
    }

    public function activeProjects(Request $request){
        $activeProjects = Project::with('city:id,name', 'state:id,name')
            ->select('id','state_id','city_id','name')->where('status',1)
            ->get()
            ->map(function($item){
                return [
                    'label' => $item->name . ' (' . $item->city->name . ', ' . $item->state->name . ')',
                    'value' => $item->id
                ];
            });
        return response()->json(['status'=>true, 'message'=>'Active Projects Fetched Successfully!', 'data'=>$activeProjects], 200);
    }

}
