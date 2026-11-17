@extends('admin.layouts.app')

@section('title', 'Verified Site Visits Report')
@section('page-title', 'Verified Ground Site Visits Audit')
@section('page-subtitle', 'Audit on-site client inspections with photographic proof and GPS geographical tags')

@section('content')
<div class="space-y-6" x-data="{ imgModalOpen: false, modalImgSrc: '', modalImgTitle: '' }">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.reports.site-visits') }}" class="flex-1 flex flex-wrap items-center gap-2">
            
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">From:</span>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">To:</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="text-xs rounded-xl border border-slate-200 p-2 bg-white focus:ring-2 focus:ring-brand-500">
            </div>

            @if($isSuperAdmin && $allAgents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500 font-medium">
                    <option value="">All Leaders</option>
                    @foreach($allAgents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                Apply
            </button>
            @if(request()->hasAny(['date_from', 'date_to', 'agent_id']))
                <a href="{{ route('admin.reports.site-visits') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.site-visits', array_merge(request()->query(), ['export' => 'csv'])) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Visits Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Ground Inspections Audit Log</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $visits->total() }} Site Visits Recorded</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Project Visited</th>
                        <th class="py-3.5 px-4">Field Executive</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Site Photo Proof</th>
                        <th class="py-3.5 px-4">GPS Verification</th>
                        <th class="py-3.5 px-4">Inspection Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visits as $v)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            <td class="py-3 px-4 font-bold text-slate-900">
                                @if($v->contact)
                                    <a href="{{ route('admin.leads.show', $v->contact_id) }}" class="hover:text-brand-600 block">
                                        {{ $v->contact->name }}
                                    </a>
                                    <span class="text-[11px] text-slate-500 font-normal">{{ $v->contact->mobile }}</span>
                                @else
                                    <span class="text-slate-400">Customer #{{ $v->contact_id }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $v->contact->project->name ?? 'General Project' }}
                            </td>

                            <td class="py-3 px-4 text-slate-700">
                                <span class="font-semibold block">{{ $v->user->name ?? 'Staff' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $v->user->company_name ?? ($v->user->company->company_name ?? 'Agency') }}</span>
                            </td>

                            <td class="py-3 px-4 font-semibold text-slate-800 whitespace-nowrap">{{ $v->date }}</td>

                            <td class="py-3 px-4">
                                @if($v->image)
                                    <img src="{{ $v->image_url }}" alt="Proof" 
                                         @click="modalImgSrc = '{{ $v->image_url }}'; modalImgTitle = 'Site Inspection Photo Proof'; imgModalOpen = true"
                                         class="w-10 h-10 rounded-lg object-cover border border-slate-300 shadow-2xs cursor-pointer hover:opacity-85">
                                @else
                                    <span class="text-slate-400 text-[11px] italic">No image</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($v->latitude && $v->longitude)
                                    <a href="https://maps.google.com/?q={{ $v->latitude }},{{ $v->longitude }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-brand-600 hover:bg-brand-50 border border-brand-200">
                                        <i class="fa-solid fa-location-crosshairs text-rose-500"></i>
                                        <span>{{ number_format($v->latitude, 4) }}, {{ number_format($v->longitude, 4) }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 max-w-xs text-slate-700 leading-snug">
                                {{ $v->remark }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                No ground site visits recorded for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $visits->links() }}
            </div>
        @endif
    </div>

    <!-- Image Enlarge Modal -->
    <div x-show="imgModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs">
        <div @click.away="imgModalOpen = false" class="bg-white rounded-2xl max-w-2xl w-full p-4 shadow-2xl space-y-3">
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
@endsection
