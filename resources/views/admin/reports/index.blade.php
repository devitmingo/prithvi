@extends('admin.layouts.app')

@section('title', 'Analytics & Reports Hub')
@section('page-title', 'Enterprise Reports & Analytics Hub')
@section('page-subtitle', 'Generate real-time reports, conversion funnels, staff audits, and exportable business metrics')

@section('content')
<div class="space-y-6">

    <!-- Overview Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="max-w-2xl relative z-10 space-y-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-500/20 text-brand-300 border border-brand-500/30 inline-block mb-1">
                Data Intelligence
            </span>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Central Reporting & Audit Center</h2>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                Filter by date periods, agency teams, and projects. Review pipeline conversions, sales velocity, executive productivity, and field site visits with one-click CSV and Print exports.
            </p>
        </div>

        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-white text-9xl">
            <i class="fa-solid fa-chart-pie"></i>
        </div>
    </div>

    <!-- Reports Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Report 1: Leads & Pipeline Funnel -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-filter-circle-dollar"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Leads & Pipeline Funnel</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Full conversion trajectory from first customer inquiry to site visit scheduled, completed, and booking closed. Analyze stage drop-offs.
                </p>
            </div>
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-4">
                <span class="text-xs text-slate-400 font-semibold">Interactive Funnel</span>
                <a href="{{ route('admin.reports.leads') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition-colors">
                    <span>Generate</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Report 2: Agent & Staff Performance -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Agent & Staff Performance</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Productivity matrix comparing inquiries handled, phone calls logged, verified site visits conducted, closed bookings, and conversion rates.
                </p>
            </div>
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-4">
                <span class="text-xs text-slate-400 font-semibold">Leaderboard</span>
                <a href="{{ route('admin.reports.agent-performance') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition-colors">
                    <span>Generate</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Report 3: Attendance & Staff Punctuality -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Attendance & Punctuality</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Staff regularity audit, total working days punched, clock-in timestamps, GPS location markers, and absenteeism tracking.
                </p>
            </div>
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-4">
                <span class="text-xs text-slate-400 font-semibold">Punch Analytics</span>
                <a href="{{ route('admin.reports.attendance') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition-colors">
                    <span>Generate</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Report 4: Verified Site Visits & GPS Ground Audit -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Verified Site Visits Audit</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Field verification report of all customer project visits, photographic proofs captured on-site, and GPS latitude/longitude stamps.
                </p>
            </div>
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-4">
                <span class="text-xs text-slate-400 font-semibold">Ground Audit</span>
                <a href="{{ route('admin.reports.site-visits') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition-colors">
                    <span>Generate</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Report 5: Project Inventory & Demand Sales -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xl group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-city"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Project Demand & Sales</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Project-wise inquiry volume, site visits demand, closed booking conversion rate, RERA status, and active scheme analytics.
                </p>
            </div>
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-4">
                <span class="text-xs text-slate-400 font-semibold">Inventory Stats</span>
                <a href="{{ route('admin.reports.projects') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition-colors">
                    <span>Generate</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Report 6: User Growth & Platform Demographics -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-xl group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">User Growth & Demographics</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Month-over-month partner registration velocity, role distribution (Superadmin, Agent, Sub-Agent), and regional presence.
                </p>
            </div>
            <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-4">
                <span class="text-xs text-slate-400 font-semibold">Growth Trends</span>
                <a href="{{ route('admin.reports.user-growth') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition-colors">
                    <span>Generate</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
