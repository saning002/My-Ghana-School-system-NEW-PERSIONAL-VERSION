<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price',
        'billing_cycle', 'is_active', 'max_students', 'max_lecturers',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_active'    => 'boolean',
        'max_students' => 'integer',
        'max_lecturers'=> 'integer',
    ];

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'plan_features')->withTimestamps();
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
