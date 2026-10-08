<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteProgram extends Model
{
    protected $table = 'website_programs';
    protected $fillable = ['name','level','age_range','description','curriculum','schedule','icon','color','order','is_active','image_path','features','highlights'];
    protected $casts = ['is_active' => 'boolean', 'features' => 'array'];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn($q) => $q->orderBy('order'));
    }

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            'daycare'      => 'Day Care',
            'nursery'      => 'Nursery',
            'preschool'    => 'Preschool',
            'kindergarten' => 'Kindergarten',
            default        => ucfirst($this->level),
        };
    }
}
