<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'state_id',
        'status',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
