@extends('admin.layouts.app')

@section('title', 'Add New Lead')
@section('page-title', 'Create Customer Inquiry / Lead')
@section('page-subtitle', 'Register a new customer inquiry, connect with project, and assign an executive')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.leads.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Leads CRM</span>
        </a>
    </div>

    <form action="{{ route('admin.leads.store') }}" method="POST" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
        @csrf

        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-brand-600"></i>
            <span>Lead Information</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Customer Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Customer Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Vikas Patel"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Customer Mobile -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Phone (10 digits) <span class="text-rose-500">*</span></label>
                <input type="tel" name="mobile" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10" required placeholder="9876543210"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Interested Project -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Interested Project <span class="text-rose-500">*</span></label>
                <select name="project_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">-- Choose Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ (old('project_id', request('project_id')) == $p->id) ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->city->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Assigned Staff Member -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Assign To Executive / Agent</label>
                <select name="user_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="{{ Auth::id() }}">Assign to Myself ({{ Auth::user()->name }})</option>
                    @foreach($users as $u)
                        @if($u->id !== Auth::id())
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }}{{ $u->company_name ? ' - ' . $u->company_name : '' }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Initial Pipeline Status <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="follow_up" {{ old('status', 'follow_up') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                    <option value="visit_scheduled" {{ old('status') === 'visit_scheduled' ? 'selected' : '' }}>Site Visit Scheduled</option>
                    <option value="site_visit_done" {{ old('status') === 'site_visit_done' ? 'selected' : '' }}>Site Visit Completed</option>
                    <option value="booking_done" {{ old('status') === 'booking_done' ? 'selected' : '' }}>Booking Done</option>
                    <option value="interested" {{ old('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                    <option value="call_later" {{ old('status') === 'call_later' ? 'selected' : '' }}>Call Later</option>
                    <option value="call_not_connecting" {{ old('status') === 'call_not_connecting' ? 'selected' : '' }}>Call Not Connecting</option>
                    <option value="not_interested" {{ old('status') === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                </select>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Inquiry Date</label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
            </div>

        </div>

        <!-- Initial Remark -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Initial Discussion / Client Requirements</label>
            <textarea name="remark" rows="3" placeholder="e.g. Inquired about 1000 sq ft plot in Phase 2. Wants to visit on Sunday with family..."
                      class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('remark') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('admin.leads.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Save Lead</span>
            </button>
        </div>

    </form>
</div>
@endsection
