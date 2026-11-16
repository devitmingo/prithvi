<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'from_date',
        'to_date',
        'description'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function getFromDateAttribute($value){
        return formatDateTodmY($value);
    }

    public function getToDateAttribute($value){
        return formatDateTodmY($value);
    }
}
