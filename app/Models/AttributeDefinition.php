<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantAwareConnection;

class AttributeDefinition extends Model
{
    use TenantAwareConnection;
    protected $fillable = ['name', 'type', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function ratings()
    {
        return $this->hasMany(StudentReportAttribute::class);
    }
}
