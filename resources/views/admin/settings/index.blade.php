@extends('admin.layouts.app')

@section('title', 'Application Settings')
@section('page-title', 'Global Application Settings')
@section('page-subtitle', 'Manage contact information, branding parameters, version releases, and legal policies')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Company & Contact Information -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-building-circle-check text-brand-600"></i>
                <span>1. Enterprise & Contact Identity</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Company / App Name</label>
                    <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'Prithvi Buildcon' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Support Email</label>
                    <input type="email" name="email" value="{{ $settings['email'] ?? 'support@prithvi.com' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Support Mobile Phone</label>
                    <input type="text" name="mobile" value="{{ $settings['mobile'] ?? '1234567890' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Official Website URL</label>
                    <input type="url" name="web_url" value="{{ $settings['web_url'] ?? 'https://prithvibuildcon.com/' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Registered Office Address</label>
                    <textarea name="address" rows="2" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ $settings['address'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Releases & Versions -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-code-branch text-brand-600"></i>
                <span>2. Release Versions & Social Handles</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile App Version</label>
                    <input type="text" name="app_version" value="{{ $settings['app_version'] ?? '1.0.0' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Web Portal Version</label>
                    <input type="text" name="web_version" value="{{ $settings['web_version'] ?? '2.0.0' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Facebook Page URL</label>
                    <input type="url" name="facebook_url" value="{{ $settings['facebook_url'] ?? '' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Instagram URL</label>
                    <input type="url" name="instagram_url" value="{{ $settings['instagram_url'] ?? '' }}" 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                </div>
            </div>
        </div>

        <!-- 3. Legal Documents / CMS -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-brand-600"></i>
                <span>3. Terms, Policies & Disclaimers</span>
            </h2>

            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Terms & Conditions</label>
                        <span class="inline-flex items-center text-[11px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
                            <i class="fa-solid fa-pen-nib mr-1"></i> Rich CKEditor
                        </span>
                    </div>
                    <textarea name="terms_conditions" rows="5" data-ckeditor="true" class="ckeditor-classic w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ $settings['terms_conditions'] ?? '' }}</textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">Privacy Policy</label>
                        <span class="inline-flex items-center text-[11px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
                            <i class="fa-solid fa-pen-nib mr-1"></i> Rich CKEditor
                        </span>
                    </div>
                    <textarea name="privacy_policy" rows="5" data-ckeditor="true" class="ckeditor-classic w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ $settings['privacy_policy'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Application Settings</span>
            </button>
        </div>

    </form>
</div>
@endsection
