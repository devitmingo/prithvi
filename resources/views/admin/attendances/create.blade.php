@extends('admin.layouts.app')

@section('title', 'Manual Attendance Entry')
@section('page-title', 'Manual Attendance Entry')
@section('page-subtitle', 'Add or correct an employee attendance punch record with date, time, and coordinates')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.attendances.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Punch Logs</span>
        </a>
    </div>

    <form action="{{ route('admin.attendances.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
        @csrf

        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-fingerprint text-brand-600"></i>
            <span>Attendance Punch Details</span>
        </h2>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Staff Member <span class="text-rose-500">*</span></label>
            <select name="user_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">-- Choose Employee --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->role }}{{ $u->company_name ? ' - ' . $u->company_name : '' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Date <span class="text-rose-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Time <span class="text-rose-500">*</span></label>
                <input type="time" name="time" value="{{ old('time', date('H:i')) }}" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">GPS Latitude (Optional)</label>
                <input type="text" name="latitude" value="{{ old('latitude', '21.2514') }}" placeholder="21.2514" class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">GPS Longitude (Optional)</label>
                <input type="text" name="longitude" value="{{ old('longitude', '81.6296') }}" placeholder="81.6296" class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Selfie Photo / Ground Proof (Optional)</label>
            <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('admin.attendances.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Save Punch Record</span>
            </button>
        </div>

    </form>
</div>
@endsection
