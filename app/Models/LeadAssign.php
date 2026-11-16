<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAssign extends Model
{
    protected $fillable = ['contact_id', 'user_id', 'other_assign', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
