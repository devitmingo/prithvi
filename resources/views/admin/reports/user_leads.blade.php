@extends('admin.layouts.app')

@section('title', 'User-wise Leads & CRM Cards')
@section('page-title', 'Executive Leads & Pipeline Cards')
@section('page-subtitle', 'Card-by-card CRM lead performance, conversion velocity, calls logged, and full inquiry history drill-down')

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
                    <span>Active with Leads ({{ $overallStats['users_with_leads'] }})</span>
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
                <option value="leads_desc" {{ request('sort') === 'leads_desc' ? 'selected' : '' }}>Sort: Most Leads ↓</option>
                <option value="bookings_desc" {{ request('sort') === 'bookings_desc' ? 'selected' : '' }}>Sort: Most Bookings ↓</option>
                <option value="visits_desc" {{ request('sort') === 'visits_desc' ? 'selected' : '' }}>Sort: Most Site Visits ↓</option>
                <option value="conversion_desc" {{ request('sort') === 'conversion_desc' ? 'selected' : '' }}>Sort: Conversion Rate ↓</option>
                <option value="calls_desc" {{ request('sort') === 'calls_desc' ? 'selected' : '' }}>Sort: Most Calls ↓</option>
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

    <!-- Overall Aggregate KPI Banner Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Executives -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Executives</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-id-badge"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-slate-900">{{ $overallStats['total_users'] }}</span>
                <span class="text-[11px] text-emerald-600 font-bold">({{ $overallStats['users_with_leads'] }} active)</span>
            </div>
        </div>

        <!-- Total Inquiries -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Leads</span>
                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 mt-2 block">{{ $overallStats['total_leads'] }}</span>
        </div>

        <!-- Follow Ups -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Follow Ups</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-amber-600 mt-2 block">{{ $overallStats['follow_up'] }}</span>
        </div>

        <!-- Site Visits -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Site Visits</span>
                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-location-dot"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-indigo-600 mt-2 block">{{ $overallStats['site_visit_done'] }}</span>
        </div>

        <!-- Bookings Closed -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Bookings</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-emerald-600 mt-2 block">{{ $overallStats['booking_done'] }}</span>
        </div>

        <!-- Total Calls Logged -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-cyan-700 uppercase tracking-wider">Calls Made</span>
                <span class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-phone-volume"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-cyan-700 mt-2 block">{{ $overallStats['total_calls'] }}</span>
        </div>
    </div>

    <!-- User-Wise Leads Cards Grid -->
    @if(count($userCards) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($userCards as $c)
                @php
                    $u = $c['user'];
                    $hasLeads = $c['total_leads'] > 0;
                    $isLeader = $u->role === 'Agent';
                    $isStaff = $u->role === 'Sub Agent';
                    $isSuper = $u->role === 'Superadmin';
                    $leaderName = $isStaff ? ($u->company->name ?? 'Unassigned Leader') : null;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-brand-500/60 hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group {{ $hasLeads ? 'ring-1 ring-emerald-500/20' : '' }}">
                    
                    <!-- Card Body -->
                    <div class="p-5 space-y-4">
                        
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
                                    
                                    @if($hasLeads)
                                        <span class="text-[10px] font-black px-1.5 py-0.2 rounded-md bg-emerald-100 text-emerald-800 shrink-0">
                                            {{ $c['total_leads'] }} Leads
                                        </span>
                                    @endif
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

                                <!-- Phone & Direct WhatsApp Action -->
                                <div class="mt-1.5 flex items-center gap-2 text-xs text-slate-600">
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

                        <!-- Highlight Leads & Calls Bar -->
                        <div class="p-3 rounded-xl {{ $hasLeads ? 'bg-gradient-to-r from-blue-50/80 to-indigo-50/40 border border-blue-100' : 'bg-slate-50 border border-slate-100' }} flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-white text-brand-600 shadow-2xs flex items-center justify-center text-xs font-bold border border-blue-100">
                                    <i class="fa-solid fa-folder-open"></i>
                                </span>
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Assigned Leads</span>
                                    <span class="text-sm font-black text-slate-900 leading-tight">{{ $c['total_leads'] }} Leads</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider block">Customer Calls</span>
                                <span class="text-xs font-extrabold text-cyan-700 flex items-center gap-1 justify-end">
                                    <i class="fa-solid fa-phone-flip text-[9px]"></i> {{ $c['calls_count'] }}
                                </span>
                            </div>
                        </div>

                        <!-- 3-Column Status Counters -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 rounded-xl bg-amber-50/80 border border-amber-200/60">
                                <span class="text-[9px] font-bold text-amber-800 uppercase tracking-wider block">Follow Up</span>
                                <span class="text-sm font-black text-amber-700 mt-0.5 block">{{ $c['follow_up'] }}</span>
                            </div>

                            <div class="p-2 rounded-xl bg-indigo-50/80 border border-indigo-200/60">
                                <span class="text-[9px] font-bold text-indigo-800 uppercase tracking-wider block">Site Visit</span>
                                <span class="text-sm font-black text-indigo-700 mt-0.5 block">{{ $c['site_visit_done'] }}</span>
                            </div>

                            <div class="p-2 rounded-xl bg-emerald-50/80 border border-emerald-200/60">
                                <span class="text-[9px] font-bold text-emerald-800 uppercase tracking-wider block">Booking</span>
                                <span class="text-sm font-black text-emerald-700 mt-0.5 block">{{ $c['booking_done'] }}</span>
                            </div>
                        </div>

                        <!-- Secondary Status & Conversion Bar -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-[10px] text-slate-500">
                                <span>Call Later: <strong class="text-slate-800">{{ $c['call_later'] }}</strong></span>
                                <span>Not Reached: <strong class="text-slate-800">{{ $c['not_connecting'] }}</strong></span>
                                <span class="font-bold text-emerald-700">{{ $c['conversion_rate'] }}% Conv.</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-600 to-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, $c['conversion_rate']) }}%"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Card Footer: Direct Drill-Down Button into Leads & CRM -->
                    <div class="p-3.5 bg-slate-50 border-t border-slate-100 flex items-center gap-2">
                        <!-- Primary Action: Leads & CRM Drilldown -->
                        <a href="{{ route('admin.leads.index', ['user_id' => $u->id]) }}" 
                           class="flex-1 inline-flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition shadow-xs group-hover:bg-brand-600">
                            <i class="fa-solid fa-users-line text-xs"></i>
                            <span>View Leads & CRM ({{ $c['total_leads'] }})</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>

                        <!-- Dossier Profile Link -->
                        <a href="{{ route('admin.users.show', $u->id) }}" 
                           class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 hover:text-slate-900 flex items-center justify-center text-xs transition shadow-2xs" 
                           title="User Profile Dossier">
                            <i class="fa-solid fa-id-card"></i>
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
