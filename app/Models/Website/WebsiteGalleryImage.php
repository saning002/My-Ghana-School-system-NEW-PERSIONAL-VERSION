<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WebsiteGalleryImage extends Model
{
    protected $table = 'website_gallery_images';
    protected $fillable = ['title','image_path','caption','category_id'];

    protected static function booted(): void
    {
        static::addGlobalScope('newest', fn($q) => $q->orderByDesc('created_at'));
    }

    public function category()
    {
        return $this->belongsTo(WebsiteGalleryCategory::class, 'category_id');
    }

    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) return $this->image_path;
        if (str_starts_with($this->image_path, 'uploads/')) return asset($this->image_path);
        return Storage::disk('public')->url($this->image_path);
    }
}
