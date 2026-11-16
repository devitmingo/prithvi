@extends('admin.layouts.app')

@section('title', 'Call History & Visits')
@section('page-title', 'Calls & Field Visit Activity Logs')
@section('page-subtitle', 'Real-time record of customer calls, repeated call frequency, and verified site visits')

@section('content')
<div class="space-y-6" x-data="{ imgModalOpen: false, modalImgSrc: '', modalImgTitle: '' }">

    <!-- KPI / Frequency Quick Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Calls Logged</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-phone-volume"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 mt-2 block">{{ \App\Models\ContactCallHistory::where('visited', 0)->count() }}</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Site Visits Done</span>
                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-location-dot"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-indigo-600 mt-2 block">{{ \App\Models\ContactCallHistory::where('visited', 1)->orWhere('status', 'site_visit_done')->count() }}</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Repeated Contacts</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </span>
            </div>
            @php
                $repeatedCount = \Illuminate\Support\Facades\DB::table('contacts')
                    ->join('contact_call_histories', 'contacts.id', '=', 'contact_call_histories.contact_id')
                    ->select('contacts.mobile')
                    ->groupBy('contacts.mobile')
                    ->havingRaw('count(contact_call_histories.id) > 1')
                    ->get()
                    ->count();
            @endphp
            <span class="text-2xl font-black text-amber-600 mt-2 block">{{ $repeatedCount }}</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Converted Bookings</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-emerald-600 mt-2 block">{{ \App\Models\ContactCallHistory::where('status', 'booking_done')->count() }}</span>
        </div>
    </div>

    <!-- Filter & Action Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.call-histories.index') }}" class="flex-1 flex flex-wrap items-center gap-2.5">
            
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search remarks, customer or phone..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Repeated Calls Filter Toggle -->
            <a href="{{ route('admin.call-histories.index', array_merge(request()->query(), ['repeated_calls' => request('repeated_calls') == '1' ? null : '1'])) }}" 
               class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('repeated_calls') == '1' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 border border-amber-200 text-amber-800 hover:bg-amber-100' }}" 
               title="Filter contacts called more than once">
                <i class="fa-solid fa-phone-volume text-[11px]"></i>
                <span>Repeated Calls (> 1)</span>
                @if(request('repeated_calls') == '1')
                    <i class="fa-solid fa-check text-[10px]"></i>
                @endif
            </a>

            <!-- Visited Filter -->
            <select name="visited" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                <option value="">All Activity Types</option>
                <option value="1" {{ request('visited') === '1' ? 'selected' : '' }}>Site Visits Only</option>
                <option value="0" {{ request('visited') === '0' ? 'selected' : '' }}>Phone Calls Only</option>
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="site_visit_done" {{ request('status') === 'site_visit_done' ? 'selected' : '' }}>Site Visit Done</option>
                <option value="booking_done" {{ request('status') === 'booking_done' ? 'selected' : '' }}>Booking Done</option>
                <option value="follow_up" {{ request('status') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                <option value="visit_scheduled" {{ request('status') === 'visit_scheduled' ? 'selected' : '' }}>Visit Scheduled</option>
                <option value="call_later" {{ request('status') === 'call_later' ? 'selected' : '' }}>Call Later</option>
                <option value="call_not_connecting" {{ request('status') === 'call_not_connecting' ? 'selected' : '' }}>Call Not Connecting</option>
            </select>

            <!-- Executive Filter -->
            <select name="user_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                <option value="">All Executives</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['search', 'visited', 'status', 'user_id', 'agent_id', 'repeated_calls']))
                <a href="{{ route('admin.call-histories.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition" title="Clear All Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <a href="{{ route('admin.reports.site-visits') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition whitespace-nowrap">
            <i class="fa-solid fa-chart-line text-blue-600"></i>
            <span>Ground Audit Report</span>
        </a>
    </div>

    <!-- Call Histories Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-4">Customer Details & Call Count</th>
                        <th class="py-3.5 px-4">Project</th>
                        <th class="py-3.5 px-4">Executive</th>
                        <th class="py-3.5 px-4">Type / Status</th>
                        <th class="py-3.5 px-4">Discussion Remark</th>
                        <th class="py-3.5 px-4">Proof / GPS</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $h)
                        @php
                            $phone = $h->contact?->mobile;
                            $cleanPhone = $phone ? preg_replace('/[^0-9]/', '', $phone) : '';
                            $totalCallsForPhone = $phone && isset($callCountsByPhone[$phone]) ? $callCountsByPhone[$phone] : 1;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            
                            <!-- Customer & Call Frequency -->
                            <td class="py-3.5 px-4">
                                @if($h->contact)
                                    <a href="{{ route('admin.leads.show', $h->contact_id) }}" class="font-bold text-slate-900 hover:text-blue-600 block text-sm">
                                        {{ $h->contact->name }}
                                    </a>
                                    
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <!-- Click to call -->
                                        <a href="tel:{{ $phone }}" class="text-xs font-semibold text-slate-700 hover:text-blue-600 flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded-md">
                                            <i class="fa-solid fa-phone text-[10px] text-emerald-600"></i>
                                            <span>{{ $phone }}</span>
                                        </a>

                                        <!-- WhatsApp icon -->
                                        @if($cleanPhone)
                                        <a href="https://wa.me/91{{ $cleanPhone }}" target="_blank" class="w-5 h-5 rounded-md bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center transition" title="Chat on WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-xs"></i>
                                        </a>
                                        @endif

                                        <!-- Call count badge -->
                                        @if($totalCallsForPhone > 1)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs" title="Total calls made to {{ $phone }}">
                                                <i class="fa-solid fa-phone-volume text-[8px] text-amber-600"></i>
                                                <span>Called {{ $totalCallsForPhone }} times</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">
                                                1 call
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">Deleted Contact (#{{ $h->contact_id }})</span>
                                @endif
                            </td>

                            <!-- Project -->
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $h->contact->project->name ?? 'General Inquiry' }}
                            </td>

                            <!-- Executive -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $h->user->name ?? 'Staff Executive' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $h->user->company_name ?? ($h->user->company->company_name ?? $h->user->role ?? '') }}</span>
                            </td>

                            <!-- Type / Status -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    @if($h->visited || $h->status === 'site_visit_done')
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 flex items-center gap-1">
                                            <i class="fa-solid fa-location-dot text-[8px]"></i> Visit
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                                            <i class="fa-solid fa-phone text-[8px]"></i> Call
                                        </span>
                                    @endif

                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $h->status === 'booking_done' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $h->status === 'site_visit_done' ? 'bg-indigo-50 text-indigo-700' : '' }}
                                        {{ $h->status === 'follow_up' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ in_array($h->status, ['call_later', 'call_not_connecting']) ? 'bg-slate-100 text-slate-600' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $h->status)) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Remark -->
                            <td class="py-3.5 px-4 max-w-xs text-slate-700 leading-snug font-medium" title="{{ $h->remark }}">
                                {{ $h->remark ?? '—' }}
                            </td>

                            <!-- Proof / GPS -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    @if($h->image)
                                        <img src="{{ $h->image_url }}" alt="Proof" 
                                             @click="modalImgSrc = '{{ $h->image_url }}'; modalImgTitle = 'Site Visit Photo'; imgModalOpen = true"
                                             class="w-9 h-9 rounded-lg object-cover border border-slate-200 cursor-pointer hover:opacity-80 shadow-2xs">
                                    @endif

                                    @if($h->latitude && $h->longitude)
                                        <a href="https://maps.google.com/?q={{ $h->latitude }},{{ $h->longitude }}" target="_blank" 
                                           class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-blue-600 text-xs transition" title="Google Maps Coordinates ({{ $h->latitude }}, {{ $h->longitude }})">
                                            <i class="fa-solid fa-location-crosshairs text-rose-500"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="py-3.5 px-4 text-slate-500 text-[11px] whitespace-nowrap">
                                <span class="font-medium text-slate-700">{{ $h->date }}</span>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    @if($phone)
                                        <!-- Call -->
                                        <a href="tel:{{ $phone }}" 
                                           class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                           title="Call {{ $phone }}">
                                            <i class="fa-solid fa-phone text-xs"></i>
                                        </a>

                                        <!-- WhatsApp -->
                                        <a href="https://wa.me/91{{ $cleanPhone }}" target="_blank" 
                                           class="w-7 h-7 rounded-lg bg-green-50 hover:bg-green-600 text-green-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                           title="WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-xs"></i>
                                        </a>
                                    @endif

                                    <!-- View Lead Details -->
                                    <a href="{{ route('admin.leads.show', $h->contact_id) }}" 
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-800 text-slate-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                       title="View Full Lead Timeline">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    <!-- Delete (Superadmin / Owner) -->
                                    @if(auth()->user()?->isSuperAdmin())
                                    <form action="{{ route('admin.call-histories.destroy', $h->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this call history log?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                                title="Delete Log">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-phone-slash text-3xl text-slate-300 mb-2 block"></i>
                                No call history or site visits recorded matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $histories->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Image Zoom View -->
    <div x-show="imgModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" @click="imgModalOpen = false">
        <div @click.stop class="bg-white rounded-2xl max-w-xl w-full p-4 shadow-2xl space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h4 class="text-xs font-bold text-slate-800" x-text="modalImgTitle"></h4>
                <button type="button" @click="imgModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-hidden rounded-xl bg-slate-100 flex items-center justify-center">
                <img :src="modalImgSrc" class="max-h-full max-w-full object-contain">
            </div>
        </div>
    </div>

</div>
@endsection
