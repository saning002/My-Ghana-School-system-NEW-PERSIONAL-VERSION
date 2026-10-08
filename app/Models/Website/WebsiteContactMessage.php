<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteContactMessage extends Model
{
    protected $table = 'website_contact_messages';
    protected $fillable = ['name','email','phone','subject','message','is_read'];
    protected $casts = ['is_read' => 'boolean'];

    protected static function booted(): void
    {
        static::addGlobalScope('newest', fn($q) => $q->orderByDesc('created_at'));
    }

    public function scopeUnread($query) { return $query->where('is_read', false); }
}
