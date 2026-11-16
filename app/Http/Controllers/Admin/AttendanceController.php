<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance punches.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $query = Attendance::with(['user.designation', 'user.company', 'user.city']);

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
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $query->whereYear('date', $parts[0])->whereMonth('date', $parts[1]);
            }
        }

        $attendances = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->paginate(15)->withQueryString();

        // Calculate summary counters
        $today = Carbon::today()->toDateString();
        $todayCountQuery = Attendance::where('date', $today);
        if (!$isSuperAdmin) {
            $todayCountQuery->whereIn('user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $todayCountQuery->whereIn('user_id', $agentTeamIds);
        }
        $todayPunches = $todayCountQuery->count();

        if ($isSuperAdmin) {
            $users = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
            $agents = User::where('role', 'Agent')->orderBy('name')->get();
        } else {
            $users = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
            $agents = collect();
        }

        return view('admin.attendances.index', compact('attendances', 'todayPunches', 'users', 'agents', 'isSuperAdmin'));
    }

    /**
     * Show form for manual attendance creation.
     */
    public function create()
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        if ($isSuperAdmin) {
            $users = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
        } else {
            $users = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
        }

        return view('admin.attendances.create', compact('users', 'isSuperAdmin'));
    }

    /**
     * Store manual attendance.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = upload($request->file('image'), 'attendances/');
        }

        Attendance::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'time' => $request->time . ':00',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'image' => $imageName,
        ]);

        return redirect()->route('admin.attendances.index')->with('success', 'Attendance record added successfully.');
    }

    /**
     * Delete attendance punch.
     */
    public function destroy(Attendance $attendance)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            $teamIds = User::where('id', $currentUser->id)->orWhere('company_id', $currentUser->id)->pluck('id')->toArray();
            if (!in_array($attendance->user_id, $teamIds)) {
                abort(403, 'Unauthorized access.');
            }
        }

        if ($attendance->image) {
            deleteFile($attendance->image, 'attendances/');
        }

        $attendance->delete();

        return back()->with('success', 'Attendance punch deleted.');
    }

    /**
     * Monthly attendance matrix / summary.
     */
    public function monthly(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $parts = explode('-', $selectedMonth);
        $year = (int)$parts[0];
        $month = (int)$parts[1];

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $usersQuery = User::with('designation');
        if (!$isSuperAdmin) {
            $usersQuery->where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            });
        } elseif ($request->filled('agent_id')) {
            $usersQuery->where(function ($q) use ($request) {
                $q->where('id', $request->agent_id)->orWhere('company_id', $request->agent_id);
            });
        }
        $users = $usersQuery->whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();

        // Get attendances for this month
        $attendances = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('user_id', $users->pluck('id'))
            ->get();

        // Build attendance map: user_id => [day => count]
        $attendanceMap = [];
        foreach ($attendances as $att) {
            $day = (int)Carbon::parse($att->date)->format('j');
            $attendanceMap[$att->user_id][$day] = ($attendanceMap[$att->user_id][$day] ?? 0) + 1;
        }

        $allAgents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.attendances.monthly', compact(
            'users',
            'attendanceMap',
            'selectedMonth',
            'daysInMonth',
            'allAgents',
            'isSuperAdmin'
        ));
    }
}
