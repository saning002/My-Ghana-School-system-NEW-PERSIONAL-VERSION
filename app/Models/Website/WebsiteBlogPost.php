<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteBlogPost extends Model
{
    protected $table = 'website_blog_posts';
    protected $fillable = ['title','slug','excerpt','content','featured_image_path','published'];
    protected $casts = ['published' => 'boolean'];

    protected static function booted(): void
    {
        static::addGlobalScope('newest', fn($q) => $q->orderByDesc('created_at'));

        static::creating(function (self $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function scopePublished($query) { return $query->where('published', true); }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (! $this->featured_image_path) return null;
        if (filter_var($this->featured_image_path, FILTER_VALIDATE_URL)) return $this->featured_image_path;
        return Storage::disk('public')->url($this->featured_image_path);
    }

    public function getAutoExcerptAttribute(): string
    {
        if ($this->excerpt) return $this->excerpt;
        return Str::words(strip_tags($this->content), 25);
    }
}
