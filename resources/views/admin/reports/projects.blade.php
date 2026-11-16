@extends('admin.layouts.app')

@section('title', 'Project Demand Report')
@section('page-title', 'Project Demand & Inventory Sales Report')
@section('page-subtitle', 'Analyze market inquiry volume, site visit popularity, and booking conversions per project')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Project Performance & Demand Ranking</h3>
            <p class="text-xs text-slate-500">Ranked by total customer inquiries & booking conversion</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.projects', ['export' => 'csv']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Projects Demand Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4">Project Name & Location</th>
                        <th class="py-3.5 px-4">RERA Registration</th>
                        <th class="py-3.5 px-4">Starting Price</th>
                        <th class="py-3.5 px-4 text-center">Inquiries</th>
                        <th class="py-3.5 px-4 text-center">Site Visits</th>
                        <th class="py-3.5 px-4 text-center">Bookings Done</th>
                        <th class="py-3.5 px-4 text-center">Conversion %</th>
                        <th class="py-3.5 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $p)
                        @php
                            $rate = $p->total_inquiries > 0 ? round(($p->total_bookings / $p->total_inquiries) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <a href="{{ route('admin.projects.show', $p->id) }}" class="hover:text-brand-600 text-sm">
                                    {{ $p->name }}
                                </a>
                                <span class="text-[11px] text-slate-400 font-normal block">
                                    {{ $p->city->name ?? '' }}, {{ $p->state->name ?? '' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700">
                                {{ $p->rera_no ?? 'Under Process' }}
                            </td>

                            <td class="py-3.5 px-4 font-extrabold text-amber-700">
                                {{ $p->price ? '₹ ' . number_format($p->price, 2) : 'On Request' }}
                            </td>

                            <td class="py-3.5 px-4 text-center font-bold text-slate-800 text-sm">
                                {{ $p->total_inquiries }}
                            </td>

                            <td class="py-3.5 px-4 text-center font-bold text-blue-700">
                                {{ $p->total_visits }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-black text-xs bg-emerald-100 text-emerald-800">
                                    {{ $p->total_bookings }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center font-black text-brand-600 text-sm">
                                {{ $rate }}%
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold 
                                    {{ $p->project_status === 'Ongoing' ? 'bg-blue-50 text-blue-800' : '' }}
                                    {{ $p->project_status === 'Upcoming' ? 'bg-amber-50 text-amber-800' : '' }}
                                    {{ $p->project_status === 'Completed' ? 'bg-emerald-50 text-emerald-800' : '' }}">
                                    {{ $p->project_status }}
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No project inventory recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
