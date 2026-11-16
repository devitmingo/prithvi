@extends('admin.layouts.app')

@section('title', 'Leads & Pipeline Funnel Report')
@section('page-title', 'Leads & Pipeline Funnel Report')
@section('page-subtitle', 'Track prospect conversion rates, pipeline velocity, and project inquiry distribution')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.reports.leads') }}" class="flex-1 flex flex-wrap items-center gap-2">
            
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">From:</span>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">To:</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-brand-500">
            </div>

            <select name="project_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>

            @if($isSuperAdmin && $agents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">All Agencies</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                Apply Filters
            </button>
            @if(request()->hasAny(['date_from', 'date_to', 'project_id', 'agent_id']))
                <a href="{{ route('admin.reports.leads') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.leads', array_merge(request()->query(), ['export' => 'csv'])) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Funnel Conversion Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase">1. Inquiries</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $totalLeads }}</span>
            <span class="text-[11px] text-slate-500 mt-1 block">Top of funnel</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-amber-600 uppercase">2. Follow Ups</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">{{ $totalFollowUps }}</span>
            <span class="text-[11px] text-slate-500 mt-1 block">Actively nurturing</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-blue-600 uppercase">3. Site Visits</span>
            <span class="text-2xl font-black text-blue-600 mt-1 block">{{ $totalVisits }}</span>
            <span class="text-[11px] text-blue-700 font-bold mt-1 block">{{ $visitConversionRate }}% visit rate</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-emerald-600 uppercase">4. Bookings Done</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $totalBookings }}</span>
            <span class="text-[11px] text-emerald-700 font-bold mt-1 block">{{ $bookingConversionRate }}% conversion</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-rose-600 uppercase">5. Lost / Unreachable</span>
            <span class="text-2xl font-black text-rose-600 mt-1 block">{{ $totalLost }}</span>
            <span class="text-[11px] text-slate-500 mt-1 block">Drop-offs</span>
        </div>

    </div>

    <!-- Project Breakdown Summary -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Project-wise Inquiry & Booking Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Project Name</th>
                        <th class="py-3 px-4 text-center">Total Inquiries</th>
                        <th class="py-3 px-4 text-center">Confirmed Bookings</th>
                        <th class="py-3 px-4 text-right">Conversion %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projectBreakdown as $row)
                        @php
                            $rate = $row->total > 0 ? round(($row->bookings / $row->total) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $row->project->name ?? 'General Inquiry' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $row->total }}</td>
                            <td class="py-3 px-4 text-center font-extrabold text-emerald-600">{{ $row->bookings }}</td>
                            <td class="py-3 px-4 text-right font-black text-brand-600">{{ $rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-400">No project breakdown data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Leads Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Detailed Leads Registry</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $leads->total() }} records matching criteria</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Project</th>
                        <th class="py-3.5 px-4">Assigned Executive</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Inquiry Date</th>
                        <th class="py-3.5 px-4">Remark</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $l)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('admin.leads.show', $l->id) }}" class="hover:text-brand-600">
                                    {{ $l->name }}
                                </a>
                                <span class="text-[11px] text-slate-500 block font-normal">{{ $l->mobile }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $l->project->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $l->user->name ?? 'Unassigned' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $l->status === 'booking_done' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $l->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $l->date }}</td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $l->remark }}">{{ $l->remark ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No leads recorded for this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
