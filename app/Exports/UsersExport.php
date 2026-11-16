<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UsersExport implements FromView
{
    protected $records;
    public $agentId;
    public $subAgentId;
    public $month;

    public function __construct($agentId = null, $subAgentId = null, $month = null)
    { 
        $this->agentId = $agentId;
        $this->subAgentId = $subAgentId;
        $this->month = $month;

        $status = [
            '0' => 'Pending',
            '1' => 'Approved',
            '2' => 'Rejected',
            '3' => 'Deactivated'
        ];

        // $this->records = User::with([
        //         'state:id,name', 
        //         'city:id,name',
        //         'contacts:id,user_id,name,mobile,project_id,remark,date,time,status',
        //         'contacts.project:id,name',
        //         'contacts.contactcallHistories:id,contact_id,date,remark,visited,status',
        //         'subAgents:id,user_id,name,mobile,email,role,state_id,city_id,pin_code,address,aadhar_no,pan_no,company_id,designation_id,dob,approved_date,status,rejected_reason',
        //         'subAgents.state:id,name',
        //         'subAgents.city:id,name',
        //         'subAgents.designation:id,name',
        //         'subAgents.contacts:id,user_id,name,mobile,project_id,remark,date,time,status',
        //         'subAgents.contacts.project:id,name',
        //         'subAgents.contacts.contactcallHistories:id,contact_id,date,remark,visited,status',
        //     ])
        //     ->select('id','user_id','name','mobile','email','role','state_id','city_id','pin_code','address','aadhar_no','pan_no','company_name','gst_no','approved_date','status','rejected_reason','company_id')
        //     ->where('role', 'Agent');

        // // -------------------
        // // Agent Filter
        // // -------------------
        // if ($this->agentId && $this->agentId !== "null") {
        //     $this->records->where('id', $this->agentId);
        // }

        // // -------------------
        // // Sub Agent Filter
        // // -------------------
        // if ($this->subAgentId && $this->subAgentId !== "null") {
        //     $this->records->whereHas('subAgents', function($q){
        //         $q->where('id', $this->subAgentId);
        //     });
        // }

        // // -------------------
        // // Month Filter
        // // -------------------
        // if ($this->month && $this->month !== "null") {
        //     $months = (int)$this->month;
        //     $this->records->where('approved_date', '<=', now()->subMonths($months));
        // } else {
        //     $months = null;
        // }

        // // -------------------
        // // Fetch and map records
        // // -------------------
        // $this->records = $this->records->get()->map(function ($user) use ($status, $months) {

        //     $user->approved_date = formatDateTodmY($user->approved_date);
        //     $user->status = $status[$user->status];

        //     // Filter contacts by month
        //     $contactQuery = $user->contacts();
        //     if ($months) {
        //         $contactQuery->where('created_at', '<=', now()->subMonths($months));
        //     }

        //     $user->total_contacts = $contactQuery->count();
        //     $user->total_leads = (clone $contactQuery)->where('status', 'interested')->count();
        //     $user->total_deals = (clone $contactQuery)->where('status', 'booking_done')->count();

        //     // Filter sub-agents
        //     $user->subAgents = $user->subAgents->when($this->subAgentId && $this->subAgentId !== "null", function ($query) {
        //         return $query->where('id', $this->subAgentId);
        //     })->map(function ($subAgent) use ($status, $months) {

        //         $subAgent->approved_date = formatDateTodmY($subAgent->approved_date);
        //         $subAgent->status = $status[$subAgent->status];

        //         $subContactQuery = $subAgent->contacts();
        //         if ($months) {
        //             $subContactQuery->where('created_at', '<=', now()->subMonths($months));
        //         }

        //         $subAgent->total_contacts = $subContactQuery->count();
        //         $subAgent->total_leads = (clone $subContactQuery)->where('status', 'interested')->count();
        //         $subAgent->total_deals = (clone $subContactQuery)->where('status', 'booking_done')->count();

        //         return $subAgent;
        //     });

        //     return $user;
        // });


        // -----------------------------------------------------
        // Month Filter
        // -----------------------------------------------------
        $months = ($this->month && $this->month !== "null")
            ? (int)$this->month
            : null;

        $subAgentId = $this->subAgentId;
        $agentId = $this->agentId;

        // -----------------------------------------------------
        // Base User Query WITH MONTH FILTER ON MAIN TABLE
        // -----------------------------------------------------
        $this->records = User::with([
            'state:id,name', 
            'city:id,name',

            'contacts:id,user_id,name,mobile,project_id,remark,date,time,status,created_at',
            'contacts.project:id,name',
            'contacts.contactcallHistories:id,contact_id,date,remark,visited,status,created_at',

            'subAgents:id,user_id,name,mobile,email,role,state_id,city_id,pin_code,address,aadhar_no,pan_no,company_id,designation_id,dob,approved_date,status,rejected_reason,created_at',
            'subAgents.state:id,name',
            'subAgents.city:id,name',
            'subAgents.designation:id,name',

            'subAgents.contacts:id,user_id,name,mobile,project_id,remark,date,time,status,created_at',
            'subAgents.contacts.project:id,name',
            'subAgents.contacts.contactcallHistories:id,contact_id,date,remark,visited,status,created_at',
        ])
        ->select('id','user_id','name','mobile','email','role','state_id','city_id',
                'pin_code','address','aadhar_no','pan_no','company_name','gst_no',
                'approved_date','status','rejected_reason','company_id')
        ->where('role', 'Agent');

        // -----------------------------------------------------
        // Agent Filter
        // -----------------------------------------------------
        if ($agentId && $agentId !== "null") {
            $this->records->where('id', $agentId);
        }

        // -----------------------------------------------------
        // Sub Agent Filter (Main Query)
        // -----------------------------------------------------
        if ($subAgentId && $subAgentId !== "null") {
            $this->records->whereHas('subAgents', function ($q) use ($subAgentId) {
                $q->where('id', $subAgentId);
            });
        }

        // -----------------------------------------------------
        // MAIN TABLE MONTH FILTER (NEW - this was missing)
        // -----------------------------------------------------
        if ($months) {
            $this->records->whereDate('approved_date', '<=', now()->subMonths($months)->format('Y-m-d'));
        }

        // -----------------------------------------------------
        // Fetch Data and Apply Child Month Filters
        // -----------------------------------------------------
        $this->records = $this->records->get()->map(function ($user) use ($status, $months, $subAgentId) {

            $user->approved_date = formatDateTodmY($user->approved_date);
            $user->status = $status[$user->status];

            // -----------------------------------------------------
            // USER CONTACTS MONTH FILTER
            // -----------------------------------------------------
            $user->contacts = $months
                ? $user->contacts->where('created_at', '<=', now()->subMonths($months))
                : $user->contacts;

            $user->total_contacts = $user->contacts->count();
            $user->total_leads = $user->contacts->where('status', 'interested')->count();
            $user->total_deals = $user->contacts->where('status', 'booking_done')->count();

            // CONTACT CALL HISTORY FILTER
            $user->contacts->each(function ($c) use ($months) {
                $c->contactcallHistories = $months
                    ? $c->contactcallHistories->where('created_at', '<=', now()->subMonths($months))
                    : $c->contactcallHistories;
            });

            // -----------------------------------------------------
            // SUB AGENTS + FILTER
            // -----------------------------------------------------
            $user->subAgents = $user->subAgents
                ->filter(function ($subAgent) use ($months) {
        if (!$months) return true; // no filter
        return $subAgent->approved_date <= now()->subMonths($months)->format('Y-m-d');
    })
                ->when($subAgentId && $subAgentId !== "null", function ($list) use ($subAgentId) {
                    return $list->where('id', $subAgentId);
                })
                ->map(function ($subAgent) use ($status, $months) {

                    $subAgent->approved_date = formatDateTodmY($subAgent->approved_date);
                    $subAgent->status = $status[$subAgent->status];

                    // SUB AGENT contacts
                    $subAgent->contacts = $months
                        ? $subAgent->contacts->where('created_at', '<=', now()->subMonths($months))
                        : $subAgent->contacts;

                    $subAgent->total_contacts = $subAgent->contacts->count();
                    $subAgent->total_leads = $subAgent->contacts->where('status', 'interested')->count();
                    $subAgent->total_deals = $subAgent->contacts->where('status', 'booking_done')->count();

                    // SUB AGENT contact call history
                    $subAgent->contacts->each(function ($c) use ($months) {
                        $c->contactcallHistories = $months
                            ? $c->contactcallHistories->where('created_at', '<=', now()->subMonths($months))
                            : $c->contactcallHistories;
                    });

                    return $subAgent;
                });

            return $user;
        });

    }

    public function view(): View
    {  
        return view('excel.users', [
            'records' => $this->records,
            'agentId' => $this->agentId,
            'subAgentId' => $this->subAgentId,
            'month' => now()->subMonths((int)$this->month),
        ]);
    }
}