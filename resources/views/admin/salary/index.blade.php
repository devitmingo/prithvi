@extends('admin.layouts.app')

@section('title', 'Staff Salary & Payroll Calculation')
@section('page-title', 'Staff Salary & Payroll Calculation')
@section('page-subtitle', 'Automated salary computation based on punched attendance, weekly offs, and holidays marked as present')

@section('content')
<div class="space-y-6" x-data="{ 
    weeklyOffModalOpen: false, 
    salaryModalOpen: false, 
    salaryUserId: null, 
    salaryUserName: '', 
    salaryCurrentAmount: 0,
    breakdownModalOpen: false,
    breakdownStaffName: '',
    breakdownDays: [] 
}">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.salary.index') }}" class="flex-1 flex flex-wrap items-center gap-2.5">
            
            <!-- From Date -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">From:</span>
                <input type="date" name="from_date" value="{{ $fromDate }}" required
                       class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-blue-500 font-semibold">
            </div>

            <!-- To Date -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">To:</span>
                <input type="date" name="to_date" value="{{ $toDate }}" required
                       class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-blue-500 font-semibold">
            </div>

            <!-- Staff Member Filter -->
            <select name="user_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                <option value="">All Staff & Leaders</option>
                @foreach($staffMembers as $sm)
                    <option value="{{ $sm->id }}" {{ request('user_id') == $sm->id ? 'selected' : '' }}>
                        {{ $sm->name }} ({{ $sm->role_display_name }})
                    </option>
                @endforeach
            </select>

            <!-- Leader Filter for Superadmin -->
            @if($isSuperAdmin && $agents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 font-medium">
                    <option value="">All Leaders</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>
                            {{ $ag->name }} {{ $ag->company_name ? "({$ag->company_name})" : '' }}
                        </option>
                    @endforeach
                </select>
            @endif

            <!-- Search -->
            <div class="relative flex-1 min-w-[160px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search staff, phone, ID..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-calculator text-[10px]"></i>
                <span>Compute</span>
            </button>

            @if(request()->hasAny(['search', 'from_date', 'to_date', 'user_id', 'agent_id']))
                <a href="{{ route('admin.salary.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <!-- Right Quick Action Buttons -->
        <div class="flex items-center gap-2">
            <!-- Weekly Off Settings Trigger -->
            <button type="button" @click="weeklyOffModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition" title="Weekly Off Setting">
                <i class="fa-solid fa-calendar-week text-amber-600"></i>
                <span>Weekly Off: <strong>{{ implode(', ', $weeklyOffDays) }}</strong></span>
            </button>

            <!-- Holiday Master Link -->
            <a href="{{ route('admin.masters.holidays.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition" title="Manage Holidays">
                <i class="fa-solid fa-gift text-indigo-600"></i>
                <span>Holidays ({{ $holidays->count() }})</span>
            </a>

            <!-- Export CSV -->
            <a href="{{ route('admin.salary.index', array_merge(request()->query(), ['export' => 'csv', 'from_date' => $fromDate, 'to_date' => $toDate])) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Quick Month Selector Pills -->
    <div class="flex items-center gap-2 text-xs">
        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Quick Period:</span>
        <a href="{{ route('admin.salary.index', ['from_date' => now()->startOfMonth()->toDateString(), 'to_date' => now()->endOfMonth()->toDateString()]) }}" 
           class="px-2.5 py-1 rounded-lg font-semibold transition {{ $fromDate === now()->startOfMonth()->toDateString() && $toDate === now()->endOfMonth()->toDateString() ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
            This Month ({{ now()->format('M Y') }})
        </a>
        <a href="{{ route('admin.salary.index', ['from_date' => now()->subMonth()->startOfMonth()->toDateString(), 'to_date' => now()->subMonth()->endOfMonth()->toDateString()]) }}" 
           class="px-2.5 py-1 rounded-lg font-semibold transition {{ $fromDate === now()->subMonth()->startOfMonth()->toDateString() && $toDate === now()->subMonth()->endOfMonth()->toDateString() ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
            Last Month ({{ now()->subMonth()->format('M Y') }})
        </a>
        <a href="{{ route('admin.salary.index', ['from_date' => now()->subDays(30)->toDateString(), 'to_date' => now()->toDateString()]) }}" 
           class="px-2.5 py-1 rounded-lg font-semibold transition {{ $fromDate === now()->subDays(30)->toDateString() && $toDate === now()->toDateString() ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
            Last 30 Days
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Staff -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Staff Count</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 mt-2 block">{{ count($payroll) }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Staff members evaluated</p>
        </div>

        <!-- Total Payroll Amount -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Total Net Payroll</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-emerald-600 mt-2 block">₹{{ number_format($totalPayrollAmount, 2) }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Net payable for selected period</p>
        </div>

        <!-- Period Days & Punched Days -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Period & Attendance</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-indigo-600 mt-2 block">{{ $totalDaysInPeriod }} Days</span>
            <p class="text-[11px] text-slate-500 font-semibold mt-1">{{ $totalPresentDaysSum }} Total Punched Days</p>
        </div>

        <!-- Holidays & Weekly Offs -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Paid Holidays & Offs</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-gift"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-amber-600 mt-2 block">{{ $holidays->count() }} Holidays</span>
            <p class="text-[11px] text-slate-500 font-semibold mt-1">Auto-credited as Present</p>
        </div>
    </div>

    <!-- Payroll Calculation Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 to-white">
            <div>
                <h3 class="text-base font-bold text-slate-900">Staff Salary Calculation Sheet</h3>
                <p class="text-xs text-slate-500">Payable Days = Present (Punched) + Paid Holidays + Paid Weekly Offs. Net Salary = Payable Days × (Base Salary / {{ $totalDaysInPeriod }}).</p>
            </div>
            <div class="text-xs font-mono font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
                Period: {{ date('d M Y', strtotime($fromDate)) }} &rarr; {{ date('d M Y', strtotime($toDate)) }} ({{ $totalDaysInPeriod }} days)
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-4">Staff Member</th>
                        <th class="py-3.5 px-4">Role & Agency</th>
                        <th class="py-3.5 px-4">Base Salary</th>
                        <th class="py-3.5 px-4 text-center">Present (P)</th>
                        <th class="py-3.5 px-4 text-center">Holidays (H)</th>
                        <th class="py-3.5 px-4 text-center">Weekly Off (WO)</th>
                        <th class="py-3.5 px-4 text-center">Payable Days</th>
                        <th class="py-3.5 px-4 text-center">Absent (A)</th>
                        <th class="py-3.5 px-4 text-right">Per-Day Rate</th>
                        <th class="py-3.5 px-4 text-right">Net Payable</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payroll as $row)
                        @php
                            $s = $row['staff'];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            
                            <!-- Staff Member -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if($s->profile_photo)
                                        <img src="{{ $s->profile_photo_url }}" 
                                             alt="{{ $s->name }}" 
                                             class="w-9 h-9 rounded-full object-cover border border-slate-200 shrink-0"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($s->name) }}&color=1d4ed8&background=dbeafe';">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200">
                                            {{ substr($s->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.users.show', $s->id) }}" class="font-bold text-slate-900 hover:text-blue-600 block text-sm">
                                            {{ $s->name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                                            <a href="tel:{{ $s->mobile }}" class="hover:text-blue-600">
                                                <i class="fa-solid fa-phone text-[9px] text-emerald-600"></i> {{ $s->mobile }}
                                            </a>
                                            <span>•</span>
                                            <span class="font-mono text-slate-400">{{ $s->user_id ?? 'ID: ' . $s->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role & Agency -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ $s->role === 'Agent' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $s->role_display_name }}
                                </span>
                                @if($s->role === 'Sub Agent' && $s->company)
                                    <p class="text-[11px] text-blue-700 font-medium mt-1 truncate max-w-[150px]" title="Leader: {{ $s->company->name }}">
                                        <i class="fa-solid fa-user-tie text-[9px] text-blue-500"></i> {{ $s->company->name }}
                                    </p>
                                @else
                                    <p class="text-[11px] text-slate-500 mt-1 truncate max-w-[140px]" title="{{ $s->company_name ?? ($s->company->company_name ?? 'Direct Agency') }}">
                                        {{ $s->company_name ?? ($s->company->company_name ?? 'Direct Agency') }}
                                    </p>
                                @endif
                            </td>

                            <!-- Base Salary with Quick Edit -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900 text-xs">₹{{ number_format($row['monthly_salary'], 2) }}</span>
                                    <button type="button" 
                                            @click="salaryUserId = {{ $s->id }}; salaryUserName = '{{ addslashes($s->name) }}'; salaryCurrentAmount = {{ $row['monthly_salary'] }}; salaryModalOpen = true"
                                            class="text-blue-600 hover:text-blue-800 p-1" title="Edit Monthly Base Salary">
                                        <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                    </button>
                                </div>
                                <span class="text-[10px] text-slate-400">/ month</span>
                            </td>

                            <!-- Present Days -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-bold text-xs bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    {{ $row['present_days'] }}
                                </span>
                            </td>

                            <!-- Paid Holidays (Marked Present) -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200" title="Public Holidays (Marked Present)">
                                    +{{ $row['holiday_days'] }}
                                </span>
                            </td>

                            <!-- Weekly Offs (Paid) -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg font-bold text-xs bg-amber-50 text-amber-700 border border-amber-200" title="Weekly Offs (Paid)">
                                    +{{ $row['weekly_off_days'] }}
                                </span>
                            </td>

                            <!-- Total Payable Days -->
                            <td class="py-3.5 px-4 text-center font-bold">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs font-black bg-blue-100 text-blue-900 border border-blue-300">
                                    {{ $row['payable_days'] }} / {{ $row['total_days'] }}
                                </span>
                            </td>

                            <!-- Absent Days -->
                            <td class="py-3.5 px-4 text-center">
                                @if($row['absent_days'] > 0)
                                    <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg font-bold text-xs bg-rose-100 text-rose-800 border border-rose-300">
                                        {{ $row['absent_days'] }} d
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-bold text-xs">0</span>
                                @endif
                            </td>

                            <!-- Per-Day Rate -->
                            <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                ₹{{ number_format($row['per_day_rate'], 2) }}
                            </td>

                            <!-- Net Payable Salary -->
                            <td class="py-3.5 px-4 text-right">
                                <span class="font-extrabold text-sm text-emerald-700 block">
                                    ₹{{ number_format($row['net_payable'], 2) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- View Day-by-Day Breakdown Modal Trigger -->
                                    <button type="button" 
                                            @click="breakdownStaffName = '{{ addslashes($s->name) }}'; breakdownDays = {{ json_encode($row['day_logs']) }}; breakdownModalOpen = true"
                                            class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                            title="View Day-by-Day Attendance Calendar">
                                        <i class="fa-solid fa-calendar-days text-xs"></i>
                                    </button>

                                    <!-- Payslip View / Print -->
                                    <a href="{{ route('admin.salary.payslip', array_merge(['user' => $s->id], ['from_date' => $fromDate, 'to_date' => $toDate])) }}" 
                                       target="_blank"
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-800 text-slate-700 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                       title="View & Print Detailed Payslip">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </a>

                                    <!-- Quick Edit Salary -->
                                    <button type="button" 
                                            @click="salaryUserId = {{ $s->id }}; salaryUserName = '{{ addslashes($s->name) }}'; salaryCurrentAmount = {{ $row['monthly_salary'] }}; salaryModalOpen = true"
                                            class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                            title="Change Base Salary">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-users-slash text-3xl text-slate-300 mb-2 block"></i>
                                No staff members found matching your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Day-by-Day Attendance & Holiday Breakdown -->
    <div x-show="breakdownModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="breakdownModalOpen = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Attendance & Holiday Calendar Log</h3>
                        <p class="text-[11px] text-slate-500">Staff: <span class="font-bold text-blue-600" x-text="breakdownStaffName"></span> ({{ $fromDate }} to {{ $toDate }})</p>
                    </div>
                </div>
                <button type="button" @click="breakdownModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-3 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Present (Punched)</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500"></span> Holiday (Marked Present)</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Paid Weekly Off</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Absent</span>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar pr-1">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <template x-for="day in breakdownDays" :key="day.date">
                        <div class="p-2.5 rounded-xl border text-xs flex flex-col justify-between"
                             :class="{
                                 'bg-emerald-50/70 border-emerald-200 text-emerald-900': day.status === 'present',
                                 'bg-indigo-50/70 border-indigo-200 text-indigo-900': day.status === 'holiday',
                                 'bg-amber-50/70 border-amber-200 text-amber-900': day.status === 'weekly_off',
                                 'bg-rose-50/70 border-rose-200 text-rose-900': day.status === 'absent'
                             }">
                            <div class="flex items-center justify-between">
                                <span class="font-bold font-mono" x-text="day.date"></span>
                                <span class="text-[10px] font-semibold uppercase" x-text="day.day"></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-[11px] font-semibold truncate" x-text="day.label"></span>
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0 ml-1"
                                      :class="{
                                          'bg-emerald-600': day.status === 'present',
                                          'bg-indigo-600': day.status === 'holiday',
                                          'bg-amber-500': day.status === 'weekly_off',
                                          'bg-rose-600': day.status === 'absent'
                                      }">
                                    <span x-text="day.status === 'present' ? 'P' : (day.status === 'holiday' ? 'H' : (day.status === 'weekly_off' ? 'WO' : 'A'))"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex justify-end">
                <button type="button" @click="breakdownModalOpen = false" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Weekly Off Settings -->
    <div x-show="weeklyOffModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="weeklyOffModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-calendar-week"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Weekly Off Day Setting</h3>
                        <p class="text-[11px] text-slate-500">Configure which days of the week are paid weekly offs</p>
                    </div>
                </div>
                <button type="button" @click="weeklyOffModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.salary.weekly-off') }}" method="POST" class="space-y-4">
                @csrf

                <p class="text-xs text-slate-600">Select one or more weekly off days. These days will count as paid days in salary calculations:</p>

                <div class="space-y-2 border border-slate-200/80 rounded-xl p-3 bg-slate-50/50">
                    @php
                        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    @endphp
                    @foreach($daysOfWeek as $dow)
                        <label class="flex items-center gap-2.5 text-xs text-slate-700 font-medium cursor-pointer hover:bg-slate-100 p-1.5 rounded-lg transition">
                            <input type="checkbox" name="weekly_off_days[]" value="{{ $dow }}" 
                                   {{ in_array($dow, $weeklyOffDays) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span>{{ $dow }}</span>
                            @if($dow === 'Sunday')
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200 ml-auto">Standard</span>
                            @endif
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="weeklyOffModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition">
                        Save Weekly Off Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Quick Update Staff Base Salary -->
    <div x-show="salaryModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="salaryModalOpen = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Update Base Salary</h3>
                        <p class="text-[11px] text-slate-500"><span x-text="salaryUserName" class="font-bold text-slate-800"></span></p>
                    </div>
                </div>
                <button type="button" @click="salaryModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/users') }}/' + salaryUserId + '/salary'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Monthly Base Salary (₹) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">₹</span>
                        <input type="number" step="0.01" min="0" name="salary" x-model="salaryCurrentAmount" required
                               class="w-full pl-8 pr-3 py-2.5 text-sm font-bold text-slate-900 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">This will update the staff profile's monthly base salary rate.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="salaryModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">
                        Save Salary
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
