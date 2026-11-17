<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use App\Models\Attendance;
use App\Models\ContactCallHistory;
use App\Models\Offer;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Reports Hub index.
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Helper to get allowed user IDs based on auth role and filter.
     */
    private function getScopedUserIds(?int $filterAgentId = null): ?array
    {
        $currentUser = Auth::user();
        if ($currentUser->isSuperAdmin()) {
            if ($filterAgentId) {
                return User::where('id', $filterAgentId)
                    ->orWhere('company_id', $filterAgentId)
                    ->pluck('id')->toArray();
            }
            return null; // All
        } else {
            return User::where('id', $currentUser->id)
                ->orWhere('company_id', $currentUser->id)
                ->pluck('id')->toArray();
        }
    }

    /**
     * 1. Leads & Pipeline Report
     */
    public function leads(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $scopedIds = $this->getScopedUserIds($request->agent_id);

        $query = Contact::with(['project', 'user']);

        if ($scopedIds !== null) {
            $query->whereIn('user_id', $scopedIds);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // CSV Export Trigger
        if ($request->get('export') === 'csv') {
            $leads = $query->orderBy('id', 'desc')->get();
            $filename = 'leads_report_' . date('Y_m_d_His') . '.csv';

            return new StreamedResponse(function () use ($leads) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Lead ID', 'Customer Name', 'Mobile', 'Project', 'Status', 'Assigned Agent', 'Date', 'Time', 'Remark']);
                foreach ($leads as $l) {
                    fputcsv($handle, [
                        $l->id,
                        $l->name,
                        $l->mobile,
                        $l->project->name ?? 'N/A',
                        ucfirst(str_replace('_', ' ', $l->status)),
                        $l->user->name ?? 'N/A',
                        $l->date,
                        $l->time,
                        $l->remark
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $leads = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Metrics Calculation
        $metricQuery = Contact::query();
        if ($scopedIds !== null) {
            $metricQuery->whereIn('user_id', $scopedIds);
        }
        if ($request->filled('project_id')) {
            $metricQuery->where('project_id', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $metricQuery->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $metricQuery->whereDate('date', '<=', $request->date_to);
        }

        $totalLeads = (clone $metricQuery)->count();
        $totalBookings = (clone $metricQuery)->where('status', 'booking_done')->count();
        $totalVisits = (clone $metricQuery)->where('status', 'site_visit_done')->count();
        $totalFollowUps = (clone $metricQuery)->where('status', 'follow_up')->count();
        $totalLost = (clone $metricQuery)->whereIn('status', ['call_not_connecting', 'not_interested'])->count();

        $visitConversionRate = $totalLeads > 0 ? round(($totalVisits / $totalLeads) * 100, 1) : 0;
        $bookingConversionRate = $totalVisits > 0 ? round(($totalBookings / $totalVisits) * 100, 1) : ($totalLeads > 0 ? round(($totalBookings / $totalLeads) * 100, 1) : 0);

        // Project Breakdown
        $projectBreakdown = (clone $metricQuery)
            ->select('project_id', DB::raw('count(*) as total'), DB::raw('sum(case when status = "booking_done" then 1 else 0 end) as bookings'))
            ->groupBy('project_id')
            ->with('project')
            ->get();

        $projects = Project::where('status', 1)->orderBy('name')->get();
        $agents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.reports.leads', compact(
            'leads',
            'totalLeads',
            'totalBookings',
            'totalVisits',
            'totalFollowUps',
            'totalLost',
            'visitConversionRate',
            'bookingConversionRate',
            'projectBreakdown',
            'projects',
            'agents',
            'isSuperAdmin'
        ));
    }

    /**
     * 2. Agent & Staff Performance Report
     */
    public function agentPerformance(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $usersQuery = User::with(['company', 'designation']);

        if (!$isSuperAdmin) {
            $usersQuery->where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            });
        } elseif ($request->filled('agent_id')) {
            $usersQuery->where(function ($q) use ($request) {
                $q->where('id', $request->agent_id)->orWhere('company_id', $request->agent_id);
            });
        }

        $users = $usersQuery->whereIn('role', ['Agent', 'Sub Agent'])->get();

        $performanceData = [];
        foreach ($users as $u) {
            $leadsQuery = Contact::where('user_id', $u->id);
            $callsQuery = ContactCallHistory::where('user_id', $u->id);
            $attQuery = Attendance::where('user_id', $u->id);

            if ($request->filled('date_from')) {
                $leadsQuery->whereDate('date', '>=', $request->date_from);
                $callsQuery->whereDate('date', '>=', $request->date_from);
                $attQuery->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $leadsQuery->whereDate('date', '<=', $request->date_to);
                $callsQuery->whereDate('date', '<=', $request->date_to);
                $attQuery->whereDate('date', '<=', $request->date_to);
            }

            $totalLeads = $leadsQuery->count();
            $bookings = (clone $leadsQuery)->where('status', 'booking_done')->count();
            $calls = $callsQuery->count();
            $visits = (clone $callsQuery)->where(function ($q) {
                $q->where('visited', 1)->orWhere('status', 'site_visit_done');
            })->count();
            $attendances = $attQuery->count();

            $conversion = $totalLeads > 0 ? round(($bookings / $totalLeads) * 100, 1) : 0;

            $performanceData[] = [
                'user' => $u,
                'total_leads' => $totalLeads,
                'bookings' => $bookings,
                'calls' => $calls,
                'visits' => $visits,
                'attendances' => $attendances,
                'conversion' => $conversion,
            ];
        }

        // Sort by total leads or bookings
        usort($performanceData, fn($a, $b) => $b['total_leads'] <=> $a['total_leads']);

        // CSV Export Trigger
        if ($request->get('export') === 'csv') {
            $filename = 'agent_performance_' . date('Y_m_d_His') . '.csv';

            return new StreamedResponse(function () use ($performanceData) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Name', 'Role', 'Mobile', 'Agency / Company', 'Designation', 'Total Leads', 'Calls Logged', 'Site Visits', 'Bookings Done', 'Punches', 'Conversion %']);
                foreach ($performanceData as $row) {
                    fputcsv($handle, [
                        $row['user']->name,
                        $row['user']->role,
                        $row['user']->mobile,
                        $row['user']->company_name ?? ($row['user']->company->company_name ?? 'N/A'),
                        $row['user']->designation->name ?? 'N/A',
                        $row['total_leads'],
                        $row['calls'],
                        $row['visits'],
                        $row['bookings'],
                        $row['attendances'],
                        $row['conversion'] . '%',
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $allAgents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.reports.agent_performance', compact('performanceData', 'allAgents', 'isSuperAdmin'));
    }

    /**
     * 3. Attendance & Staff Activity Report
     */
    public function attendance(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $scopedIds = $this->getScopedUserIds($request->agent_id);

        $query = Attendance::with(['user.designation', 'user.company']);

        if ($scopedIds !== null) {
            $query->whereIn('user_id', $scopedIds);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->get('export') === 'csv') {
            $attendances = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->get();
            $filename = 'attendance_report_' . date('Y_m_d_His') . '.csv';

            return new StreamedResponse(function () use ($attendances) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Punch ID', 'User Name', 'Role', 'Agency', 'Date', 'Time', 'Latitude', 'Longitude']);
                foreach ($attendances as $att) {
                    fputcsv($handle, [
                        $att->id,
                        $att->user->name ?? 'N/A',
                        $att->user->role ?? 'N/A',
                        $att->user->company_name ?? ($att->user->company->company_name ?? 'N/A'),
                        $att->date,
                        $att->time,
                        $att->latitude ?? 'N/A',
                        $att->longitude ?? 'N/A',
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $attendances = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->paginate(20)->withQueryString();

        $totalPunches = (clone $query)->count();
        $uniqueActiveStaff = (clone $query)->distinct('user_id')->count('user_id');

        $usersQuery = User::whereIn('role', ['Agent', 'Sub Agent'])->where('status', 1);
        if ($scopedIds !== null) {
            $usersQuery->whereIn('id', $scopedIds);
        }
        $users = $usersQuery->orderBy('name')->get();
        $allAgents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.reports.attendance', compact('attendances', 'totalPunches', 'uniqueActiveStaff', 'users', 'allAgents', 'isSuperAdmin'));
    }

    /**
     * 4. Site Visits & Ground Audit Report
     */
    public function siteVisits(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $scopedIds = $this->getScopedUserIds($request->agent_id);

        $query = ContactCallHistory::with(['contact.project', 'user.company'])
            ->where(function ($q) {
                $q->where('visited', 1)->orWhere('status', 'site_visit_done');
            });

        if ($scopedIds !== null) {
            $query->whereIn('user_id', $scopedIds);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->get('export') === 'csv') {
            $visits = $query->orderBy('date', 'desc')->get();
            $filename = 'site_visits_report_' . date('Y_m_d_His') . '.csv';

            return new StreamedResponse(function () use ($visits) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID', 'Customer', 'Mobile', 'Project', 'Field Executive', 'Agency', 'Date', 'GPS Coordinates', 'Remark']);
                foreach ($visits as $v) {
                    fputcsv($handle, [
                        $v->id,
                        $v->contact->name ?? 'N/A',
                        $v->contact->mobile ?? 'N/A',
                        $v->contact->project->name ?? 'N/A',
                        $v->user->name ?? 'N/A',
                        $v->user->company_name ?? ($v->user->company->company_name ?? 'N/A'),
                        $v->date,
                        ($v->latitude && $v->longitude) ? "{$v->latitude}, {$v->longitude}" : 'N/A',
                        $v->remark,
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $visits = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();
        $allAgents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.reports.site_visits', compact('visits', 'allAgents', 'isSuperAdmin'));
    }

    /**
     * 5. Project Inventory & Demand Report
     */
    public function projects(Request $request)
    {
        $projects = Project::with(['state', 'city', 'offers'])
            ->withCount([
                'contacts as total_inquiries',
                'contacts as total_bookings' => function ($q) {
                    $q->where('status', 'booking_done');
                },
                'contacts as total_visits' => function ($q) {
                    $q->where('status', 'site_visit_done');
                }
            ])
            ->orderBy('total_inquiries', 'desc')
            ->get();

        if ($request->get('export') === 'csv') {
            $filename = 'projects_report_' . date('Y_m_d_His') . '.csv';

            return new StreamedResponse(function () use ($projects) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Project Name', 'Location', 'Price (INR)', 'RERA No', 'Project Status', 'Total Inquiries', 'Site Visits', 'Bookings', 'Active Status']);
                foreach ($projects as $p) {
                    fputcsv($handle, [
                        $p->name,
                        ($p->city->name ?? '') . ', ' . ($p->state->name ?? ''),
                        $p->price ?? 'On Request',
                        $p->rera_no ?? 'N/A',
                        $p->project_status,
                        $p->total_inquiries,
                        $p->total_visits,
                        $p->total_bookings,
                        $p->status ? 'Active' : 'Inactive',
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return view('admin.reports.projects', compact('projects'));
    }

    /**
     * 6. User Growth & Demographics Report
     */
    public function userGrowth(Request $request)
    {
        $usersByRole = User::select('role', DB::raw('count(*) as count'))->groupBy('role')->pluck('count', 'role')->toArray();
        $usersByStatus = User::select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();

        // Monthly registrations over last 12 months
        $months = [];
        $counts = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $months[] = $dt->format('M Y');
            $counts[] = User::whereYear('created_at', $dt->year)->whereMonth('created_at', $dt->month)->count();
        }

        $recentUsers = User::with(['company', 'city', 'designation'])->orderBy('id', 'desc')->take(15)->get();

        if ($request->get('export') === 'csv') {
            $allUsers = User::with(['company', 'state', 'city', 'designation'])->orderBy('id', 'desc')->get();
            $filename = 'users_demographics_' . date('Y_m_d_His') . '.csv';

            return new StreamedResponse(function () use ($allUsers) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['User ID', 'Name', 'Role', 'Mobile', 'Email', 'Company', 'Designation', 'Status', 'Registered On']);
                foreach ($allUsers as $u) {
                    fputcsv($handle, [
                        $u->user_id,
                        $u->name,
                        $u->role,
                        $u->mobile,
                        $u->email,
                        $u->company_name ?? ($u->company->company_name ?? 'N/A'),
                        $u->designation->name ?? 'N/A',
                        $u->status_label,
                        $u->created_at ? $u->created_at->format('Y-m-d') : 'N/A',
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return view('admin.reports.user_growth', compact('usersByRole', 'usersByStatus', 'months', 'counts', 'recentUsers'));
    }

    /**
     * 7. User-wise Leads Report (Card View)
     * Rich card representation of each team member's leads, calls, visits, and bookings
     * with one-click drill-down to full history in Leads & CRM.
     */
    public function userLeads(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        $usersQuery = User::with(['company', 'designation'])->withCount(['subAgents']);

        // Team scoping
        if (!$isSuperAdmin) {
            $usersQuery->where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)->orWhere('company_id', $currentUser->id);
            });
        } elseif ($request->filled('agent_id')) {
            $usersQuery->where(function ($q) use ($request) {
                $q->where('id', $request->agent_id)->orWhere('company_id', $request->agent_id);
            });
        }

        // Role filtering
        if ($request->filled('role')) {
            $usersQuery->where('role', $request->role);
        } else {
            $usersQuery->whereIn('role', ['Agent', 'Sub Agent', 'Superadmin']);
        }

        // Search query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('agency_name', 'like', "%{$search}%");
            });
        }

        $allUsers = $usersQuery->orderBy('name')->get();

        // Calculate lead metrics for each user
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $projectId = $request->project_id;

        // Build lead statistics aggregation query
        $leadStatsQuery = Contact::select(
            'user_id',
            DB::raw('count(*) as total_leads'),
            DB::raw('sum(case when status = "follow_up" then 1 else 0 end) as follow_up'),
            DB::raw('sum(case when status = "site_visit_done" then 1 else 0 end) as site_visit_done'),
            DB::raw('sum(case when status = "booking_done" then 1 else 0 end) as booking_done'),
            DB::raw('sum(case when status = "call_later" then 1 else 0 end) as call_later'),
            DB::raw('sum(case when status = "call_not_connecting" then 1 else 0 end) as not_connecting'),
            DB::raw('sum(case when status = "interested" then 1 else 0 end) as interested'),
            DB::raw('max(created_at) as latest_lead_at')
        );

        if ($projectId) {
            $leadStatsQuery->where('project_id', $projectId);
        }
        if ($dateFrom) {
            $leadStatsQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $leadStatsQuery->whereDate('date', '<=', $dateTo);
        }

        $leadStatsByUser = $leadStatsQuery->groupBy('user_id')->get()->keyBy('user_id');

        // Build call logs aggregation query
        $callsQuery = ContactCallHistory::select('user_id', DB::raw('count(*) as total_calls'));
        if ($dateFrom) {
            $callsQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $callsQuery->whereDate('created_at', '<=', $dateTo);
        }
        $callCountsByUser = $callsQuery->groupBy('user_id')->pluck('total_calls', 'user_id')->toArray();

        // Build composite user cards
        $userCards = [];
        $overallStats = [
            'total_users' => 0,
            'users_with_leads' => 0,
            'total_leads' => 0,
            'follow_up' => 0,
            'site_visit_done' => 0,
            'booking_done' => 0,
            'call_later' => 0,
            'not_connecting' => 0,
            'total_calls' => 0,
        ];

        foreach ($allUsers as $u) {
            $stats = $leadStatsByUser->get($u->id);
            $totalLeads = $stats ? (int)$stats->total_leads : 0;
            $followUps = $stats ? (int)$stats->follow_up : 0;
            $siteVisits = $stats ? (int)$stats->site_visit_done : 0;
            $bookings = $stats ? (int)$stats->booking_done : 0;
            $callLater = $stats ? (int)$stats->call_later : 0;
            $notConnecting = $stats ? (int)$stats->not_connecting : 0;
            $interested = $stats ? (int)$stats->interested : 0;
            $latestLeadAt = $stats ? $stats->latest_lead_at : null;

            $callsCount = $callCountsByUser[$u->id] ?? 0;

            // Conversion Rate: % of leads converted to visit or booking
            $conversionRate = $totalLeads > 0 ? round((($siteVisits + $bookings) / $totalLeads) * 100, 1) : 0;

            $card = [
                'user' => $u,
                'total_leads' => $totalLeads,
                'follow_up' => $followUps,
                'site_visit_done' => $siteVisits,
                'booking_done' => $bookings,
                'call_later' => $callLater,
                'not_connecting' => $notConnecting,
                'interested' => $interested,
                'calls_count' => $callsCount,
                'latest_lead_at' => $latestLeadAt,
                'conversion_rate' => $conversionRate,
            ];

            $userCards[] = $card;

            $overallStats['total_users']++;
            if ($totalLeads > 0) {
                $overallStats['users_with_leads']++;
            }
            $overallStats['total_leads'] += $totalLeads;
            $overallStats['follow_up'] += $followUps;
            $overallStats['site_visit_done'] += $siteVisits;
            $overallStats['booking_done'] += $bookings;
            $overallStats['call_later'] += $callLater;
            $overallStats['not_connecting'] += $notConnecting;
            $overallStats['total_calls'] += $callsCount;
        }

        // Sorting
        $sortBy = $request->get('sort', 'leads_desc');
        if ($sortBy === 'leads_desc') {
            usort($userCards, fn($a, $b) => $b['total_leads'] <=> $a['total_leads']);
        } elseif ($sortBy === 'bookings_desc') {
            usort($userCards, fn($a, $b) => $b['booking_done'] <=> $a['booking_done']);
        } elseif ($sortBy === 'visits_desc') {
            usort($userCards, fn($a, $b) => $b['site_visit_done'] <=> $a['site_visit_done']);
        } elseif ($sortBy === 'conversion_desc') {
            usort($userCards, fn($a, $b) => $b['conversion_rate'] <=> $a['conversion_rate']);
        } elseif ($sortBy === 'calls_desc') {
            usort($userCards, fn($a, $b) => $b['calls_count'] <=> $a['calls_count']);
        } elseif ($sortBy === 'name_asc') {
            usort($userCards, fn($a, $b) => strcasecmp($a['user']->name, $b['user']->name));
        }

        // CSV Export Trigger
        if ($request->get('export') === 'csv') {
            $filename = 'userwise_leads_report_' . date('Y_m_d_His') . '.csv';
            return new StreamedResponse(function () use ($userCards) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['User ID', 'Executive Name', 'Role', 'Leader / Agency', 'Mobile', 'Total Leads', 'Follow Ups', 'Site Visits', 'Bookings', 'Call Later', 'Not Reached', 'Calls Made', 'Conversion %']);
                foreach ($userCards as $c) {
                    $u = $c['user'];
                    $leaderOrAgency = $u->role === 'Agent' 
                        ? ($u->agency_name ?: 'Self Agency') 
                        : ($u->company->name ?? 'Unassigned');
                    fputcsv($handle, [
                        $u->user_id,
                        $u->name,
                        $u->role === 'Agent' ? 'Leader' : ($u->role === 'Sub Agent' ? 'Staff' : $u->role),
                        $leaderOrAgency,
                        $u->mobile,
                        $c['total_leads'],
                        $c['follow_up'],
                        $c['site_visit_done'],
                        $c['booking_done'],
                        $c['call_later'],
                        $c['not_connecting'],
                        $c['calls_count'],
                        $c['conversion_rate'] . '%'
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $projects = Project::where('status', 1)->orderBy('name')->get();
        $allAgents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();

        return view('admin.reports.user_leads', compact(
            'userCards',
            'overallStats',
            'projects',
            'allAgents',
            'isSuperAdmin'
        ));
    }
}
