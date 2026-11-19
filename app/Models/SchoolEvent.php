<?php

namespace App\Models;

use App\Models\Concerns\TenantAwareConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SchoolEvent extends Model
{
    use HasFactory;
    use TenantAwareConnection;

    public static array $typeColors = [
        'academic'  => '#6366f1',
        'sports'    => '#10b981',
        'cultural'  => '#f59e0b',
        'holiday'   => '#ef4444',
        'meeting'   => '#3b82f6',
        'other'     => '#64748b',
    ];

    protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
        'start_time', 'end_time', 'all_day',
        'type', 'location', 'image_path', 'is_published',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }
}
