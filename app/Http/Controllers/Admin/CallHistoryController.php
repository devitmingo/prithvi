<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ContactCallHistory;
use App\Models\Contact;
use App\Models\User;

class CallHistoryController extends Controller
{
    /**
     * Display a listing of call histories & site visits.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $query = ContactCallHistory::with(['contact.project', 'user.company']);

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('visited')) {
            $query->where('visited', $request->visited);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->whereHas('contact', function ($cq) use ($request) {
                $cq->where('project_id', $request->project_id);
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
                $q->where('remark', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('repeated_calls') && $request->repeated_calls == '1') {
            $repeatedMobiles = ContactCallHistory::join('contacts', 'contact_call_histories.contact_id', '=', 'contacts.id')
                ->select('contacts.mobile')
                ->groupBy('contacts.mobile')
                ->havingRaw('count(contact_call_histories.id) > 1')
                ->pluck('contacts.mobile');
            $query->whereHas('contact', function ($cq) use ($repeatedMobiles) {
                $cq->whereIn('mobile', $repeatedMobiles);
            });
        }

        $histories = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Count how many times each contact's mobile has been called in total
        $mobiles = $histories->map(fn($h) => $h->contact?->mobile)->filter()->unique();
        $callCountsByPhone = [];
        if ($mobiles->isNotEmpty()) {
            $callCountsByPhone = ContactCallHistory::join('contacts', 'contact_call_histories.contact_id', '=', 'contacts.id')
                ->whereIn('contacts.mobile', $mobiles)
                ->select('contacts.mobile', \Illuminate\Support\Facades\DB::raw('count(contact_call_histories.id) as total_calls'))
                ->groupBy('contacts.mobile')
                ->pluck('total_calls', 'contacts.mobile')
                ->toArray();
        }

        if ($isSuperAdmin) {
            $users = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
            $agents = User::where('role', 'Agent')->orderBy('name')->get();
        } else {
            $users = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
            $agents = collect();
        }

        return view('admin.call_histories.index', compact('histories', 'users', 'agents', 'isSuperAdmin', 'callCountsByPhone'));
    }

    /**
     * Remove the specified call history record.
     */
    public function destroy(ContactCallHistory $callHistory)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            $teamIds = User::where('id', $currentUser->id)->orWhere('company_id', $currentUser->id)->pluck('id')->toArray();
            if (!in_array($callHistory->user_id, $teamIds)) {
                abort(403, 'Unauthorized access.');
            }
        }

        if ($callHistory->image) {
            deleteFile($callHistory->image, 'visits/');
        }

        $callHistory->delete();

        return back()->with('success', 'Call / visit log deleted successfully.');
    }
}
