<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'owner_user_id', 'tenant_id', 'action',
        'subject_type', 'subject_id',
        'old_values', 'new_values',
        'ip_address', 'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(OwnerUser::class, 'owner_user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Log an action easily from anywhere.
     */
    public static function record(
        string $action,
        string $description = '',
        ?int $tenantId = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        static::create([
            'owner_user_id' => auth('owner')->id(),
            'tenant_id'     => $tenantId,
            'action'        => $action,
            'description'   => $description,
            'old_values'    => $oldValues ?: null,
            'new_values'    => $newValues ?: null,
            'ip_address'    => request()->ip(),
        ]);
    }
}
