<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['name', 'status'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
