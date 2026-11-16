@extends('admin.layouts.app')

@section('title', 'Add Project')
@section('page-title', 'Create Real Estate Project')
@section('page-subtitle', 'Add property specifications, pricing, RERA compliance details, and marketing collateral')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Projects Inventory</span>
        </a>
    </div>

    <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. Basic Project Identity -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-building text-brand-600"></i>
                <span>1. Project Details & Status</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Urban Estate or Galaxy Greens"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">RERA Registration No</label>
                    <input type="text" name="rera_no" value="{{ old('rera_no') }}" placeholder="e.g. PCGRERA240123001595"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Starting Price (₹ INR)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price') }}" placeholder="e.g. 2500000"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project Status <span class="text-rose-500">*</span></label>
                    <select name="project_status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="Ongoing" {{ old('project_status') === 'Ongoing' ? 'selected' : '' }}>Ongoing Construction</option>
                        <option value="Upcoming" {{ old('project_status') === 'Upcoming' ? 'selected' : '' }}>Upcoming Launch</option>
                        <option value="Completed" {{ old('project_status') === 'Completed' ? 'selected' : '' }}>Completed / Ready to Move</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Listing Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active / Visible</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive / Hidden</option>
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
                            <option value="{{ $st->id }}" {{ old('state_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">City <span class="text-rose-500">*</span></label>
                    <select name="city_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select City --</option>
                        @foreach($cities as $ct)
                            <option value="{{ $ct->id }}" {{ old('city_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">GPS Latitude</label>
                    <input type="text" name="latitude" value="{{ old('latitude') }}" placeholder="e.g. 21.2514"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">GPS Longitude</label>
                    <input type="text" name="longitude" value="{{ old('longitude') }}" placeholder="e.g. 81.6296"
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
                    <input type="text" name="short_description" value="{{ old('short_description') }}" placeholder="e.g. Town & Country Approved RERA Registered Project near AIIMS"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Detailed Description & Amenities</label>
                        <span class="inline-flex items-center text-[11px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
                            <i class="fa-solid fa-pen-nib mr-1"></i> Rich CKEditor
                        </span>
                    </div>
                    <textarea name="detailed_description" rows="6" data-ckeditor="true" placeholder="Plot sizes, Modern amenities, Connectivity highlights, Payment plans..."
                              class="ckeditor-classic w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('detailed_description') }}</textarea>
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
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Main Project Banner Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="text-[10px] text-slate-400 mt-1">High resolution JPG/PNG (Up to 5MB)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Project Brochure (PDF)</label>
                    <input type="file" name="document" accept=".pdf,.doc,.docx" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="text-[10px] text-slate-400 mt-1">Full marketing brochure (Up to 10MB)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Availability / Floor Plan Sheet</label>
                    <input type="file" name="availability_document" accept=".pdf,.xlsx,.xls,.png,.jpg" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="text-[10px] text-slate-400 mt-1">Current inventory chart or layout</p>
                </div>

            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.projects.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Publish Project</span>
            </button>
        </div>

    </form>
</div>
@endsection
