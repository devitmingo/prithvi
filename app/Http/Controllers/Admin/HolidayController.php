<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays.
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $query = Holiday::query();

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $holidays = $query->orderBy('date', 'asc')->paginate(20)->withQueryString();

        $availableYears = Holiday::selectRaw('YEAR(date) as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        if (!in_array(date('Y'), $availableYears)) {
            $availableYears[] = (int)date('Y');
            rsort($availableYears);
        }

        return view('admin.masters.holidays', compact('holidays', 'availableYears', 'year'));
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'date' => 'required|date|unique:holidays,date',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|boolean',
        ], [
            'date.unique' => 'A holiday is already recorded for this date.',
        ]);

        Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : ($request->status ?? 1),
        ]);

        return back()->with('success', "Holiday '{$request->name}' added successfully. This date will automatically be marked as Present in salary calculations.");
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|boolean',
        ]);

        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return back()->with('success', "Holiday '{$holiday->name}' updated successfully.");
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        return back()->with('success', "Holiday '{$name}' deleted successfully.");
    }

    /**
     * Quick toggle status.
     */
    public function toggleStatus(Holiday $holiday)
    {
        $holiday->status = !$holiday->status;
        $holiday->save();

        $statusStr = $holiday->status ? 'Active' : 'Inactive';
        return back()->with('success', "Holiday '{$holiday->name}' set to {$statusStr}.");
    }
}
