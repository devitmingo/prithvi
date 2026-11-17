<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') - Prithvi CRM</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                            800: '#1e3a8a',
                            900: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome 6 Free Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js for lightweight UI interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CKEditor 5 Classic Build -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [x-cloak] { display: none !important; }

        /* CKEditor 5 Modern Theme Customization */
        .ck-editor { width: 100% !important; }
        .ck.ck-editor__top .ck-sticky-panel .ck-toolbar {
            border-top-left-radius: 0.75rem !important;
            border-top-right-radius: 0.75rem !important;
            border-color: #e2e8f0 !important;
            background: #f8fafc !important;
        }
        .ck.ck-content {
            border-bottom-left-radius: 0.75rem !important;
            border-bottom-right-radius: 0.75rem !important;
            border-color: #e2e8f0 !important;
            min-height: 220px;
            font-size: 0.8125rem !important;
            line-height: 1.6 !important;
            color: #1e293b !important;
        }
        .ck.ck-focused {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        .ck.ck-toolbar .ck-button {
            border-radius: 0.5rem !important;
        }
    </style>
    @yield('styles')
</head>
<body class="h-full antialiased text-slate-800" x-data="{ sidebarOpen: false, userDropdownOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-cloak 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden">
        </div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-white text-slate-700 transition-transform duration-300 ease-in-out flex flex-col border-r border-slate-200/90 shadow-sm lg:static lg:h-screen lg:sticky lg:top-0">
            
            <!-- Logo & Brand Header -->
            <div class="h-20 flex items-center justify-between px-5 bg-white border-b border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-slate-50 p-1 flex items-center justify-center shadow-xs group-hover:scale-105 transition-all overflow-hidden border border-slate-200 shrink-0">
                        <img src="{{ asset('images/prithvi-logo.png') }}" alt="Prithvi Buildcon" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-1.5 leading-snug">
                            PRITHVI <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-400 text-slate-950 font-black shadow-xs">BUILDCON</span>
                        </span>
                        <p class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider mt-0.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> CRM & Real Estate Portal
                        </p>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-700 p-1">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Nav Links List -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5 custom-scrollbar">
                
                <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Main Navigation</p>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-base {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Leads / CRM (Expandable with Project-wise Submenu) -->
                @php
                    $sidebarProjects = \App\Models\Project::withCount('contacts')->orderBy('name')->get();
                    $totalAllContacts = \App\Models\Contact::count();
                    $isLeadsActive = request()->routeIs('admin.leads.*');
                    $currentProjId = request('project_id');
                @endphp
                <div x-data="{ leadsOpen: {{ $isLeadsActive ? 'true' : 'false' }} }">
                    <button @click="leadsOpen = !leadsOpen" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ $isLeadsActive ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users-line w-5 text-center text-base {{ $isLeadsActive ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span>Leads & CRM</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $isLeadsActive ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $totalAllContacts }}
                            </span>
                            <i class="fa-solid text-[10px] font-bold text-slate-400" :class="leadsOpen ? 'fa-minus' : 'fa-plus'"></i>
                        </div>
                    </button>
                    <div x-show="leadsOpen" x-cloak class="mt-1 pl-7 pr-2 space-y-1">
                        <!-- All Leads Pipeline -->
                        <a href="{{ route('admin.leads.index') }}" 
                           class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.leads.index') && !request('project_id') ? 'text-brand-700 bg-brand-50 border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group text-[10px] text-slate-400"></i> All Inquiries
                            </span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-slate-100 text-slate-600 font-medium">
                                {{ $totalAllContacts }}
                            </span>
                        </a>

                        <!-- Project Wise Submenu Header -->
                        <div class="pt-2 pb-1 px-3 flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>By Project</span>
                            <i class="fa-solid fa-city text-[9px]"></i>
                        </div>

                        <!-- Project Specific Submenu Links -->
                        @foreach($sidebarProjects as $sp)
                            @php
                                $isThisProj = request()->routeIs('admin.leads.index') && (string)$currentProjId === (string)$sp->id;
                            @endphp
                            <a href="{{ route('admin.leads.index', ['project_id' => $sp->id]) }}" 
                               class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $isThisProj ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5 shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}"
                               title="{{ $sp->name }} ({{ $sp->contacts_count }} leads)">
                                <span class="truncate max-w-[130px] flex items-center gap-1.5">
                                    <i class="fa-solid fa-building text-[10px] {{ $isThisProj ? 'text-brand-600' : 'text-slate-400' }}"></i>
                                    {{ $sp->name }}
                                </span>
                                <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded-full {{ $sp->contacts_count > 0 ? ($isThisProj ? 'bg-brand-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') : 'bg-slate-100 text-slate-400' }}">
                                    {{ $sp->contacts_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Projects & Inventory -->
                <div x-data="{ projOpen: {{ request()->routeIs('admin.projects.*') ? 'true' : 'false' }} }">
                    <button @click="projOpen = !projOpen" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.projects.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-city w-5 text-center text-base {{ request()->routeIs('admin.projects.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span>Projects & Inventory</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ request()->routeIs('admin.projects.*') ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ \App\Models\Project::count() }}
                            </span>
                            <i class="fa-solid text-[10px] font-bold text-slate-400" :class="projOpen ? 'fa-minus' : 'fa-plus'"></i>
                        </div>
                    </button>
                    <div x-show="projOpen" x-cloak class="mt-1 pl-9 pr-2 space-y-1">
                        <a href="{{ route('admin.projects.index') }}" 
                           class="block px-3 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('admin.projects.index') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • All Projects List
                        </a>
                        <a href="{{ route('admin.projects.create') }}" 
                           class="block px-3 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('admin.projects.create') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • + Add New Project
                        </a>
                        <a href="{{ route('admin.projects.maps') }}" 
                           class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('admin.projects.maps') ? 'text-emerald-800 bg-emerald-100 border border-emerald-300' : 'text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50' }}">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-map-location-dot text-emerald-600 text-[11px]"></i> Project Map Updates
                            </span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase font-black">Shortcut</span>
                        </a>
                    </div>
                </div>

                <!-- Users / Staff Management -->
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-id-card-clip w-5 text-center text-base {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>User Management</span>
                    </div>
                    @php $pendingCnt = \App\Models\User::where('status', 0)->count(); @endphp
                    @if($pendingCnt > 0)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500 text-white shadow-xs">
                            {{ $pendingCnt }} New
                        </span>
                    @endif
                </a>

                <!-- Call History & Site Visits -->
                <a href="{{ route('admin.call-histories.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.call-histories.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-phone-volume w-5 text-center text-base {{ request()->routeIs('admin.call-histories.*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Call History & Visits</span>
                </a>

                <!-- Attendance & Salary Management -->
                <div x-data="{ attOpen: {{ request()->routeIs('admin.attendances.*') || request()->routeIs('admin.salary.*') ? 'true' : 'false' }} }">
                    <button @click="attOpen = !attOpen" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.attendances.*') || request()->routeIs('admin.salary.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-clock-rotate-left w-5 text-center text-base {{ request()->routeIs('admin.attendances.*') || request()->routeIs('admin.salary.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span>Attendance & Salary</span>
                        </div>
                        <i class="fa-solid text-[10px] font-bold text-slate-400" :class="attOpen ? 'fa-minus' : 'fa-plus'"></i>
                    </button>
                    <div x-show="attOpen" x-cloak class="mt-1 pl-9 pr-2 space-y-1">
                        <a href="{{ route('admin.attendances.index') }}" 
                           class="block px-3 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('admin.attendances.index') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Daily Punch Logs
                        </a>
                        <a href="{{ route('admin.attendances.monthly') }}" 
                           class="block px-3 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('admin.attendances.monthly') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Monthly Summary Sheet
                        </a>
                        <a href="{{ route('admin.salary.index') }}" 
                           class="block px-3 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('admin.salary.*') ? 'text-emerald-700 bg-emerald-50 font-bold border-l-2 border-emerald-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Salary & Payroll Sheet
                        </a>
                        <a href="{{ route('admin.attendances.create') }}" 
                           class="block px-3 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('admin.attendances.create') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Manual Entry
                        </a>
                    </div>
                </div>

                <!-- Offers & Promotions -->
                <a href="{{ route('admin.offers.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.offers.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-tags w-5 text-center text-base {{ request()->routeIs('admin.offers.*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Offers & Deals</span>
                </a>

                <!-- Reports Hub -->
                <div x-data="{ repOpen: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }" class="pt-2">
                    <p class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Analytics & Reports</p>
                    <button @click="repOpen = !repOpen" 
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.reports.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chart-line w-5 text-center text-base {{ request()->routeIs('admin.reports.*') ? 'text-brand-600' : 'text-slate-400' }}"></i>
                            <span>Reports Hub</span>
                        </div>
                        <i class="fa-solid text-[10px] font-bold text-slate-400" :class="repOpen ? 'fa-minus' : 'fa-plus'"></i>
                    </button>
                    <div x-show="repOpen" x-cloak class="mt-1 pl-9 pr-2 space-y-1">
                        <a href="{{ route('admin.reports.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.reports.index') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Reports Overview
                        </a>
                        <a href="{{ route('admin.reports.leads') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.reports.leads') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Leads & Pipeline Funnel
                        </a>
                        <a href="{{ route('admin.reports.agent-performance') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.reports.agent-performance') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Agent & Staff Performance
                        </a>
                        <a href="{{ route('admin.reports.attendance') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.reports.attendance') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Attendance & Punctuality
                        </a>
                        <a href="{{ route('admin.reports.site-visits') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.reports.site-visits') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Verified Site Visits
                        </a>
                        <a href="{{ route('admin.reports.projects') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.reports.projects') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • Project Demand & Sales
                        </a>
                        <a href="{{ route('admin.reports.user-growth') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.reports.user-growth') ? 'text-brand-700 bg-brand-50 font-bold border-l-2 border-brand-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            • User Growth & Demographics
                        </a>
                    </div>
                </div>

                @if(Auth::user()->isSuperAdmin())
                    <!-- Super Admin Control Center -->
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-[11px] font-bold text-amber-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-crown text-[10px]"></i>
                            <span>Super Admin Only</span>
                        </p>

                        <!-- Masters Dropdown -->
                        <div x-data="{ masterOpen: {{ request()->routeIs('admin.masters.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <button @click="masterOpen = !masterOpen" 
                                    class="w-full flex items-center justify-between px-3.5 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.masters.*') ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-database w-5 text-center text-base {{ request()->routeIs('admin.masters.*') ? 'text-amber-600' : 'text-slate-400' }}"></i>
                                    <span>Master Records</span>
                                </div>
                                <i class="fa-solid text-[10px] font-bold text-slate-400" :class="masterOpen ? 'fa-minus' : 'fa-plus'"></i>
                            </button>
                            <div x-show="masterOpen" x-cloak class="mt-1 pl-9 pr-2 space-y-1">
                                <a href="{{ route('admin.masters.states.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.masters.states.*') ? 'text-amber-800 bg-amber-100/70 font-bold border-l-2 border-amber-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                                    • States Master
                                </a>
                                <a href="{{ route('admin.masters.cities.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.masters.cities.*') ? 'text-amber-800 bg-amber-100/70 font-bold border-l-2 border-amber-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                                    • Cities Master
                                </a>
                                <a href="{{ route('admin.masters.designations.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.masters.designations.*') ? 'text-amber-800 bg-amber-100/70 font-bold border-l-2 border-amber-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                                    • Designations Master
                                </a>
                                <a href="{{ route('admin.masters.holidays.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('admin.masters.holidays.*') ? 'text-amber-800 bg-amber-100/70 font-bold border-l-2 border-amber-600 pl-2.5' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                                    • Holidays Master
                                </a>
                            </div>
                        </div>

                        <!-- Forgot Password Requests -->
                        @php $pwdReqCount = \App\Models\UserForgotPassword::where('status', 1)->count(); @endphp
                        <a href="{{ route('admin.forgot-passwords.index') }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.forgot-passwords.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-key w-5 text-center text-base {{ request()->routeIs('admin.forgot-passwords.*') ? 'text-white' : 'text-slate-400' }}"></i>
                                <span>Password Requests</span>
                            </div>
                            @if($pwdReqCount > 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-500 text-white shadow-xs animate-pulse">
                                    {{ $pwdReqCount }}
                                </span>
                            @endif
                        </a>

                        <!-- App Settings -->
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i class="fa-solid fa-sliders w-5 text-center text-base {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-slate-400' }}"></i>
                            <span>App Settings & CMS</span>
                        </a>

                        <!-- FAQs -->
                        <a href="{{ route('admin.faqs.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.faqs.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <i class="fa-solid fa-circle-question w-5 text-center text-base {{ request()->routeIs('admin.faqs.*') ? 'text-white' : 'text-slate-400' }}"></i>
                            <span>FAQs Manager</span>
                        </a>
                    </div>
                @endif
            </nav>

            <!-- Sidebar Footer & Logout -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/70">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.profile') }}" class="text-xs text-slate-600 hover:text-slate-900 flex items-center gap-2 font-medium">
                        <i class="fa-solid fa-gear text-slate-400"></i>
                        <span>Account Settings</span>
                    </a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();" class="text-xs text-rose-600 hover:text-rose-700 flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <!-- Top App Bar -->
            <header class="h-20 bg-white border-b border-slate-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-8 shadow-xs">
                
                <!-- Left: Hamburger & Breadcrumbs -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 focus:outline-none">
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">@yield('page-title', 'Overview')</h1>
                        <p class="text-xs text-slate-500 font-medium hidden sm:block">@yield('page-subtitle', 'Welcome to Prithvi CRM Administration')</p>
                    </div>
                </div>

                <!-- Right: Quick Actions & Profile Dropdown -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    
                    <!-- Quick Map Update Shortcut -->
                    <button type="button" onclick="openGlobalMapModal()" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-600/30 transition-all cursor-pointer"
                            title="Quick Update Project Layout Map">
                        <i class="fa-solid fa-map-location-dot text-xs text-amber-300"></i>
                        <span class="hidden sm:inline">Update Map</span>
                    </button>

                    <!-- Quick New Lead Button -->
                    <a href="{{ route('admin.leads.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-600/30 transition-all">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span class="hidden sm:inline">New Lead</span>
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-3 p-1 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none">
                            @if(Auth::user()->profile_photo)
                                <img src="{{ Auth::user()->profile_photo_url }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="w-9 h-9 rounded-full object-cover border border-slate-300 shadow-xs"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=1d4ed8&background=dbeafe';">
                            @else
                                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs border border-brand-200">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
                            @endif
                            <div class="text-left hidden md:block">
                                <span class="text-xs font-bold text-slate-800 block leading-snug">{{ Auth::user()->name }}</span>
                                <span class="text-[11px] text-slate-500 font-medium">{{ Auth::user()->role_display_name }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs hidden md:block"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" x-cloak 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/10 divide-y divide-slate-100 py-2 z-50">
                            
                            <div class="px-4 py-3">
                                <p class="text-xs text-slate-500 font-medium">Signed in as</p>
                                <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->email }}</p>
                                <p class="text-[11px] text-brand-600 font-semibold mt-0.5">{{ Auth::user()->mobile }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('admin.profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition-colors">
                                    <i class="fa-solid fa-user-gear w-4 text-slate-400"></i>
                                    <span>Profile & Account</span>
                                </a>
                                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition-colors">
                                    <i class="fa-solid fa-chart-pie w-4 text-slate-400"></i>
                                    <span>Reports Hub</span>
                                </a>
                            </div>

                            <div class="py-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-rose-500"></i>
                                        <span>Log Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Alert Messages -->
            <div class="px-4 sm:px-8 pt-4">
                @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-xs" role="alert">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-check text-base"></i>
                        </div>
                        <div class="text-sm font-medium flex-1">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-center gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs" role="alert">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-base"></i>
                        </div>
                        <div class="text-sm font-medium flex-1">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                        <div class="flex items-center gap-2 font-bold text-sm mb-1 text-rose-900">
                            <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                            <span>Please correct the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700 ml-5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body Slot -->
            <main class="flex-1 px-4 sm:px-8 py-6">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 bg-white py-4 px-4 sm:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <p>&copy; {{ date('Y') }} <strong>Prithvi Buildcon CRM</strong>. All rights reserved.</p>
                <div class="flex items-center gap-4 text-slate-400">
                    <span>Admin Panel v2.0</span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5 text-emerald-600 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        API Active & Stable
                    </span>
                </div>
            </footer>
        </div>
    </div>

    @yield('modals')
    @yield('scripts')

    <!-- Global Project Map Quick Update Modal -->
    @php
        $navProjectsForModal = \App\Models\Project::orderBy('name')->get(['id', 'name', 'rera_no']);
    @endphp
    <div id="globalMapModal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150" onclick="event.stopPropagation()">
            
            <!-- Modal Header -->
            <div class="px-6 py-4.5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-amber-300">
                        <i class="fa-solid fa-map-location-dot text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold leading-tight">Update Project Layout Map</h3>
                        <p class="text-[11px] text-emerald-100 font-medium">Broadcasts instantly to mobile application</p>
                    </div>
                </div>
                <button type="button" onclick="closeGlobalMapModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.projects.quick-map-update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                <!-- Select Target Project -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Select Project <span class="text-rose-500">*</span>
                    </label>
                    <select name="project_id" id="globalMapProjectId" required
                            class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-medium">
                        <option value="">-- Choose Target Project --</option>
                        @foreach($navProjectsForModal as $np)
                            <option value="{{ $np->id }}">{{ $np->name }} ({{ $np->rera_no ?: 'ID #'.$np->id }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- File Drag & Drop / Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Upload Layout Map / Availability Document <span class="text-rose-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl p-5 text-center cursor-pointer bg-slate-50 hover:bg-emerald-50/30 transition group"
                         onclick="document.getElementById('globalMapFileInput').click()">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mx-auto mb-2 group-hover:scale-110 transition">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-700" id="globalMapFileName">Click to select Map Image or PDF</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Supports JPG, PNG, WEBP, or PDF (Up to 20MB)</p>
                        <input type="file" name="availability_document" id="globalMapFileInput" class="hidden" accept=".jpg,.jpeg,.png,.webp,.pdf" required onchange="handleGlobalMapFile(this)">
                    </div>
                </div>

                <!-- Instant Sync Notice -->
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200/80 text-emerald-900 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-tower-broadcast text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                    <div>
                        <strong>Live Mobile App Broadcast:</strong> This will update the project's layout map and refresh the live timestamp on all mobile devices immediately.
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeGlobalMapModal()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Save & Broadcast Map</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function openGlobalMapModal(projectId = null) {
            const modal = document.getElementById('globalMapModal');
            if (modal) {
                if (projectId) {
                    const select = document.getElementById('globalMapProjectId');
                    if (select) select.value = projectId;
                }
                modal.classList.remove('hidden');
            }
        }

        function closeGlobalMapModal() {
            const modal = document.getElementById('globalMapModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function handleGlobalMapFile(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                document.getElementById('globalMapFileName').innerHTML = '<span class="text-emerald-700 font-bold">' + file.name + '</span> (' + sizeMb + ' MB)';
            }
        }
    </script>

    <!-- CKEditor 5 Auto-Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('textarea.ckeditor-classic, textarea[data-ckeditor="true"]').forEach(function(el) {
                if (typeof ClassicEditor !== 'undefined' && !el.classList.contains('ckeditor-initialized')) {
                    el.classList.add('ckeditor-initialized');
                    ClassicEditor
                        .create(el, {
                            toolbar: {
                                items: [
                                    'heading', '|',
                                    'bold', 'italic', '|',
                                    'bulletedList', 'numberedList', '|',
                                    'link', 'blockQuote', 'insertTable', '|',
                                    'undo', 'redo'
                                ]
                            },
                            placeholder: el.getAttribute('placeholder') || 'Enter content details here...'
                        })
                        .then(editor => {
                            el._ckeditorInstance = editor;
                            editor.model.document.on('change:data', () => {
                                el.value = editor.getData();
                            });
                            if (el.form) {
                                el.form.addEventListener('submit', () => {
                                    el.value = editor.getData();
                                });
                            }
                        })
                        .catch(err => {
                            console.error('CKEditor Init Error:', err);
                        });
                }
            });
        });
    </script>
</body>
</html>
