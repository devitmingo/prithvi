@extends('admin.layouts.app')

@section('title', 'Holiday Master')
@section('page-title', 'Holiday Master')
@section('page-subtitle', 'Manage company and public holidays — All active holidays are automatically marked as Present in staff salary calculations')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false, editModalOpen: false, editId: null, editName: '', editDate: '', editDesc: '', editStatus: true }">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.masters.holidays.index') }}" class="flex-1 flex flex-wrap items-center gap-2.5">
            <!-- Year Filter -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Year:</span>
                <select name="year" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 font-bold">
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search holiday name..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['search', 'year']))
                <a href="{{ route('admin.masters.holidays.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.salary.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                <i class="fa-solid fa-calculator text-blue-600"></i>
                <span>Salary Calculation</span>
            </a>
            <button type="button" @click="createModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-semibold shadow-sm transition whitespace-nowrap">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Holiday</span>
            </button>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 text-blue-900 flex items-start gap-3 text-xs leading-relaxed">
        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 text-sm font-bold mt-0.5">
            <i class="fa-solid fa-gift"></i>
        </div>
        <div>
            <span class="font-bold block text-sm">Automated Salary Integration:</span>
            <span>All active holidays recorded here are automatically treated as <strong>Paid Holidays (Marked as Present)</strong> in staff salary and payroll calculations between selected From Date and To Date.</span>
        </div>
    </div>

    <!-- Holidays Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-4">Holiday Name</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Day of Week</th>
                        <th class="py-3.5 px-4">Description</th>
                        <th class="py-3.5 px-4">Payroll Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($holidays as $h)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
                                    <span>{{ $h->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ $h->date->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">
                                <span class="px-2 py-0.5 rounded-md text-[11px] {{ $h->date->format('l') === 'Sunday' ? 'bg-amber-100 text-amber-800 font-bold' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $h->date->format('l') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate" title="{{ $h->description }}">
                                {{ $h->description ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($h->status)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-circle-check text-[9px] text-emerald-600"></i>
                                        <span>Active (Marked Present)</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Toggle Status Form -->
                                    <form action="{{ route('admin.masters.holidays.toggle', $h->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="w-7 h-7 rounded-lg {{ $h->status ? 'bg-amber-50 hover:bg-amber-600 text-amber-600' : 'bg-emerald-50 hover:bg-emerald-600 text-emerald-600' }} hover:text-white flex items-center justify-center transition shadow-2xs" 
                                                title="{{ $h->status ? 'Deactivate Holiday' : 'Activate Holiday' }}">
                                            <i class="fa-solid {{ $h->status ? 'fa-pause' : 'fa-play' }} text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Edit Trigger -->
                                    <button type="button" 
                                            @click="editId = {{ $h->id }}; editName = '{{ addslashes($h->name) }}'; editDate = '{{ $h->date->format('Y-m-d') }}'; editDesc = '{{ addslashes($h->description ?? '') }}'; editStatus = {{ $h->status ? 'true' : 'false' }}; editModalOpen = true"
                                            class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                            title="Edit Holiday">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>

                                    <!-- Delete Button with SweetAlert2 -->
                                    <button type="button" 
                                            onclick="confirmDeleteHoliday({{ $h->id }}, '{{ addslashes($h->name) }}')"
                                            class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                            title="Delete Holiday">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                    <form id="delete-holiday-form-{{ $h->id }}" action="{{ route('admin.masters.holidays.destroy', $h->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl text-slate-300 mb-2 block"></i>
                                No holidays found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($holidays->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $holidays->links() }}
            </div>
        @endif
    </div>

    <!-- Modal 1: Create Holiday -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="createModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Add New Holiday</h3>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('admin.masters.holidays.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Holiday Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Independence Day, Diwali, Eid..." 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Holiday Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description / Notes (Optional)</label>
                    <textarea name="description" rows="2" placeholder="e.g. Mandatory public holiday, office closed..." 
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="status" id="createStatus" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="createStatus" class="text-xs font-medium text-slate-700">Active (Auto-credit as Present in Salary)</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">
                        Save Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Edit Holiday -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="editModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Edit Holiday</h3>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/masters/holidays') }}/' + editId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Holiday Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editName" required 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Holiday Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" x-model="editDate" required 
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description / Notes (Optional)</label>
                    <textarea name="description" x-model="editDesc" rows="2" 
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="status" id="editStatus" value="1" :checked="editStatus" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="editStatus" class="text-xs font-medium text-slate-700">Active (Auto-credit as Present in Salary)</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">
                        Update Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function confirmDeleteHoliday(id, name) {
    Swal.fire({
        title: 'Delete Holiday?',
        html: 'Are you sure you want to delete <b>' + name + '</b>?<br><span class="text-xs text-slate-500 mt-2 block">This date will no longer be credited as a paid holiday in payroll calculations.</span>',
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
            document.getElementById('delete-holiday-form-' + id).submit();
        }
    });
}
</script>
@endsection
