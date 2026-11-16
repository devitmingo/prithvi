<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactCallHistory extends Model
{
    protected $fillable = [
        'user_id',
        'contact_id',
        'date',
        'remark',
        'latitude',
        'longitude',
        'image',
        'visited',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function contact(){
        return $this->belongsTo(Contact::class);
    }

    public function getDateAttribute($value){
        return formatDateTodmY($value);
    }

    public function getImageUrlAttribute(){
        if ($this->image) {
            return asset('storage/public/uploads/visits/' . $this->image);
        }
        return null;
    }
}
