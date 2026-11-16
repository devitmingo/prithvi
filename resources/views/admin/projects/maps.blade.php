@extends('admin.layouts.app')

@section('title', 'Project Layout Maps & Availability')
@section('page-title', 'Project Map Updates')
@section('page-subtitle', 'Manage live master plans and plot availability maps broadcasted to mobile app users')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-white flex items-center justify-center text-lg shadow-sm shadow-emerald-500/30">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Project Layout Maps & Plot Availability</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Mobile app users see these layout maps under the Availability tab in real-time.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.projects.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Projects Inventory</span>
            </a>
            <button type="button" onclick="openGlobalMapModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Quick Update Map</span>
            </button>
        </div>
    </div>

    <!-- Summary KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Projects -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Projects</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalProjects }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Active catalog inventory</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-city"></i>
            </div>
        </div>

        <!-- Maps Uploaded -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Maps Live In App</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $withMapCount }}</h3>
                <p class="text-[11px] text-emerald-700/80 mt-0.5">Synced with mobile users</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <!-- Missing Maps -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-500">Missing Maps</p>
                <h3 class="text-2xl font-black text-amber-500 mt-1">{{ $missingMapCount }}</h3>
                <p class="text-[11px] text-amber-600/80 mt-0.5">Need layout attachment</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <!-- Latest Sync Broadcast -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Latest Broadcast</p>
                <h3 class="text-sm font-extrabold text-slate-800 mt-1 truncate max-w-[130px]">
                    {{ $latestUpdated ? $latestUpdated->name : 'N/A' }}
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $latestUpdated && $latestUpdated->map_updated_at_formatted ? $latestUpdated->map_updated_at_formatted : ($latestUpdated ? $latestUpdated->updated_at->diffForHumans() : 'No updates yet') }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-tower-broadcast animate-pulse"></i>
            </div>
        </div>

    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.projects.maps') }}" class="flex-1 w-full flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search project by name or RERA number..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50/50">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="filter" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 py-2 px-3 bg-slate-50/50 focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Projects ({{ $totalProjects }})</option>
                    <option value="has_map" {{ request('filter') == 'has_map' ? 'selected' : '' }}>Live Maps Only ({{ $withMapCount }})</option>
                    <option value="no_map" {{ request('filter') == 'no_map' ? 'selected' : '' }}>Missing Maps ({{ $missingMapCount }})</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if(request('search') || request('filter'))
                    <a href="{{ route('admin.projects.maps') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 text-xs font-medium" title="Clear filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="text-xs text-slate-500 font-medium whitespace-nowrap">
            Showing <strong class="text-slate-800">{{ $projects->count() }}</strong> projects
        </div>

    </div>

    <!-- Projects Layout Maps Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
            @php
                $hasMap = !empty($project->availability_document);
                $isImage = $project->is_availability_document_image;
                $isPdf = $project->is_availability_document_pdf;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-emerald-300 transition-all group">
                
                <!-- Card Header -->
                <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $hasMap ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                <i class="fa-solid {{ $hasMap ? 'fa-circle-check text-emerald-500' : 'fa-triangle-exclamation text-amber-500' }}"></i>
                                {{ $hasMap ? 'Map Active & Live' : 'Map Missing' }}
                            </span>
                            <h3 class="text-base font-extrabold text-slate-900 truncate mt-1.5 group-hover:text-emerald-700 transition-colors">
                                {{ $project->name }}
                            </h3>
                            <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                                <i class="fa-solid fa-location-dot text-slate-400 text-[11px]"></i>
                                {{ $project->city?->name ?? 'Raipur' }}, {{ $project->state?->name ?? 'Chhattisgarh' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.projects.show', $project->id) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs shrink-0 transition" title="View Project Details">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                </div>

                <!-- Map Preview Slot -->
                <div class="p-5 flex-1 flex flex-col justify-center">
                    @if($hasMap)
                        @if($isImage)
                            <!-- Image Layout Map Preview -->
                            <div class="relative group/img rounded-xl overflow-hidden border border-slate-200 bg-slate-950 h-52 flex items-center justify-center">
                                <img src="{{ $project->availability_document_url }}" alt="{{ $project->name }} Map" class="w-full h-full object-cover group-hover/img:scale-105 transition duration-300">
                                
                                <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button type="button" onclick="openZoomModal('{{ $project->availability_document_url }}')" class="px-3 py-1.5 bg-white text-slate-900 rounded-lg text-xs font-bold shadow hover:bg-slate-100 transition flex items-center gap-1">
                                        <i class="fa-solid fa-expand"></i> Zoom
                                    </button>
                                    <a href="{{ $project->availability_document_url }}" download class="px-3 py-1.5 bg-slate-900/80 text-white rounded-lg text-xs font-bold shadow hover:bg-slate-900 transition flex items-center gap-1">
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>
                                </div>

                                <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-900/80 text-white backdrop-blur-xs flex items-center gap-1">
                                    <i class="fa-solid fa-image text-emerald-400"></i> JPG / PNG Map
                                </span>
                            </div>
                        @else
                            <!-- PDF Map Preview -->
                            <div class="rounded-xl border border-slate-200 bg-rose-50/50 p-6 h-52 flex flex-col items-center justify-center text-center">
                                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl shadow-xs mb-3">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <h4 class="text-xs font-bold text-slate-800">Master Layout Map (PDF)</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Uploaded document file</p>
                                <div class="flex items-center gap-2 mt-3">
                                    <a href="{{ $project->availability_document_url }}" target="_blank" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-xs transition flex items-center gap-1">
                                        <i class="fa-solid fa-eye"></i> View PDF
                                    </a>
                                    <a href="{{ $project->availability_document_url }}" download class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-lg transition">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                                Updated: <strong>{{ $project->map_updated_at_formatted ?? $project->updated_at->format('d M Y, h:i A') }}</strong>
                            </span>
                            @if(auth()->user()?->isSuperAdmin())
                                <form action="{{ route('admin.projects.remove-map', $project->id) }}" method="POST" onsubmit="return confirm('Remove layout map for {{ $project->name }}?')">
                                    @csrf
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold hover:underline">
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </div>

                    @else
                        <!-- No Map Placeholder -->
                        <div class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/70 p-6 h-52 flex flex-col items-center justify-center text-center">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl mb-2">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800">No Layout Map Uploaded</h4>
                            <p class="text-[11px] text-slate-500 mt-1 max-w-[200px]">Mobile users currently see a placeholder for this project.</p>
                            <button type="button" onclick="openGlobalMapModal({{ $project->id }})" class="mt-3 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center gap-1.5">
                                <i class="fa-solid fa-upload"></i> Upload Map
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Card Footer Actions -->
                <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-2">
                    <span class="text-[11px] text-slate-400 font-medium">
                        RERA: {{ $project->rera_no ?: 'Registered' }}
                    </span>
                    <button type="button" onclick="openGlobalMapModal({{ $project->id }})" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs font-bold shadow-xs transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up text-amber-400"></i>
                        <span>{{ $hasMap ? 'Replace Map' : 'Upload Map' }}</span>
                    </button>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 border border-slate-200 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-map"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No Projects Found</h3>
                <p class="text-xs text-slate-500 mt-1">Try adjusting your search or filter keywords.</p>
            </div>
        @endforelse
    </div>

</div>

<!-- Fullscreen Zoom Modal -->
<div id="zoomModal" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4" onclick="closeZoomModal()">
    <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeZoomModal()" class="absolute -top-10 right-0 text-white hover:text-slate-300 text-2xl font-bold">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="zoomedImage" src="" alt="Zoomed Layout Map" class="max-h-[85vh] max-w-full object-contain rounded-lg shadow-2xl">
    </div>
</div>
@endsection

@section('scripts')
<script>
function openZoomModal(src) {
    document.getElementById('zoomedImage').src = src;
    document.getElementById('zoomModal').classList.remove('hidden');
}

function closeZoomModal() {
    document.getElementById('zoomModal').classList.add('hidden');
}
</script>
@endsection