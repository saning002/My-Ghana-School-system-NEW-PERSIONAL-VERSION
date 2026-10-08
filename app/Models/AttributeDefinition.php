<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeDefinition extends Model
{
    protected $fillable = ['name', 'type', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function ratings()
    {
        return $this->hasMany(StudentReportAttribute::class);
    }
}
