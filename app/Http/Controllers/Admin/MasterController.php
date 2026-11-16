<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Masters\State;
use App\Models\Masters\City;
use App\Models\Masters\Designation;

class MasterController extends Controller
{
    // ================= STATES =================
    public function statesIndex(Request $request)
    {
        $query = State::withCount('cities');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }
        $states = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.masters.states', compact('states'));
    }

    public function stateStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:states,name',
            'status' => 'required|in:0,1',
        ]);

        State::create($request->only('name', 'status'));
        return back()->with('success', 'State added successfully.');
    }

    public function stateUpdate(Request $request, State $state)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:states,name,' . $state->id,
            'status' => 'required|in:0,1',
        ]);

        $state->update($request->only('name', 'status'));
        return back()->with('success', 'State updated successfully.');
    }

    public function stateDestroy(State $state)
    {
        if ($state->cities()->count() > 0) {
            return back()->with('error', 'Cannot delete state because it has associated cities.');
        }
        $state->delete();
        return back()->with('success', 'State deleted successfully.');
    }

    public function stateToggle(State $state)
    {
        $state->status = $state->status ? 0 : 1;
        $state->save();
        return back()->with('success', 'State status toggled.');
    }

    // ================= CITIES =================
    public function citiesIndex(Request $request)
    {
        $query = City::with('state');
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }
        $cities = $query->orderBy('name')->paginate(15)->withQueryString();
        $states = State::where('status', 1)->orderBy('name')->get();
        return view('admin.masters.cities', compact('cities', 'states'));
    }

    public function cityStore(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        City::create($request->only('state_id', 'name', 'status'));
        return back()->with('success', 'City added successfully.');
    }

    public function cityUpdate(Request $request, City $city)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        $city->update($request->only('state_id', 'name', 'status'));
        return back()->with('success', 'City updated successfully.');
    }

    public function cityDestroy(City $city)
    {
        $city->delete();
        return back()->with('success', 'City deleted successfully.');
    }

    public function cityToggle(City $city)
    {
        $city->status = $city->status ? 0 : 1;
        $city->save();
        return back()->with('success', 'City status toggled.');
    }

    // ================= DESIGNATIONS =================
    public function designationsIndex(Request $request)
    {
        $query = Designation::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }
        $designations = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.masters.designations', compact('designations'));
    }

    public function designationStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:designations,name',
            'status' => 'required|in:0,1',
        ]);

        Designation::create($request->only('name', 'status'));
        return back()->with('success', 'Designation added successfully.');
    }

    public function designationUpdate(Request $request, Designation $designation)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:designations,name,' . $designation->id,
            'status' => 'required|in:0,1',
        ]);

        $designation->update($request->only('name', 'status'));
        return back()->with('success', 'Designation updated successfully.');
    }

    public function designationDestroy(Designation $designation)
    {
        $designation->delete();
        return back()->with('success', 'Designation deleted successfully.');
    }

    public function designationToggle(Designation $designation)
    {
        $designation->status = $designation->status ? 0 : 1;
        $designation->save();
        return back()->with('success', 'Designation status toggled.');
    }
}
