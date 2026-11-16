<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'user_id',
        'theme',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
