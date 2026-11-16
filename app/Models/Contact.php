<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'project_id',
        'remark',
        'date',
        'time',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function leadAssigns(){
        return $this->hasMany(LeadAssign::class);
    }

    public function contactCallHistories(){
        return $this->hasMany(ContactCallHistory::class);
    }

    public function getDateAttribute($value){
        return formatDateTodmY($value);
    }

    public function getTimeAttribute($value){
        if(!$value){
            return null;
        }
        return date('h:i A', strtotime($value));
    }
}
