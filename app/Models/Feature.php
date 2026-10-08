<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = [
        'key', 'label', 'description', 'group', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_features')->withTimestamps();
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_features')
                    ->withPivot('enabled')
                    ->withTimestamps();
    }
}
