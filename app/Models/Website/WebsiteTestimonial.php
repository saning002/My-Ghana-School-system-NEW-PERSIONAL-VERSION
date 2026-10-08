<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WebsiteTestimonial extends Model
{
    protected $table = 'website_testimonials';
    protected $fillable = ['parent_name','child_name','message','rating','photo_path','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) return null;
        if (filter_var($this->photo_path, FILTER_VALIDATE_URL)) return $this->photo_path;
        return Storage::disk('public')->url($this->photo_path);
    }
}
