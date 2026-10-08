<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Check if the student portal is open.
     */
    public static function portalIsOpen(): bool
    {
        return (bool) static::get('portal_access', false);
    }

    /**
     * Check if the teachers portal is open.
     */
    public static function teachersPortalIsOpen(): bool
    {
        return (bool) static::get('teachers_portal_access', true);
    }

    /**
     * Check if a lecturer permission is enabled.
     *
     * Keys: lecturer_can_attendance, lecturer_can_exams,
     *       lecturer_can_reports,    lecturer_can_notifications,
     *       lecturer_can_edit_profile
     *
     * All permissions default to ON (true) so existing behaviour is
     * preserved until an admin explicitly disables one.
     */
    public static function lecturerCan(string $permission): bool
    {
        return (bool) static::get('lecturer_can_' . $permission, true);
    }
}
