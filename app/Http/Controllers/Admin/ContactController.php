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

        $query = Contact::with(['project', 'user.company', 'leadAssigns.user']);

        // Team scoping
        if (!$isSuperAdmin) {
            $teamUserIds = User::where('id', $currentUser->id)
                ->orWhere('company_id', $currentUser->id)
                ->pluck('id')->toArray();
            $query->whereIn('user_id', $teamUserIds);
        } else {
            if ($request->filled('agent_id')) {
                $agentTeamIds = User::where('id', $request->agent_id)
                    ->orWhere('company_id', $request->agent_id)
                    ->pluck('id')->toArray();
                $query->whereIn('user_id', $agentTeamIds);
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        // Stats calculation
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
            'total' => (clone $statsQuery)->count(),
            'follow_up' => (clone $statsQuery)->where('status', 'follow_up')->count(),
            'site_visit_done' => (clone $statsQuery)->where('status', 'site_visit_done')->count(),
            'booking_done' => (clone $statsQuery)->where('status', 'booking_done')->count(),
            'not_connecting' => (clone $statsQuery)->where('status', 'call_not_connecting')->count(),
            'call_later' => (clone $statsQuery)->where('status', 'call_later')->count(),
        ];

        $projects = Project::where('status', 1)->orderBy('name')->get();

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

        return view('admin.leads.index', compact('contacts', 'stats', 'projects', 'assignableUsers', 'agents', 'isSuperAdmin', 'callCountsByPhone', 'selectedUser'));
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

        // Update lead's main status and remark
        $lead->update([
            'status' => $request->status,
            'remark' => $request->remark,
        ]);

        return back()->with('success', 'Call / visit activity logged successfully.');
    }
}
