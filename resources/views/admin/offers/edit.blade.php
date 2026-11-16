@extends('admin.layouts.app')

@section('title', 'Edit Offer')
@section('page-title', 'Edit Promotional Offer')
@section('page-subtitle', 'Update dates, terms, or change project affiliation')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.offers.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Offers</span>
        </a>
    </div>

    <form action="{{ route('admin.offers.update', $offer->id) }}" method="POST" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
        @csrf
        @method('PUT')

        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-brand-600"></i>
            <span>Edit Offer Details</span>
        </h2>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Applicable Project <span class="text-rose-500">*</span></label>
            <select name="project_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ old('project_id', $offer->project_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Valid From Date <span class="text-rose-500">*</span></label>
                <input type="date" name="from_date" value="{{ old('from_date', $offer->getRawOriginal('from_date')) }}" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Valid To Date <span class="text-rose-500">*</span></label>
                <input type="date" name="to_date" value="{{ old('to_date', $offer->getRawOriginal('to_date')) }}" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold text-slate-700">Offer Description & Terms <span class="text-rose-500">*</span></label>
                <span class="inline-flex items-center text-[11px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md">
                    <i class="fa-solid fa-pen-nib mr-1"></i> Rich CKEditor
                </span>
            </div>
            <textarea name="description" rows="5" required data-ckeditor="true"
                      class="ckeditor-classic w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('description', $offer->description) }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('admin.offers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Update Offer</span>
            </button>
        </div>

    </form>
</div>
@endsection
