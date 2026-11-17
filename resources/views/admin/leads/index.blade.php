@extends('admin.layouts.app')

@section('title', 'Leads & CRM')
@section('page-title', 'Customer Inquiries & Leads')
@section('page-subtitle', 'Track prospect pipeline, schedule site visits, monitor follow-ups, and track call frequencies')

@section('content')
<div class="space-y-6" x-data="{ assignModalOpen: false, assignLeadId: null, assignLeadName: '', callModalOpen: false, callLeadId: null, callLeadName: '' }">

    <!-- Active Project Specific Filter Header Banner -->
    @if(request('project_id') && ($activeProj = $projects->first(fn($p) => $p->id == request('project_id'))))
        <div class="p-4 bg-gradient-to-r from-blue-50/80 via-indigo-50/40 to-white text-slate-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs border border-blue-200/80">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid fa-city"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-extrabold text-slate-900 tracking-tight">{{ $activeProj->name }}</span>
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md bg-brand-600 text-white shadow-2xs">Project Pipeline</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-0.5">Showing inquiries, visits, and bookings exclusively for <strong class="text-slate-900">{{ $activeProj->name }}</strong> ({{ $contacts->total() }} total found)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.leads.create', ['project_id' => $activeProj->id]) }}" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>+ Add Lead for {{ $activeProj->name }}</span>
                </a>
                <a href="{{ route('admin.leads.index', array_filter(['user_id' => request('user_id')])) }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-xmark text-slate-400"></i>
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
                <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id')])) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-xmark text-slate-300"></i>
                    <span>Clear User Filter</span>
                </a>
            </div>
        </div>
    @endif

    <!-- KPI Summary Cards with Beautiful Icons & Click-to-Filter -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- All Inquiries -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id')])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ !request('status') ? 'border-blue-500 ring-2 ring-blue-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">All Inquiries</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 mt-2 block">{{ $stats['total'] }}</span>
        </a>

        <!-- Follow Ups -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'follow_up'])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'follow_up' ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Follow Ups</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-amber-600 mt-2 block">{{ $stats['follow_up'] }}</span>
        </a>

        <!-- Site Visits -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'site_visit_done'])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'site_visit_done' ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Site Visits</span>
                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-location-dot"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-indigo-600 mt-2 block">{{ $stats['site_visit_done'] }}</span>
        </a>

        <!-- Bookings Done -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'booking_done'])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'booking_done' ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Bookings</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-emerald-600 mt-2 block">{{ $stats['booking_done'] }}</span>
        </a>

        <!-- Call Later -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'call_later'])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'call_later' ? 'border-purple-500 ring-2 ring-purple-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Call Later</span>
                <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-calendar-day"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-purple-600 mt-2 block">{{ $stats['call_later'] }}</span>
        </a>

        <!-- Not Connecting -->
        <a href="{{ route('admin.leads.index', array_filter(['project_id' => request('project_id'), 'user_id' => request('user_id'), 'status' => 'call_not_connecting'])) }}" 
           class="group relative bg-white p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'call_not_connecting' ? 'border-rose-500 ring-2 ring-rose-500/20 shadow-sm' : 'border-slate-200/80 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Not Reached</span>
                <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs group-hover:bg-rose-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-phone-slash"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-rose-600 mt-2 block">{{ $stats['not_connecting'] }}</span>
        </a>
    </div>

    <!-- Filter & Action Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <form method="GET" action="{{ route('admin.leads.index') }}" class="flex-1 flex flex-wrap items-center gap-2.5">
            
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search customer, phone, remark..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Project Filter -->
            <select name="project_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
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
            <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="follow_up" {{ request('status') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                <option value="site_visit_done" {{ request('status') === 'site_visit_done' ? 'selected' : '' }}>Site Visit Done</option>
                <option value="booking_done" {{ request('status') === 'booking_done' ? 'selected' : '' }}>Booking Done</option>
                <option value="visit_scheduled" {{ request('status') === 'visit_scheduled' ? 'selected' : '' }}>Visit Scheduled</option>
                <option value="call_later" {{ request('status') === 'call_later' ? 'selected' : '' }}>Call Later</option>
                <option value="call_not_connecting" {{ request('status') === 'call_not_connecting' ? 'selected' : '' }}>Call Not Connecting</option>
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
                <a href="{{ route('admin.leads.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.user-leads') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs transition whitespace-nowrap">
                <i class="fa-solid fa-address-card text-brand-600"></i>
                <span>User-wise Cards</span>
            </a>
            <a href="{{ route('admin.leads.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-semibold shadow-sm transition-all whitespace-nowrap">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>Add New Lead</span>
            </a>
        </div>
    </div>

    <!-- Leads Table Card -->
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
                                    <a href="{{ route('admin.projects.show', $lead->project_id) }}" class="hover:text-blue-600 flex items-center gap-1.5">
                                        <i class="fa-solid fa-building text-slate-400 text-xs"></i>
                                        <span>{{ $lead->project->name }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">General Inquiry</span>
                                @endif
                            </td>

                            <!-- Assigned Executive -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $lead->user->name ?? 'Unassigned' }}</span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $lead->user->company_name ?? ($lead->user->company->company_name ?? $lead->user->role ?? '') }}
                                </span>
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

    <!-- Modal 1: Quick Log Call (Super Admin No Photo Needed) -->
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

                <!-- Collapsible Optional Site Visit Proof (Not Required for Super Admin) -->
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select New Executive / Sub-Agent <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Choose Staff Member --</option>
                        @foreach($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }}{{ $u->company_name ? ' - ' . $u->company_name : '' }})</option>
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
