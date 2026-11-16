@extends('admin.layouts.app')

@section('title', 'Attendance & Activity Report')
@section('page-title', 'Staff Attendance & Regularity Audit')
@section('page-subtitle', 'Audit employee punch regularity, timestamps, GPS locations, and active field presence')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.reports.attendance') }}" class="flex-1 flex flex-wrap items-center gap-2">
            
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

            <select name="user_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All Staff</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>

            @if($isSuperAdmin && $allAgents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">All Agencies</option>
                    @foreach($allAgents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                Apply
            </button>
            @if(request()->hasAny(['date_from', 'date_to', 'user_id', 'agent_id']))
                <a href="{{ route('admin.reports.attendance') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.attendance', array_merge(request()->query(), ['export' => 'csv'])) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Counters Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase">Total Punches Logged</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($totalPunches) }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase">Active Field Staff Members</p>
                <p class="text-2xl font-black text-slate-900">{{ $uniqueActiveStaff }}</p>
            </div>
        </div>
    </div>

    <!-- Attendance Punches Audit Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Punches Audit Trail</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $attendances->total() }} records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Agency / Designation</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Punch Time</th>
                        <th class="py-3 px-4">GPS Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('admin.users.show', $att->user_id) }}" class="hover:text-brand-600">
                                    {{ $att->user->name ?? 'User #' . $att->user_id }}
                                </a>
                                <span class="text-[11px] text-slate-500 block font-normal">{{ $att->user->mobile ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                <span class="font-semibold block">{{ $att->user->designation->name ?? $att->user->role }}</span>
                                <span class="text-[10px] text-slate-400">{{ $att->user->company_name ?? ($att->user->company->company_name ?? 'Direct Agency') }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ date('d M Y', strtotime($att->date)) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ date('h:i A', strtotime($att->time)) }}</td>
                            <td class="py-3 px-4">
                                @if($att->latitude && $att->longitude)
                                    <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="text-brand-600 font-semibold hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-location-crosshairs text-rose-500"></i>
                                        <span>{{ number_format($att->latitude, 4) }}, {{ number_format($att->longitude, 4) }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No punch records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
