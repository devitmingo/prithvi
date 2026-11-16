@extends('admin.layouts.app')

@section('title', 'Offers & Promotions')
@section('page-title', 'Promotional Offers & Discounts')
@section('page-subtitle', 'Manage project-specific seasonal schemes, spot discounts, and marketing campaigns')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.offers.index') }}" class="flex items-center gap-2 flex-1">
            <select name="project_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            @if(request('project_id'))
                <a href="{{ route('admin.offers.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <a href="{{ route('admin.offers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm shadow-brand-600/30 transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Create New Offer</span>
        </a>
    </div>

    <!-- Offers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($offers as $offer)
            @php
                $isExpired = Carbon\Carbon::parse($offer->getRawOriginal('to_date'))->isPast();
            @endphp
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow p-6 flex flex-col justify-between space-y-4">
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $isExpired ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                            <i class="fa-solid {{ $isExpired ? 'fa-clock text-slate-400' : 'fa-circle-check text-emerald-600' }} text-[10px]"></i>
                            <span>{{ $isExpired ? 'Expired' : 'Active Scheme' }}</span>
                        </span>

                        <span class="text-[11px] text-slate-400 font-medium">#{{ $offer->id }}</span>
                    </div>

                    <div class="pt-1">
                        <span class="text-xs text-slate-400 font-semibold block uppercase tracking-wider">Applicable Project</span>
                        <a href="{{ route('admin.projects.show', $offer->project_id) }}" class="text-base font-extrabold text-slate-900 hover:text-brand-600">
                            {{ $offer->project->name ?? 'Any Project' }}
                        </a>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 whitespace-pre-line leading-relaxed">
                        {{ $offer->description }}
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Valid From: <strong>{{ $offer->from_date }}</strong></span>
                        <span>To: <strong class="{{ $isExpired ? 'text-rose-600' : 'text-slate-800' }}">{{ $offer->to_date }}</strong></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('admin.offers.edit', $offer->id) }}" class="text-center py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('admin.offers.destroy', $offer->id) }}" method="POST" onsubmit="return confirm('Delete this promotional offer?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2 px-3 rounded-xl border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-bold transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-slate-200 p-8">
                <i class="fa-solid fa-tags text-4xl text-slate-300 mb-3 block"></i>
                <h3 class="text-sm font-bold text-slate-800">No offers found</h3>
                <p class="text-xs text-slate-500 mt-1 mb-4">Create special festive schemes or spot booking discounts for your projects.</p>
                <a href="{{ route('admin.offers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-xs font-bold">
                    <i class="fa-solid fa-plus"></i> Add New Offer
                </a>
            </div>
        @endforelse
    </div>

    @if($offers->hasPages())
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
            {{ $offers->links() }}
        </div>
    @endif

</div>
@endsection
