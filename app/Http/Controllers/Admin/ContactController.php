<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use App\Models\ContactCallHistory;
use App\Models\LeadAssign;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts / leads.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $teamUserIds = [];
        $agentTeamIds = [];
        if (!$isSuperAdmin) {
            $teamUserIds = User::where('id', $currentUser->id)
                ->orWhere('company_id', $currentUser->id)
                ->pluck('id')->toArray();
        } else {
            if ($request->filled('agent_id')) {
                $agentTeamIds = User::where('id', $request->agent_id)
                    ->orWhere('company_id', $request->agent_id)
                    ->pluck('id')->toArray();
            }
        }

        // View Mode: 'cards' (Project-wise Cards) vs 'table' (Flat Inquiries Table)
        // Default to 'cards' when viewing All Inquiries without specific project/user/status/search filters
        $hasSpecificFilter = $request->filled('status') || $request->filled('project_id') || $request->filled('user_id') || $request->filled('search') || $request->filled('date_from') || $request->filled('date_to');
        $viewMode = $request->get('view', $hasSpecificFilter ? 'table' : 'cards');

        $query = Contact::with(['project', 'user.company', 'leadAssigns.user']);

        // Team scoping
        if (!$isSuperAdmin) {
            $query->whereIn('user_id', $teamUserIds);
        } else {
            if ($request->filled('agent_id')) {
                $query->whereIn('user_id', $agentTeamIds);
            }
        }

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'active_leads') {
                $query->whereIn('status', ['follow_up', 'call_later', 'site_visit_done', 'visit_scheduled', 'booking_done', 'interested']);
            } elseif ($request->status === 'leads') {
                $query->where('status', '!=', 'call_not_connecting');
            } elseif ($request->status === 'call_not_connecting') {
                $query->whereIn('status', ['call_not_connecting', 'not_interested']);
            } elseif ($request->status === 'site_visit_done') {
                $query->whereIn('status', ['site_visit_done', 'visit_scheduled']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('user_id')) {
            $userId = $request->user_id;
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('leadAssigns', function ($sub) use ($userId) {
                      $sub->where('user_id', $userId);
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Count how many times each mobile number has been called across the system
        $mobiles = $contacts->pluck('mobile')->filter()->unique();
        $callCountsByPhone = [];
        if ($mobiles->isNotEmpty()) {
            $callCountsByPhone = ContactCallHistory::join('contacts', 'contact_call_histories.contact_id', '=', 'contacts.id')
                ->whereIn('contacts.mobile', $mobiles)
                ->select('contacts.mobile', \Illuminate\Support\Facades\DB::raw('count(contact_call_histories.id) as total_calls'))
                ->groupBy('contacts.mobile')
                ->pluck('total_calls', 'contacts.mobile')
                ->toArray();
        }

        // Overall stats calculation
        $statsQuery = Contact::query();
        if (!$isSuperAdmin) {
            $statsQuery->whereIn('user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $statsQuery->whereIn('user_id', $agentTeamIds);
        }

        if ($request->filled('user_id')) {
            $userId = $request->user_id;
            $statsQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('leadAssigns', function ($sub) use ($userId) {
                      $sub->where('user_id', $userId);
                  });
            });
        }

        if ($request->filled('project_id')) {
            $statsQuery->where('project_id', $request->project_id);
        }

        $stats = [
            'total' => (clone $statsQuery)->where('status', '!=', 'call_not_connecting')->count(),
            'total_inquiries' => (clone $statsQuery)->count(),
            'active_leads' => (clone $statsQuery)->whereIn('status', ['follow_up', 'call_later', 'site_visit_done', 'visit_scheduled', 'booking_done', 'interested'])->count(),
            'follow_up' => (clone $statsQuery)->where('status', 'follow_up')->count(),
            'call_later' => (clone $statsQuery)->where('status', 'call_later')->count(),
            'site_visit_done' => (clone $statsQuery)->whereIn('status', ['site_visit_done', 'visit_scheduled'])->count(),
            'booking_done' => (clone $statsQuery)->where('status', 'booking_done')->count(),
            'not_connecting' => (clone $statsQuery)->whereIn('status', ['call_not_connecting', 'not_interested'])->count(),
        ];

        // All active projects with relationships
        $projects = Project::with(['city', 'state'])->where('status', 1)->orderBy('name')->get();

        // Project-wise Aggregated Stats for Project Cards View
        // NOTE: "call_not_connecting" (Unreachable/Not Connecting) is strictly separated as call outcome and NOT counted as lead
        $projectStatsQuery = Contact::select(
            'project_id',
            \Illuminate\Support\Facades\DB::raw('sum(case when status != "call_not_connecting" then 1 else 0 end) as total_leads'),
            \Illuminate\Support\Facades\DB::raw('count(*) as total_inquiries'),
            \Illuminate\Support\Facades\DB::raw('sum(case when status in ("follow_up", "call_later", "site_visit_done", "visit_scheduled", "booking_done", "interested") then 1 else 0 end) as active_leads'),
            \Illuminate\Support\Facades\DB::raw('sum(case when status = "follow_up" then 1 else 0 end) as follow_up'),
            \Illuminate\Support\Facades\DB::raw('sum(case when status = "call_later" then 1 else 0 end) as call_later'),
            \Illuminate\Support\Facades\DB::raw('sum(case when status in ("site_visit_done", "visit_scheduled") then 1 else 0 end) as site_visit_done'),
            \Illuminate\Support\Facades\DB::raw('sum(case when status = "booking_done" then 1 else 0 end) as booking_done'),
            \Illuminate\Support\Facades\DB::raw('sum(case when status = "visit_scheduled" then 1 else 0 end) as visit_scheduled'),
            \Illuminate\Support\Facades\DB::raw('sum(case when status in ("call_not_connecting", "not_interested") then 1 else 0 end) as not_connecting')
        );

        if (!$isSuperAdmin) {
            $projectStatsQuery->whereIn('user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $projectStatsQuery->whereIn('user_id', $agentTeamIds);
        }

        if ($request->filled('user_id')) {
            $userId = $request->user_id;
            $projectStatsQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('leadAssigns', function ($sub) use ($userId) {
                      $sub->where('user_id', $userId);
                  });
            });
        }

        $projectStats = $projectStatsQuery->groupBy('project_id')->get()->keyBy('project_id');

        // Total call attempts logged per project
        $projectCallsQuery = ContactCallHistory::join('contacts', 'contact_call_histories.contact_id', '=', 'contacts.id')
            ->select('contacts.project_id', \Illuminate\Support\Facades\DB::raw('count(contact_call_histories.id) as total_calls'));

        if (!$isSuperAdmin) {
            $projectCallsQuery->whereIn('contacts.user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $projectCallsQuery->whereIn('contacts.user_id', $agentTeamIds);
        }

        if ($request->filled('user_id')) {
            $projectCallsQuery->where('contact_call_histories.user_id', $request->user_id);
        }

        $projectCalls = $projectCallsQuery->groupBy('contacts.project_id')
            ->pluck('total_calls', 'contacts.project_id')
            ->toArray();

        $projectCards = [];
        foreach ($projects as $proj) {
            $ps = $projectStats->get($proj->id);
            $totalLeads = (int)($ps->total_leads ?? 0);
            $totalInquiries = (int)($ps->total_inquiries ?? 0);
            $activeLeads = (int)($ps->active_leads ?? 0);
            $followUp = (int)($ps->follow_up ?? 0);
            $callLater = (int)($ps->call_later ?? 0);
            $siteVisit = (int)($ps->site_visit_done ?? 0);
            $booking = (int)($ps->booking_done ?? 0);
            $notConnecting = (int)($ps->not_connecting ?? 0);
            $callsCount = (int)($projectCalls[$proj->id] ?? 0);
            $conversionRate = $totalLeads > 0 ? round(($booking / $totalLeads) * 100, 1) : 0;

            $projectCards[] = [
                'project' => $proj,
                'total_leads' => $totalLeads,
                'total_inquiries' => $totalInquiries,
                'active_leads' => $activeLeads,
                'follow_up' => $followUp,
                'call_later' => $callLater,
                'site_visit' => $siteVisit,
                'booking' => $booking,
                'not_connecting' => $notConnecting,
                'calls_count' => $callsCount,
                'conversion_rate' => $conversionRate,
            ];
        }

        // Sort Project Cards:
        $sort = $request->get('sort', 'leads_desc');
        if ($sort === 'active_desc' || $sort === 'leads_desc') {
            usort($projectCards, function ($a, $b) {
                if ($b['active_leads'] !== $a['active_leads']) {
                    return $b['active_leads'] <=> $a['active_leads'];
                }
                if ($b['total_leads'] !== $a['total_leads']) {
                    return $b['total_leads'] <=> $a['total_leads'];
                }
                return strcmp($a['project']->name, $b['project']->name);
            });
        } elseif ($sort === 'total_desc') {
            usort($projectCards, function ($a, $b) {
                if ($b['total_leads'] !== $a['total_leads']) {
                    return $b['total_leads'] <=> $a['total_leads'];
                }
                return strcmp($a['project']->name, $b['project']->name);
            });
        } elseif ($sort === 'calls_desc') {
            usort($projectCards, fn($a, $b) => $b['calls_count'] <=> $a['calls_count']);
        } elseif ($sort === 'name_asc') {
            usort($projectCards, fn($a, $b) => strcmp($a['project']->name, $b['project']->name));
        }

        if ($isSuperAdmin) {
            $assignableUsers = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
            $agents = User::where('role', 'Agent')->orderBy('name')->get();
        } else {
            $assignableUsers = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
            $agents = collect();
        }

        $selectedUser = $request->filled('user_id') ? User::with('company')->find($request->user_id) : null;
        $selectedProject = $request->filled('project_id') ? Project::with(['city', 'state'])->find($request->project_id) : null;

        return view('admin.leads.index', compact(
            'contacts',
            'stats',
            'projects',
            'projectCards',
            'viewMode',
            'assignableUsers',
            'agents',
            'isSuperAdmin',
            'callCountsByPhone',
            'selectedUser',
            'selectedProject'
        ));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $projects = Project::where('status', 1)->orderBy('name')->get();

        if ($isSuperAdmin) {
            $users = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
        } else {
            $users = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
        }

        return view('admin.leads.create', compact('projects', 'users', 'isSuperAdmin'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $request->validate([
            'name' => 'required|string|max:70',
            'mobile' => 'required|digits:10',
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'required|string|max:50',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'remark' => 'nullable|string|max:255',
        ]);

        $assignedUserId = $request->user_id ?: $currentUser->id;

        $contact = Contact::create([
            'user_id' => $assignedUserId,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'project_id' => $request->project_id,
            'remark' => $request->remark,
            'date' => $request->date ?? now()->toDateString(),
            'time' => $request->time ?? now()->format('H:i:s'),
            'status' => $request->status,
        ]);

        // Automatically log initial call history entry
        ContactCallHistory::create([
            'user_id' => $assignedUserId,
            'contact_id' => $contact->id,
            'date' => now()->toDateString(),
            'remark' => $request->remark ?? 'Lead created in admin panel',
            'visited' => $request->status === 'site_visit_done' ? 1 : 0,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.leads.show', $contact->id)->with('success', 'Lead created successfully.');
    }

    /**
     * Display lead details.
     */
    public function show(Contact $lead)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            $teamIds = User::where('id', $currentUser->id)->orWhere('company_id', $currentUser->id)->pluck('id')->toArray();
            if (!in_array($lead->user_id, $teamIds)) {
                abort(403, 'Unauthorized access.');
            }
        }

        $lead->load(['project', 'user.company', 'contactCallHistories.user']);

        // Users available for reassignment
        if ($currentUser->isSuperAdmin()) {
            $assignableUsers = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
        } else {
            $assignableUsers = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
        }

        $projects = Project::where('status', 1)->orderBy('name')->get();

        // Count how many total calls have been made to this customer's phone number across all records
        $leadTotalCalls = ContactCallHistory::whereHas('contact', function ($q) use ($lead) {
            $q->where('mobile', $lead->mobile);
        })->count();

        return view('admin.leads.show', compact('lead', 'assignableUsers', 'projects', 'leadTotalCalls'));
    }

    /**
     * Show edit form.
     */
    public function edit(Contact $lead)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            $teamIds = User::where('id', $currentUser->id)->orWhere('company_id', $currentUser->id)->pluck('id')->toArray();
            if (!in_array($lead->user_id, $teamIds)) {
                abort(403, 'Unauthorized access.');
            }
        }

        $projects = Project::where('status', 1)->orderBy('name')->get();

        if ($currentUser->isSuperAdmin()) {
            $users = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
        } else {
            $users = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
        }

        return view('admin.leads.edit', compact('lead', 'projects', 'users'));
    }

    /**
     * Update lead in storage.
     */
    public function update(Request $request, Contact $lead)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            $teamIds = User::where('id', $currentUser->id)->orWhere('company_id', $currentUser->id)->pluck('id')->toArray();
            if (!in_array($lead->user_id, $teamIds)) {
                abort(403, 'Unauthorized access.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:70',
            'mobile' => 'required|digits:10',
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|string|max:50',
            'remark' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $statusChanged = $lead->status !== $request->status;

        $lead->update([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'project_id' => $request->project_id,
            'status' => $request->status,
            'remark' => $request->remark,
            'user_id' => $request->user_id ?: $lead->user_id,
        ]);

        // If status changed or new remark provided, append to call history
        if ($statusChanged || $request->filled('remark')) {
            ContactCallHistory::create([
                'user_id' => $currentUser->id,
                'contact_id' => $lead->id,
                'date' => now()->toDateString(),
                'remark' => $request->remark ?? "Status updated to " . ucfirst(str_replace('_', ' ', $request->status)),
                'visited' => $request->status === 'site_visit_done' ? 1 : 0,
                'status' => $request->status,
            ]);
        }

        return redirect()->route('admin.leads.show', $lead->id)->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove lead from storage.
     */
    public function destroy(Contact $lead)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            $teamIds = User::where('id', $currentUser->id)->orWhere('company_id', $currentUser->id)->pluck('id')->toArray();
            if (!in_array($lead->user_id, $teamIds)) {
                abort(403, 'Unauthorized access.');
            }
        }

        // Delete associated call history images
        foreach ($lead->contactCallHistories as $call) {
            if ($call->image) {
                deleteFile($call->image, 'visits/');
            }
            $call->delete();
        }

        $lead->leadAssigns()->delete();
        $name = $lead->name;
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', "Lead '{$name}' has been deleted.");
    }

    /**
     * Reassign lead to another agent or sub-agent.
     */
    public function assign(Request $request, Contact $lead)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'remark' => 'nullable|string|max:255',
        ]);

        $newUserId = $request->user_id;
        $newUser = User::find($newUserId);

        LeadAssign::create([
            'contact_id' => $lead->id,
            'user_id' => $newUserId,
            'other_assign' => 1,
            'status' => 1,
        ]);

        $lead->update([
            'user_id' => $newUserId,
        ]);

        ContactCallHistory::create([
            'user_id' => Auth::id(),
            'contact_id' => $lead->id,
            'date' => now()->toDateString(),
            'remark' => 'Reassigned to ' . $newUser->name . ($request->filled('remark') ? ': ' . $request->remark : ''),
            'status' => $lead->status,
        ]);

        return back()->with('success', "Lead successfully assigned to {$newUser->name}.");
    }

    /**
     * Add a call history / site visit log directly to lead.
     */
    public function addCallHistory(Request $request, Contact $lead)
    {
        $request->validate([
            'status' => 'required|string|max:50',
            'remark' => 'required|string|max:255',
            'date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $visited = ($request->status === 'site_visit_done') ? 1 : 0;

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = upload($request->file('image'), 'visits/');
        }

        ContactCallHistory::create([
            'user_id' => Auth::id(),
            'contact_id' => $lead->id,
            'date' => $request->date,
            'remark' => $request->remark,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'image' => $imageName,
            'visited' => $visited,
            'status' => $request->status,
        ]);

        // Update lead's main status, remark, and schedule date
        $lead->update([
            'status' => $request->status,
            'remark' => $request->remark,
            'date' => $request->date,
        ]);

        return back()->with('success', 'Call / visit activity logged successfully.');
    }
}
