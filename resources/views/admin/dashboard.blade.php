@extends('admin.layouts.app')

@section('title', 'Executive Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-subtitle', 'Real-time performance metrics, leads pipeline, and field operations')

@section('content')
<div class="space-y-6">

    <!-- Super Admin Scope Filter Bar (Only for Superadmin) -->
    @if($isSuperAdmin)
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-2xl p-4 sm:p-5 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm border border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-crown text-base"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                        Super Admin Global Control View
                    </h2>
                    <p class="text-xs text-slate-300">Displaying aggregated data across all agencies and field agents.</p>
                </div>
            </div>

            <!-- Leader Filter Form -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2 w-full md:w-auto">
                <select name="agent_id" onchange="this.form.submit()" class="bg-slate-800 text-xs text-slate-200 border border-slate-700 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:outline-none w-full md:w-56 font-semibold">
                    <option value="">⚡ All Leaders & Teams</option>
                    @foreach($allAgents as $ag)
                        <option value="{{ $ag->id }}" {{ $selectedAgentId == $ag->id ? 'selected' : '' }}>
                            {{ $ag->name }} {{ $ag->company_name ? "({$ag->company_name})" : '' }}
                        </option>
                    @endforeach
                </select>
                @if($selectedAgentId)
                    <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs border border-slate-700" title="Clear Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </form>
        </div>
    @endif

    <!-- Alert Banners for Pending Tasks (Superadmin) -->
    @if($isSuperAdmin && ($pendingUsers > 0 || $pendingPasswordRequests > 0))
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if($pendingUsers > 0)
                <div class="flex items-center justify-between p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-user-clock"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ $pendingUsers }} User Registration{{ $pendingUsers > 1 ? 's' : '' }} Pending</p>
                            <p class="text-xs text-amber-700">New agents or staff awaiting approval.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index', ['status' => 0]) }}" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs">
                        Review
                    </a>
                </div>
            @endif

            @if($pendingPasswordRequests > 0)
                <div class="flex items-center justify-between p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ $pendingPasswordRequests }} Password Reset Request{{ $pendingPasswordRequests > 1 ? 's' : '' }}</p>
                            <p class="text-xs text-rose-700">App users requested admin reset.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.forgot-passwords.index', ['status' => 1]) }}" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs">
                        Resolve
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- High Level KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total Leads -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Leads / CRM</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-users-line text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($totalContacts) }}</span>
                <span class="text-xs font-semibold text-emerald-600 ml-2">
                    <i class="fa-solid fa-arrow-trend-up"></i> In pipeline
                </span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Bookings Closed: <strong class="text-emerald-600">{{ $totalBookings }}</strong></span>
                <a href="{{ route('admin.leads.index') }}" class="text-brand-600 hover:text-brand-700 font-semibold">View all &rarr;</a>
            </div>
        </div>

        <!-- Site Visits -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Site Visits Done</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-location-dot text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($totalSiteVisits) }}</span>
                <span class="text-xs font-semibold text-emerald-600 ml-2">Field verified</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>With GPS Coordinates</span>
                <a href="{{ route('admin.call-histories.index', ['visited' => 1]) }}" class="text-brand-600 hover:text-brand-700 font-semibold">Audit logs &rarr;</a>
            </div>
        </div>

        <!-- Today Attendances -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Attendance</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class="fa-solid fa-fingerprint text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($todayAttendances) }}</span>
                <span class="text-xs font-semibold text-purple-600 ml-2">Punched Today</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Date: {{ date('d M Y') }}</span>
                <a href="{{ route('admin.attendances.index', ['date' => date('Y-m-d')]) }}" class="text-brand-600 hover:text-brand-700 font-semibold">Check sheet &rarr;</a>
            </div>
        </div>

        <!-- Active Projects -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Projects</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-building-columns text-lg"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($totalProjects) }}</span>
                <span class="text-xs font-semibold text-amber-600 ml-2">{{ $activeOffers }} Active Offers</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Ongoing & Upcoming</span>
                <a href="{{ route('admin.projects.index') }}" class="text-brand-600 hover:text-brand-700 font-semibold">Explore &rarr;</a>
            </div>
        </div>

    </div>

    <!-- Analytics Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Chart 1: Monthly Leads Trend (Spans 2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Lead Inflow Trend (Last 6 Months)</h3>
                    <p class="text-xs text-slate-500">Monthly contact acquisition trajectory</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-50 text-brand-600 border border-brand-100">
                    Monthly Activity
                </span>
            </div>
            <div class="h-64 sm:h-72 w-full">
                <canvas id="monthlyLeadsChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Leads by Status Funnel -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Leads Pipeline Status</h3>
                    <p class="text-xs text-slate-500">Distribution across active stages</p>
                </div>
                <a href="{{ route('admin.reports.leads') }}" class="text-xs font-bold text-brand-600 hover:underline">
                    Report &rarr;
                </a>
            </div>
            <div class="h-64 sm:h-72 w-full flex items-center justify-center">
                <canvas id="statusDoughnutChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Attendance Weekly Trend + Top Projects Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Attendance Activity (Last 7 Days) -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">7-Day Attendance Punches</h3>
                    <p class="text-xs text-slate-500">Staff attendance volume this week</p>
                </div>
                <a href="{{ route('admin.attendances.index') }}" class="text-xs font-bold text-brand-600 hover:underline">
                    Logs &rarr;
                </a>
            </div>
            <div class="h-56 w-full">
                <canvas id="attendanceWeekChart"></canvas>
            </div>
        </div>

        <!-- Top Projects Leaderboard (Spans 2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Top Performing Projects</h3>
                    <p class="text-xs text-slate-500">Projects with highest lead volume & inquiries</p>
                </div>
                <a href="{{ route('admin.projects.index') }}" class="text-xs font-bold text-brand-600 hover:underline">
                    All Projects &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase border-b border-slate-100">
                            <th class="pb-3">Project Name</th>
                            <th class="pb-3 text-center">Inquiries</th>
                            <th class="pb-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topProjects as $proj)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 font-semibold text-slate-900 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <span>{{ $proj->name }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $proj->leads_count }} leads
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.projects.show', $proj->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold">
                                        Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-slate-400">No projects registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Data Tables: Recent Leads & Recent Punches -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Leads -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Recent Leads & Inquiries</h3>
                    <p class="text-xs text-slate-500">Newly assigned customer inquiries</p>
                </div>
                <a href="{{ route('admin.leads.index') }}" class="text-xs font-bold text-brand-600 hover:underline">
                    View all &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase border-b border-slate-100">
                            <th class="pb-3">Customer</th>
                            <th class="pb-3">Project</th>
                            <th class="pb-3">Executive</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentContacts as $lead)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3">
                                    <a href="{{ route('admin.leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-blue-600">
                                        {{ $lead->name }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <a href="tel:{{ $lead->mobile }}" class="text-[11px] font-semibold text-slate-700 hover:text-blue-600 flex items-center gap-1">
                                            <i class="fa-solid fa-phone text-[9px] text-emerald-600"></i> {{ $lead->mobile }}
                                        </a>
                                        @php
                                            $cCount = $lead->contactCallHistories->count();
                                        @endphp
                                        @if($cCount > 1)
                                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                                {{ $cCount }} calls
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 font-medium text-slate-700 truncate max-w-[120px]">
                                    {{ $lead->project->name ?? 'General' }}
                                </td>
                                <td class="py-3 text-slate-600">
                                    {{ $lead->user->name ?? 'N/A' }}
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $lead->status === 'booking_done' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $lead->status === 'site_visit_done' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $lead->status === 'follow_up' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ in_array($lead->status, ['call_later', 'call_not_connecting']) ? 'bg-slate-100 text-slate-700' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400">No leads recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Today's Attendance Feed -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Today's Attendance Punches</h3>
                    <p class="text-xs text-slate-500">Real-time clock-in feed with coordinates</p>
                </div>
                <a href="{{ route('admin.attendances.index') }}" class="text-xs font-bold text-brand-600 hover:underline">
                    View all &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentAttendances as $att)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @if($att->image)
                                <img src="{{ $att->image_url }}" alt="Proof" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">
                                    {{ substr($att->user->name ?? 'U', 0, 2) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $att->user->name ?? 'User' }}</p>
                                <p class="text-[11px] text-slate-500">
                                    {{ $att->user->designation->name ?? $att->user->role }} 
                                    @if($att->user->company_name)
                                        • {{ $att->user->company_name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-900 block">{{ date('h:i A', strtotime($att->time)) }}</span>
                            @if($att->latitude && $att->longitude)
                                <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="text-[10px] text-brand-600 hover:underline flex items-center justify-end gap-1 font-semibold">
                                    <i class="fa-solid fa-location-crosshairs text-[9px]"></i> Map
                                </a>
                            @else
                                <span class="text-[10px] text-slate-400">No GPS</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400 text-xs">
                        No punches recorded for today yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Monthly Leads Line Chart
    const monthlyCtx = document.getElementById('monthlyLeadsChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyLabels) !!},
                datasets: [{
                    label: 'Leads Generated',
                    data: {!! json_encode($monthlyLeadCounts) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8' },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Status Doughnut Chart
    const statusCtx = document.getElementById('statusDoughnutChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusChartLabels) !!},
                datasets: [{
                    data: {!! json_encode($statusChartData) !!},
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#64748b'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11 }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 3. Attendance Weekly Chart
    const attWeekCtx = document.getElementById('attendanceWeekChart');
    if (attWeekCtx) {
        new Chart(attWeekCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($attendanceWeekLabels) !!},
                datasets: [{
                    label: 'Punches',
                    data: {!! json_encode($attendanceWeekData) !!},
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8' },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endsection
