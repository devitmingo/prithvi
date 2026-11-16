@extends('admin.layouts.app')

@section('title', 'Edit Project')
@section('page-title', 'Edit Project: ' . $project->name)
@section('page-subtitle', 'Update specifications, media assets, pricing, and project status')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.projects.show', $project->id) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Project Showcase</span>
        </a>
    </div>

    <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Basic Project Identity -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-building text-brand-600"></i>
                <span>1. Project Details & Status</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">RERA Registration No</label>
                    <input type="text" name="rera_no" value="{{ old('rera_no', $project->rera_no) }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Starting Price (₹ INR)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $project->price) }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project Status <span class="text-rose-500">*</span></label>
                    <select name="project_status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="Ongoing" {{ old('project_status', $project->project_status) === 'Ongoing' ? 'selected' : '' }}>Ongoing Construction</option>
                        <option value="Upcoming" {{ old('project_status', $project->project_status) === 'Upcoming' ? 'selected' : '' }}>Upcoming Launch</option>
                        <option value="Completed" {{ old('project_status', $project->project_status) === 'Completed' ? 'selected' : '' }}>Completed / Ready to Move</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Listing Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="1" {{ old('status', $project->status) == 1 ? 'selected' : '' }}>Active / Visible</option>
                        <option value="0" {{ old('status', $project->status) == 0 ? 'selected' : '' }}>Inactive / Hidden</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- 2. Location & Geo-Coordinates -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-brand-600"></i>
                <span>2. Location & Coordinates</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">State <span class="text-rose-500">*</span></label>
                    <select name="state_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select State --</option>
                        @foreach($states as $st)
                            <option value="{{ $st->id }}" {{ old('state_id', $project->state_id) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">City <span class="text-rose-500">*</span></label>
                    <select name="city_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select City --</option>
                        @foreach($cities as $ct)
                            <option value="{{ $ct->id }}" {{ old('city_id', $project->city_id) == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">GPS Latitude</label>
                    <input type="text" name="latitude" value="{{ old('latitude', $project->latitude) }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">GPS Longitude</label>
                    <input type="text" name="longitude" value="{{ old('longitude', $project->longitude) }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

            </div>
        </div>

        <!-- 3. Descriptions & Amenities -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-align-left text-brand-600"></i>
                <span>3. Descriptions & Marketing Copy</span>
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Short Summary / Tagline</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $project->short_description) }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Detailed Description & Amenities</label>
                        <span class="inline-flex items-center text-[11px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
                            <i class="fa-solid fa-pen-nib mr-1"></i> Rich CKEditor
                        </span>
                    </div>
                    <textarea name="detailed_description" rows="6" data-ckeditor="true"
                              class="ckeditor-classic w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('detailed_description', $project->detailed_description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. Marketing Media & Documents -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-images text-brand-600"></i>
                <span>4. Media & Collateral Uploads</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Project Banner Image</label>
                    @if($project->image)
                        <img src="{{ $project->image_url }}" alt="Current Image" class="w-full h-32 object-cover rounded-xl border border-slate-200 shadow-2xs mb-2">
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Project Brochure (PDF)</label>
                    @if($project->document)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-file-pdf text-rose-500"></i> Brochure Attached
                            </span>
                            <a href="{{ $project->document_url }}" target="_blank" class="text-xs text-brand-600 font-bold hover:underline">View</a>
                        </div>
                    @endif
                    <input type="file" name="document" accept=".pdf,.doc,.docx" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Availability Sheet</label>
                    @if($project->availability_document)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-table-list text-emerald-500"></i> Sheet Attached
                            </span>
                            <a href="{{ $project->availability_document_url }}" target="_blank" class="text-xs text-brand-600 font-bold hover:underline">View</a>
                        </div>
                    @endif
                    <input type="file" name="availability_document" accept=".pdf,.xlsx,.xls,.png,.jpg" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.projects.show', $project->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Update Project</span>
            </button>
        </div>

    </form>
</div>
@endsection
