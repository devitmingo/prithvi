<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'image',
        'latitude',
        'longitude',
        'date',
        'time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(){
        if ($this->image) {
            return asset('storage/public/uploads/attendances/' . $this->image);
        }
        return null;
    }
}
