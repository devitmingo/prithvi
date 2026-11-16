@extends('admin.layouts.app')

@section('title', $project->title . ' - Project Details')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Projects Inventory
        </a>
        <div class="flex items-center space-x-2">
            <!-- Update Map Button -->
            <button type="button" onclick="openMapModal()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-semibold rounded-lg shadow-sm hover:from-blue-700 hover:to-indigo-700 transition">
                <i class="fa-solid fa-map-location-dot mr-2"></i> Update Project Map
            </button>

            <a href="{{ route('admin.projects.edit', $project->id) }}" class="inline-flex items-center px-3.5 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-800 transition">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i> Edit Project
            </a>
            @if(auth()->user()?->isSuperAdmin())
            <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Delete this project permanently?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Project">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Hero Card -->
    <div class="relative bg-slate-900 rounded-2xl overflow-hidden shadow-lg border border-slate-800">
        <div class="h-56 sm:h-72 w-full overflow-hidden relative">
            @if($project->image_url)
                <img src="{{ $project->image_url }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-r from-blue-900 to-slate-900 flex items-center justify-center">
                    <i class="fa-solid fa-city text-white/20 text-7xl"></i>
                </div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
        </div>

        <div class="absolute bottom-0 inset-x-0 p-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-600 text-white uppercase tracking-wider">
                        {{ $project->category ?? 'Property' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $project->status ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                        {{ $project->status ? 'Active' : 'Inactive' }}
                    </span>
                    @if($project->availability_document_url)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/90 text-white flex items-center">
                        <i class="fa-solid fa-map-pin mr-1"></i> Map Updated
                    </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $project->title }}</h1>
                <p class="text-sm text-slate-300 flex items-center mt-1">
                    <i class="fa-solid fa-location-dot text-rose-400 mr-1.5"></i>
                    {{ $project->city_name ?? ($project->city->name ?? 'Location') }}{{ $project->state_name ? ', '.$project->state_name : '' }}
                </p>
            </div>

            <div class="sm:text-right">
                <span class="text-xs uppercase tracking-wider text-slate-400 font-medium block">Starting Price</span>
                <span class="text-2xl font-black text-amber-400">{{ $project->price ?? 'Price on Request' }}</span>
                @if($project->rera_number)
                <p class="text-[11px] text-slate-400 font-mono mt-0.5">RERA: {{ $project->rera_number }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats & Action Row -->
    <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            @if($project->brochure_document_url)
            <a href="{{ $project->brochure_document_url }}" target="_blank" class="inline-flex items-center px-3.5 py-1.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-lg hover:bg-rose-100 transition">
                <i class="fa-solid fa-file-pdf mr-1.5 text-rose-600"></i> Download Brochure PDF
            </a>
            @endif
            @if($project->availability_document_url)
            <a href="{{ $project->availability_document_url }}" target="_blank" class="inline-flex items-center px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold rounded-lg hover:bg-blue-100 transition">
                <i class="fa-solid fa-map mr-1.5 text-blue-600"></i> View Current Map / Layout
            </a>
            @endif
        </div>
        <div class="flex items-center space-x-4 text-xs text-slate-500 font-medium">
            <span>Total Inquiries: <strong class="text-slate-800">{{ $project->contacts_count ?? 0 }}</strong></span>
            <span>•</span>
            <span>Confirmed Bookings: <strong class="text-emerald-600">{{ $project->bookings_count ?? 0 }}</strong></span>
        </div>
    </div>

    <!-- PROJECT LAYOUT MAP & PLOT AVAILABILITY SECTION -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 to-white">
            <div>
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Project Layout Map & Plot Availability</h2>
                        <p class="text-xs text-slate-500">Live map image/PDF rendered in the mobile app. Update anytime to broadcast newest availability.</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if($project->availability_document_url)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fa-solid fa-clock-rotate-left mr-1.5"></i>
                    Last Updated: {{ $project->map_updated_at_formatted ?? $project->updated_at->format('d M Y, h:i A') }}
                </span>
                @endif
                <button type="button" onclick="openMapModal()" class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-upload mr-1.5"></i> {{ $project->availability_document_url ? 'Upload New Map' : 'Upload Map' }}
                </button>
            </div>
        </div>

        <div class="p-6">
            @if($project->availability_document_url)
                @php
                    $isImage = $project->is_availability_document_image ?? preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $project->availability_document);
                @endphp

                @if($isImage)
                    <!-- Image Layout Map View -->
                    <div class="space-y-4">
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-950 flex items-center justify-center max-h-[550px]">
                            <img id="projectMapPreview" src="{{ $project->availability_document_url }}" alt="Project Map / Availability Layout" class="max-h-[550px] w-auto max-w-full object-contain mx-auto cursor-zoom-in transition transform hover:scale-[1.01]" onclick="openZoomModal(this.src)">
                            <div class="absolute bottom-3 right-3 flex items-center space-x-2 opacity-90 group-hover:opacity-100 transition">
                                <button type="button" onclick="openZoomModal('{{ $project->availability_document_url }}')" class="px-3 py-1.5 bg-slate-900/80 hover:bg-slate-900 text-white text-xs font-medium rounded-lg backdrop-blur shadow flex items-center">
                                    <i class="fa-solid fa-expand mr-1.5"></i> Fullscreen Zoom
                                </button>
                                <a href="{{ $project->availability_document_url }}" download class="px-3 py-1.5 bg-slate-900/80 hover:bg-slate-900 text-white text-xs font-medium rounded-lg backdrop-blur shadow flex items-center">
                                    <i class="fa-solid fa-download mr-1.5"></i> Download
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                            <span class="flex items-center">
                                <i class="fa-solid fa-circle-info text-blue-500 mr-2"></i>
                                Mobile App Synchronization: Mobile users see this map under the "Availability / Layout" tab in real time.
                            </span>
                            @if(auth()->user()?->isSuperAdmin())
                            <form action="{{ route('admin.projects.remove-map', $project->id) }}" method="POST" onsubmit="return confirm('Remove layout map file? Mobile app will show no layout map.')">
                                @csrf
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold hover:underline flex items-center">
                                    <i class="fa-solid fa-trash mr-1"></i> Remove Map
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- PDF Document View -->
                    <div class="p-8 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 text-center space-y-4">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Layout Map Document (PDF)</h3>
                            <p class="text-xs text-slate-500 mt-1">Availability document is uploaded as a PDF file.</p>
                        </div>
                        <div class="flex items-center justify-center space-x-3">
                            <a href="{{ $project->availability_document_url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i> Open PDF in New Tab
                            </a>
                            <a href="{{ $project->availability_document_url }}" download class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg transition">
                                <i class="fa-solid fa-download mr-1.5"></i> Download PDF
                            </a>
                            @if(auth()->user()?->isSuperAdmin())
                            <form action="{{ route('admin.projects.remove-map', $project->id) }}" method="POST" onsubmit="return confirm('Remove this layout map PDF?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-2 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-trash mr-1"></i> Remove
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <!-- Empty State: No Map Uploaded Yet -->
                <div class="p-10 border-2 border-dashed border-slate-200 rounded-xl text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">No Layout Map / Plot Availability Uploaded Yet</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Upload a master plan, layout map image (JPG, PNG, WEBP), or PDF document. Super Admin and Agents can view or update it at any time.</p>
                    </div>
                    <button type="button" onclick="openMapModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition mt-2">
                        <i class="fa-solid fa-upload mr-1.5"></i> Upload Layout Map Image
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Two Column Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Description & Leads -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-base font-bold text-slate-800 mb-3 flex items-center">
                    <i class="fa-solid fa-circle-info text-blue-600 mr-2"></i> Project Description & Specifications
                </h2>
                <div class="prose prose-sm max-w-none text-slate-700 text-sm leading-relaxed">
                    @php
                        $desc = $project->detailed_description ?: $project->description;
                    @endphp
                    @if($desc)
                        @if(strip_tags($desc) !== $desc)
                            {!! $desc !!}
                        @else
                            {!! nl2br(e($desc)) !!}
                        @endif
                    @else
                        <span class="text-slate-400 italic">No detailed description added.</span>
                    @endif
                </div>
            </div>

            <!-- Leads Interested in this Project -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-users text-blue-600 mr-2"></i> Inquired Leads
                        </h2>
                        <p class="text-xs text-slate-500">Customers interested in {{ $project->title }}</p>
                    </div>
                    <a href="{{ route('admin.leads.create', ['project_id' => $project->id]) }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-800">
                        + Add Lead
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="px-5 py-3">Lead</th>
                                <th class="px-4 py-3">Stage</th>
                                <th class="px-4 py-3">Budget</th>
                                <th class="px-4 py-3">Assigned To</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($leads as $lead)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-900">{{ $lead->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $lead->phone }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                        {{ $lead->lead_type === 'hot' ? 'bg-rose-100 text-rose-700' :
                                           ($lead->lead_type === 'warm' ? 'bg-amber-100 text-amber-700' :
                                           ($lead->lead_type === 'booking' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700')) }}">
                                        {{ $lead->lead_type_label ?? ($lead->lead_type ?? 'New') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-medium">{{ $lead->budget ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $lead->assignedUser->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('admin.leads.show', $lead->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                    No leads recorded for this project yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Offers & Project Info -->
        <div class="space-y-6">
            <!-- Promotional Offers -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-tag text-blue-600 mr-2"></i> Promotional Offers
                    </h2>
                    <a href="{{ route('admin.offers.create', ['project_id' => $project->id]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                        + Add Offer
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($offers as $offer)
                    <div class="p-3 rounded-lg border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 transition">
                        <div class="flex items-start justify-between">
                            <span class="text-xs font-bold text-slate-800">{{ $offer->title }}</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $offer->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $offer->status ? 'Active' : 'Expired' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $offer->description }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No active offers for this project.</p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Project Attributes -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-list-check text-blue-600 mr-2"></i> Quick Attributes
                </h2>
                <dl class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-500">Project ID</dt>
                        <dd class="font-mono font-bold text-slate-800">#{{ $project->id }}</dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-500">Category</dt>
                        <dd class="font-semibold text-slate-800">{{ $project->category ?? 'N/A' }}</dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-500">City / State</dt>
                        <dd class="font-semibold text-slate-800">{{ $project->city_name ?? ($project->city->name ?? '—') }}, {{ $project->state_name ?? ($project->state->name ?? '') }}</dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $project->status ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $project->status ? 'Active in App' : 'Hidden in App' }}
                            </span>
                        </dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-500">Created Date</dt>
                        <dd class="text-slate-700 font-medium">{{ $project->created_at ? $project->created_at->format('d M Y') : '—' }}</dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-500">Last Modified</dt>
                        <dd class="text-slate-700 font-medium">{{ $project->updated_at ? $project->updated_at->format('d M Y, h:i A') : '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Update Layout Map / Availability Document -->
<div id="mapModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-fadeIn">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Update Project Layout Map</h3>
                    <p class="text-[11px] text-slate-500">Upload new map / plot availability (JPG, PNG, WEBP, PDF)</p>
                </div>
            </div>
            <button type="button" onclick="closeMapModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('admin.projects.update-map', $project->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Select New Map File <span class="text-rose-500">*</span>
                </label>
                <div class="border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-xl p-6 text-center cursor-pointer bg-slate-50 hover:bg-blue-50/40 transition" onclick="document.getElementById('mapFileInput').click()">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-blue-500 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-700" id="mapFileName">Click to choose image or PDF</p>
                    <p class="text-[11px] text-slate-400 mt-1">Supports JPG, JPEG, PNG, WEBP, PDF up to 20MB</p>
                    <input type="file" name="availability_document" id="mapFileInput" class="hidden" accept=".jpg,.jpeg,.png,.webp,.pdf" required onchange="handleFileSelected(this)">
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-amber-800 text-xs flex items-start space-x-2">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-600 flex-shrink-0"></i>
                <span>
                    <strong>Instant App Sync:</strong> Uploading will replace the existing map document and touch the project update timestamp. Mobile users will immediately see the new layout map and "Last updated at" time.
                </span>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeMapModal()" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center">
                    <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Save & Broadcast Map
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Fullscreen Map Zoom View -->
<div id="zoomModal" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4" onclick="closeZoomModal()">
    <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeZoomModal()" class="absolute -top-10 right-0 text-white hover:text-slate-300 text-2xl font-bold">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="zoomedImage" src="" alt="Zoomed Layout Map" class="max-h-[85vh] max-w-full object-contain rounded-lg shadow-2xl">
    </div>
</div>

<script>
function openMapModal() {
    document.getElementById('mapModal').classList.remove('hidden');
}

function closeMapModal() {
    document.getElementById('mapModal').classList.add('hidden');
}

function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        document.getElementById('mapFileName').innerHTML = '<span class="text-blue-600 font-bold">' + file.name + '</span> (' + sizeMb + ' MB)';
    }
}

function openZoomModal(src) {
    document.getElementById('zoomedImage').src = src;
    document.getElementById('zoomModal').classList.remove('hidden');
}

function closeZoomModal() {
    document.getElementById('zoomModal').classList.add('hidden');
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMapModal();
        closeZoomModal();
    }
});
</script>
@endsection
