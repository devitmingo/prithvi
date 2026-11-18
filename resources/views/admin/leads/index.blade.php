@extends('admin.layouts.app')

@section('title', 'Leads & CRM')
@section('page-title', $viewMode === 'cards' ? 'Customer Inquiries & Project Cards' : 'Customer Inquiries & Leads')
@section('page-subtitle', $viewMode === 'cards' ? 'Project-wise CRM pipeline, active inquiries breakdown, site visits, and conversion rates' : 'Track prospect pipeline, schedule site visits, monitor follow-ups, and track call frequencies')

@section('content')
<div class="space-y-6" x-data="{ 
    assignModalOpen: false, 
    assignLeadId: null, 
    assignLeadName: '', 
    callModalOpen: false, 
    callLeadId: null, 
    callLeadName: '',
    pSearch: '',
    pFilter: 'all'
}">

    <!-- Active Project Specific Filter Header Banner -->
    @if(request('project_id') && ($activeProj = ($selectedProject ?? $projects->first(fn($p) => $p->id == request('project_id')))))
        <div class="p-4 bg-gradient-to-r from-blue-50/90 via-indigo-50/50 to-white text-slate-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs border border-blue-200/90">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-city"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-extrabold text-slate-900 tracking-tight">{{ $activeProj->name }}</span>
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-brand-600 text-white shadow-2xs">Project Pipeline</span>
                        @if(request('status') === 'active_leads')
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-emerald-600 text-white shadow-2xs flex items-center gap-1">
                                <i class="fa-solid fa-fire text-[9px]"></i> Active Leads Only
                            </span>
                        @elseif(request('status') === 'call_not_connecting')
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-rose-600 text-white shadow-2xs flex items-center gap-1">
                                <i class="fa-solid fa-phone-slash text-[9px]"></i> Unreached Calls Only
                            </span>
                        @elseif(request('status'))
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-slate-700 text-white shadow-2xs">
                                {{ ucfirst(str_replace('_', ' ', request('status'))) }}
                            </span>
                        @endif
                        @if($activeProj->city || $activeProj->state)
                            <span class="text-xs text-slate-500 font-medium">
                                <i class="fa-solid fa-location-dot text-rose-500 text-[10px]"></i> {{ $activeProj->city->name ?? '' }}, {{ $activeProj->state->name ?? '' }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Showing inquiries, site visits, and bookings exclusively for <strong class="text-slate-900">{{ $activeProj->name }}</strong> ({{ $contacts->total() }} leads found)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.leads.create', ['project_id' => $activeProj->id]) }}" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>+ Add Lead</span>
                </a>
                <a href="{{ route('admin.leads.index', ['view' => 'cards']) }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-city text-brand-600"></i>
                    <span>Project Cards View</span>
                </a>
                <a href="{{ route('admin.leads.index', array_filter(['user_id' => request('user_id'), 'view' => 'table'])) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-xmark text-slate-300"></i>
                    <span>All Projects Pipeline</span>
                </a>
            </div>
        </div>
    @endif

    <!-- Active User Specific Filter Header Banner -->
    @if(request('user_id') && ($activeUser = ($selectedUser ?? \App\Models\User::find(request('user_id')))))
        <div class="p-4 bg-gradient-to-r from-blue-50/90 via-indigo-50/50 to-white text-slate-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs border border-blue-200/90">
            <div class="flex items-center gap-3.5">
                <div class="relative shrink-0">
                    <img src="{{ $activeUser->profile_photo_url }}" 
                         alt="{{ $activeUser->name }}" 
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($activeUser->name) }}&background=0D8ABC&color=fff';"
                         class="w-11 h-11 rounded-xl object-cover border-2 border-white shadow-xs">
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-extrabold text-slate-900 tracking-tight">{{ $activeUser->name }}</span>
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-brand-600 text-white shadow-2xs">
                            {{ $activeUser->role === 'Agent' ? 'Leader' : ($activeUser->role === 'Sub Agent' ? 'Staff' : $activeUser->role) }}
                        </span>
                        @if(request('status') === 'active_leads')
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-emerald-600 text-white shadow-2xs flex items-center gap-1">
                                <i class="fa-solid fa-fire text-[9px]"></i> Active Leads Only
                            </span>
                        @elseif(request('status') === 'call_not_connecting')
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-rose-600 text-white shadow-2xs flex items-center gap-1">
                                <i class="fa-solid fa-phone-slash text-[9px]"></i> Unreached Calls Only
                            </span>
                        @elseif(request('status'))
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-slate-700 text-white shadow-2xs">
                                {{ ucfirst(str_replace('_', ' ', request('status'))) }}
                            </span>
                        @endif
                        @if($activeUser->mobile)
                            <span class="text-xs text-slate-500 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-phone text-[10px] text-slate-400"></i> {{ $activeUser->mobile }}
                            </span>
                        @endif
                        @if($activeUser->company)
                            <span class="text-xs text-slate-500">
                                • Under: <strong class="text-slate-700">{{ $activeUser->company->name }}</strong>
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Showing all CRM inquiries, calls, and history handled by <strong class="text-slate-900">{{ $activeUser->name }}</strong> ({{ $contacts->total() }} total found)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.user-leads') }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-address-card text-brand-600"></i>
                    <span>All User Cards</span>
                </a>
                <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'view' => $viewMode])) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-xmark text-slate-300"></i>
                    <span>Clear User Filter</span>
                </a>
            </div>
        </div>
    @endif

    <!-- Overall Aggregate KPI Summary Cards (ALL NUMBERS ARE CLICKABLE) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- 1. All Inquiries -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'view' => $viewMode])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ !request('status') ? 'border-blue-500 ring-2 ring-blue-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}"
           title="Click to view all inquiries">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">All Inquiries</span>
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 mt-2 block">{{ $stats['total'] }}</span>
        </a>

        <!-- 2. Active Leads (Engaged Pipeline) -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'active_leads', 'view' => $viewMode])) }}" 
           class="group relative bg-gradient-to-br from-emerald-500 to-teal-600 p-4 rounded-2xl text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'active_leads' ? 'ring-2 ring-emerald-500 ring-offset-2' : '' }}"
           title="Click to view all active qualified leads">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-100">Active Leads</span>
                <span class="w-6 h-6 rounded-lg bg-white/20 text-white flex items-center justify-center text-xs">
                    <i class="fa-solid fa-fire text-amber-300"></i>
                </span>
            </div>
            <span class="text-2xl font-black mt-2 block">{{ $stats['active_leads'] }}</span>
        </a>

        <!-- 3. Follow Ups -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'follow_up', 'view' => $viewMode])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'follow_up' ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}"
           title="Click to view follow up leads">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Follow Ups</span>
                <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-amber-600 mt-2 block">{{ $stats['follow_up'] }}</span>
        </a>

        <!-- 4. Call Later -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'call_later', 'view' => $viewMode])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'call_later' ? 'border-purple-500 ring-2 ring-purple-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}"
           title="Click to view call later leads">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Call Later</span>
                <span class="w-6 h-6 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-calendar-day"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-purple-600 mt-2 block">{{ $stats['call_later'] }}</span>
        </a>

        <!-- 5. Site Visits -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'site_visit_done', 'view' => $viewMode])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'site_visit_done' ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}"
           title="Click to view site visits done">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Site Visits</span>
                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-location-dot"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-indigo-600 mt-2 block">{{ $stats['site_visit_done'] }}</span>
        </a>

        <!-- 6. Bookings Done -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'booking_done', 'view' => $viewMode])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'booking_done' ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}"
           title="Click to view booked deals">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Bookings</span>
                <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-emerald-600 mt-2 block">{{ $stats['booking_done'] }}</span>
        </a>

        <!-- 7. Not Connecting / Not Received -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'call_not_connecting', 'view' => $viewMode])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'call_not_connecting' ? 'border-rose-500 ring-2 ring-rose-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}"
           title="Click to view unreached calls">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Not Reached</span>
                <span class="w-6 h-6 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs group-hover:bg-rose-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-phone-slash"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-rose-600 mt-2 block">{{ $stats['not_connecting'] }}</span>
        </a>
    </div>

    <!-- View Mode Switcher Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
        
        <!-- View Mode Segmented Controls -->
        <div class="inline-flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/70 shadow-2xs">
            <!-- 1. Project-wise Cards Switcher -->
            <a href="{{ route('admin.leads.index', array_filter(['view' => 'cards', 'user_id' => request('user_id'), 'status' => request('status')])) }}" 
               class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $viewMode === 'cards' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-city {{ $viewMode === 'cards' ? 'text-brand-600' : 'text-slate-400' }}"></i>
                <span>Project-wise Cards</span>
                <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded-md {{ $viewMode === 'cards' ? 'bg-brand-50 text-brand-700' : 'bg-slate-200 text-slate-600' }}">
                    {{ count($projectCards) }}
                </span>
            </a>

            <!-- 2. Inquiries Table Switcher -->
            <a href="{{ route('admin.leads.index', array_filter(['view' => 'table', 'project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => request('status'), 'search' => request('search')])) }}" 
               class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $viewMode === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-table-list {{ $viewMode === 'table' ? 'text-blue-600' : 'text-slate-400' }}"></i>
                <span>Inquiries Table</span>
                <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded-md {{ $viewMode === 'table' ? 'bg-blue-50 text-blue-700' : 'bg-slate-200 text-slate-600' }}">
                    {{ $stats['total'] }}
                </span>
            </a>

            <!-- 3. User-wise Cards Link -->
            <a href="{{ route('admin.reports.user-leads') }}" 
               class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-white/60 transition-all">
                <i class="fa-solid fa-address-card text-brand-600"></i>
                <span>User Cards</span>
            </a>
        </div>

        <!-- Right Quick Action: Add New Lead -->
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.call-histories.index', array_filter(['project_id' => request('project_id')])) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs transition whitespace-nowrap">
                <i class="fa-solid fa-phone-volume text-cyan-600"></i>
                <span>Call Logs</span>
            </a>
            <a href="{{ route('admin.leads.create', array_filter(['project_id' => request('project_id')])) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-semibold shadow-sm transition-all whitespace-nowrap">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>+ Add New Lead</span>
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 1: PROJECT-WISE CARDS (LIKE USER-WISE CARDS)                          -->
    <!-- ========================================================================= -->
    @if($viewMode === 'cards')
        
        <!-- Project Cards Filters & Quick Filter Pills -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                
                <!-- Quick Filter Pills -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" 
                            @click="pFilter = 'all'" 
                            :class="pFilter === 'all' ? 'bg-slate-900 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3 py-1.5 rounded-xl text-xs transition">
                        All Projects ({{ count($projectCards) }})
                    </button>
                    <button type="button" 
                            @click="pFilter = 'active'" 
                            :class="pFilter === 'active' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1">
                        <i class="fa-solid fa-fire text-amber-300 text-[10px]"></i>
                        <span>Active Pipeline ({{ count(array_filter($projectCards, fn($c) => $c['active_leads'] > 0)) }})</span>
                    </button>
                    <button type="button" 
                            @click="pFilter = 'with_leads'" 
                            :class="pFilter === 'with_leads' ? 'bg-brand-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3 py-1.5 rounded-xl text-xs transition">
                        With Inquiries ({{ count(array_filter($projectCards, fn($c) => $c['total_leads'] > 0)) }})
                    </button>
                    <button type="button" 
                            @click="pFilter = 'no_leads'" 
                            :class="pFilter === 'no_leads' ? 'bg-slate-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3 py-1.5 rounded-xl text-xs transition">
                        No Inquiries ({{ count(array_filter($projectCards, fn($c) => $c['total_leads'] == 0)) }})
                    </button>
                </div>

                <!-- Instant Project Search Input -->
                <div class="relative min-w-[240px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           x-model="pSearch" 
                           placeholder="Search project name, RERA, city..."
                           class="w-full pl-9 pr-3.5 py-1.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Project-wise Cards Grid (ALL NUMBERS ARE CLICKABLE) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($projectCards as $c)
                @php
                    $proj = $c['project'];
                    $hasActiveLeads = $c['active_leads'] > 0;
                    $hasAnyLeads = $c['total_leads'] > 0;
                    $searchData = strtolower($proj->name . ' ' . ($proj->city->name ?? '') . ' ' . ($proj->state->name ?? '') . ' ' . ($proj->rera_no ?? ''));
                @endphp
                <div x-show="(pFilter === 'all' || 
                             (pFilter === 'active' && {{ $c['active_leads'] }} > 0) || 
                             (pFilter === 'with_leads' && {{ $c['total_leads'] }} > 0) || 
                             (pFilter === 'no_leads' && {{ $c['total_leads'] }} === 0)) && 
                             (pSearch === '' || '{{ addslashes($searchData) }}'.includes(pSearch.toLowerCase().trim()))"
                     class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:border-brand-500/60 hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group {{ $hasActiveLeads ? 'ring-2 ring-emerald-500/20' : '' }}">
                    
                    <!-- Card Top: Project Image Banner (Strictly Size-Locked, No Blowout) -->
                    <div class="h-44 sm:h-48 w-full bg-slate-900 relative overflow-hidden shrink-0">
                        @if($proj->image)
                            <img src="{{ $proj->image_url }}" 
                                 alt="{{ $proj->name }}" 
                                 onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-tr from-slate-800 to-indigo-900 flex items-center justify-center text-slate-500\'><i class=\'fa-solid fa-city text-4xl\'></i></div>';"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-tr from-slate-800 to-indigo-900 flex items-center justify-center text-slate-500">
                                <i class="fa-solid fa-city text-4xl"></i>
                            </div>
                        @endif

                        <!-- Gradient Overlay for Contrast -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent"></div>

                        <!-- Top Floating Badges -->
                        <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wide backdrop-blur-md shadow-xs
                                {{ $proj->project_status === 'Ongoing' ? 'bg-blue-600/90 text-white' : '' }}
                                {{ $proj->project_status === 'Upcoming' ? 'bg-amber-600/90 text-white' : '' }}
                                {{ $proj->project_status === 'Completed' ? 'bg-emerald-600/90 text-white' : 'bg-slate-700/90 text-white' }}">
                                {{ $proj->project_status ?? 'Active Project' }}
                            </span>

                            <!-- Active Leads Pill on Image -->
                            <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'status' => 'active_leads', 'view' => 'table']) }}" 
                               class="px-2.5 py-1 rounded-lg text-[10px] font-black backdrop-blur-md shadow-xs transition flex items-center gap-1 {{ $hasActiveLeads ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-slate-800/80 text-slate-300' }}"
                               title="Click to view {{ $c['active_leads'] }} active leads">
                                <i class="fa-solid fa-fire text-[9px] {{ $hasActiveLeads ? 'text-amber-300' : 'text-slate-400' }}"></i>
                                <span>{{ $c['active_leads'] }} Active Leads</span>
                            </a>
                        </div>

                        <!-- Bottom Project Name & Location Overlay -->
                        <div class="absolute bottom-3 left-3 right-3 text-white">
                            <h3 class="text-base font-extrabold truncate drop-shadow-sm group-hover:text-amber-300 transition-colors" title="{{ $proj->name }}">
                                {{ $proj->name }}
                            </h3>
                            <p class="text-[11px] text-slate-200 flex items-center gap-1.5 mt-0.5">
                                <i class="fa-solid fa-location-dot text-rose-400"></i>
                                <span>{{ $proj->city->name ?? 'Raipur' }}, {{ $proj->state->name ?? 'Chhattisgarh' }}</span>
                                @if($proj->rera_no)
                                    <span class="text-slate-400">•</span>
                                    <span class="font-mono text-[10px] text-slate-300 truncate max-w-[130px]">{{ $proj->rera_no }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 space-y-4 flex-1 flex flex-col justify-between">
                        
                        <!-- Section 1: Pricing & Quick Meta -->
                        <div class="flex items-center justify-between text-xs pb-1 border-b border-slate-100">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pricing</span>
                                <span class="font-extrabold text-brand-600 text-sm">
                                    {{ $proj->price ? '₹ ' . number_format($proj->price, 2) : 'Starting on request' }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Inquiries</span>
                                <span class="font-black text-slate-800 text-sm">
                                    {{ $c['total_leads'] }} Leads
                                </span>
                            </div>
                        </div>

                        <!-- Section 2: Active Pipeline Banner (CLICKABLE) -->
                        <div class="p-3 rounded-2xl {{ $hasActiveLeads ? 'bg-gradient-to-r from-emerald-50/90 via-teal-50/40 to-white border border-emerald-200/80 shadow-2xs' : 'bg-slate-50 border border-slate-100' }} flex items-center justify-between">
                            <!-- Clickable Active Qualified Leads -->
                            <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'status' => 'active_leads', 'view' => 'table']) }}" 
                               class="flex items-center gap-2.5 group/active hover:opacity-90 transition"
                               title="Click to view all {{ $c['active_leads'] }} active leads for {{ $proj->name }}">
                                <span class="w-8 h-8 rounded-lg {{ $hasActiveLeads ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-sm font-bold">
                                    <i class="fa-solid fa-fire"></i>
                                </span>
                                <div>
                                    <span class="text-[9px] font-bold uppercase tracking-wider {{ $hasActiveLeads ? 'text-emerald-800' : 'text-slate-400' }} block">
                                        Active Pipeline
                                    </span>
                                    <span class="text-base font-black text-slate-900 leading-tight group-hover/active:text-emerald-700 transition">
                                        {{ $c['active_leads'] }} Active
                                    </span>
                                </div>
                            </a>

                            <!-- Clickable Total Call Attempts Logged for this Project -->
                            <a href="{{ route('admin.call-histories.index', ['project_id' => $proj->id]) }}" 
                               class="text-right group/calls hover:opacity-90 transition"
                               title="Click to view all {{ $c['calls_count'] }} call logs for {{ $proj->name }}">
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider block">Calls Made</span>
                                <span class="text-xs font-black text-cyan-700 flex items-center gap-1 justify-end group-hover/calls:underline">
                                    <i class="fa-solid fa-phone-volume text-[9px]"></i> {{ $c['calls_count'] }} Calls
                                </span>
                            </a>
                        </div>

                        <!-- Section 3: 4-Tile Pipeline Matrix (ALL TILES CLICKABLE) -->
                        <div class="grid grid-cols-4 gap-1.5 text-center">
                            <!-- Follow Up (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'status' => 'follow_up', 'view' => 'table']) }}" 
                               class="p-2 rounded-xl bg-amber-50/80 hover:bg-amber-100 border border-amber-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['follow_up'] }} follow up leads">
                                <span class="text-[8px] font-extrabold text-amber-800 uppercase tracking-wider block truncate">Follow Up</span>
                                <span class="text-sm font-black text-amber-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['follow_up'] }}</span>
                            </a>

                            <!-- Call Later (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'status' => 'call_later', 'view' => 'table']) }}" 
                               class="p-2 rounded-xl bg-purple-50/80 hover:bg-purple-100 border border-purple-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['call_later'] }} call later leads">
                                <span class="text-[8px] font-extrabold text-purple-800 uppercase tracking-wider block truncate">Call Later</span>
                                <span class="text-sm font-black text-purple-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['call_later'] }}</span>
                            </a>

                            <!-- Site Visit (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'status' => 'site_visit_done', 'view' => 'table']) }}" 
                               class="p-2 rounded-xl bg-indigo-50/80 hover:bg-indigo-100 border border-indigo-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['site_visit'] }} site visits done">
                                <span class="text-[8px] font-extrabold text-indigo-800 uppercase tracking-wider block truncate">Site Visit</span>
                                <span class="text-sm font-black text-indigo-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['site_visit'] }}</span>
                            </a>

                            <!-- Bookings Done (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'status' => 'booking_done', 'view' => 'table']) }}" 
                               class="p-2 rounded-xl bg-emerald-50/80 hover:bg-emerald-100 border border-emerald-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['booking'] }} bookings">
                                <span class="text-[8px] font-extrabold text-emerald-800 uppercase tracking-wider block truncate">Booking</span>
                                <span class="text-sm font-black text-emerald-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['booking'] }}</span>
                            </a>
                        </div>

                        <!-- Section 4: Secondary Outcomes & Conversion Rate -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-xs">
                                <!-- Clickable Not Reached -->
                                <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'status' => 'call_not_connecting', 'view' => 'table']) }}" 
                                   class="text-[11px] font-semibold text-rose-700 hover:text-rose-800 flex items-center gap-1 group/nr"
                                   title="Click to view unreached calls">
                                    <i class="fa-solid fa-phone-slash text-[10px]"></i>
                                    <span>Not Reached: <strong class="font-extrabold group-hover/nr:underline">{{ $c['not_connecting'] }}</strong></span>
                                </a>

                                <span class="text-[11px] font-semibold text-slate-500">
                                    Booking Rate: <strong class="text-slate-800 font-black">{{ $c['conversion_rate'] }}%</strong>
                                </span>
                            </div>

                            <!-- Conversion Progress Bar -->
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-brand-500 via-teal-500 to-emerald-500 h-1.5 rounded-full" 
                                     style="width: {{ $c['conversion_rate'] > 0 ? min(100, max(6, $c['conversion_rate'])) : ($hasActiveLeads ? 15 : 0) }}%"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Card Footer: Direct Drilldown Actions -->
                    <div class="p-4 bg-slate-50/90 border-t border-slate-100 flex items-center gap-2">
                        <!-- Primary: View Project Leads in Table -->
                        <a href="{{ route('admin.leads.index', ['project_id' => $proj->id, 'view' => 'table']) }}" 
                           class="flex-1 py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold text-center transition flex items-center justify-center gap-2 shadow-xs group-hover:bg-brand-600">
                            <span>View Inquiries ({{ $c['total_leads'] }})</span>
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <!-- Secondary: Add Lead for this Project -->
                        <a href="{{ route('admin.leads.create', ['project_id' => $proj->id]) }}" 
                           class="py-2.5 px-3 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold transition shadow-2xs flex items-center gap-1.5"
                           title="Add New Lead for {{ $proj->name }}">
                            <i class="fa-solid fa-user-plus text-brand-600"></i>
                        </a>

                        <!-- Tertiary: View Project Inventory & Details -->
                        <a href="{{ route('admin.projects.show', $proj->id) }}" 
                           class="py-2.5 px-3 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold transition shadow-2xs flex items-center gap-1.5"
                           title="Project Details & Documents">
                            <i class="fa-solid fa-circle-info text-slate-500"></i>
                        </a>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-3xl border border-slate-200/80">
                    <i class="fa-solid fa-city text-4xl text-slate-300 mb-2 block"></i>
                    No real estate projects found.
                </div>
            @endforelse
        </div>

    <!-- ========================================================================= -->
    <!-- VIEW 2: FLAT INQUIRIES TABLE VIEW                                         -->
    <!-- ========================================================================= -->
    @else

        <!-- Filter & Search Bar for Table View -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            
            <form method="GET" action="{{ route('admin.leads.index') }}" class="flex-1 flex flex-wrap items-center gap-2.5">
                <input type="hidden" name="view" value="table">

                <!-- Search -->
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search customer, phone, remark..."
                           class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Project Filter -->
                <select name="project_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 font-medium">
                    <option value="">All Projects</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                    @endforeach
                </select>

                <!-- Executive / User Filter -->
                @if(isset($assignableUsers) && $assignableUsers->count() > 0)
                    <select name="user_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 font-medium">
                        <option value="">All Executives</option>
                        @foreach($assignableUsers as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role === 'Agent' ? 'Leader' : ($u->role === 'Sub Agent' ? 'Staff' : $u->role) }})
                            </option>
                        @endforeach
                    </select>
                @endif

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 font-medium">
                    <option value="">All Statuses</option>
                    <option value="active_leads" {{ request('status') === 'active_leads' ? 'selected' : '' }}>⭐ Active Leads (Engaged)</option>
                    <option value="follow_up" {{ request('status') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                    <option value="call_later" {{ request('status') === 'call_later' ? 'selected' : '' }}>Call Later</option>
                    <option value="site_visit_done" {{ request('status') === 'site_visit_done' ? 'selected' : '' }}>Site Visit Done</option>
                    <option value="booking_done" {{ request('status') === 'booking_done' ? 'selected' : '' }}>Booking Done</option>
                    <option value="visit_scheduled" {{ request('status') === 'visit_scheduled' ? 'selected' : '' }}>Visit Scheduled</option>
                    <option value="call_not_connecting" {{ request('status') === 'call_not_connecting' ? 'selected' : '' }}>Not Received / Unconnected</option>
                    <option value="interested" {{ request('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                    <option value="not_interested" {{ request('status') === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                </select>

                <!-- Leader Filter for Superadmin -->
                @if($isSuperAdmin && $agents->count() > 0)
                    <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="">All Leaders</option>
                        @foreach($agents as $ag)
                            <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                        @endforeach
                    </select>
                @endif

                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Filter</span>
                </button>

                @if(request()->hasAny(['search', 'project_id', 'status', 'agent_id', 'user_id']))
                    <a href="{{ route('admin.leads.index', ['view' => 'table']) }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.leads.index', ['view' => 'cards']) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs transition whitespace-nowrap">
                    <i class="fa-solid fa-city text-brand-600"></i>
                    <span>Project Cards</span>
                </a>
                <a href="{{ route('admin.reports.user-leads') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs transition whitespace-nowrap">
                    <i class="fa-solid fa-address-card text-brand-600"></i>
                    <span>User Cards</span>
                </a>
            </div>
        </div>

        <!-- Inquiries Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/90 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                            <th class="py-3.5 px-4">Customer Details & Phone</th>
                            <th class="py-3.5 px-4">Project</th>
                            <th class="py-3.5 px-4">Assigned Executive</th>
                            <th class="py-3.5 px-4">Pipeline Status</th>
                            <th class="py-3.5 px-4">Latest Remark</th>
                            <th class="py-3.5 px-4">Date & Time</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contacts as $lead)
                            @php
                                $phoneClean = preg_replace('/[^0-9]/', '', $lead->mobile);
                                $callCount = $callCountsByPhone[$lead->mobile] ?? $lead->contactCallHistories->count();
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- Customer Details & Call Frequency -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('admin.leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm block">
                                        {{ $lead->name }}
                                    </a>
                                    
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <!-- Click-to-call link -->
                                        <a href="tel:{{ $lead->mobile }}" class="text-xs text-slate-700 font-semibold hover:text-blue-600 flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded-md">
                                            <i class="fa-solid fa-phone text-[10px] text-emerald-600"></i>
                                            <span>{{ $lead->mobile }}</span>
                                        </a>

                                        <!-- WhatsApp quick icon -->
                                        <a href="https://wa.me/91{{ $phoneClean }}" target="_blank" class="w-5 h-5 rounded-md bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center transition" title="Chat on WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-xs"></i>
                                        </a>

                                        <!-- Call Frequency Badge -->
                                        @if($callCount > 1)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs" title="Same number has been called {{ $callCount }} times across system">
                                                <i class="fa-solid fa-phone-volume text-[8px] text-amber-600 animate-pulse"></i>
                                                <span>Called {{ $callCount }} times</span>
                                            </span>
                                        @elseif($callCount == 1)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fa-solid fa-phone text-[8px] text-blue-500"></i>
                                                <span>1 call</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-400">
                                                No calls yet
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Project -->
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    @if($lead->project)
                                        <a href="{{ route('admin.leads.index', ['project_id' => $lead->project_id, 'view' => 'table']) }}" class="hover:text-blue-600 flex items-center gap-1.5" title="Filter leads for {{ $lead->project->name }}">
                                            <i class="fa-solid fa-building text-slate-400 text-xs"></i>
                                            <span>{{ $lead->project->name }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">General Inquiry</span>
                                    @endif
                                </td>

                                <!-- Assigned Executive -->
                                <td class="py-3.5 px-4">
                                    @if($lead->user)
                                        <a href="{{ route('admin.leads.index', ['user_id' => $lead->user->id, 'view' => 'table']) }}" class="font-semibold text-slate-800 hover:text-blue-600 block">
                                            {{ $lead->user->name }}
                                        </a>
                                        <span class="text-[10px] text-slate-400">
                                            {{ $lead->user->company_name ?? ($lead->user->company->name ?? $lead->user->role ?? '') }}
                                        </span>
                                    @else
                                        <span class="font-semibold text-slate-400">Unassigned</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                        {{ $lead->status === 'booking_done' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                        {{ $lead->status === 'site_visit_done' ? 'bg-blue-100 text-blue-800 border border-blue-300' : '' }}
                                        {{ $lead->status === 'visit_scheduled' ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : '' }}
                                        {{ $lead->status === 'follow_up' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                                        {{ in_array($lead->status, ['call_later', 'call_not_connecting']) ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}
                                        {{ in_array($lead->status, ['interested', 'not_interested']) ? 'bg-purple-100 text-purple-800 border border-purple-300' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                    </span>
                                </td>

                                <!-- Latest Remark -->
                                <td class="py-3.5 px-4 max-w-xs truncate text-slate-600" title="{{ $lead->remark }}">
                                    {{ $lead->remark ?? '—' }}
                                </td>

                                <!-- Date & Time -->
                                <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                    <span class="block font-medium text-slate-700">{{ $lead->date }}</span>
                                    <span class="text-slate-400">{{ $lead->time }}</span>
                                </td>

                                <!-- High-Visibility Interactive Actions Column -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        
                                        <!-- Direct Phone Call Button -->
                                        <a href="tel:{{ $lead->mobile }}" 
                                           class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                           title="Call {{ $lead->mobile }}">
                                            <i class="fa-solid fa-phone text-xs"></i>
                                        </a>

                                        <!-- Quick WhatsApp Button -->
                                        <a href="https://wa.me/91{{ $phoneClean }}" target="_blank"
                                           class="w-7 h-7 rounded-lg bg-green-50 hover:bg-green-600 text-green-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                           title="Chat on WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-xs"></i>
                                        </a>

                                        <!-- Quick Log Call Modal Trigger -->
                                        <button type="button" 
                                                @click="callLeadId = {{ $lead->id }}; callLeadName = '{{ addslashes($lead->name) }}'; callModalOpen = true"
                                                class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                                title="Log Interaction / Call">
                                            <i class="fa-solid fa-phone-volume text-xs"></i>
                                        </button>

                                        <!-- Reassign Trigger -->
                                        <button type="button" 
                                                @click="assignLeadId = {{ $lead->id }}; assignLeadName = '{{ addslashes($lead->name) }}'; assignModalOpen = true"
                                                class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                                title="Reassign Executive">
                                            <i class="fa-solid fa-user-gear text-xs"></i>
                                        </button>

                                        <!-- View Details -->
                                        <a href="{{ route('admin.leads.show', $lead->id) }}" 
                                           class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-800 text-slate-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                           title="View Full Profile & Timeline">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('admin.leads.edit', $lead->id) }}" 
                                           class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                           title="Edit Lead">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>

                                        <!-- Delete (Superadmin) -->
                                        @if(auth()->user()?->isSuperAdmin())
                                        <form action="{{ route('admin.leads.destroy', $lead->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this lead and all interaction history?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                                    title="Delete Lead">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-users-slash text-3xl text-slate-300 mb-2 block"></i>
                                    No customer leads found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contacts->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>

    @endif

    <!-- ========================================================================= -->
    <!-- MODALS: QUICK LOG CALL & REASSIGN LEAD                                     -->
    <!-- ========================================================================= -->

    <!-- Modal 1: Quick Log Call -->
    <div x-show="callModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="callModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Log Interaction for <span x-text="callLeadName" class="text-blue-600"></span></h3>
                        <p class="text-[11px] text-slate-400">Save call discussion or site visit in 1 click</p>
                    </div>
                </div>
                <button type="button" @click="callModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/leads') }}/' + callLeadId + '/call-history'" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Pipeline Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="follow_up">Follow Up</option>
                        <option value="site_visit_done">Site Visit Completed</option>
                        <option value="booking_done">Booking Confirmed</option>
                        <option value="visit_scheduled">Visit Scheduled</option>
                        <option value="call_later">Call Later</option>
                        <option value="call_not_connecting">Call Not Connecting</option>
                        <option value="interested">Interested</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Date of Activity <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Discussion Remarks / Meeting Notes <span class="text-rose-500">*</span></label>
                    <textarea name="remark" rows="3" required placeholder="Enter customer feedback, next scheduled date, requirements discussed..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Optional Site Visit Proof (Not Required for Super Admin) -->
                <div x-data="{ showProof: false }" class="pt-1">
                    <button type="button" @click="showProof = !showProof" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-camera"></i>
                        <span x-text="showProof ? '- Hide Visit Photo & GPS (Optional)' : '+ Add Photo Proof or GPS (Optional)'"></span>
                    </button>
                    <div x-show="showProof" x-cloak class="mt-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <p class="text-[11px] text-slate-500">Photo and GPS coordinates are completely optional.</p>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Site Visit Photo (Optional)</label>
                            <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:border file:border-slate-300">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">GPS Lat</label>
                                <input type="text" name="latitude" placeholder="e.g. 21.25" class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">GPS Long</label>
                                <input type="text" name="longitude" placeholder="e.g. 81.63" class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="callModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition">
                        Save Activity Log
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Reassign Lead -->
    <div x-show="assignModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="assignModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                    <span>Reassign Lead: <span x-text="assignLeadName" class="text-indigo-600"></span></span>
                </h3>
                <button type="button" @click="assignModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/leads') }}/' + assignLeadId + '/assign'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select New Executive / Staff Member <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Choose Staff Member --</option>
                        @foreach($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role === 'Agent' ? 'Leader' : ($u->role === 'Sub Agent' ? 'Staff' : $u->role) }}{{ $u->company_name ? ' - ' . $u->company_name : '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reassignment Instructions (Optional)</label>
                    <textarea name="remark" rows="2" placeholder="e.g. Please arrange site visit this Saturday..." class="w-full text-xs rounded-xl border border-slate-200 p-2.5"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs transition">
                        Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
