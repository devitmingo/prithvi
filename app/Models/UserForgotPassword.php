<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserForgotPassword extends Model
{
    protected $fillable = ['user_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match((int)$this->status) {
            1 => 'Pending',
            2 => 'Approved / Reset',
            3 => 'Rejected',
            default => 'Unknown',
        };
    }
}
