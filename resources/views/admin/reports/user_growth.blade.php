@extends('admin.layouts.app')

@section('title', 'User Growth Report')
@section('page-title', 'User Growth & Platform Demographics')
@section('page-subtitle', 'Month-over-month partner registration velocity, role distribution, and approval breakdown')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">User Network Demographics</h3>
            <p class="text-xs text-slate-500">Distribution of administrators, agency partners, and field staff</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.user-growth', ['export' => 'csv']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Growth Chart & Role Breakdown Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Monthly Registrations Chart (Spans 2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">12-Month Registration Trajectory</h3>
                    <p class="text-xs text-slate-500">New agency partners & field staff onboarded</p>
                </div>
                <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-1 rounded-lg">
                    Growth Trends
                </span>
            </div>
            <div class="h-64 sm:h-72 w-full">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Role & Status Distribution -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-3 pb-2 border-b border-slate-100">
                    Network Breakdown by Role
                </h3>
                <div class="space-y-2.5 text-xs">
                    @foreach($usersByRole as $role => $cnt)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid {{ $role === 'Superadmin' ? 'fa-crown text-amber-500' : ($role === 'Agent' ? 'fa-user-tie text-blue-500' : 'fa-id-badge text-purple-500') }}"></i>
                                <span>{{ $role }}</span>
                            </span>
                            <span class="font-black text-slate-900 text-sm">{{ $cnt }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-2 pb-1 border-b border-slate-100">
                    Approval Statuses
                </h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-100 text-center">
                        <span class="text-lg font-black block">{{ $usersByStatus[1] ?? 0 }}</span>
                        <span class="text-[10px] font-bold uppercase">Approved</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-100 text-center">
                        <span class="text-lg font-black block">{{ $usersByStatus[0] ?? 0 }}</span>
                        <span class="text-[10px] font-bold uppercase">Pending</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Registrations Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Recent Registrations</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Agency Affiliation</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Registered On</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentUsers as $ru)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.users.show', $ru->id) }}" class="font-bold text-slate-900 hover:text-brand-600 block">
                                    {{ $ru->name }}
                                </a>
                                <span class="text-[11px] text-slate-500 font-normal">{{ $ru->mobile }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $ru->role }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $ru->company_name ?? ($ru->company->company_name ?? 'Direct Agency') }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $ru->city->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $ru->status_badge_class }}">
                                    {{ $ru->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-slate-500 text-[11px]">
                                {{ $ru->created_at ? $ru->created_at->format('d M Y') : 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('userGrowthChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'New Registrations',
                    data: {!! json_encode($counts) !!},
                    backgroundColor: '#2563eb',
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
