@extends('admin.layouts.app')

@section('title', 'Designations Master')
@section('page-title', 'Designations Master Records')
@section('page-subtitle', 'Manage corporate roles and organizational staff designations')

@section('content')
<div class="space-y-6" x-data="{ addModal: false, editModal: false, editId: null, editName: '', editStatus: 1 }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.masters.designations.index') }}" class="flex-1 max-w-md">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search designation title..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </form>

        <button type="button" @click="addModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm shadow-brand-600/30 transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add Designation</span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-4"># ID</th>
                        <th class="py-3.5 px-4">Designation Name</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($designations as $ds)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">{{ $ds->id }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">{{ $ds->name }}</td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.masters.designations.toggle', $ds->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $ds->status ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $ds->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="editId = {{ $ds->id }}; editName = '{{ addslashes($ds->name) }}'; editStatus = {{ $ds->status }}; editModal = true"
                                            class="p-1.5 text-slate-500 hover:text-amber-600 rounded-lg">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.masters.designations.destroy', $ds->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete designation {{ $ds->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 rounded-lg">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">No designations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($designations->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $designations->links() }}
            </div>
        @endif
    </div>

    <!-- Add Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="addModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Add New Designation</h3>
            <form action="{{ route('admin.masters.designations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Designation Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Senior Sales Manager" class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="editModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Edit Designation</h3>
            <form :action="'{{ url('admin/masters/designations') }}/' + editId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Designation Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editName" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" x-model="editStatus" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
