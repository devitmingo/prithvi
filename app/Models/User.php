<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Masters\{
    State,
    City,
    Designation
};


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'email',
        'password',
        'otp',
        'role',
        'profile_photo',
        'state_id',
        'city_id',
        'pin_code',
        'address',
        'aadhar_no',
        'aadhar_front_photo',
        'aadhar_back_photo',
        'pan_no',
        'pan_photo',

        'company_name',
        'gst_no',
        'company_logo',

        'company_id',
        'agent_code',
        'salary',
        'designation_id',
        'dob',

        'status',
        'deletion_requested_at',
        'deactivated_by_superadmin_id',
        'deactivated_agent_subagent_by_superadmin_id',
        'approved_date',
        'rejected_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function state(){
        return $this->belongsTo(State::class);
    }

    public function city(){
        return $this->belongsTo(City::class);
    }

    public function company(){
        return $this->belongsTo(User::class, 'company_id');
    }


    public function subAgents()
    {
        return $this->hasMany(User::class, 'company_id', 'id')
                    ->where('role', 'Sub Agent');
    }

    public function designation(){
        return $this->belongsTo(Designation::class);
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

    public function contacts(){
        return $this->hasMany(Contact::class);
    }

    public function contactCallHistories()
    {
        return $this->hasMany(ContactCallHistory::class, 'user_id');
    }

    public function getProfilePhotoUrlAttribute(){
        if ($this->profile_photo) {
            return asset('storage/public/uploads/users/' . $this->profile_photo);
        }
        return null;
    }

    public function getAadharFrontPhotoUrlAttribute(){
        if ($this->aadhar_front_photo) {
            return asset('storage/public/uploads/users/' . $this->aadhar_front_photo);
        }
        return null;
    }

    public function getAadharBackPhotoUrlAttribute(){
        if ($this->aadhar_back_photo) {
            return asset('storage/public/uploads/users/' . $this->aadhar_back_photo);
        }
        return null;
    }

    public function getCompanyLogoUrlAttribute(){
        if ($this->company_logo) {
            return asset('storage/public/uploads/users/' . $this->company_logo);
        }
        return null;
    }

    public function getPanPhotoUrlAttribute(){
        if ($this->pan_photo) {
            return asset('storage/public/uploads/users/' . $this->pan_photo);
        }
        return null;
    }

    public function isSuperAdmin(): bool {
        return $this->role === 'Superadmin';
    }

    public function isAgent(): bool {
        return $this->role === 'Agent';
    }

    public function isSubAgent(): bool {
        return $this->role === 'Sub Agent';
    }

    public function isLeader(): bool {
        return $this->isAgent();
    }

    public function isStaff(): bool {
        return $this->isSubAgent();
    }

    public function getRoleDisplayNameAttribute(): string {
        return match($this->role) {
            'Agent' => 'Leader',
            'Sub Agent' => 'Staff',
            'Superadmin' => 'Super Admin',
            default => (string)($this->role ?? 'User'),
        };
    }

    public function getStatusLabelAttribute(): string {
        return match((int)$this->status) {
            1 => 'Active / Approved',
            0 => 'Pending Approval',
            2 => 'Rejected',
            3 => 'Deactivated',
            default => 'Unknown',
        };
    }

    public function getStatusBadgeClassAttribute(): string {
        return match((int)$this->status) {
            1 => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            0 => 'bg-amber-100 text-amber-800 border-amber-200',
            2 => 'bg-rose-100 text-rose-800 border-rose-200',
            3 => 'bg-slate-100 text-slate-700 border-slate-300',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    /**
     * Delete user and all associated physical files and records.
     */
    public function deleteWithAllRecords(): void
    {
        // 1. Delete user photos and documents
        deleteFile($this->profile_photo, 'users/');
        deleteFile($this->aadhar_front_photo, 'users/');
        deleteFile($this->aadhar_back_photo, 'users/');
        deleteFile($this->pan_photo, 'users/');
        deleteFile($this->company_logo, 'users/');

        // 2. Delete media attached to user's attendance records
        foreach ($this->attendances as $attendance) {
            if (!empty($attendance->image)) {
                deleteFile($attendance->image, 'attendances/');
            }
        }

        // 3. Delete media attached to user's contact call histories
        foreach ($this->contactCallHistories as $history) {
            if (!empty($history->image)) {
                deleteFile($history->image, 'contact_call_histories/');
            }
        }

        // 4. Detach sub-agents if this user was an agent/company
        User::where('company_id', $this->id)->update([
            'company_id' => null,
        ]);

        // 5. Delete API tokens
        $this->tokens()->delete();

        // 6. Delete the user (Foreign keys in database cascade delete related tables)
        $this->delete();
    }
}
