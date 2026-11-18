<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance punches.
     * Default from_date and to_date to current date.
     * Each staff member appears 1 time with total days for current month.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $today = Carbon::today()->toDateString();

        // Default from_date and to_date to current date
        if ($request->has('date_from') || $request->has('date_to') || $request->has('date')) {
            $dateFrom = $request->get('date_from', $request->get('date'));
            $dateTo = $request->get('date_to', $request->get('date'));
        } else {
            // First page load defaults to today
            $dateFrom = $today;
            $dateTo = $today;
        }

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

        // Subquery to get latest punch ID for each user within the filtered date range
        // Ensures each staff member appears only 1 time in the listing
        $subQuery = Attendance::query();

        if (!$isSuperAdmin) {
            $subQuery->whereIn('user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $subQuery->whereIn('user_id', $agentTeamIds);
        }

        if ($request->filled('user_id')) {
            $subQuery->where('user_id', $request->user_id);
        }

        if (!empty($dateFrom)) {
            $subQuery->whereDate('date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $subQuery->whereDate('date', '<=', $dateTo);
        }

        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $subQuery->whereYear('date', $parts[0])->whereMonth('date', $parts[1]);
            }
        }

        $latestIds = $subQuery->select(DB::raw('max(id) as id'))
            ->groupBy('user_id')
            ->pluck('id');

        $attendances = Attendance::with(['user.designation', 'user.company', 'user.city'])
            ->whereIn('id', $latestIds)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calculate total days for each user in the CURRENT MONTH
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $monthlyDaysQuery = Attendance::select('user_id', DB::raw('count(distinct date) as total_days'))
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth);

        if (!$isSuperAdmin) {
            $monthlyDaysQuery->whereIn('user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $monthlyDaysQuery->whereIn('user_id', $agentTeamIds);
        }

        $monthlyDaysByUser = $monthlyDaysQuery->groupBy('user_id')
            ->pluck('total_days', 'user_id')
            ->toArray();

        // Calculate summary counters
        $todayCountQuery = Attendance::where('date', $today);
        if (!$isSuperAdmin) {
            $todayCountQuery->whereIn('user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $todayCountQuery->whereIn('user_id', $agentTeamIds);
        }
        $todayPunches = $todayCountQuery->distinct('user_id')->count('user_id');

        // Current month total unique active punches
        $monthCountQuery = Attendance::whereYear('date', $currentYear)->whereMonth('date', $currentMonth);
        if (!$isSuperAdmin) {
            $monthCountQuery->whereIn('user_id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $monthCountQuery->whereIn('user_id', $agentTeamIds);
        }
        $currentMonthPunches = $monthCountQuery->count();

        // Total active staff members
        $totalUsersQuery = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1);
        if (!$isSuperAdmin) {
            $totalUsersQuery->whereIn('id', $teamUserIds);
        } elseif ($request->filled('agent_id')) {
            $totalUsersQuery->whereIn('id', $agentTeamIds);
        }
        $totalActiveStaff = $totalUsersQuery->count();

        if ($isSuperAdmin) {
            $users = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1)->orderBy('name')->get();
            $agents = User::where('role', 'Agent')->orderBy('name')->get();
        } else {
            $users = User::where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            })->where('status', 1)->orderBy('name')->get();
            $agents = collect();
        }

        return view('admin.attendances.index', compact(
            'attendances', 
            'todayPunches', 
            'currentMonthPunches',
            'totalActiveStaff',
            'monthlyDaysByUser', 
            'dateFrom', 
            'dateTo', 
            'today',
            'users', 
            'agents', 
            'isSuperAdmin'
        ));
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
