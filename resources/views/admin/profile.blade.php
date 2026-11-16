@extends('admin.layouts.app')

@section('title', 'Account Profile')
@section('page-title', 'My Account Profile')
@section('page-subtitle', 'Manage personal information, agency branding, and account password')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Profile Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center gap-5">
        @if($user->profile_photo)
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-brand-500 shadow-md shrink-0">
        @else
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                {{ substr($user->name, 0, 2) }}
            </div>
        @endif

        <div class="text-center sm:text-left flex-1">
            <h2 class="text-xl font-extrabold text-slate-900">{{ $user->name }}</h2>
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs text-slate-500 mt-1">
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-brand-50 text-brand-700 border border-brand-200">
                    {{ $user->role }}
                </span>
                <span>•</span>
                <span>{{ $user->email }}</span>
                <span>•</span>
                <span>{{ $user->mobile }}</span>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
        @csrf

        <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-user-pen text-brand-600"></i>
            <span>Personal & Agency Details</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Phone <span class="text-rose-500">*</span></label>
                <input type="tel" name="mobile" value="{{ old('mobile', $user->mobile) }}" required pattern="[0-9]{10}" maxlength="10"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Company / Agency Name</label>
                <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">GST Number</label>
                <input type="text" name="gst_no" value="{{ old('gst_no', $user->gst_no) }}" maxlength="15"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500 uppercase">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">PIN Code</label>
                <input type="text" name="pin_code" value="{{ old('pin_code', $user->pin_code) }}" maxlength="6"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">State</label>
                <select name="state_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">-- Select State --</option>
                    @foreach($states as $st)
                        <option value="{{ $st->id }}" {{ old('state_id', $user->state_id) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">City</label>
                <select name="city_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">-- Select City --</option>
                    @foreach($cities as $ct)
                        <option value="{{ $ct->id }}" {{ old('city_id', $user->city_id) == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Physical Address</label>
                <textarea name="address" rows="2" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('address', $user->address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Update Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Update Agency Logo</label>
                <input type="file" name="company_logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
            </div>
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Save Profile Changes</span>
            </button>
        </div>
    </form>

    <!-- Change Password Form -->
    <form action="{{ route('admin.password.update') }}" method="POST" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-5">
        @csrf

        <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-lock text-brand-600"></i>
            <span>Update Password</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Current Password <span class="text-rose-500">*</span></label>
                <input type="password" name="current_password" required placeholder="••••••••"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">New Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required placeholder="Minimum 6 characters"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Confirm New Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" required placeholder="••••••••"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md shadow-slate-900/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-key"></i>
                <span>Change Password</span>
            </button>
        </div>
    </form>

</div>
@endsection
