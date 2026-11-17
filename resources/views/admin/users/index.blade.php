@extends('admin.layouts.app')

@section('title', 'Leader & Staff Directory')
@section('page-title', 'User Directory')
@section('page-subtitle', 'Manage Superadmins, Leaders, Staff members, and team registrations')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 flex flex-wrap items-center gap-2.5">
            
            <!-- Retain active role filter if set -->
            @if(request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif
            @if(request('status') !== null)
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Search Input -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, mobile, email, code..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Leader Filter for Superadmin -->
            @if($isSuperAdmin)
                <select name="agent_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white font-medium">
                    <option value="">All Leaders</option>
                    @foreach($allAgents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>
                            {{ $ag->name }} {{ $ag->company_name ? "({$ag->company_name})" : '' }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['search', 'agent_id', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <!-- Right Buttons: Export & Create -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.users.export', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-colors">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-semibold shadow-sm transition-all whitespace-nowrap">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>Create User</span>
            </a>
        </div>
    </div>

    <!-- Status Tabs Nav (Leader & Staff) -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-xs">
        <a href="{{ route('admin.users.index') }}" 
           class="px-3.5 py-2 rounded-xl font-semibold whitespace-nowrap transition-colors {{ !request('role') && request('status') === null ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            All Users <span class="ml-1 opacity-75">({{ $counts['all'] }})</span>
        </a>
        @if($isSuperAdmin)
            <a href="{{ route('admin.users.index', ['role' => 'Agent']) }}" 
               class="px-3.5 py-2 rounded-xl font-semibold whitespace-nowrap transition-colors {{ request('role') === 'Agent' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-user-tie mr-1 text-[11px]"></i> Leaders <span class="ml-1 opacity-90">({{ $counts['agent'] }})</span>
            </a>
        @endif
        <a href="{{ route('admin.users.index', ['role' => 'Sub Agent']) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold whitespace-nowrap transition-colors {{ request('role') === 'Sub Agent' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-users mr-1 text-[11px]"></i> Staff <span class="ml-1 opacity-90">({{ $counts['sub_agent'] }})</span>
        </a>
        @if($isSuperAdmin)
            <a href="{{ route('admin.users.index', ['role' => 'Superadmin']) }}" 
               class="px-3.5 py-2 rounded-xl font-semibold whitespace-nowrap transition-colors {{ request('role') === 'Superadmin' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-crown mr-1 text-[11px]"></i> Superadmins <span class="ml-1 opacity-90">({{ $counts['superadmin'] }})</span>
            </a>
        @endif
        <a href="{{ route('admin.users.index', ['status' => 0]) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold whitespace-nowrap transition-colors {{ request('status') === '0' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
            Pending Approvals <span class="ml-1 font-bold">({{ $counts['pending'] }})</span>
        </a>
        <a href="{{ route('admin.users.index', ['status' => 3]) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold whitespace-nowrap transition-colors {{ request('status') === '3' ? 'bg-slate-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Deactivated <span class="ml-1 opacity-75">({{ $counts['deactivated'] }})</span>
        </a>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-4">User / Executive</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Leader / Designation</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Registered</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            
                            <!-- User info & avatar -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if($user->profile_photo)
                                        <img src="{{ $user->profile_photo_url }}" 
                                             alt="{{ $user->name }}" 
                                             class="w-10 h-10 rounded-full object-cover border border-slate-200 shrink-0"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=1d4ed8&background=dbeafe';">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm">
                                            {{ $user->name }}
                                        </a>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                            <a href="tel:{{ $user->mobile }}" class="hover:text-blue-600 flex items-center gap-1">
                                                <i class="fa-solid fa-phone text-[9px] text-emerald-600"></i> {{ $user->mobile }}
                                            </a>
                                            <span>•</span>
                                            <span class="font-mono text-slate-400">{{ $user->user_id ?? 'ID: ' . $user->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge (Leader & Staff) -->
                            <td class="py-3.5 px-4">
                                @if($user->role === 'Superadmin')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-crown text-[10px] text-amber-600"></i>
                                        <span>Superadmin</span>
                                    </span>
                                @elseif($user->role === 'Agent')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 shadow-2xs">
                                        <i class="fa-solid fa-user-tie text-[10px] text-blue-600"></i>
                                        <span>Leader</span>
                                    </span>
                                @elseif($user->role === 'Sub Agent')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200 shadow-2xs">
                                        <i class="fa-solid fa-users text-[10px] text-purple-600"></i>
                                        <span>Staff</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $user->role }}
                                    </span>
                                @endif
                            </td>

                            <!-- Leader Team / Designation -->
                            <td class="py-3.5 px-4">
                                @if($user->role === 'Sub Agent')
                                    <!-- Staff Member: Clearly show reporting Leader -->
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 shadow-2xs">
                                                <i class="fa-solid fa-user-tie text-[9px] text-blue-600"></i> Leader
                                            </span>
                                            @if($user->company)
                                                <a href="{{ route('admin.users.show', $user->company->id) }}" class="font-bold text-slate-900 hover:text-blue-600 transition-colors truncate max-w-[150px]" title="Leader: {{ $user->company->name }}">
                                                    {{ $user->company->name }}
                                                </a>
                                            @else
                                                <span class="text-slate-400 italic font-medium">Direct / None</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 flex items-center gap-1 truncate max-w-[190px]">
                                            <span class="font-medium text-slate-700">{{ $user->designation->name ?? 'Staff' }}</span>
                                            @if($user->company && $user->company->company_name)
                                                <span>•</span>
                                                <span class="text-slate-400 truncate" title="{{ $user->company->company_name }}">{{ $user->company->company_name }}</span>
                                            @endif
                                        </p>
                                    </div>
                                @elseif($user->role === 'Agent')
                                    <!-- Leader / Agent: Show Agency and Team size -->
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 shadow-2xs">
                                                <i class="fa-solid fa-building text-[9px] text-emerald-600"></i> Agency
                                            </span>
                                            <span class="font-bold text-slate-900 truncate max-w-[150px]" title="{{ $user->company_name ?? 'Independent Agency' }}">
                                                {{ $user->company_name ?? 'Independent Agency' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                                            <span>{{ $user->designation->name ?? 'Agency Head' }}</span>
                                            @php $staffCount = $user->sub_agents_count ?? $user->subAgents()->count(); @endphp
                                            <a href="{{ route('admin.users.index', ['role' => 'Sub Agent', 'agent_id' => $user->id]) }}" 
                                               class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-[10px] font-bold transition-colors"
                                               title="Click to view all {{ $staffCount }} staff members under this leader">
                                                <i class="fa-solid fa-users text-[8px]"></i>
                                                <span>{{ $staffCount }} Staff</span>
                                            </a>
                                        </p>
                                    </div>
                                @elseif($user->role === 'Superadmin')
                                    <!-- Super Admin -->
                                    <div class="space-y-0.5">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-300 shadow-2xs">
                                            <i class="fa-solid fa-crown text-[9px] text-amber-600"></i> Super Admin
                                        </span>
                                        <p class="text-[11px] text-slate-500 font-medium">Head Office Administration</p>
                                    </div>
                                @else
                                    <p class="font-semibold text-slate-800">{{ $user->company_name ?? '—' }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $user->designation->name ?? '—' }}</p>
                                @endif
                            </td>

                            <!-- Location -->
                            <td class="py-3.5 px-4 text-slate-600">
                                <p class="font-medium text-slate-800">{{ $user->city->name ?? '—' }}</p>
                                <p class="text-[11px] text-slate-400">{{ $user->state->name ?? '' }}</p>
                            </td>

                            <!-- Status & Quick Toggle -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $user->status_badge_class }}">
                                    {{ $user->status_label }}
                                </span>
                            </td>

                            <!-- Registered date -->
                            <td class="py-3.5 px-4 text-slate-500 text-[11px] whitespace-nowrap">
                                {{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- View Details -->
                                    <a href="{{ route('admin.users.show', $user->id) }}" 
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-800 text-slate-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                       title="View Full Dossier">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    <!-- Edit Profile -->
                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                       class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                       title="Edit Profile">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>

                                    <!-- Deactivate / Reactivate Account (Instead of Delete) -->
                                    @if(auth()->user()?->isSuperAdmin() && $user->id !== Auth::id())
                                        @if($user->status == 3)
                                            <!-- Already Deactivated: Show Reactivate -->
                                            <button type="button" 
                                                    onclick="confirmReactivate({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                                    title="Reactivate User Account">
                                                <i class="fa-solid fa-user-check text-xs"></i>
                                            </button>
                                            <form id="status-form-{{ $user->id }}" action="{{ route('admin.users.status', $user->id) }}" method="POST" class="hidden">
                                                @csrf
                                                <input type="hidden" name="status" value="1">
                                            </form>
                                        @else
                                            <!-- Active / Pending / Rejected: Show Deactivate -->
                                            <button type="button" 
                                                    onclick="confirmDeactivate({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center transition shadow-2xs" 
                                                    title="Deactivate User Account">
                                                <i class="fa-solid fa-user-slash text-xs"></i>
                                            </button>
                                            <form id="status-form-{{ $user->id }}" action="{{ route('admin.users.status', $user->id) }}" method="POST" class="hidden">
                                                @csrf
                                                <input type="hidden" name="status" value="3">
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-users text-3xl text-slate-300 mb-2 block"></i>
                                No users found matching your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<script>
function confirmDeactivate(userId, userName) {
    Swal.fire({
        title: 'Deactivate Account?',
        html: 'Are you sure you want to deactivate <b>' + userName + '</b>?<br><span class="text-xs text-slate-500 mt-2 block">They will be blocked from logging into the mobile app and CRM until reactivated.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-user-slash mr-1.5"></i> Yes, Deactivate',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-200'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('status-form-' + userId).submit();
        }
    });
}

function confirmReactivate(userId, userName) {
    Swal.fire({
        title: 'Reactivate Account?',
        html: 'Restore full access for <b>' + userName + '</b>?<br><span class="text-xs text-slate-500 mt-2 block">The user will immediately be able to log in to the mobile app and CRM.</span>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-user-check mr-1.5"></i> Yes, Reactivate',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-200'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('status-form-' + userId).submit();
        }
    });
}
</script>
@endsection
