<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\{
    State,
    City
};


class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'short_description',
        'detailed_description',
        'state_id',
        'city_id',
        'price',
        'rera_no',
        'image',
        'document',
        'availability_document',
        'project_status',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function state(){
        return $this->belongsTo(State::class);
    }

    public function city(){
        return $this->belongsTo(City::class);
    }

    public function contacts(){
        return $this->hasMany(Contact::class);
    }

    public function offers(){
        return $this->hasMany(Offer::class);
    }

    public function getImageUrlAttribute(){
        if ($this->image) {
            return asset('storage/public/uploads/projects/images/' . $this->image);
        }
        return null;
    }

    public function getDocumentUrlAttribute(){
        if ($this->document) {
            return asset('storage/public/uploads/projects/documents/' . $this->document);
        }
        return null;
    }

    public function getavailabilityDocumentUrlAttribute(){
        if ($this->availability_document) {
            return asset('storage/public/uploads/projects/availability_documents/' . $this->availability_document);
        }
        return null;
    }

    public function getIsAvailabilityDocumentImageAttribute(): bool
    {
        if (!$this->availability_document) {
            return false;
        }
        $ext = strtolower(pathinfo($this->availability_document, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    public function getIsAvailabilityDocumentPdfAttribute(): bool
    {
        if (!$this->availability_document) {
            return false;
        }
        $ext = strtolower(pathinfo($this->availability_document, PATHINFO_EXTENSION));
        return $ext === 'pdf';
    }

    public function getMapUpdatedAtFormattedAttribute(): ?string
    {
        if (!$this->availability_document || !$this->updated_at) {
            return null;
        }
        return $this->updated_at->format('d M Y, h:i A');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->detailed_description;
    }
}
