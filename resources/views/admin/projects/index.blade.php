@extends('admin.layouts.app')

@section('title', 'Projects & Inventory')
@section('page-title', 'Real Estate Projects')
@section('page-subtitle', 'Manage residential & commercial property inventory, RERA approvals, and marketing documents')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.projects.index') }}" class="flex-1 flex flex-wrap items-center gap-2">
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search project name, RERA no, location..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Status Filter -->
            <select name="project_status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All Statuses</option>
                <option value="Ongoing" {{ request('project_status') === 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="Upcoming" {{ request('project_status') === 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="Completed" {{ request('project_status') === 'Completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <!-- State Filter -->
            <select name="state_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All States</option>
                @foreach($states as $st)
                    <option value="{{ $st->id }}" {{ request('state_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'project_status', 'state_id']))
                <a href="{{ route('admin.projects.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm shadow-brand-600/30 transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add New Project</span>
        </a>
    </div>

    <!-- Projects Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all overflow-hidden flex flex-col group">
                
                <!-- Project Image & Badges -->
                <div class="h-48 bg-slate-900 relative overflow-hidden">
                    @if($project->image)
                        <img src="{{ $project->image_url }}" alt="{{ $project->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full bg-gradient-to-tr from-slate-800 to-indigo-900 flex items-center justify-center text-slate-500">
                            <i class="fa-solid fa-city text-5xl"></i>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                    <!-- Top Status Badges -->
                    <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wide backdrop-blur-md shadow-xs
                            {{ $project->project_status === 'Ongoing' ? 'bg-blue-600/90 text-white' : '' }}
                            {{ $project->project_status === 'Upcoming' ? 'bg-amber-600/90 text-white' : '' }}
                            {{ $project->project_status === 'Completed' ? 'bg-emerald-600/90 text-white' : '' }}">
                            {{ $project->project_status }}
                        </span>

                        <form action="{{ route('admin.projects.toggle', $project->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[10px] font-bold backdrop-blur-md transition-colors
                                {{ $project->status ? 'bg-emerald-500/90 text-white hover:bg-emerald-600' : 'bg-slate-700/90 text-slate-300 hover:bg-slate-800' }}" title="Click to toggle active status">
                                <i class="fa-solid fa-circle text-[8px] mr-1"></i>
                                {{ $project->status ? 'Active' : 'Hidden' }}
                            </button>
                        </form>
                    </div>

                    <!-- Bottom Overlay Info -->
                    <div class="absolute bottom-3 left-3 right-3 text-white">
                        <h3 class="text-base font-extrabold truncate">{{ $project->name }}</h3>
                        <p class="text-[11px] text-slate-300 flex items-center gap-1.5 mt-0.5">
                            <i class="fa-solid fa-location-dot text-rose-400"></i>
                            <span>{{ $project->city->name ?? '' }}, {{ $project->state->name ?? '' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Body Content -->
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400 font-medium">RERA Registration:</span>
                            <span class="font-bold text-slate-800 font-mono text-[11px]">{{ $project->rera_no ?? 'Under Process' }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400 font-medium">Pricing / Units:</span>
                            <span class="font-extrabold text-brand-600 text-sm">
                                {{ $project->price ? '₹ ' . number_format($project->price, 2) : 'Starting on request' }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 line-clamp-2 mt-2">
                            {{ $project->short_description ?? 'Town & Country Approved and RERA registered premium project.' }}
                        </p>
                    </div>

                    <!-- Meta Tags & Docs -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">
                            <i class="fa-solid fa-users text-[10px]"></i>
                            <span>{{ $project->contacts_count }} Inquiries</span>
                        </span>

                        <div class="flex items-center gap-2">
                            @if($project->document)
                                <a href="{{ $project->document_url }}" target="_blank" class="p-1.5 rounded-lg text-slate-600 hover:text-brand-600 hover:bg-slate-100" title="Brochure PDF">
                                    <i class="fa-solid fa-file-pdf text-rose-500 text-sm"></i>
                                </a>
                            @endif
                            @if($project->availability_document)
                                <a href="{{ $project->availability_document_url }}" target="_blank" class="p-1.5 rounded-lg text-slate-600 hover:text-brand-600 hover:bg-slate-100" title="Availability Sheet">
                                    <i class="fa-solid fa-table-list text-emerald-600 text-sm"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-100">
                        <a href="{{ route('admin.projects.show', $project->id) }}" class="col-span-1 text-center py-2 px-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-2xs">
                            Details
                        </a>
                        <a href="{{ route('admin.projects.edit', $project->id) }}" class="col-span-1 text-center py-2 px-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold">
                            Edit
                        </a>
                        <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" class="col-span-1" onsubmit="return confirm('Delete this project and all associated media files?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2 px-2 rounded-xl border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-semibold">
                                Delete
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-slate-200 p-8">
                <i class="fa-solid fa-city text-4xl text-slate-300 mb-3 block"></i>
                <h3 class="text-sm font-bold text-slate-800">No projects found</h3>
                <p class="text-xs text-slate-500 mt-1 mb-4">Get started by creating your first residential or commercial project.</p>
                <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-xs font-bold">
                    <i class="fa-solid fa-plus"></i> Add Project
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
            {{ $projects->links() }}
        </div>
    @endif

</div>
@endsection
