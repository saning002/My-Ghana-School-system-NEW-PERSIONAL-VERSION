<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'tenant_id', 'filename', 'file_path', 'file_size',
        'storage_type', 'trigger', 'status', 'error_message',
        'cloud_synced', 'cloud_synced_at', 'emailed', 'emailed_at',
    ];

    protected $casts = [
        'file_size'       => 'integer',
        'cloud_synced'    => 'boolean',
        'cloud_synced_at' => 'datetime',
        'emailed'         => 'boolean',
        'emailed_at'      => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        for ($i = 0; $bytes >= 1024 && $i < 3; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
