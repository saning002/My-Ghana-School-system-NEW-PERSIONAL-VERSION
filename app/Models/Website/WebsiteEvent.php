<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WebsiteEvent extends Model
{
    protected $table = 'website_events';
    protected $fillable = ['title','description','date','time','location','image_path','is_published'];
    protected $casts = ['date' => 'date', 'is_published' => 'boolean'];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn($q) => $q->orderBy('date'));
    }

    public function scopePublished($query) { return $query->where('is_published', true); }
    public function scopeUpcoming($query)  { return $query->where('date', '>=', now()->toDateString()); }
    public function scopePast($query)      { return $query->where('date', '<', now()->toDateString()); }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) return null;
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) return $this->image_path;
        return Storage::disk('public')->url($this->image_path);
    }
}
