@extends('admin.layouts.app')

@section('title', 'Create User')
@section('page-title', 'Register New User / Staff')
@section('page-subtitle', 'Add an Administrator, Agency Partner, or Field Executive to the system')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to User Directory</span>
        </a>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. Account Credentials & Role -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-user-lock text-brand-600"></i>
                <span>1. Account Identity & Credentials</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Role Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">User Role <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        @if($isSuperAdmin)
                            <option value="Agent" {{ old('role') === 'Agent' ? 'selected' : '' }}>Leader (Agency Admin)</option>
                            <option value="Sub Agent" {{ old('role', 'Sub Agent') === 'Sub Agent' ? 'selected' : '' }}>Staff (Field Team Member)</option>
                            <option value="Superadmin" {{ old('role') === 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                        @else
                            <option value="Sub Agent" selected>Staff (Field Team Member)</option>
                        @endif
                    </select>
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Ramesh Sharma"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Mobile -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Number (10 digits) <span class="text-rose-500">*</span></label>
                    <input type="tel" name="mobile" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10" required placeholder="9876543210"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@prithvi.com"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Login Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Initial Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Account Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active / Approved</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="3" {{ old('status') == '3' ? 'selected' : '' }}>Deactivated</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- 2. Agency & Team Affiliation -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-building text-brand-600"></i>
                <span>2. Agency & Designation Assignment</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                @if($isSuperAdmin)
                    <!-- Parent Leader / Agency Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Assigned Leader / Agency (for Staff)</label>
                        <select name="company_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Direct / None --</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ old('company_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->name }} {{ $ag->company_name ? "({$ag->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Designation -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Designation</label>
                    <select name="designation_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select Designation --</option>
                        @foreach($designations as $d)
                            <option value="{{ $d->id }}" {{ old('designation_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Monthly Base Salary -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Monthly Base Salary (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                        <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', 0) }}" placeholder="e.g. 25000"
                               class="w-full pl-7 pr-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500 font-bold">
                    </div>
                </div>

                <!-- Company Name (For Agents) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Company / Agency Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Prithvi Realty Pvt Ltd"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- GST No -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">GST Number</label>
                    <input type="text" name="gst_no" value="{{ old('gst_no') }}" maxlength="15" placeholder="15-character GSTIN"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500 uppercase">
                </div>

                <!-- Date of Birth -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob') }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

            </div>
        </div>

        <!-- 3. Location & Address -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-brand-600"></i>
                <span>3. Location & Address</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">State</label>
                    <select name="state_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select State --</option>
                        @foreach($states as $st)
                            <option value="{{ $st->id }}" {{ old('state_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">City</label>
                    <select name="city_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select City --</option>
                        @foreach($cities as $ct)
                            <option value="{{ $ct->id }}" {{ old('city_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">PIN Code</label>
                    <input type="text" name="pin_code" value="{{ old('pin_code') }}" maxlength="6" pattern="[0-9]{6}" placeholder="492001"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Street Address</label>
                <textarea name="address" rows="2" placeholder="Building, Street, Landmark..."
                          class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('address') }}</textarea>
            </div>
        </div>

        <!-- 4. Identity Documents & Photos -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-id-card text-brand-600"></i>
                <span>4. Identification Documents & Uploads</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Aadhar Number (12 digits)</label>
                    <input type="text" name="aadhar_no" value="{{ old('aadhar_no') }}" maxlength="12" pattern="[0-9]{12}" placeholder="123456789012"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">PAN Card Number</label>
                    <input type="text" name="pan_no" value="{{ old('pan_no') }}" maxlength="10" placeholder="ABCDE1234F"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500 uppercase">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Profile Photo</label>
                    <input type="file" name="profile_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Aadhar Front Side</label>
                    <input type="file" name="aadhar_front_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Aadhar Back Side</label>
                    <input type="file" name="aadhar_back_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">PAN Card Photo</label>
                    <input type="file" name="pan_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Logo</label>
                    <input type="file" name="company_logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Save & Create User</span>
            </button>
        </div>

    </form>
</div>
@endsection
