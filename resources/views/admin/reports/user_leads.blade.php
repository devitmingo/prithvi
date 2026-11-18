@extends('admin.layouts.app')

@section('title', 'User-wise Leads & CRM')
@section('page-title', 'Executive Leads & Pipeline')
@section('page-subtitle', 'Active leads (follow ups, visits, call later), call attempts, unreached calls, and one-click list drill-down')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4">
        
        <!-- Header Quick Filter Tabs & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <!-- Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 custom-scrollbar">
                @php
                    $currFilterLeads = request('filter_leads');
                    $currRole = request('role');
                @endphp
                <a href="{{ route('admin.reports.user-leads') }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ !$currFilterLeads && !$currRole ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    <i class="fa-solid fa-users text-[11px]"></i>
                    <span>All Team ({{ $overallStats['total_users'] }})</span>
                </a>
                <a href="{{ route('admin.reports.user-leads', ['filter_leads' => 'active']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $currFilterLeads === 'active' ? 'bg-emerald-600 text-white shadow-2xs ring-2 ring-emerald-500/20' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                    <i class="fa-solid fa-fire text-[11px] text-amber-500"></i>
                    <span>Active Leads Only ({{ $overallStats['users_with_active_leads'] }})</span>
                </a>
                <a href="{{ route('admin.reports.user-leads', ['filter_leads' => 'unreached']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $currFilterLeads === 'unreached' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200' }}">
                    <i class="fa-solid fa-phone-slash text-[11px]"></i>
                    <span>Unreached Calls</span>
                </a>
                <a href="{{ route('admin.reports.user-leads', ['role' => 'Agent']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $currRole === 'Agent' ? 'bg-purple-600 text-white shadow-2xs' : 'bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200' }}">
                    <i class="fa-solid fa-user-tie text-[11px]"></i>
                    <span>Leaders ({{ $totalLeaders }})</span>
                </a>
                <a href="{{ route('admin.reports.user-leads', ['role' => 'Sub Agent']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ $currRole === 'Sub Agent' ? 'bg-brand-600 text-white shadow-2xs' : 'bg-brand-50 hover:bg-brand-100 text-brand-800 border border-brand-200' }}">
                    <i class="fa-solid fa-id-badge text-[11px]"></i>
                    <span>Staff ({{ $totalStaff }})</span>
                </a>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                <a href="{{ route('admin.leads.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                    <i class="fa-solid fa-table-list text-slate-500 text-[11px]"></i>
                    <span>Leads Table</span>
                </a>
                <a href="{{ route('admin.reports.user-leads', array_merge(request()->query(), ['export' => 'csv'])) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition">
                    <i class="fa-solid fa-file-excel text-emerald-600 text-[11px]"></i>
                    <span>CSV</span>
                </a>
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    <i class="fa-solid fa-print text-[11px]"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>

        <!-- Filter Inputs Row -->
        <form method="GET" action="{{ route('admin.reports.user-leads') }}" class="flex flex-wrap items-center gap-2.5">
            @if(request('filter_leads'))
                <input type="hidden" name="filter_leads" value="{{ request('filter_leads') }}">
            @endif

            <!-- Search Executive -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search executive name, mobile, leader..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
            </div>

            <!-- Role Filter -->
            <select name="role" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All Roles</option>
                <option value="Agent" {{ request('role') === 'Agent' ? 'selected' : '' }}>Leaders only</option>
                <option value="Sub Agent" {{ request('role') === 'Sub Agent' ? 'selected' : '' }}>Staff only</option>
                <option value="Superadmin" {{ request('role') === 'Superadmin' ? 'selected' : '' }}>Superadmins only</option>
            </select>

            <!-- Leader Filter for Superadmin -->
            @if($isSuperAdmin && $allAgents->count() > 0)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500 font-medium">
                    <option value="">All Leaders</option>
                    @foreach($allAgents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Project Filter -->
            <select name="project_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>

            <!-- Sort By -->
            <select name="sort" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-brand-500 font-medium">
                <option value="leads_desc" {{ request('sort') === 'leads_desc' ? 'selected' : '' }}>Sort: Active Leads ↓</option>
                <option value="bookings_desc" {{ request('sort') === 'bookings_desc' ? 'selected' : '' }}>Sort: Most Bookings ↓</option>
                <option value="visits_desc" {{ request('sort') === 'visits_desc' ? 'selected' : '' }}>Sort: Most Site Visits ↓</option>
                <option value="conversion_desc" {{ request('sort') === 'conversion_desc' ? 'selected' : '' }}>Sort: Conversion Rate ↓</option>
                <option value="calls_desc" {{ request('sort') === 'calls_desc' ? 'selected' : '' }}>Sort: Calls Attempted ↓</option>
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Name (A-Z)</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['search', 'role', 'agent_id', 'project_id', 'sort', 'filter_leads']))
                <a href="{{ route('admin.reports.user-leads') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Overall Aggregate KPI Banner Cards (ALL NUMBERS ARE CLICKABLE) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- 1. Active Leads (Engaged Pipeline) -->
        <a href="{{ route('admin.leads.index', ['status' => 'active_leads']) }}" 
           class="bg-gradient-to-br from-emerald-500 to-teal-600 p-4 rounded-2xl text-white shadow-sm hover:scale-[1.02] transition-all block group"
           title="Click to view all active engaged leads">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-100">Active Leads</span>
                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs text-white">
                    <i class="fa-solid fa-fire text-amber-300"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black">{{ $overallStats['active_leads'] }}</span>
                <span class="text-[10px] text-emerald-100 underline decoration-white/40 group-hover:decoration-white font-medium">Show List →</span>
            </div>
            <span class="text-[10px] text-emerald-100 mt-1 block">Follow ups, visits & bookings</span>
        </a>

        <!-- 2. Follow Ups -->
        <a href="{{ route('admin.leads.index', ['status' => 'follow_up']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-amber-400 hover:shadow-xs transition-all block group"
           title="Click to view follow up leads">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Follow Ups</span>
                <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-amber-600">{{ $overallStats['follow_up'] }}</span>
                <span class="text-[10px] text-amber-600 opacity-0 group-hover:opacity-100 font-bold transition">View →</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">Active prospects</span>
        </a>

        <!-- 3. Call Later -->
        <a href="{{ route('admin.leads.index', ['status' => 'call_later']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-purple-400 hover:shadow-xs transition-all block group"
           title="Click to view call later leads">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-purple-800 uppercase tracking-wider">Call Later</span>
                <span class="w-6 h-6 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-calendar-day"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-purple-600">{{ $overallStats['call_later'] }}</span>
                <span class="text-[10px] text-purple-600 opacity-0 group-hover:opacity-100 font-bold transition">View →</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">Scheduled callbacks</span>
        </a>

        <!-- 4. Site Visits -->
        <a href="{{ route('admin.leads.index', ['status' => 'site_visit_done']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-indigo-400 hover:shadow-xs transition-all block group"
           title="Click to view site visits done">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-indigo-800 uppercase tracking-wider">Site Visits</span>
                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-location-dot"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-indigo-600">{{ $overallStats['site_visit_done'] }}</span>
                <span class="text-[10px] text-indigo-600 opacity-0 group-hover:opacity-100 font-bold transition">View →</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">Field visits done</span>
        </a>

        <!-- 5. Bookings -->
        <a href="{{ route('admin.leads.index', ['status' => 'booking_done']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-emerald-400 hover:shadow-xs transition-all block group"
           title="Click to view bookings">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Bookings</span>
                <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-emerald-600">{{ $overallStats['booking_done'] }}</span>
                <span class="text-[10px] text-emerald-600 opacity-0 group-hover:opacity-100 font-bold transition">View →</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">Closed deals</span>
        </a>

        <!-- 6. Calls Attempted -->
        <a href="{{ route('admin.call-histories.index') }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-cyan-400 hover:shadow-xs transition-all block group"
           title="Click to view call history logs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-cyan-800 uppercase tracking-wider">Calls Made</span>
                <span class="w-6 h-6 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-phone-volume"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-cyan-700">{{ $overallStats['total_calls'] }}</span>
                <span class="text-[10px] text-cyan-600 opacity-0 group-hover:opacity-100 font-bold transition">Logs →</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">Total calls attempted</span>
        </a>

        <!-- 7. Not Reached / Not Connecting -->
        <a href="{{ route('admin.leads.index', ['status' => 'call_not_connecting']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-rose-400 hover:shadow-xs transition-all block group"
           title="Click to view unreached calls">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Not Reached</span>
                <span class="w-6 h-6 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-phone-slash"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-rose-600">{{ $overallStats['not_connecting'] }}</span>
                <span class="text-[10px] text-rose-600 opacity-0 group-hover:opacity-100 font-bold transition">View →</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">Unconnected calls</span>
        </a>
    </div>

    <!-- User-Wise Leads Cards Grid (ALL NUMBERS ARE CLICKABLE) -->
    @if(count($userCards) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($userCards as $c)
                @php
                    $u = $c['user'];
                    $hasActiveLeads = $c['active_leads'] > 0;
                    $hasAnyLeads = $c['total_leads'] > 0;
                    $isLeader = $u->role === 'Agent';
                    $isStaff = $u->role === 'Sub Agent';
                    $isSuper = $u->role === 'Superadmin';
                    $leaderName = $isStaff ? ($u->company->name ?? 'Unassigned Leader') : null;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-brand-500/60 hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group {{ $hasActiveLeads ? 'ring-2 ring-emerald-500/20' : '' }}">
                    
                    <!-- Card Body -->
                    <div class="p-5 space-y-3.5">
                        
                        <!-- Top Row: Avatar & Profile Info (strictly size-locked 48px) -->
                        <div class="flex items-start gap-3">
                            
                            <!-- Size-Locked Avatar (Can NEVER exceed 48x48) -->
                            <div class="w-12 h-12 min-w-[48px] max-w-[48px] h-[48px] rounded-xl overflow-hidden shrink-0 border border-slate-200 bg-slate-100 relative shadow-2xs">
                                <img src="{{ $u->profile_photo_url }}" 
                                     alt="{{ $u->name }}" 
                                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&background=0D8ABC&color=fff';"
                                     class="w-full h-full object-cover">
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border border-white {{ $u->status == 1 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                            </div>

                            <!-- Executive Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <h4 class="text-sm font-extrabold text-slate-900 truncate tracking-tight group-hover:text-brand-600 transition-colors" title="{{ $u->name }}">
                                        {{ $u->name }}
                                    </h4>
                                    
                                    <!-- Clickable Active Leads Badge -->
                                    <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'active_leads']) }}" 
                                       class="text-[10px] font-black px-2 py-0.5 rounded-md transition shrink-0 flex items-center gap-1 {{ $hasActiveLeads ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500' }}"
                                       title="Click to view {{ $c['active_leads'] }} active leads">
                                        <i class="fa-solid fa-fire text-[9px] {{ $hasActiveLeads ? 'text-amber-500' : 'text-slate-400' }}"></i>
                                        <span>{{ $c['active_leads'] }} Active</span>
                                    </a>
                                </div>

                                <!-- Role & Affiliation -->
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    @if($isLeader)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200">
                                            <i class="fa-solid fa-user-tie text-[9px]"></i> Leader
                                        </span>
                                        @if($u->sub_agents_count > 0)
                                            <span class="text-[10px] font-semibold text-slate-500">
                                                ({{ $u->sub_agents_count }} staff)
                                            </span>
                                        @endif
                                    @elseif($isStaff)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-brand-50 text-brand-700 border border-brand-200">
                                            <i class="fa-solid fa-users text-[9px]"></i> Staff
                                        </span>
                                        @if($leaderName)
                                            <span class="text-[10px] text-slate-500 truncate max-w-[110px]" title="Leader: {{ $leaderName }}">
                                                under <strong>{{ $leaderName }}</strong>
                                            </span>
                                        @endif
                                    @elseif($isSuper)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-crown text-[9px]"></i> Superadmin
                                        </span>
                                    @endif
                                </div>

                                <!-- Phone & WhatsApp Action -->
                                <div class="mt-1 flex items-center gap-2 text-xs text-slate-600">
                                    @if(!empty($u->mobile))
                                        <a href="tel:{{ $u->mobile }}" class="hover:text-brand-600 font-medium flex items-center gap-1">
                                            <i class="fa-solid fa-phone text-[10px] text-slate-400"></i>
                                            <span>{{ $u->mobile }}</span>
                                        </a>
                                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $u->mobile) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 text-xs" title="Chat on WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400">No mobile</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 1: ACTIVE LEADS BANNER (CLICKABLE) -->
                        <div class="p-3 rounded-xl {{ $hasActiveLeads ? 'bg-gradient-to-r from-emerald-50/90 via-teal-50/40 to-white border border-emerald-200/80 shadow-2xs' : 'bg-slate-50 border border-slate-100' }} flex items-center justify-between">
                            <!-- Clickable Active Leads -->
                            <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'active_leads']) }}" 
                               class="flex items-center gap-2.5 group/active hover:opacity-90 transition"
                               title="Click to view {{ $c['active_leads'] }} active qualified leads">
                                <span class="w-8 h-8 rounded-lg {{ $hasActiveLeads ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-sm font-bold">
                                    <i class="fa-solid fa-fire"></i>
                                </span>
                                <div>
                                    <span class="text-[9px] font-bold uppercase tracking-wider {{ $hasActiveLeads ? 'text-emerald-800' : 'text-slate-400' }} block">
                                        Qualified Leads
                                    </span>
                                    <span class="text-base font-black text-slate-900 leading-tight group-hover/active:text-emerald-700 transition">
                                        {{ $c['active_leads'] }} Active
                                    </span>
                                </div>
                            </a>

                            <!-- Clickable Total Call Attempts -->
                            <a href="{{ route('admin.call-histories.index', ['user_id' => $u->id]) }}" 
                               class="text-right group/calls hover:opacity-90 transition"
                               title="Click to view all {{ $c['calls_count'] }} call logs for this user">
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider block">Calls Attempted</span>
                                <span class="text-xs font-black text-cyan-700 flex items-center gap-1 justify-end group-hover/calls:underline">
                                    <i class="fa-solid fa-phone-volume text-[9px]"></i> {{ $c['calls_count'] }} Calls
                                </span>
                            </a>
                        </div>

                        <!-- SECTION 2: QUALIFIED STAGES MATRIX (CLICKABLE TILES) -->
                        <div class="grid grid-cols-4 gap-1.5 text-center">
                            <!-- Follow Up (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'follow_up']) }}" 
                               class="p-2 rounded-xl bg-amber-50/80 hover:bg-amber-100 border border-amber-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['follow_up'] }} follow up leads">
                                <span class="text-[8px] font-extrabold text-amber-800 uppercase tracking-wider block truncate">Follow Up</span>
                                <span class="text-sm font-black text-amber-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['follow_up'] }}</span>
                            </a>

                            <!-- Call Later (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'call_later']) }}" 
                               class="p-2 rounded-xl bg-purple-50/80 hover:bg-purple-100 border border-purple-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['call_later'] }} call later leads">
                                <span class="text-[8px] font-extrabold text-purple-800 uppercase tracking-wider block truncate">Call Later</span>
                                <span class="text-sm font-black text-purple-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['call_later'] }}</span>
                            </a>

                            <!-- Site Visit (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'site_visit_done']) }}" 
                               class="p-2 rounded-xl bg-indigo-50/80 hover:bg-indigo-100 border border-indigo-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['site_visit_done'] }} site visits">
                                <span class="text-[8px] font-extrabold text-indigo-800 uppercase tracking-wider block truncate">Visit</span>
                                <span class="text-sm font-black text-indigo-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['site_visit_done'] }}</span>
                            </a>

                            <!-- Booking (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'booking_done']) }}" 
                               class="p-2 rounded-xl bg-emerald-50/80 hover:bg-emerald-100 border border-emerald-200/70 transition-all block group/tile"
                               title="Click to view {{ $c['booking_done'] }} bookings">
                                <span class="text-[8px] font-extrabold text-emerald-800 uppercase tracking-wider block truncate">Booking</span>
                                <span class="text-sm font-black text-emerald-700 mt-0.5 block group-hover/tile:scale-110 transition-transform">{{ $c['booking_done'] }}</span>
                            </a>
                        </div>

                        <!-- SECTION 3: CALL OUTCOME & UNCONNECTED STRIP (CLICKABLE) -->
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                            <!-- Unconnected / Not Received (Clickable) -->
                            <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'call_not_connecting']) }}" 
                               class="flex items-center gap-1.5 text-rose-700 hover:text-rose-900 font-semibold transition group/un"
                               title="Click to view {{ $c['not_connecting'] }} unreached / not connected calls">
                                <i class="fa-solid fa-phone-slash text-[10px] text-rose-500"></i>
                                <span>Not Received: <strong class="text-rose-800 group-hover/un:underline">{{ $c['not_connecting'] }}</strong></span>
                            </a>

                            <!-- Connected Outcome (Clickable) -->
                            <a href="{{ route('admin.call-histories.index', ['user_id' => $u->id]) }}" 
                               class="text-[11px] text-slate-500 hover:text-brand-600 font-semibold flex items-center gap-1 transition"
                               title="Click to view call outcome logs">
                                <span>{{ $c['connected_calls'] }} Connected</span>
                                <i class="fa-solid fa-angle-right text-[10px]"></i>
                            </a>
                        </div>

                        <!-- Pipeline Velocity Bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[10px] text-slate-500">
                                <span>Total Inquiries: <strong class="text-slate-700">{{ $c['total_leads'] }}</strong></span>
                                <span class="font-bold text-emerald-700">{{ $c['conversion_rate'] }}% Conversion</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 h-1.5 rounded-full" style="width: {{ min(100, $c['conversion_rate']) }}%"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Card Footer: Direct Drill-Down Buttons into Lists -->
                    <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center gap-2">
                        <!-- Primary Action: Show Active Leads List -->
                        <a href="{{ route('admin.leads.index', ['user_id' => $u->id, 'status' => 'active_leads']) }}" 
                           class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs font-bold transition shadow-xs group-hover:bg-slate-900">
                            <i class="fa-solid fa-fire text-amber-400 text-xs"></i>
                            <span>Active Leads ({{ $c['active_leads'] }})</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>

                        <!-- All Inquiries Link -->
                        <a href="{{ route('admin.leads.index', ['user_id' => $u->id]) }}" 
                           class="px-2.5 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold transition shadow-2xs whitespace-nowrap"
                           title="View all {{ $c['total_leads'] }} inquiries">
                            <span>All ({{ $c['total_leads'] }})</span>
                        </a>

                        <!-- Call Logs Link -->
                        <a href="{{ route('admin.call-histories.index', ['user_id' => $u->id]) }}" 
                           class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-cyan-700 hover:text-cyan-800 flex items-center justify-center text-xs transition shadow-2xs" 
                           title="View {{ $c['calls_count'] }} Call Logs">
                            <i class="fa-solid fa-phone-volume"></i>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs max-w-md mx-auto space-y-4">
            <div class="w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-2xl">
                <i class="fa-solid fa-address-card"></i>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-900">No Executives Match Filters</h3>
                <p class="text-xs text-slate-500 mt-1">Try clearing your search query, leader, or role filters to view executive lead cards.</p>
            </div>
            <a href="{{ route('admin.reports.user-leads') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Reset All Filters</span>
            </a>
        </div>
    @endif

</div>
@endsection
