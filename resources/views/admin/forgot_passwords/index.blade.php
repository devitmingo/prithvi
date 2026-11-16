@extends('admin.layouts.app')

@section('title', 'Password Reset Requests')
@section('page-title', 'Password Reset Requests')
@section('page-subtitle', 'Review and process forgotten password recovery requests submitted by mobile app users')

@section('content')
<div class="space-y-6" x-data="{ resetModal: false, resetId: null, resetUserName: '', resetUserMobile: '' }">

    <!-- KPI Counters -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <a href="{{ route('admin.forgot-passwords.index', ['status' => 1]) }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between hover:border-slate-300 transition-colors {{ request('status') === '1' ? 'ring-2 ring-rose-500' : '' }}">
            <div>
                <p class="text-xs font-bold text-rose-600 uppercase">Pending Verification</p>
                <p class="text-2xl font-black text-rose-600 mt-1">{{ $pendingCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-clock"></i>
            </div>
        </a>

        <a href="{{ route('admin.forgot-passwords.index', ['status' => 2]) }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between hover:border-slate-300 transition-colors {{ request('status') === '2' ? 'ring-2 ring-emerald-500' : '' }}">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase">Reset / Approved</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $approvedCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-check"></i>
            </div>
        </a>

        <a href="{{ route('admin.forgot-passwords.index', ['status' => 3]) }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between hover:border-slate-300 transition-colors {{ request('status') === '3' ? 'ring-2 ring-slate-500' : '' }}">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase">Rejected</p>
                <p class="text-2xl font-black text-slate-700 mt-1">{{ $rejectedCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center font-bold">
                <i class="fa-solid fa-ban"></i>
            </div>
        </a>

    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">User Password Reset Applications</h3>
            @if(request('status'))
                <a href="{{ route('admin.forgot-passwords.index') }}" class="text-xs font-bold text-brand-600 hover:underline">Show All</a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4">Staff Member</th>
                        <th class="py-3.5 px-4">Role & Agency</th>
                        <th class="py-3.5 px-4">Contact Phone</th>
                        <th class="py-3.5 px-4">Request Timestamp</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            <td class="py-3.5 px-4">
                                @if($req->user)
                                    <a href="{{ route('admin.users.show', $req->user_id) }}" class="font-bold text-slate-900 hover:text-brand-600 block text-sm">
                                        {{ $req->user->name }}
                                    </a>
                                    <span class="text-[11px] text-slate-400 font-normal">Code: {{ $req->user->user_id ?? $req->user->agent_code ?? 'N/A' }}</span>
                                @else
                                    <span class="text-slate-400">User #{{ $req->user_id }}</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $req->user->designation->name ?? $req->user->role ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $req->user->company_name ?? ($req->user->company->company_name ?? 'Direct Agency') }}</span>
                            </td>

                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ $req->user->mobile ?? 'N/A' }}
                            </td>

                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $req->created_at ? $req->created_at->format('d M Y, h:i A') : 'N/A' }}
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                                    {{ $req->status == 1 ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                    {{ $req->status == 2 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : '' }}
                                    {{ $req->status == 3 ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                @if($req->status == 1)
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" 
                                                @click="resetId = {{ $req->id }}; resetUserName = '{{ addslashes($req->user->name ?? 'User') }}'; resetUserMobile = '{{ $req->user->mobile ?? '' }}'; resetModal = true"
                                                class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs">
                                            Reset Password
                                        </button>

                                        <form action="{{ route('admin.forgot-passwords.reject', $req->id) }}" method="POST" class="inline" onsubmit="return confirm('Reject this password reset request?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-semibold">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">Processed</span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">No password recovery requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Password Reset Modal -->
    <div x-show="resetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="resetModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-key text-brand-600"></i>
                    <span>Set New Password for <span x-text="resetUserName" class="text-brand-600"></span></span>
                </h3>
                <button type="button" @click="resetModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500">
                Enter a new password for user mobile <strong x-text="resetUserMobile"></strong>. The request will automatically be marked as Resolved.
            </p>

            <form :action="'{{ url('admin/forgot-passwords') }}/' + resetId + '/reset'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Password (Min 6 chars) <span class="text-rose-500">*</span></label>
                    <input type="text" name="new_password" value="123456" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Default set to '123456'. You can customize it as desired.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="resetModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs">
                        Confirm & Save Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
