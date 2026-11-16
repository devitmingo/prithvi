<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Project;
use App\Models\Contact;
use App\Models\ContactCallHistory;
use App\Models\Attendance;
use App\Models\Offer;
use App\Models\UserForgotPassword;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        // Selected agent filter for Superadmin view
        $selectedAgentId = $isSuperAdmin ? $request->get('agent_id') : $currentUser->id;

        // Base user IDs query for team scoping
        if ($isSuperAdmin) {
            if ($selectedAgentId) {
                $teamUserIds = User::where(function ($q) use ($selectedAgentId) {
                    $q->where('id', $selectedAgentId)
                      ->orWhere('company_id', $selectedAgentId);
                })->pluck('id')->toArray();
            } else {
                $teamUserIds = null; // null indicates ALL
            }
        } else {
            // Agent sees themselves + their sub-agents
            $teamUserIds = User::where('id', $currentUser->id)
                ->orWhere('company_id', $currentUser->id)
                ->pluck('id')->toArray();
        }

        // Stats Counters
        $today = Carbon::today()->toDateString();

        if ($isSuperAdmin && !$selectedAgentId) {
            $totalUsers = User::count();
            $totalAgents = User::where('role', 'Agent')->count();
            $totalSubAgents = User::where('role', 'Sub Agent')->count();
            $totalSuperadmins = User::where('role', 'Superadmin')->count();
            $pendingUsers = User::where('status', 0)->count();
            $pendingPasswordRequests = UserForgotPassword::where('status', 1)->count();

            $totalProjects = Project::where('status', 1)->count();
            $totalContacts = Contact::count();
            $totalBookings = Contact::where('status', 'booking_done')->count();
            $totalSiteVisits = ContactCallHistory::where(function ($q) {
                $q->where('visited', 1)->orWhere('status', 'site_visit_done');
            })->count();
            $todayAttendances = Attendance::where('date', $today)->count();
            $activeOffers = Offer::where('to_date', '>=', $today)->count();
        } else {
            $totalUsers = count($teamUserIds);
            $totalAgents = $isSuperAdmin ? 1 : 1;
            $totalSubAgents = User::whereIn('id', $teamUserIds)->where('role', 'Sub Agent')->count();
            $totalSuperadmins = 0;
            $pendingUsers = User::whereIn('id', $teamUserIds)->where('status', 0)->count();
            $pendingPasswordRequests = UserForgotPassword::whereIn('user_id', $teamUserIds)->where('status', 1)->count();

            $totalProjects = Project::where('status', 1)->count();
            $totalContacts = Contact::whereIn('user_id', $teamUserIds)->count();
            $totalBookings = Contact::whereIn('user_id', $teamUserIds)->where('status', 'booking_done')->count();
            $totalSiteVisits = ContactCallHistory::whereIn('user_id', $teamUserIds)->where(function ($q) {
                $q->where('visited', 1)->orWhere('status', 'site_visit_done');
            })->count();
            $todayAttendances = Attendance::whereIn('user_id', $teamUserIds)->where('date', $today)->count();
            $activeOffers = Offer::where('to_date', '>=', $today)->count();
        }

        // Charts: Leads by Status
        $leadStatusQuery = Contact::select('status', DB::raw('count(*) as count'));
        if ($teamUserIds) {
            $leadStatusQuery->whereIn('user_id', $teamUserIds);
        }
        $leadsByStatus = $leadStatusQuery->groupBy('status')->pluck('count', 'status')->toArray();

        // Standardize status labels
        $statusLabelsMap = [
            'follow_up' => 'Follow Up',
            'site_visit_done' => 'Site Visit Done',
            'booking_done' => 'Booking Done',
            'call_later' => 'Call Later',
            'call_not_connecting' => 'Not Connecting',
            'visit_scheduled' => 'Visit Scheduled',
            'interested' => 'Interested',
            'not_interested' => 'Not Interested',
        ];

        $statusChartLabels = [];
        $statusChartData = [];
        foreach ($leadsByStatus as $status => $count) {
            $statusChartLabels[] = $statusLabelsMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
            $statusChartData[] = $count;
        }

        // Monthly Leads Trend (Last 6 months)
        $monthlyLabels = [];
        $monthlyLeadCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthName = $monthDate->format('M Y');
            $year = $monthDate->year;
            $month = $monthDate->month;

            $monthQuery = Contact::whereYear('created_at', $year)->whereMonth('created_at', $month);
            if ($teamUserIds) {
                $monthQuery->whereIn('user_id', $teamUserIds);
            }
            $monthlyLabels[] = $monthName;
            $monthlyLeadCounts[] = $monthQuery->count();
        }

        // Attendance Trend (Last 7 Days)
        $attendanceWeekLabels = [];
        $attendanceWeekData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayDate = Carbon::today()->subDays($i);
            $dayStr = $dayDate->toDateString();
            $attendanceWeekLabels[] = $dayDate->format('D, d M');

            $attQuery = Attendance::where('date', $dayStr);
            if ($teamUserIds) {
                $attQuery->whereIn('user_id', $teamUserIds);
            }
            $attendanceWeekData[] = $attQuery->count();
        }

        // Top Projects by Leads
        $projectQuery = Project::select('projects.id', 'projects.name', DB::raw('count(contacts.id) as leads_count'))
            ->leftJoin('contacts', 'projects.id', '=', 'contacts.project_id')
            ->groupBy('projects.id', 'projects.name')
            ->orderBy('leads_count', 'desc')
            ->take(5);
        if ($teamUserIds) {
            $projectQuery->where(function ($q) use ($teamUserIds) {
                $q->whereIn('contacts.user_id', $teamUserIds)
                  ->orWhereNull('contacts.id');
            });
        }
        $topProjects = $projectQuery->get();

        // Recent Contacts
        $recentContactsQuery = Contact::with(['project', 'user'])->orderBy('id', 'desc')->take(6);
        if ($teamUserIds) {
            $recentContactsQuery->whereIn('user_id', $teamUserIds);
        }
        $recentContacts = $recentContactsQuery->get();

        // Today's Recent Attendances
        $recentAttendancesQuery = Attendance::with(['user.designation', 'user.company'])
            ->where('date', $today)
            ->orderBy('id', 'desc')
            ->take(6);
        if ($teamUserIds) {
            $recentAttendancesQuery->whereIn('user_id', $teamUserIds);
        }
        $recentAttendances = $recentAttendancesQuery->get();

        // Recent Call Histories
        $recentCallsQuery = ContactCallHistory::with(['contact', 'user'])
            ->orderBy('id', 'desc')
            ->take(5);
        if ($teamUserIds) {
            $recentCallsQuery->whereIn('user_id', $teamUserIds);
        }
        $recentCalls = $recentCallsQuery->get();

        // Agents list for Superadmin filter dropdown
        $allAgents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.dashboard', compact(
            'isSuperAdmin',
            'currentUser',
            'selectedAgentId',
            'allAgents',
            'totalUsers',
            'totalAgents',
            'totalSubAgents',
            'totalSuperadmins',
            'pendingUsers',
            'pendingPasswordRequests',
            'totalProjects',
            'totalContacts',
            'totalBookings',
            'totalSiteVisits',
            'todayAttendances',
            'activeOffers',
            'statusChartLabels',
            'statusChartData',
            'monthlyLabels',
            'monthlyLeadCounts',
            'attendanceWeekLabels',
            'attendanceWeekData',
            'topProjects',
            'recentContacts',
            'recentAttendances',
            'recentCalls'
        ));
    }
}
