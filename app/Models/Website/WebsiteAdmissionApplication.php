<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteAdmissionApplication extends Model
{
    protected $table = 'website_admission_applications';

    protected $fillable = [
        'child_first_name','child_last_name','child_dob','child_gender',
        'program_applying','parent_name','parent_email','parent_phone',
        'relationship','address','previous_school','special_needs',
        'how_did_you_hear','status','notes',
    ];

    protected $casts = ['child_dob' => 'date'];

    protected static function booted(): void
    {
        static::addGlobalScope('newest', fn($q) => $q->orderByDesc('created_at'));
    }

    public function getChildFullNameAttribute(): string
    {
        return $this->child_first_name.' '.$this->child_last_name;
    }

    public function getProgramLabelAttribute(): string
    {
        return match($this->program_applying) {
            'daycare'      => 'Day Care',
            'nursery'      => 'Nursery',
            'preschool'    => 'Preschool',
            'kindergarten' => 'Kindergarten',
            default        => ucfirst($this->program_applying),
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'pending'   => ['label' => 'Pending',       'color' => 'yellow'],
            'reviewing' => ['label' => 'Under Review',  'color' => 'blue'],
            'accepted'  => ['label' => 'Accepted',      'color' => 'green'],
            'rejected'  => ['label' => 'Rejected',      'color' => 'red'],
            default     => ['label' => ucfirst($this->status), 'color' => 'gray'],
        };
    }
}
