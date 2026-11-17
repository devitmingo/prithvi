@extends('admin.layouts.app')

@section('title', 'Monthly Attendance Sheet')
@section('page-title', 'Monthly Attendance Calendar Matrix')
@section('page-subtitle', 'Month-at-a-glance attendance tracking, total working days, and employee regularity')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Month Selector Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.attendances.monthly') }}" class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Select Month:</label>
            <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                   class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-brand-500 font-semibold">
            
            @if($isSuperAdmin && $allAgents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">All Leaders</option>
                    @foreach($allAgents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                Apply
            </button>
        </form>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Print Sheet</span>
            </button>
            <a href="{{ route('admin.attendances.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-list-check"></i>
                <span>Daily Punch Logs</span>
            </a>
        </div>
    </div>

    <!-- Attendance Grid Matrix Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                Monthly Sheet: {{ date('F Y', strtotime($selectedMonth . '-01')) }}
            </span>
            <span class="text-xs text-slate-500 font-semibold">
                Total Staff Active: {{ $users->count() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-center text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3 text-left min-w-[160px] sticky left-0 bg-slate-100 z-10">Staff Name</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $colDate = Carbon\Carbon::parse($selectedMonth . '-' . sprintf('%02d', $d));
                                $isWeekend = in_array($colDate->dayOfWeek, [0]); // Sunday
                            @endphp
                            <th class="py-2 px-1 min-w-[28px] {{ $isWeekend ? 'bg-amber-100 text-amber-900' : '' }}">
                                <span class="block">{{ $d }}</span>
                                <span class="text-[9px] font-normal opacity-75">{{ substr($colDate->format('D'), 0, 1) }}</span>
                            </th>
                        @endfor
                        <th class="py-3 px-3 font-extrabold text-emerald-800 bg-emerald-50 min-w-[70px]">Present</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        @php
                            $userDays = $attendanceMap[$user->id] ?? [];
                            $totalPresent = count($userDays);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-2.5 px-3 text-left font-bold text-slate-900 sticky left-0 bg-white z-10 border-r border-slate-100 truncate max-w-[180px]">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="hover:text-brand-600 block">
                                    {{ $user->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 font-normal block">{{ $user->designation->name ?? $user->role }}</span>
                            </td>

                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $colDate = Carbon\Carbon::parse($selectedMonth . '-' . sprintf('%02d', $d));
                                    $isSunday = in_array($colDate->dayOfWeek, [0]);
                                    $punched = isset($userDays[$d]);
                                @endphp
                                <td class="py-2 px-1 text-center {{ $isSunday ? 'bg-amber-50/40' : '' }}">
                                    @if($punched)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-emerald-500 text-white text-[10px] font-black shadow-2xs" title="{{ $userDays[$d] }} punch(es)">
                                            ✓
                                        </span>
                                    @elseif($isSunday)
                                        <span class="text-[9px] text-amber-500 font-bold">Sun</span>
                                    @else
                                        <span class="text-slate-200 text-xs">•</span>
                                    @endif
                                </td>
                            @endfor

                            <td class="py-2.5 px-3 font-black text-emerald-700 bg-emerald-50/50 border-l border-emerald-100">
                                {{ $totalPresent }} <span class="text-[10px] font-normal text-emerald-600">/ {{ $daysInMonth }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 2 }}" class="py-8 text-center text-slate-400">
                                No active employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
