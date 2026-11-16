<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalaryController extends Controller
{
    /**
     * Display staff salary calculation and payroll sheet.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        // Default to current month start and end date
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->endOfMonth()->toDateString());

        // Validate date order
        if ($fromDate > $toDate) {
            $temp = $fromDate;
            $fromDate = $toDate;
            $toDate = $temp;
        }

        // 1. Weekly Off Settings (defaults to Sunday)
        $weeklyOffSetting = Setting::where('key_name', 'weekly_off_days')->first();
        $weeklyOffValue = $weeklyOffSetting ? $weeklyOffSetting->value : 'Sunday';
        $weeklyOffDays = array_map('trim', explode(',', $weeklyOffValue));

        // 2. Active Holidays within date range
        $holidays = Holiday::where('status', 1)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get();
        $holidayDates = $holidays->map(fn($h) => $h->date->format('Y-m-d'))->toArray();
        $holidayMap = $holidays->keyBy(fn($h) => $h->date->format('Y-m-d'));

        // 3. Calendar Days in Period
        $periodDates = [];
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);
        $curr = $start->copy();
        while ($curr->lte($end)) {
            $periodDates[] = [
                'date' => $curr->format('Y-m-d'),
                'day_name' => $curr->format('l'),
                'is_holiday' => in_array($curr->format('Y-m-d'), $holidayDates),
                'holiday_name' => $holidayMap[$curr->format('Y-m-d')]->name ?? null,
                'is_weekly_off' => in_array($curr->format('l'), $weeklyOffDays),
            ];
            $curr->addDay();
        }
        $totalDaysInPeriod = count($periodDates);

        // 4. Query Staff Members
        $query = User::with(['company', 'designation']);

        if (!$isSuperAdmin) {
            // Agent sees only their staff
            $query->where('company_id', $currentUser->id);
        } else {
            if ($request->filled('agent_id')) {
                $query->where('company_id', $request->agent_id);
            }
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        } else {
            // Default show Staff (Sub Agent) and Leaders (Agent)
            $query->whereIn('role', ['Sub Agent', 'Agent']);
        }

        if ($request->filled('user_id')) {
            $query->where('id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        $allStaff = $query->orderBy('name', 'asc')->get();

        // 5. Calculate attendance and salary breakdown for each staff
        $payroll = [];
        $totalPayrollAmount = 0;
        $totalPresentDaysSum = 0;
        $totalPayableDaysSum = 0;

        foreach ($allStaff as $staff) {
            // Get all punch dates for this staff in date range
            $punchDates = Attendance::where('user_id', $staff->id)
                ->whereBetween('date', [$fromDate, $toDate])
                ->pluck('date')
                ->unique()
                ->toArray();

            $presentDays = 0;
            $holidayDays = 0;
            $weeklyOffDaysCount = 0;
            $absentDays = 0;
            $dayLogs = [];

            foreach ($periodDates as $pDay) {
                $d = $pDay['date'];
                $isPunched = in_array($d, $punchDates);
                $isHoliday = $pDay['is_holiday'];
                $isWeeklyOff = $pDay['is_weekly_off'];

                if ($isPunched) {
                    $status = 'present';
                    $statusLabel = 'Present (Punched)';
                    $presentDays++;
                } elseif ($isHoliday) {
                    $status = 'holiday';
                    $statusLabel = 'Holiday: ' . $pDay['holiday_name'];
                    $holidayDays++; // Holiday marked as Present!
                } elseif ($isWeeklyOff) {
                    $status = 'weekly_off';
                    $statusLabel = 'Weekly Off (' . $pDay['day_name'] . ')';
                    $weeklyOffDaysCount++;
                } else {
                    $status = 'absent';
                    $statusLabel = 'Absent';
                    $absentDays++;
                }

                $dayLogs[] = [
                    'date' => $d,
                    'day' => $pDay['day_name'],
                    'status' => $status,
                    'label' => $statusLabel,
                ];
            }

            // Total Payable Days = Present + Holiday + Weekly Off
            $payableDays = min($totalDaysInPeriod, $presentDays + $holidayDays + $weeklyOffDaysCount);
            $effectiveAbsentDays = max(0, $totalDaysInPeriod - $payableDays);

            $monthlySalary = (float)$staff->salary;
            $perDayRate = $totalDaysInPeriod > 0 ? round($monthlySalary / $totalDaysInPeriod, 2) : 0;
            $netPayable = round($payableDays * $perDayRate, 2);

            $totalPayrollAmount += $netPayable;
            $totalPresentDaysSum += $presentDays;
            $totalPayableDaysSum += $payableDays;

            $payroll[] = [
                'staff' => $staff,
                'monthly_salary' => $monthlySalary,
                'total_days' => $totalDaysInPeriod,
                'present_days' => $presentDays,
                'holiday_days' => $holidayDays,
                'weekly_off_days' => $weeklyOffDaysCount,
                'payable_days' => $payableDays,
                'absent_days' => $effectiveAbsentDays,
                'per_day_rate' => $perDayRate,
                'net_payable' => $netPayable,
                'day_logs' => $dayLogs,
            ];
        }

        // Export CSV if requested
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($payroll, $fromDate, $toDate);
        }

        // All agents for filter dropdown
        $agents = $isSuperAdmin ? User::where('role', 'Agent')->orderBy('name')->get() : collect();
        $staffMembers = $isSuperAdmin 
            ? User::whereIn('role', ['Sub Agent', 'Agent'])->orderBy('name')->get()
            : User::where('company_id', $currentUser->id)->orderBy('name')->get();

        return view('admin.salary.index', compact(
            'payroll',
            'fromDate',
            'toDate',
            'totalDaysInPeriod',
            'weeklyOffDays',
            'weeklyOffValue',
            'holidays',
            'totalPayrollAmount',
            'totalPresentDaysSum',
            'totalPayableDaysSum',
            'agents',
            'staffMembers',
            'isSuperAdmin'
        ));
    }

    /**
     * Update weekly off day setting.
     */
    public function updateWeeklyOff(Request $request)
    {
        $request->validate([
            'weekly_off_days' => 'required|array',
            'weekly_off_days.*' => 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
        ]);

        $value = implode(',', $request->weekly_off_days);

        Setting::updateOrCreate(
            ['key_name' => 'weekly_off_days'],
            ['value' => $value, 'status' => 1]
        );

        return back()->with('success', "Weekly off setting updated to: {$value}. Salary calculations will now consider these days as paid weekly offs.");
    }

    /**
     * Display individual printable payslip view.
     */
    public function payslip(Request $request, User $user)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', now()->endOfMonth()->toDateString());

        // Weekly Off
        $weeklyOffSetting = Setting::where('key_name', 'weekly_off_days')->first();
        $weeklyOffValue = $weeklyOffSetting ? $weeklyOffSetting->value : 'Sunday';
        $weeklyOffDays = array_map('trim', explode(',', $weeklyOffValue));

        // Holidays
        $holidays = Holiday::where('status', 1)->whereBetween('date', [$fromDate, $toDate])->get();
        $holidayDates = $holidays->map(fn($h) => $h->date->format('Y-m-d'))->toArray();
        $holidayMap = $holidays->keyBy(fn($h) => $h->date->format('Y-m-d'));

        // Calendar Days
        $periodDates = [];
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);
        $curr = $start->copy();
        while ($curr->lte($end)) {
            $periodDates[] = [
                'date' => $curr->format('Y-m-d'),
                'day_name' => $curr->format('l'),
                'is_holiday' => in_array($curr->format('Y-m-d'), $holidayDates),
                'holiday_name' => $holidayMap[$curr->format('Y-m-d')]->name ?? null,
                'is_weekly_off' => in_array($curr->format('l'), $weeklyOffDays),
            ];
            $curr->addDay();
        }
        $totalDaysInPeriod = count($periodDates);

        // Punches
        $punchDates = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->pluck('date')
            ->unique()
            ->toArray();

        $presentDays = 0;
        $holidayDays = 0;
        $weeklyOffDaysCount = 0;
        $absentDays = 0;
        $dayLogs = [];

        foreach ($periodDates as $pDay) {
            $d = $pDay['date'];
            $isPunched = in_array($d, $punchDates);
            $isHoliday = $pDay['is_holiday'];
            $isWeeklyOff = $pDay['is_weekly_off'];

            if ($isPunched) {
                $status = 'present';
                $statusLabel = 'Present';
                $presentDays++;
            } elseif ($isHoliday) {
                $status = 'holiday';
                $statusLabel = 'Holiday: ' . $pDay['holiday_name'];
                $holidayDays++;
            } elseif ($isWeeklyOff) {
                $status = 'weekly_off';
                $statusLabel = 'Weekly Off';
                $weeklyOffDaysCount++;
            } else {
                $status = 'absent';
                $statusLabel = 'Absent';
                $absentDays++;
            }

            $dayLogs[] = [
                'date' => $d,
                'day' => $pDay['day_name'],
                'status' => $status,
                'label' => $statusLabel,
            ];
        }

        $payableDays = min($totalDaysInPeriod, $presentDays + $holidayDays + $weeklyOffDaysCount);
        $effectiveAbsentDays = max(0, $totalDaysInPeriod - $payableDays);

        $monthlySalary = (float)$user->salary;
        $perDayRate = $totalDaysInPeriod > 0 ? round($monthlySalary / $totalDaysInPeriod, 2) : 0;
        $netPayable = round($payableDays * $perDayRate, 2);

        $companySetting = Setting::where('key_name', 'company_name')->first();
        $companyName = $companySetting ? $companySetting->value : 'Prithvi Buildcon';

        return view('admin.salary.payslip', compact(
            'user',
            'fromDate',
            'toDate',
            'totalDaysInPeriod',
            'presentDays',
            'holidayDays',
            'weeklyOffDaysCount',
            'payableDays',
            'effectiveAbsentDays',
            'monthlySalary',
            'perDayRate',
            'netPayable',
            'dayLogs',
            'companyName'
        ));
    }

    /**
     * Export payroll sheet as CSV.
     */
    protected function exportCsv(array $payroll, string $fromDate, string $toDate)
    {
        $filename = 'salary_payroll_' . $fromDate . '_to_' . $toDate . '.csv';

        return new StreamedResponse(function () use ($payroll, $fromDate, $toDate) {
            $handle = fopen('php://output', 'w');

            // Header info
            fputcsv($handle, ['Prithvi Buildcon - Staff Salary Payroll Report']);
            fputcsv($handle, ['Period:', $fromDate . ' to ' . $toDate]);
            fputcsv($handle, []);

            // Columns
            fputcsv($handle, [
                'Staff ID',
                'Staff Name',
                'Role',
                'Designation',
                'Agency / Leader',
                'Phone',
                'Monthly Base Salary (INR)',
                'Total Days',
                'Present Days',
                'Paid Holidays',
                'Paid Weekly Offs',
                'Total Payable Days',
                'Absent Days',
                'Per Day Rate (INR)',
                'Net Payable Salary (INR)',
            ]);

            foreach ($payroll as $row) {
                $s = $row['staff'];
                fputcsv($handle, [
                    $s->user_id ?? $s->id,
                    $s->name,
                    $s->role_display_name,
                    $s->designation->name ?? 'Executive',
                    $s->company_name ?? ($s->company->name ?? 'Direct'),
                    $s->mobile,
                    $row['monthly_salary'],
                    $row['total_days'],
                    $row['present_days'],
                    $row['holiday_days'],
                    $row['weekly_off_days'],
                    $row['payable_days'],
                    $row['absent_days'],
                    $row['per_day_rate'],
                    $row['net_payable'],
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
