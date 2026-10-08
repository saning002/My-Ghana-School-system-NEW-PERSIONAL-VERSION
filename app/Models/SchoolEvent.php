<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SchoolEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
        'start_time', 'end_time', 'all_day',
        'event_type', 'show_on_website', 'created_by',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'end_date'        => 'date',
        'all_day'         => 'boolean',
        'show_on_website' => 'boolean',
    ];

    public static array $typeColors = [
        'holiday'  => '#ef4444',
        'exam'     => '#8b5cf6',
        'meeting'  => '#3b82f6',
        'activity' => '#10b981',
        'term'     => '#f59e0b',
        'other'    => '#6b7280',
    ];

    public function getColorAttribute(): string
    {
        return self::$typeColors[$this->event_type] ?? '#6b7280';
    }

    /** Format for FullCalendar JSON */
    public function toCalendarArray(): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'start'           => $this->all_day
                                    ? $this->start_date->format('Y-m-d')
                                    : $this->start_date->format('Y-m-d') . ($this->start_time ? 'T'.$this->start_time : ''),
            'end'             => $this->end_date
                                    ? ($this->all_day
                                        ? $this->end_date->addDay()->format('Y-m-d')  // FullCalendar end is exclusive
                                        : $this->end_date->format('Y-m-d') . ($this->end_time ? 'T'.$this->end_time : ''))
                                    : null,
            'allDay'          => $this->all_day,
            'color'           => $this->color,
            'extendedProps'   => [
                'type'        => $this->event_type,
                'description' => $this->description,
            ],
        ];
    }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
