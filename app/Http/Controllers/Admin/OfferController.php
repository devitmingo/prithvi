<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Offer;
use App\Models\Project;

class OfferController extends Controller
{
    /**
     * Display a listing of offers.
     */
    public function index(Request $request)
    {
        $query = Offer::with(['project', 'user']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $offers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $projects = Project::where('status', 1)->orderBy('name')->get();

        return view('admin.offers.index', compact('offers', 'projects'));
    }

    /**
     * Show the form for creating a new offer.
     */
    public function create()
    {
        $projects = Project::where('status', 1)->orderBy('name')->get();
        return view('admin.offers.create', compact('projects'));
    }

    /**
     * Store a newly created offer in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'description' => 'required|string',
        ]);

        Offer::create([
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.offers.index')->with('success', 'Offer created successfully.');
    }

    /**
     * Show the form for editing an offer.
     */
    public function edit(Offer $offer)
    {
        $projects = Project::where('status', 1)->orderBy('name')->get();
        return view('admin.offers.edit', compact('offer', 'projects'));
    }

    /**
     * Update the specified offer in storage.
     */
    public function update(Request $request, Offer $offer)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'description' => 'required|string',
        ]);

        $offer->update([
            'project_id' => $request->project_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.offers.index')->with('success', 'Offer updated successfully.');
    }

    /**
     * Remove the specified offer from storage.
     */
    public function destroy(Offer $offer)
    {
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('success', 'Offer deleted successfully.');
    }
}
