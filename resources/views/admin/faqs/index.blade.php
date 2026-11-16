@extends('admin.layouts.app')

@section('title', 'FAQs Manager')
@section('page-title', 'Frequently Asked Questions')
@section('page-subtitle', 'Manage help center content and support answers displayed in mobile application')

@section('content')
<div class="space-y-6" x-data="{ addModal: false, editModal: false, editId: null, editQ: '', editA: '', editSeq: 1, editStatus: 1 }">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">App Knowledgebase & FAQs</h3>
            <p class="text-xs text-slate-500">{{ $faqs->count() }} active articles</p>
        </div>

        <button type="button" @click="addModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm shadow-brand-600/30 transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add FAQ</span>
        </button>
    </div>

    <!-- FAQs Accordion / List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse($faqs as $faq)
                <div class="p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                    
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                Seq: {{ $faq->sequence }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $faq->status ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $faq->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $faq->question }}</h4>
                        <div class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ $faq->answer }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <form action="{{ route('admin.faqs.toggle', $faq->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $faq->status ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' : 'border-slate-200 text-slate-500 hover:bg-slate-100' }}">
                                {{ $faq->status ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <button type="button" @click="editId = {{ $faq->id }}; editQ = '{{ addslashes($faq->question) }}'; editA = '{{ addslashes($faq->answer) }}'; editSeq = {{ $faq->sequence ?? 1 }}; editStatus = {{ $faq->status }}; editModal = true"
                                class="p-1.5 text-slate-500 hover:text-amber-600 rounded-lg">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>

                        <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this FAQ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 rounded-lg">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    No FAQs registered yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Add FAQ Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="addModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Add New FAQ</h3>
            <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Question <span class="text-rose-500">*</span></label>
                    <input type="text" name="question" required placeholder="e.g. How do I schedule a project site visit?" class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Answer Content <span class="text-rose-500">*</span></label>
                    <textarea name="answer" rows="4" required placeholder="Detailed answer..." class="w-full text-xs rounded-xl border border-slate-200 p-2.5"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sequence Order</label>
                        <input type="number" name="sequence" value="{{ ($faqs->max('sequence') ?? 0) + 1 }}" class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl">Save FAQ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit FAQ Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="editModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Edit FAQ</h3>
            <form :action="'{{ url('admin/faqs') }}/' + editId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Question <span class="text-rose-500">*</span></label>
                    <input type="text" name="question" x-model="editQ" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Answer Content <span class="text-rose-500">*</span></label>
                    <textarea name="answer" rows="4" x-model="editA" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sequence</label>
                        <input type="number" name="sequence" x-model="editSeq" class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" x-model="editStatus" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl">Update FAQ</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
