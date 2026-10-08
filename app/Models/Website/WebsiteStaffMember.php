<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WebsiteStaffMember extends Model
{
    protected $table = 'website_staff_members';
    protected $fillable = ['name','position','bio','photo_path','email','order'];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn($q) => $q->orderBy('order')->orderBy('name'));
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) return null;
        if (filter_var($this->photo_path, FILTER_VALIDATE_URL)) return $this->photo_path;
        return Storage::disk('public')->url($this->photo_path);
    }
}
