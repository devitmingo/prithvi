@extends('admin.layouts.app')

@section('title', 'Attendance Logs')
@section('page-title', 'Staff Attendance & Punches')
@section('page-subtitle', 'Real-time clock-in records, selfie photo verifications, and GPS location stamps')

@section('content')
<div class="space-y-6" x-data="{ imgModalOpen: false, modalImgSrc: '', modalImgTitle: '' }">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.attendances.index') }}" class="flex-1 flex flex-wrap items-center gap-2">
            
            <!-- Specific Date -->
            <input type="date" name="date" value="{{ request('date') }}" 
                   class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-brand-500">

            <!-- Month Filter -->
            <input type="month" name="month" value="{{ request('month') }}"
                   class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-brand-500">

            <!-- Staff User Filter -->
            <select name="user_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All Staff Members</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>

            <!-- Agent Filter for Superadmin -->
            @if($isSuperAdmin && $agents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">All Agencies</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['date', 'month', 'user_id', 'agent_id']))
                <a href="{{ route('admin.attendances.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.salary.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold shadow-xs transition">
                <i class="fa-solid fa-calculator"></i>
                <span>Salary Calculation</span>
            </a>

            <a href="{{ route('admin.attendances.monthly') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-colors">
                <i class="fa-solid fa-calendar-days text-purple-600"></i>
                <span>Monthly Sheet</span>
            </a>

            <a href="{{ route('admin.attendances.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Manual Entry</span>
            </a>
        </div>
    </div>

    <!-- Attendance Punches Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-4">Punch Selfie</th>
                        <th class="py-3.5 px-4">Staff Member</th>
                        <th class="py-3.5 px-4">Designation / Agency</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Time</th>
                        <th class="py-3.5 px-4">Location / GPS</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            <!-- Selfie Photo -->
                            <td class="py-3 px-4">
                                @if($att->image)
                                    <img src="{{ $att->image_url }}" alt="Selfie" 
                                         @click="modalImgSrc = '{{ $att->image_url }}'; modalImgTitle = 'Attendance Punch Selfie: {{ $att->user->name ?? 'User' }} ({{ $att->date }} {{ $att->time }})'; imgModalOpen = true"
                                         class="w-11 h-11 rounded-xl object-cover border border-slate-200 shadow-2xs cursor-pointer hover:opacity-85 transition-opacity"
                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($att->user->name ?? 'User') }}&color=059669&background=d1fae5';">
                                @else
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center font-bold text-xs border border-slate-200">
                                        <i class="fa-solid fa-camera"></i>
                                    </div>
                                @endif
                            </td>

                            <!-- Staff Member -->
                            <td class="py-3 px-4">
                                @if($att->user)
                                    <a href="{{ route('admin.users.show', $att->user_id) }}" class="font-bold text-slate-900 hover:text-brand-600 block text-sm">
                                        {{ $att->user->name }}
                                    </a>
                                    <span class="text-[11px] text-slate-500">{{ $att->user->mobile }}</span>
                                @else
                                    <span class="text-slate-400">User #{{ $att->user_id }}</span>
                                @endif
                            </td>

                            <!-- Designation & Agency -->
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800 block">
                                    {{ $att->user->designation->name ?? $att->user->role ?? 'Field Executive' }}
                                </span>
                                @if($att->user && $att->user->company)
                                    <span class="text-[10px] text-blue-700 block font-medium">
                                        <i class="fa-solid fa-user-tie text-[9px] text-blue-500"></i> Leader: {{ $att->user->company->name }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400">
                                        {{ $att->user->company_name ?? 'Direct Agency' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Date -->
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ date('d M Y', strtotime($att->date)) }}
                            </td>

                            <!-- Time -->
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800">
                                    <i class="fa-solid fa-clock text-[10px] text-brand-600"></i>
                                    <span>{{ date('h:i A', strtotime($att->time)) }}</span>
                                </span>
                            </td>

                            <!-- GPS Location -->
                            <td class="py-3 px-4">
                                @if($att->latitude && $att->longitude)
                                    <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-brand-600 hover:bg-brand-50 border border-brand-200 transition-colors">
                                        <i class="fa-solid fa-location-crosshairs text-rose-500"></i>
                                        <span>{{ number_format($att->latitude, 4) }}, {{ number_format($att->longitude, 4) }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">— No GPS —</span>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="py-3 px-4 text-right">
                                <button type="button" 
                                        onclick="confirmDeleteAttendance({{ $att->id }}, '{{ addslashes($att->user->name ?? 'Staff') }}', '{{ $att->date }}')"
                                        class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center transition shadow-2xs ml-auto" 
                                        title="Delete Punch">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                                <form id="delete-att-{{ $att->id }}" action="{{ route('admin.attendances.destroy', $att->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-fingerprint text-2xl text-slate-300 mb-2 block"></i>
                                No attendance punches found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

    <!-- Image Enlarge Modal -->
    <div x-show="imgModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs">
        <div @click.away="imgModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-4 shadow-2xl space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h4 class="text-xs font-bold text-slate-900" x-text="modalImgTitle"></h4>
                <button type="button" @click="imgModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            <div class="max-h-[75vh] overflow-auto flex items-center justify-center bg-slate-950/5 rounded-xl p-2">
                <img :src="modalImgSrc" class="max-h-[70vh] object-contain rounded-lg">
            </div>
        </div>
    </div>

</div>

<script>
function confirmDeleteAttendance(id, name, date) {
    Swal.fire({
        title: 'Delete Attendance Punch?',
        html: 'Are you sure you want to delete punch record for <b>' + name + '</b> on <b>' + date + '</b>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-trash mr-1.5"></i> Yes, Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-200'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-att-' + id).submit();
        }
    });
}
</script>
@endsection
