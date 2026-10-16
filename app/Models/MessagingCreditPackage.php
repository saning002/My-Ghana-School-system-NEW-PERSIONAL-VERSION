<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagingCreditPackage extends Model
{
    protected $connection = 'pgsql'; // central DB always
    protected $fillable = [
        'name', 'sms_credits', 'whatsapp_credits',
        'price', 'currency', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'sms_credits'     => 'integer',
        'whatsapp_credits'=> 'integer',
        'is_active'       => 'boolean',
    ];

    public function scopeActive($query) { return $query->where('is_active', true)->orderBy('sort_order'); }
}
