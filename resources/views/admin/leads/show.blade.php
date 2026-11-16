@extends('admin.layouts.app')

@section('title', $lead->name . ' - Lead Profile')
@section('page-title', 'Customer Lead Details')
@section('page-subtitle', 'Track prospect timeline, log interactions, and monitor phone call frequency')

@section('content')
<div class="space-y-6" x-data="{ assignModalOpen: false, imgModalOpen: false, modalImgSrc: '', modalImgTitle: '', showProof: false }">

    <!-- Top Back / Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.leads.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Inquiries & Leads
        </a>
        <div class="flex items-center gap-2">
            <button type="button" @click="assignModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-2xs transition-colors">
                <i class="fa-solid fa-user-gear text-indigo-600"></i>
                <span>Reassign Lead</span>
            </button>
            <a href="{{ route('admin.leads.edit', $lead->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-xs font-bold text-white shadow-2xs transition-colors">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Lead</span>
            </a>
        </div>
    </div>

    <!-- Lead Profile Header Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                    {{ substr($lead->name, 0, 2) }}
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-xl font-extrabold text-slate-900">{{ $lead->name }}</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                            {{ $lead->status === 'booking_done' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                            {{ $lead->status === 'site_visit_done' ? 'bg-blue-100 text-blue-800 border border-blue-300' : '' }}
                            {{ $lead->status === 'visit_scheduled' ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : '' }}
                            {{ $lead->status === 'follow_up' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                            {{ in_array($lead->status, ['call_later', 'call_not_connecting']) ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}
                            {{ in_array($lead->status, ['interested', 'not_interested']) ? 'bg-purple-100 text-purple-800 border border-purple-300' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                        </span>

                        <!-- Total Calls Frequency Badge -->
                        @php
                            $totalCallsCount = $leadTotalCalls ?? $lead->contactCallHistories->count();
                        @endphp
                        @if($totalCallsCount > 1)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                <i class="fa-solid fa-phone-volume text-amber-600 animate-pulse text-[11px]"></i>
                                <span>Called {{ $totalCallsCount }} times</span>
                            </span>
                        @elseif($totalCallsCount == 1)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                <i class="fa-solid fa-phone text-blue-500 text-[10px]"></i>
                                <span>1 call logged</span>
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                Not called yet
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 mt-2">
                        <a href="tel:{{ $lead->mobile }}" class="font-bold text-blue-600 hover:underline flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-emerald-600"></i> {{ $lead->mobile }}
                        </a>
                        <span>•</span>
                        <span>Interested In: <strong class="text-slate-800">{{ $lead->project->name ?? 'General Inquiry' }}</strong></span>
                        <span>•</span>
                        <span>Assigned Executive: <strong class="text-slate-800">{{ $lead->user->name ?? 'Unassigned' }}</strong></span>
                        <span>•</span>
                        <span>First Inquiry: <strong>{{ $lead->date }} {{ $lead->time }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Direct Call & WhatsApp Buttons -->
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $lead->mobile);
            @endphp
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <a href="tel:{{ $lead->mobile }}" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                    <i class="fa-solid fa-phone"></i>
                    <span>Call Customer</span>
                </a>
                <a href="https://wa.me/91{{ $cleanPhone }}" target="_blank" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition">
                    <i class="fa-brands fa-whatsapp text-emerald-400 text-sm"></i>
                    <span>WhatsApp</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Timeline & Fast Activity Logger Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Interactive Timeline of Past Calls & Site Visits -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-6 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-timeline"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Interaction History & Activity Timeline</h3>
                            <p class="text-[11px] text-slate-500">Chronological feed of phone calls, remarks, and verified site visits ({{ $lead->contactCallHistories->count() }} total)</p>
                        </div>
                    </div>

                    @if($totalCallsCount > 1)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            {{ $totalCallsCount }} Calls Logged
                        </span>
                    @endif
                </div>

                <!-- Timeline Entries -->
                @php
                    $histories = $lead->contactCallHistories->sortByDesc('id')->values();
                    $totalItems = $histories->count();
                @endphp

                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($histories as $idx => $history)
                        @php
                            $callNumber = $totalItems - $idx;
                        @endphp
                        <div class="relative group">
                            
                            <!-- Timeline Dot Indicator -->
                            <div class="absolute -left-6 top-1.5 w-5 h-5 rounded-full border-2 border-white shadow-xs flex items-center justify-center text-[9px]
                                {{ $history->visited || $history->status === 'site_visit_done' ? 'bg-indigo-600 text-white ring-4 ring-indigo-50' : '' }}
                                {{ $history->status === 'booking_done' ? 'bg-emerald-600 text-white ring-4 ring-emerald-50' : '' }}
                                {{ !in_array($history->status, ['booking_done', 'site_visit_done']) && !$history->visited ? 'bg-blue-600 text-white ring-4 ring-blue-50' : '' }}">
                                <i class="fa-solid {{ $history->visited ? 'fa-location-dot' : 'fa-phone' }}"></i>
                            </div>

                            <!-- Card Body -->
                            <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 space-y-2.5 hover:bg-slate-50 transition-colors">
                                
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white">
                                            Attempt #{{ $callNumber }}
                                        </span>
                                        <span class="text-xs font-bold text-slate-900">{{ $history->user->name ?? 'Staff Executive' }}</span>
                                        <span class="text-[11px] text-slate-500">• {{ $history->date }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if($history->visited)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 flex items-center gap-1">
                                                <i class="fa-solid fa-location-dot text-[9px]"></i> Site Visited
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                                                <i class="fa-solid fa-phone text-[9px]"></i> Phone Call
                                            </span>
                                        @endif

                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                            {{ $history->status === 'booking_done' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $history->status === 'site_visit_done' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                            {{ $history->status === 'follow_up' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ in_array($history->status, ['call_later', 'call_not_connecting']) ? 'bg-slate-200 text-slate-700' : '' }}
                                            {{ in_array($history->status, ['interested', 'not_interested']) ? 'bg-purple-100 text-purple-800' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Remark Text -->
                                <p class="text-xs text-slate-700 leading-relaxed font-medium bg-white p-3 rounded-xl border border-slate-200/60">
                                    {{ $history->remark }}
                                </p>

                                <!-- Media / Geo Coordinates (if site visit recorded) -->
                                @if($history->image || ($history->latitude && $history->longitude))
                                    <div class="pt-2 border-t border-slate-200/60 flex flex-wrap items-center justify-between gap-3">
                                        @if($history->image)
                                            <div class="flex items-center gap-2">
                                                <img src="{{ $history->image_url }}" alt="Site Proof" 
                                                     @click="modalImgSrc = '{{ $history->image_url }}'; modalImgTitle = 'Site Visit Proof - {{ $lead->name }}'; imgModalOpen = true"
                                                     class="w-12 h-12 rounded-lg object-cover border border-slate-300 shadow-2xs cursor-pointer hover:opacity-90">
                                                <span class="text-[11px] text-slate-500 font-semibold cursor-pointer hover:underline"
                                                      @click="modalImgSrc = '{{ $history->image_url }}'; modalImgTitle = 'Site Visit Proof - {{ $lead->name }}'; imgModalOpen = true">
                                                    View Visit Photo
                                                </span>
                                            </div>
                                        @endif

                                        @if($history->latitude && $history->longitude)
                                            <a href="https://maps.google.com/?q={{ $history->latitude }},{{ $history->longitude }}" target="_blank" 
                                               class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                                                <i class="fa-solid fa-location-crosshairs text-rose-500"></i>
                                                <span>GPS Location ({{ $history->latitude }}, {{ $history->longitude }})</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif

                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-6 text-center">No interactions logged yet. Use the quick logger on the right to add the first discussion note.</p>
                    @endforelse
                </div>

            </div>

        </div>

        <!-- Column 3: Fast Activity Logger (Super Admin No Photo Needed) -->
        <div class="space-y-6">
            
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="pb-2 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-square-phone text-emerald-600"></i>
                        <span>Log Call / Interaction</span>
                    </h3>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        Quick Save
                    </span>
                </div>

                <p class="text-[11px] text-slate-500">
                    Record customer response and set next status. <strong>Photo and GPS are not required.</strong>
                </p>

                <form action="{{ route('admin.leads.add-call-history', $lead->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">New Pipeline Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="follow_up" {{ $lead->status === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                            <option value="site_visit_done" {{ $lead->status === 'site_visit_done' ? 'selected' : '' }}>Site Visit Completed</option>
                            <option value="booking_done" {{ $lead->status === 'booking_done' ? 'selected' : '' }}>Booking Confirmed</option>
                            <option value="visit_scheduled" {{ $lead->status === 'visit_scheduled' ? 'selected' : '' }}>Visit Scheduled</option>
                            <option value="call_later" {{ $lead->status === 'call_later' ? 'selected' : '' }}>Call Later</option>
                            <option value="call_not_connecting" {{ $lead->status === 'call_not_connecting' ? 'selected' : '' }}>Call Not Connecting</option>
                            <option value="interested" {{ $lead->status === 'interested' ? 'selected' : '' }}>Interested</option>
                            <option value="not_interested" {{ $lead->status === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Activity Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Notes & Next Action <span class="text-rose-500">*</span></label>
                        <textarea name="remark" rows="3" required placeholder="Customer feedback, requirements discussed, next follow-up date..."
                                  class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Collapsible Photo & GPS Proof (Completely Optional) -->
                    <div class="pt-1">
                        <button type="button" @click="showProof = !showProof" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-camera"></i>
                            <span x-text="showProof ? '- Hide Photo & GPS (Optional)' : '+ Attach Photo Proof or GPS (Optional)'"></span>
                        </button>

                        <div x-show="showProof" x-cloak class="mt-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Site Visit Photo Proof</label>
                                <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:border file:border-slate-300">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">GPS Lat</label>
                                    <input type="text" name="latitude" placeholder="21.25" class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">GPS Long</label>
                                    <input type="text" name="longitude" placeholder="81.63" class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span>Save Interaction Log</span>
                    </button>
                </form>
            </div>

            <!-- Lead Assignment Info -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-3 text-xs">
                <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-id-card-clip text-blue-600"></i>
                    <span>Assignment Details</span>
                </h3>

                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-400">Assigned To</span>
                    <span class="font-bold text-slate-800">{{ $lead->user->name ?? 'Unassigned' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-400">Staff Mobile</span>
                    <span class="font-bold text-slate-800">{{ $lead->user->mobile ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-400">Parent Agency</span>
                    <span class="font-bold text-slate-800">{{ $lead->user->company_name ?? ($lead->user->company->company_name ?? 'Direct Agency') }}</span>
                </div>
                <div class="pt-2">
                    <button type="button" @click="assignModalOpen = true" class="w-full py-2 px-3 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-colors">
                        Reassign to Another Agent
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal 1: Image Zoom View -->
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

    <!-- Modal 2: Reassign Lead -->
    <div x-show="assignModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="assignModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                    <span>Reassign Lead</span>
                </h3>
                <button type="button" @click="assignModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('admin.leads.assign', $lead->id) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select New Executive / Sub-Agent <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Choose Staff Member --</option>
                        @foreach($assignableUsers as $u)
                            <option value="{{ $u->id }}" {{ $lead->user_id == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }}{{ $u->company_name ? ' - ' . $u->company_name : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reassignment Instructions (Optional)</label>
                    <textarea name="remark" rows="2" placeholder="e.g. Follow up on price negotiation..." class="w-full text-xs rounded-xl border border-slate-200 p-2.5"></textarea>
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
