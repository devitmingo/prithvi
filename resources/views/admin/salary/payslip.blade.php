<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Payslip - {{ $user->name }} ({{ date('d M Y', strtotime($fromDate)) }} to {{ date('d M Y', strtotime($toDate)) }})</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .payslip-container { box-shadow: none !important; border: none !important; max-width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 text-slate-800 antialiased">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('admin.salary.index', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Salary Sheet
        </a>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md transition">
                <i class="fa-solid fa-print text-sm"></i>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Payslip Document Container -->
    <div class="max-w-4xl mx-auto bg-white rounded-3xl p-8 sm:p-12 shadow-xl border border-slate-200 payslip-container space-y-8">
        
        <!-- Header: Logo, Company Info & Document Title -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b-2 border-slate-900">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white p-1.5 flex items-center justify-center border-2 border-amber-400 shadow-sm shrink-0">
                    <img src="{{ asset('images/prithvi-logo.png') }}" alt="Prithvi Buildcon" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $companyName }}</h1>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-0.5">Real Estate & Construction Developers</p>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-sm">Raipur, Chhattisgarh, India • Enterprise CRM Management</p>
                </div>
            </div>

            <div class="sm:text-right">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-slate-900 text-white">
                    Salary Statement / Payslip
                </span>
                <p class="text-xs text-slate-500 font-bold mt-2">Pay Period</p>
                <p class="text-sm font-black text-slate-900 font-mono">
                    {{ date('d M Y', strtotime($fromDate)) }} &ndash; {{ date('d M Y', strtotime($toDate)) }}
                </p>
                <p class="text-[11px] text-slate-400">Total Period: <strong>{{ $totalDaysInPeriod }} Calendar Days</strong></p>
            </div>
        </div>

        <!-- Employee Info & Organizational Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Employee Name</span>
                <span class="text-sm font-extrabold text-slate-900 mt-0.5 block">{{ $user->name }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Staff ID / Code</span>
                <span class="text-xs font-mono font-bold text-slate-800 mt-0.5 block">{{ $user->user_id ?? 'ID: #' . $user->id }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Designation / Role</span>
                <span class="text-xs font-bold text-slate-800 mt-0.5 block">{{ $user->designation->name ?? 'Executive' }} ({{ $user->role_display_name }})</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Mobile Number</span>
                <span class="text-xs font-bold text-slate-800 mt-0.5 block">{{ $user->mobile }}</span>
            </div>
            <div class="col-span-2 pt-2 border-t border-slate-200/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Leader / Parent Agency</span>
                <span class="text-xs font-bold text-slate-800 mt-0.5 block">{{ $user->company_name ?? ($user->company->name ?? 'Direct Agency Staff') }}</span>
            </div>
            <div class="col-span-2 pt-2 border-t border-slate-200/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Date of Joining / Approval</span>
                <span class="text-xs font-bold text-slate-800 mt-0.5 block">{{ $user->approved_date ? date('d M Y', strtotime($user->approved_date)) : ($user->created_at ? $user->created_at->format('d M Y') : '—') }}</span>
            </div>
        </div>

        <!-- Attendance & Days Summary -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-check text-blue-600"></i>
                <span>1. Attendance & Payable Days Computation</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center text-xs">
                <div class="p-3.5 rounded-xl border border-slate-200 bg-white">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Total Days</span>
                    <span class="text-xl font-black text-slate-900 mt-1 block">{{ $totalDaysInPeriod }}</span>
                    <span class="text-[10px] text-slate-400">Calendar days</span>
                </div>
                <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50">
                    <span class="text-[10px] font-bold text-emerald-700 uppercase block">Punched (P)</span>
                    <span class="text-xl font-black text-emerald-700 mt-1 block">{{ $presentDays }}</span>
                    <span class="text-[10px] text-emerald-600">Physical punches</span>
                </div>
                <div class="p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/50">
                    <span class="text-[10px] font-bold text-indigo-700 uppercase block">Holidays (H)</span>
                    <span class="text-xl font-black text-indigo-700 mt-1 block">+{{ $holidayDays }}</span>
                    <span class="text-[10px] text-indigo-600">Marked as Present</span>
                </div>
                <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/50">
                    <span class="text-[10px] font-bold text-amber-700 uppercase block">Weekly Offs</span>
                    <span class="text-xl font-black text-amber-700 mt-1 block">+{{ $weeklyOffDaysCount }}</span>
                    <span class="text-[10px] text-amber-600">Paid off days</span>
                </div>
                <div class="p-3.5 rounded-xl border border-blue-300 bg-blue-50/70">
                    <span class="text-[10px] font-bold text-blue-800 uppercase block">Total Payable</span>
                    <span class="text-xl font-black text-blue-900 mt-1 block">{{ $payableDays }}</span>
                    <span class="text-[10px] text-blue-700 font-bold">Days Paid</span>
                </div>
            </div>
        </div>

        <!-- Earnings Breakdown Table -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-receipt text-blue-600"></i>
                <span>2. Salary Earnings & Net Disbursement Calculation</span>
            </h3>

            <div class="rounded-2xl border border-slate-200 overflow-hidden text-xs">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Earnings / Item Component</th>
                            <th class="py-3 px-4">Basis / Units</th>
                            <th class="py-3 px-4 text-right">Rate (₹)</th>
                            <th class="py-3 px-4 text-right">Calculated Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr>
                            <td class="py-3.5 px-4 font-bold text-slate-900">Monthly Base Salary</td>
                            <td class="py-3.5 px-4">Standard Agreed Rate</td>
                            <td class="py-3.5 px-4 text-right font-mono font-medium">₹{{ number_format($monthlySalary, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold">₹{{ number_format($monthlySalary, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-4 font-medium">Computed Per-Day Wage</td>
                            <td class="py-3.5 px-4">₹{{ number_format($monthlySalary, 2) }} / {{ $totalDaysInPeriod }} days</td>
                            <td class="py-3.5 px-4 text-right font-mono font-medium">₹{{ number_format($perDayRate, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-500">—</td>
                        </tr>
                        <tr class="bg-emerald-50/30">
                            <td class="py-3.5 px-4 font-bold text-emerald-800">Earned Gross Pay</td>
                            <td class="py-3.5 px-4 text-emerald-700">{{ $payableDays }} Total Payable Days × ₹{{ number_format($perDayRate, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-medium text-emerald-800">₹{{ number_format($perDayRate, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-800">₹{{ number_format($netPayable, 2) }}</td>
                        </tr>
                        @if($effectiveAbsentDays > 0)
                        <tr class="bg-rose-50/30">
                            <td class="py-3 px-4 text-rose-700 font-medium">Leave / Absence Deduction</td>
                            <td class="py-3 px-4 text-rose-600">{{ $effectiveAbsentDays }} Unpaid Days × ₹{{ number_format($perDayRate, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-rose-700">-₹{{ number_format($perDayRate, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-medium text-rose-700">-₹{{ number_format($effectiveAbsentDays * $perDayRate, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-slate-900 text-white font-bold border-t-2 border-slate-900">
                        <tr>
                            <td colspan="3" class="py-4 px-4 text-sm uppercase tracking-wider">
                                Total Net Payable Disbursement
                            </td>
                            <td class="py-4 px-4 text-right text-lg font-black font-mono text-emerald-400">
                                ₹{{ number_format($netPayable, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Day by Day Attendance Grid -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-list-check text-blue-600"></i>
                <span>3. Daily Audit Log ({{ $fromDate }} to {{ $toDate }})</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2 text-[11px]">
                @foreach($dayLogs as $log)
                    <div class="p-2 rounded-xl border flex flex-col justify-between
                        {{ $log['status'] === 'present' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : '' }}
                        {{ $log['status'] === 'holiday' ? 'bg-indigo-50 border-indigo-200 text-indigo-900 font-bold' : '' }}
                        {{ $log['status'] === 'weekly_off' ? 'bg-amber-50 border-amber-200 text-amber-900' : '' }}
                        {{ $log['status'] === 'absent' ? 'bg-rose-50 border-rose-200 text-rose-800' : '' }}">
                        <div class="flex justify-between font-mono font-bold text-[10px]">
                            <span>{{ date('d M', strtotime($log['date'])) }}</span>
                            <span class="uppercase text-[9px]">{{ substr($log['day'], 0, 3) }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[10px]">
                            <span class="truncate font-medium">{{ $log['label'] }}</span>
                            <span class="w-4 h-4 rounded-full flex items-center justify-center font-bold text-white text-[9px] shrink-0
                                {{ $log['status'] === 'present' ? 'bg-emerald-600' : '' }}
                                {{ $log['status'] === 'holiday' ? 'bg-indigo-600' : '' }}
                                {{ $log['status'] === 'weekly_off' ? 'bg-amber-500' : '' }}
                                {{ $log['status'] === 'absent' ? 'bg-rose-600' : '' }}">
                                {{ $log['status'] === 'present' ? 'P' : ($log['status'] === 'holiday' ? 'H' : ($log['status'] === 'weekly_off' ? 'W' : 'A')) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Signatures & Authorization Block -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-2 sm:grid-cols-3 gap-8 text-xs text-center">
            <div>
                <div class="h-14 border-b border-dashed border-slate-300"></div>
                <span class="font-bold text-slate-800 mt-2 block">Prepared By</span>
                <span class="text-[10px] text-slate-400">Accounts & HR Dept.</span>
            </div>
            <div>
                <div class="h-14 border-b border-dashed border-slate-300"></div>
                <span class="font-bold text-slate-800 mt-2 block">Employee Signature</span>
                <span class="text-[10px] text-slate-400">{{ $user->name }}</span>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <div class="h-14 border-b border-dashed border-slate-300 flex items-center justify-center">
                    <span class="font-mono text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">Digitally Verified & Approved</span>
                </div>
                <span class="font-bold text-slate-800 mt-2 block">Authorized Signatory</span>
                <span class="text-[10px] text-slate-400">{{ $companyName }}</span>
            </div>
        </div>

        <!-- Footer note -->
        <div class="text-center pt-4 text-[10px] text-slate-400 border-t border-slate-100">
            This is a computer-generated salary statement issued by Prithvi Buildcon CRM Administration. Valid without physical stamp if electronically verified.
        </div>

    </div>

</body>
</html>
