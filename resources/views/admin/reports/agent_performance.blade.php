@extends('admin.layouts.app')

@section('title', 'Leader & Staff Performance')
@section('page-title', 'Leader & Staff Performance Leaderboard')
@section('page-subtitle', 'Compare sales velocity, lead conversion, customer calls, and field site visits per team member')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.reports.agent-performance') }}" class="flex-1 flex flex-wrap items-center gap-2">
            
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

            @if($isSuperAdmin && $allAgents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">All Leaders & Agencies</option>
                    @foreach($allAgents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                Apply
            </button>
            @if(request()->hasAny(['date_from', 'date_to', 'agent_id']))
                <a href="{{ route('admin.reports.agent-performance') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.agent-performance', array_merge(request()->query(), ['export' => 'csv'])) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Performance Leaderboard Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Executive Performance Ranking</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ count($performanceData) }} Executives Audited</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4">Rank / Executive</th>
                        <th class="py-3.5 px-4">Role & Agency</th>
                        <th class="py-3.5 px-4 text-center">Leads Handled</th>
                        <th class="py-3.5 px-4 text-center">Calls Logged</th>
                        <th class="py-3.5 px-4 text-center">Site Visits</th>
                        <th class="py-3.5 px-4 text-center">Bookings Closed</th>
                        <th class="py-3.5 px-4 text-center">Conversion %</th>
                        <th class="py-3.5 px-4 text-right">Punches</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($performanceData as $index => $row)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            <!-- Rank & Executive -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs shrink-0
                                        {{ $index === 0 ? 'bg-amber-400 text-amber-950 shadow-xs' : ($index === 1 ? 'bg-slate-300 text-slate-800' : ($index === 2 ? 'bg-amber-700/30 text-amber-900' : 'bg-slate-100 text-slate-600')) }}">
                                        #{{ $index + 1 }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $row['user']->id) }}" class="font-bold text-slate-900 hover:text-brand-600 text-sm">
                                            {{ $row['user']->name }}
                                        </a>
                                        <span class="text-[11px] text-slate-400 block">{{ $row['user']->mobile }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Role & Agency -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $row['user']->designation->name ?? $row['user']->role }}</span>
                                <span class="text-[10px] text-slate-400">{{ $row['user']->company_name ?? ($row['user']->company->company_name ?? 'Direct Agency') }}</span>
                            </td>

                            <!-- Leads -->
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ $row['total_leads'] }}</td>

                            <!-- Calls -->
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700">{{ $row['calls'] }}</td>

                            <!-- Visits -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-xs bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $row['visits'] }}
                                </span>
                            </td>

                            <!-- Bookings -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-black text-xs bg-emerald-100 text-emerald-800">
                                    {{ $row['bookings'] }}
                                </span>
                            </td>

                            <!-- Conversion % -->
                            <td class="py-3.5 px-4 text-center font-black text-brand-600 text-sm">
                                {{ $row['conversion'] }}%
                            </td>

                            <!-- Attendances Punches -->
                            <td class="py-3.5 px-4 text-right font-bold text-purple-700">
                                {{ $row['attendances'] }} punches
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                No executive performance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
