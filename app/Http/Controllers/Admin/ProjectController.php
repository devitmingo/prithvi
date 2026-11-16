<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Masters\State;
use App\Models\Masters\City;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::with(['state', 'city', 'user']);

        if ($request->filled('project_status')) {
            $query->where('project_status', $request->project_status);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('rera_no', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        $projects = $query->withCount('contacts')->orderBy('id', 'desc')->paginate(12)->withQueryString();

        $states = State::where('status', 1)->orderBy('name')->get();
        $cities = City::where('status', 1)->orderBy('name')->get();

        return view('admin.projects.index', compact('projects', 'states', 'cities'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $states = State::where('status', 1)->orderBy('name')->get();
        $cities = City::where('status', 1)->orderBy('name')->get();

        return view('admin.projects.create', compact('states', 'cities'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'price' => 'nullable|numeric|min:0',
            'rera_no' => 'nullable|string|max:50',
            'project_status' => 'required|in:Ongoing,Upcoming,Completed',
            'status' => 'required|in:0,1',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'short_description' => 'nullable|string',
            'detailed_description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
            'availability_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,xlsx,xls|max:10240',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'name' => $request->name,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'price' => $request->price,
            'rera_no' => $request->rera_no,
            'project_status' => $request->project_status,
            'status' => $request->status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'short_description' => $request->short_description,
            'detailed_description' => $request->detailed_description,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = upload($request->file('image'), 'projects/images/');
        }
        if ($request->hasFile('document')) {
            $data['document'] = upload($request->file('document'), 'projects/documents/');
        }
        if ($request->hasFile('availability_document')) {
            $data['availability_document'] = upload($request->file('availability_document'), 'projects/availability_documents/');
        }

        $project = Project::create($data);

        return redirect()->route('admin.projects.show', $project->id)->with('success', 'Project created successfully.');
    }

    /**
     * Display project details.
     */
    public function show(Project $project)
    {
        $project->load(['state', 'city', 'user', 'offers']);
        $leads = $project->contacts()->with('user')->orderBy('id', 'desc')->paginate(10);
        $offers = $project->offers;
        $totalBookings = $project->contacts()->where('status', 'booking_done')->count();

        return view('admin.projects.show', compact('project', 'leads', 'offers', 'totalBookings'));
    }

    /**
     * Show edit form.
     */
    public function edit(Project $project)
    {
        $states = State::where('status', 1)->orderBy('name')->get();
        $cities = City::where('status', 1)->orderBy('name')->get();

        return view('admin.projects.edit', compact('project', 'states', 'cities'));
    }

    /**
     * Update project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'price' => 'nullable|numeric|min:0',
            'rera_no' => 'nullable|string|max:50',
            'project_status' => 'required|in:Ongoing,Upcoming,Completed',
            'status' => 'required|in:0,1',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'short_description' => 'nullable|string',
            'detailed_description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
            'availability_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,xlsx,xls|max:10240',
        ]);

        $data = [
            'name' => $request->name,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'price' => $request->price,
            'rera_no' => $request->rera_no,
            'project_status' => $request->project_status,
            'status' => $request->status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'short_description' => $request->short_description,
            'detailed_description' => $request->detailed_description,
        ];

        if ($request->hasFile('image')) {
            deleteFile($project->image, 'projects/images/');
            $data['image'] = upload($request->file('image'), 'projects/images/');
        }
        if ($request->hasFile('document')) {
            deleteFile($project->document, 'projects/documents/');
            $data['document'] = upload($request->file('document'), 'projects/documents/');
        }
        if ($request->hasFile('availability_document')) {
            deleteFile($project->availability_document, 'projects/availability_documents/');
            $data['availability_document'] = upload($request->file('availability_document'), 'projects/availability_documents/');
        }

        $project->update($data);

        return redirect()->route('admin.projects.show', $project->id)->with('success', 'Project updated successfully.');
    }

    /**
     * Remove project from storage.
     */
    public function destroy(Project $project)
    {
        deleteFile($project->image, 'projects/images/');
        deleteFile($project->document, 'projects/documents/');
        deleteFile($project->availability_document, 'projects/availability_documents/');

        $name = $project->name;
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', "Project '{$name}' has been deleted.");
    }

    /**
     * Toggle project status.
     */
    public function toggleStatus(Project $project)
    {
        $project->status = $project->status ? 0 : 1;
        $project->save();

        $statusStr = $project->status ? 'Active' : 'Inactive';
        return back()->with('success', "Project status changed to {$statusStr}.");
    }

    /**
     * Dedicated method to update the project layout map / plot availability image or PDF.
     */
    public function updateMap(Request $request, Project $project)
    {
        $request->validate([
            'availability_document' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
        ], [
            'availability_document.required' => 'Please select a layout map image or PDF file to upload.',
            'availability_document.mimes' => 'The map must be an image (JPG, PNG, WEBP) or a PDF document.',
            'availability_document.max' => 'The map file size must not exceed 20MB.',
        ]);

        if ($request->hasFile('availability_document')) {
            if ($project->availability_document) {
                deleteFile($project->availability_document, 'projects/availability_documents/');
            }
            $filename = upload($request->file('availability_document'), 'projects/availability_documents/');

            $project->availability_document = $filename;
            $project->touch(); // Touch updated_at so app receives the new update timestamp
            $project->save();
        }

        return back()->with('success', 'Project layout map / availability document updated successfully. The mobile app will now display the latest map with the updated timestamp.');
    }

    /**
     * Dedicated Project Maps & Layout Management Hub
     */
    public function mapsHub(Request $request)
    {
        $query = Project::with(['state', 'city']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('rera_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter')) {
            if ($request->filter === 'has_map') {
                $query->whereNotNull('availability_document');
            } elseif ($request->filter === 'no_map') {
                $query->whereNull('availability_document');
            }
        }

        $projects = $query->orderBy('name')->get();
        $allProjects = Project::orderBy('name')->get(['id', 'name', 'rera_no']);
        $totalProjects = Project::count();
        $withMapCount = Project::whereNotNull('availability_document')->count();
        $missingMapCount = $totalProjects - $withMapCount;
        $latestUpdated = Project::whereNotNull('availability_document')->latest('updated_at')->first();

        return view('admin.projects.maps', compact(
            'projects',
            'allProjects',
            'totalProjects',
            'withMapCount',
            'missingMapCount',
            'latestUpdated'
        ));
    }

    /**
     * Quick Map Update from Nav Shortcut or Global Modal
     */
    public function quickMapUpdate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'availability_document' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
        ], [
            'project_id.required' => 'Please select a project to update.',
            'availability_document.required' => 'Please select a layout map image or PDF file to upload.',
            'availability_document.mimes' => 'The map must be an image (JPG, PNG, WEBP) or a PDF document.',
            'availability_document.max' => 'The map file size must not exceed 20MB.',
        ]);

        $project = Project::findOrFail($request->project_id);

        if ($request->hasFile('availability_document')) {
            if ($project->availability_document) {
                deleteFile($project->availability_document, 'projects/availability_documents/');
            }
            $filename = upload($request->file('availability_document'), 'projects/availability_documents/');

            $project->availability_document = $filename;
            $project->touch();
            $project->save();
        }

        return back()->with('success', "Layout map for '{$project->name}' updated successfully! Mobile app users will see the new layout map immediately.");
    }

    /**
     * Remove the layout map from the project.
     */
    public function removeMap(Project $project)
    {
        if ($project->availability_document) {
            deleteFile($project->availability_document, 'projects/availability_documents/');
            $project->availability_document = null;
            $project->touch();
            $project->save();
        }

        return back()->with('success', 'Project layout map removed successfully.');
    }
}
