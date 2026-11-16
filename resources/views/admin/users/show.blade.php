@extends('admin.layouts.app')

@section('title', 'User Dossier - ' . $user->name)
@section('page-title', 'User Profile & Documents')
@section('page-subtitle', 'Detailed record, verification documents, team affiliation, and performance')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, modalImgSrc: '', modalImgTitle: '', rejectModalOpen: false }">

    <!-- Top Navigation & Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to User Directory</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-colors">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Profile</span>
            </a>

            @if($user->id !== Auth::id())
                @if($user->status == 3)
                    <button type="button" onclick="confirmReactivateSingle()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                        <i class="fa-solid fa-user-check"></i>
                        <span>Reactivate Account</span>
                    </button>
                    <form id="single-status-form" action="{{ route('admin.users.status', $user->id) }}" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="status" value="1">
                    </form>
                @else
                    <button type="button" onclick="confirmDeactivateSingle()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition shadow-2xs">
                        <i class="fa-solid fa-user-slash"></i>
                        <span>Deactivate Account</span>
                    </button>
                    <form id="single-status-form" action="{{ route('admin.users.status', $user->id) }}" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="status" value="3">
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Header Dossier Banner -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            
            <div class="flex items-center gap-5">
                @if($user->profile_photo)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                         @click="modalImgSrc = '{{ $user->profile_photo_url }}'; modalImgTitle = 'Profile Photo: {{ $user->name }}'; modalOpen = true"
                         class="w-20 h-20 rounded-2xl object-cover border-2 border-brand-500 shadow-md cursor-pointer hover:opacity-90">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-md">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                @endif

                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-extrabold text-slate-900">{{ $user->name }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $user->status_badge_class }}">
                            {{ $user->status_label }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1.5">
                        <span class="font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-200">
                            <i class="fa-solid {{ $user->role === 'Superadmin' ? 'fa-crown text-[10px]' : ($user->role === 'Agent' ? 'fa-user-tie text-[10px]' : 'fa-users text-[10px]') }} mr-1"></i>
                            {{ $user->role_display_name }}
                        </span>
                        <span>•</span>
                        <span><i class="fa-solid fa-id-badge text-slate-400"></i> Code: <strong>{{ $user->user_id ?? $user->agent_code ?? 'N/A' }}</strong></span>
                        <span>•</span>
                        <span><i class="fa-solid fa-calendar-check text-slate-400"></i> Joined: <strong>{{ $user->approved_date ? date('d M Y', strtotime($user->approved_date)) : ($user->created_at ? $user->created_at->format('d M Y') : 'N/A') }}</strong></span>
                    </div>

                    @if($user->rejected_reason)
                        <div class="mt-2 text-xs text-rose-600 font-medium bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-lg">
                            <strong>Rejection Note:</strong> {{ $user->rejected_reason }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Status Change Controls -->
            <div class="flex flex-wrap items-center gap-2">
                @if($user->status != 1)
                    <form action="{{ route('admin.users.status', $user->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="1">
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i>
                            <span>Approve / Activate</span>
                        </button>
                    </form>
                @endif

                @if($user->status == 0)
                    <button type="button" @click="rejectModalOpen = true" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Reject</span>
                    </button>
                @endif

                @if($user->status == 1 && $user->id !== Auth::id())
                    <button type="button" onclick="confirmDeactivateSingle()" class="px-3.5 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-user-slash text-rose-600"></i>
                        <span>Deactivate Account</span>
                    </button>
                @endif
            </div>

        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-users-line"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase">Leads Handled</p>
                <p class="text-2xl font-black text-slate-900">{{ $leadsCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase">Total Attendance Punches</p>
                <p class="text-2xl font-black text-slate-900">{{ $attendancesCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-phone-volume"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase">Calls & Site Visits</p>
                <p class="text-2xl font-black text-slate-900">{{ $callsCount }}</p>
            </div>
        </div>

    </div>

    <!-- Details Grid: Personal, Agency, and Documents -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Detailed Information -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Personal & Agency Dossier -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-address-card text-brand-600"></i>
                    <span>Contact & Organizational Profile</span>
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3.5 text-xs">
                    <div>
                        <dt class="font-bold text-slate-400">Mobile Phone</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->mobile }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-400">Email Address</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-400">Designation</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->designation->name ?? 'Not Assigned' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-400">Date of Birth</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->dob ? date('d M Y', strtotime($user->dob)) : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-400">Affiliated Agency / Company</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->company_name ?? ($user->company->company_name ?? $user->company->name ?? 'Direct Agency') }}</dd>
                    </div>
                    @if($user->role === 'Sub Agent')
                        <div>
                            <dt class="font-bold text-slate-400">Reporting Leader</dt>
                            <dd class="text-slate-900 font-bold mt-0.5 flex items-center gap-2">
                                @if($user->company)
                                    <a href="{{ route('admin.users.show', $user->company->id) }}" class="text-blue-600 hover:underline flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-tie text-blue-500 text-xs"></i>
                                        <span>{{ $user->company->name }}</span>
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $user->company->agent_code ?? 'ID: ' . $user->company->id }})</span>
                                @else
                                    <span class="text-slate-400 italic">None Assigned</span>
                                @endif
                            </dd>
                        </div>
                    @elseif($user->role === 'Agent')
                        <div>
                            <dt class="font-bold text-slate-400">Team Staff Members</dt>
                            <dd class="text-slate-900 font-bold mt-0.5">
                                <a href="{{ route('admin.users.index', ['role' => 'Sub Agent', 'agent_id' => $user->id]) }}" class="text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-2.5 py-0.5 rounded-md inline-flex items-center gap-1.5 text-xs transition-colors">
                                    <i class="fa-solid fa-users text-[10px]"></i>
                                    <span>{{ $user->subAgents()->count() }} Staff Members</span>
                                </a>
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="font-bold text-slate-400">GST Registration No</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->gst_no ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-400">State & City</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->city->name ?? '' }}, {{ $user->state->name ?? '' }} (PIN: {{ $user->pin_code ?? 'N/A' }})</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-400">Aadhar / PAN Numbers</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">
                            Aadhar: {{ $user->aadhar_no ?? 'N/A' }} • PAN: {{ $user->pan_no ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-bold text-slate-400">Full Physical Address</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">{{ $user->address ?? 'Not provided' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Recent Leads Table -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-users-line text-brand-600"></i>
                        <span>Recent Assigned Leads</span>
                    </h3>
                    <a href="{{ route('admin.leads.index', ['user_id' => $user->id]) }}" class="text-xs font-bold text-brand-600 hover:underline">
                        View all leads &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 font-bold uppercase border-b border-slate-100">
                                <th class="pb-2.5">Customer</th>
                                <th class="pb-2.5">Project</th>
                                <th class="pb-2.5">Status</th>
                                <th class="pb-2.5 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentLeads as $lead)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2.5 font-bold text-slate-900">
                                        <a href="{{ route('admin.leads.show', $lead->id) }}" class="hover:text-brand-600">
                                            {{ $lead->name }}
                                        </a>
                                        <span class="block text-[11px] text-slate-500 font-normal">{{ $lead->mobile }}</span>
                                    </td>
                                    <td class="py-2.5 font-medium text-slate-700">{{ $lead->project->name ?? 'General' }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right text-slate-500">{{ $lead->date }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">No leads assigned yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Staff List (If Leader) -->
            @if($user->isAgent() && $user->subAgents->count() > 0)
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-users text-blue-600"></i>
                            <span>Team Staff Members ({{ $user->subAgents->count() }})</span>
                        </span>
                        <a href="{{ route('admin.users.index', ['agent_id' => $user->id]) }}" class="text-xs font-bold text-blue-600 hover:underline">
                            Staff Directory &rarr;
                        </a>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($user->subAgents as $sub)
                            <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    @if($sub->profile_photo)
                                        <img src="{{ $sub->profile_photo_url }}" alt="{{ $sub->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                                            {{ substr($sub->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.users.show', $sub->id) }}" class="font-bold text-slate-900 hover:text-brand-600 text-xs">
                                            {{ $sub->name }}
                                        </a>
                                        <p class="text-[10px] text-slate-500">{{ $sub->mobile }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sub->status_badge_class }}">
                                    {{ $sub->status_label }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Column 3: Uploaded Documents Verification -->
        <div class="space-y-6">
            
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-file-shield text-brand-600"></i>
                    <span>Verification Documents</span>
                </h3>

                <!-- Aadhar Front Photo -->
                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/60">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700">Aadhar Card (Front)</span>
                        @if($user->aadhar_front_photo)
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Uploaded</span>
                        @else
                            <span class="text-[10px] text-slate-400">Not Uploaded</span>
                        @endif
                    </div>
                    @if($user->aadhar_front_photo)
                        <img src="{{ $user->aadhar_front_photo_url }}" alt="Aadhar Front" 
                             @click="modalImgSrc = '{{ $user->aadhar_front_photo_url }}'; modalImgTitle = 'Aadhar Card (Front Side)'; modalOpen = true"
                             class="w-full h-32 object-cover rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition-opacity">
                    @else
                        <div class="w-full h-24 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs">
                            <i class="fa-regular fa-id-card text-2xl mr-2"></i> No file
                        </div>
                    @endif
                </div>

                <!-- Aadhar Back Photo -->
                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/60">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700">Aadhar Card (Back)</span>
                        @if($user->aadhar_back_photo)
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Uploaded</span>
                        @else
                            <span class="text-[10px] text-slate-400">Not Uploaded</span>
                        @endif
                    </div>
                    @if($user->aadhar_back_photo)
                        <img src="{{ $user->aadhar_back_photo_url }}" alt="Aadhar Back" 
                             @click="modalImgSrc = '{{ $user->aadhar_back_photo_url }}'; modalImgTitle = 'Aadhar Card (Back Side)'; modalOpen = true"
                             class="w-full h-32 object-cover rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition-opacity">
                    @else
                        <div class="w-full h-24 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs">
                            <i class="fa-regular fa-id-card text-2xl mr-2"></i> No file
                        </div>
                    @endif
                </div>

                <!-- PAN Card Photo -->
                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/60">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700">PAN Card</span>
                        @if($user->pan_photo)
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Uploaded</span>
                        @else
                            <span class="text-[10px] text-slate-400">Not Uploaded</span>
                        @endif
                    </div>
                    @if($user->pan_photo)
                        <img src="{{ $user->pan_photo_url }}" alt="PAN Card" 
                             @click="modalImgSrc = '{{ $user->pan_photo_url }}'; modalImgTitle = 'PAN Card'; modalOpen = true"
                             class="w-full h-32 object-cover rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition-opacity">
                    @else
                        <div class="w-full h-24 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs">
                            <i class="fa-regular fa-credit-card text-2xl mr-2"></i> No file
                        </div>
                    @endif
                </div>

                <!-- Company Logo -->
                @if($user->company_logo)
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/60">
                        <span class="text-xs font-bold text-slate-700 block mb-2">Agency Logo</span>
                        <img src="{{ $user->company_logo_url }}" alt="Logo" 
                             @click="modalImgSrc = '{{ $user->company_logo_url }}'; modalImgTitle = 'Agency Logo'; modalOpen = true"
                             class="w-full h-28 object-contain rounded-lg border border-slate-200 bg-white p-2 cursor-pointer hover:opacity-90">
                    </div>
                @endif

            </div>

        </div>

    </div>

    <!-- Rejection Reason Modal -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="rejectModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Reject User Application</h3>
            <p class="text-xs text-slate-500">Provide an explanation for the user on why this application is being rejected.</p>

            <form action="{{ route('admin.users.status', $user->id) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="2">
                <textarea name="rejected_reason" rows="3" required placeholder="e.g. Incomplete Aadhar card documents or invalid company registration..."
                          class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-rose-500 mb-4"></textarea>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Enlarge Modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl max-w-2xl w-full p-4 shadow-2xl space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h4 class="text-xs font-bold text-slate-900" x-text="modalImgTitle"></h4>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            <div class="max-h-[75vh] overflow-auto flex items-center justify-center bg-slate-950/5 rounded-xl p-2">
                <img :src="modalImgSrc" class="max-h-[70vh] object-contain rounded-lg">
            </div>
            <div class="text-right">
                <a :href="modalImgSrc" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 text-xs font-bold">
                    <i class="fa-solid fa-download"></i>
                    <span>Open / Download Original</span>
                </a>
            </div>
        </div>
    </div>

</div>

<script>
function confirmDeactivateSingle() {
    Swal.fire({
        title: 'Deactivate Account?',
        html: 'Are you sure you want to deactivate <b>{{ addslashes($user->name) }}</b>?<br><span class="text-xs text-slate-500 mt-2 block">They will be blocked from logging into the mobile app and CRM until reactivated.</span>',
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
            document.getElementById('single-status-form').submit();
        }
    });
}

function confirmReactivateSingle() {
    Swal.fire({
        title: 'Reactivate Account?',
        html: 'Restore full access for <b>{{ addslashes($user->name) }}</b>?<br><span class="text-xs text-slate-500 mt-2 block">The user will immediately be able to log in to the mobile app and CRM.</span>',
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
            document.getElementById('single-status-form').submit();
        }
    });
}
</script>
@endsection
