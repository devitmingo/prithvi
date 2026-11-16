@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User: ' . $user->name)
@section('page-subtitle', 'Update personal details, agency settings, and security status')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brand-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to User Dossier</span>
        </a>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Account Identity -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-user-gear text-brand-600"></i>
                <span>1. Account Identity & Credentials</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                @if($isSuperAdmin)
                    <!-- Role -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">User Role</label>
                        <select name="role" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                            <option value="Agent" {{ old('role', $user->role) === 'Agent' ? 'selected' : '' }}>Leader (Agency Admin)</option>
                            <option value="Sub Agent" {{ old('role', $user->role) === 'Sub Agent' ? 'selected' : '' }}>Staff (Field Team Member)</option>
                            <option value="Superadmin" {{ old('role', $user->role) === 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                        </select>
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">User Role</label>
                        <input type="text" value="{{ $user->role_display_name }}" disabled class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-slate-100 text-slate-500">
                    </div>
                @endif

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Mobile -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mobile Number <span class="text-rose-500">*</span></label>
                    <input type="tel" name="mobile" value="{{ old('mobile', $user->mobile) }}" pattern="[0-9]{10}" maxlength="10" required
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Password (Optional change) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Reset Password (Leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="New password"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="1" {{ old('status', $user->status) == 1 ? 'selected' : '' }}>Active / Approved</option>
                        <option value="0" {{ old('status', $user->status) == 0 ? 'selected' : '' }}>Pending Approval</option>
                        <option value="2" {{ old('status', $user->status) == 2 ? 'selected' : '' }}>Rejected</option>
                        <option value="3" {{ old('status', $user->status) == 3 ? 'selected' : '' }}>Deactivated</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- 2. Agency & Designation Assignment -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-building text-brand-600"></i>
                <span>2. Agency & Designation</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                @if($isSuperAdmin)
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Assigned Leader / Agency (for Staff)</label>
                        <select name="company_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Direct / None --</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ old('company_id', $user->company_id) == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->name }} {{ $ag->company_name ? "({$ag->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Designation</label>
                    <select name="designation_id" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select Designation --</option>
                        @foreach($designations as $d)
                            <option value="{{ $d->id }}" {{ old('designation_id', $user->designation_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Monthly Base Salary -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Monthly Base Salary (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                        <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $user->salary ?? 0) }}" placeholder="e.g. 25000"
                               class="w-full pl-7 pr-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500 font-bold">
                    </div>
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
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $user->dob) }}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

            </div>
        </div>

        <!-- 3. Address & Location -->
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

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">PIN Code</label>
                    <input type="text" name="pin_code" value="{{ old('pin_code', $user->pin_code) }}" maxlength="6" pattern="[0-9]{6}"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Street Address</label>
                <textarea name="address" rows="2" class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">{{ old('address', $user->address) }}</textarea>
            </div>
        </div>

        <!-- 4. Identification Documents & Uploads -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-id-card text-brand-600"></i>
                <span>4. Identification Documents</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Aadhar Number</label>
                    <input type="text" name="aadhar_no" value="{{ old('aadhar_no', $user->aadhar_no) }}" maxlength="12"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">PAN Card Number</label>
                    <input type="text" name="pan_no" value="{{ old('pan_no', $user->pan_no) }}" maxlength="10"
                           class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-brand-500 uppercase">
                </div>
            </div>

            <!-- Uploads grid with previews -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Profile Photo -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Profile Photo</label>
                    @if($user->profile_photo)
                        <img src="{{ $user->profile_photo_url }}" alt="Profile" class="w-20 h-20 rounded-xl object-cover border border-slate-200 shadow-2xs mb-2">
                    @endif
                    <input type="file" name="profile_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- Aadhar Front -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Aadhar Front Side</label>
                    @if($user->aadhar_front_photo)
                        <img src="{{ $user->aadhar_front_photo_url }}" alt="Aadhar Front" class="w-28 h-18 rounded-xl object-cover border border-slate-200 shadow-2xs mb-2">
                    @endif
                    <input type="file" name="aadhar_front_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- Aadhar Back -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Aadhar Back Side</label>
                    @if($user->aadhar_back_photo)
                        <img src="{{ $user->aadhar_back_photo_url }}" alt="Aadhar Back" class="w-28 h-18 rounded-xl object-cover border border-slate-200 shadow-2xs mb-2">
                    @endif
                    <input type="file" name="aadhar_back_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- PAN Photo -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">PAN Card Photo</label>
                    @if($user->pan_photo)
                        <img src="{{ $user->pan_photo_url }}" alt="PAN" class="w-28 h-18 rounded-xl object-cover border border-slate-200 shadow-2xs mb-2">
                    @endif
                    <input type="file" name="pan_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <!-- Company Logo -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Company Logo</label>
                    @if($user->company_logo)
                        <img src="{{ $user->company_logo_url }}" alt="Logo" class="w-20 h-20 rounded-xl object-contain border border-slate-200 shadow-2xs mb-2 bg-slate-50 p-1">
                    @endif
                    <input type="file" name="company_logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.show', $user->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-600/30 transition-all flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Update User Profile</span>
            </button>
        </div>

    </form>
</div>
@endsection
