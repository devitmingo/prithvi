@extends('admin.layouts.app')

@section('title', 'Edit Lead')
@section('page-title', 'Edit Lead: ' . $lead->name)
@section('page-subtitle', 'Update customer contact info, project preferences, or reassignment')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.leads.show', $lead->id) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Lead Dossier</span>
        </a>
    </div>

    <form action="{{ route('admin.leads.update', $lead->id) }}" method="POST" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
        @csrf
        @method('PUT')

        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-brand-600"></i>
            <span>Update Lead Information</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Customer Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Customer Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $lead->name) }}" required
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Customer Mobile -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Phone <span class="text-rose-500">*</span></label>
                <input type="tel" name="mobile" value="{{ old('mobile', $lead->mobile) }}" pattern="[0-9]{10}" maxlength="10" required
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Interested Project -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Interested Project <span class="text-rose-500">*</span></label>
                <select name="project_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">-- Choose Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ (old('project_id', $lead->project_id) == $p->id) ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->city->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Assigned Staff Member -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Assigned Executive / Agent</label>
                <select name="user_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id', $lead->user_id) == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role }}{{ $u->company_name ? ' - ' . $u->company_name : '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Lead Status <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="follow_up" {{ old('status', $lead->status) === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                    <option value="visit_scheduled" {{ old('status', $lead->status) === 'visit_scheduled' ? 'selected' : '' }}>Site Visit Scheduled</option>
                    <option value="site_visit_done" {{ old('status', $lead->status) === 'site_visit_done' ? 'selected' : '' }}>Site Visit Completed</option>
                    <option value="booking_done" {{ old('status', $lead->status) === 'booking_done' ? 'selected' : '' }}>Booking Confirmed</option>
                    <option value="call_later" {{ old('status', $lead->status) === 'call_later' ? 'selected' : '' }}>Call Later</option>
                    <option value="call_not_connecting" {{ old('status', $lead->status) === 'call_not_connecting' ? 'selected' : '' }}>Call Not Connecting</option>
                    <option value="interested" {{ old('status', $lead->status) === 'interested' ? 'selected' : '' }}>Interested</option>
                    <option value="not_interested" {{ old('status', $lead->status) === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                </select>
            </div>

        </div>

        <!-- Remark -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Latest Remark / Update Notes</label>
            <textarea name="remark" rows="3" placeholder="Enter latest status update..."
                      class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('remark', $lead->remark) }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('admin.leads.show', $lead->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Update Lead</span>
            </button>
        </div>

    </form>
</div>
@endsection
