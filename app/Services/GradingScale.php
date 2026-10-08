<?php

namespace App\Services;

use App\Models\Setting;

class GradingScale
{
    public static function getScale(): array
    {
        return static::loadScale();
    }

    public static function loadScale(): array
    {
        $preset = Setting::get('grading_preset', 'ghana_standard');

        if ($preset === 'custom' || Setting::get('grading_scale_json')) {
            $json = Setting::get('grading_scale_json');
            if ($json) {
                try {
                    $parsed = json_decode($json, true);
                    if (is_array($parsed) && count($parsed) > 0) {
                        usort($parsed, function ($a, $b) {
                            return ($b['min'] ?? 0) <=> ($a['min'] ?? 0);
                        });
                        return $parsed;
                    }
                } catch (\Throwable $e) {
                    // fall through to default
                }
            }
        }

        // Standard Ghana GES Basic / Secondary School Grading Scale (Grades 1 to 9)
        return [
            ['grade' => '1', 'min' => 80, 'max' => 100, 'point' => 4.0, 'description' => 'HIGHEST'],
            ['grade' => '2', 'min' => 75, 'max' => 79,  'point' => 3.8, 'description' => 'HIGHER'],
            ['grade' => '3', 'min' => 70, 'max' => 74,  'point' => 3.5, 'description' => 'HIGH'],
            ['grade' => '4', 'min' => 65, 'max' => 69,  'point' => 3.0, 'description' => 'HIGH AVERAGE'],
            ['grade' => '5', 'min' => 60, 'max' => 64,  'point' => 2.5, 'description' => 'AVERAGE'],
            ['grade' => '6', 'min' => 50, 'max' => 59,  'point' => 2.0, 'description' => 'LOW AVERAGE'],
            ['grade' => '7', 'min' => 45, 'max' => 49,  'point' => 1.5, 'description' => 'LOW'],
            ['grade' => '8', 'min' => 40, 'max' => 44,  'point' => 1.0, 'description' => 'LOWER'],
            ['grade' => '9', 'min' => 0,  'max' => 39,  'point' => 0.0, 'description' => 'LOWEST'],
        ];
    }

    public static function assign(float $score): string
    {
        $scale = static::loadScale();
        foreach ($scale as $entry) {
            if (($score ?? 0) >= ($entry['min'] ?? 0)) {
                return (string) ($entry['grade'] ?? '9');
            }
        }
        return '9';
    }

    public static function description(string $grade): string
    {
        $scale = static::loadScale();
        foreach ($scale as $entry) {
            if ((string)($entry['grade'] ?? '') === (string)$grade) {
                return $entry['description'] ?? $grade;
            }
        }
        return $grade;
    }

    public static function point(string $grade): float
    {
        $scale = static::loadScale();
        foreach ($scale as $entry) {
            if ((string)($entry['grade'] ?? '') === (string)$grade) {
                return floatval($entry['point'] ?? 0.0);
            }
        }
        return 0.0;
    }

    public static function result(float $average): string
    {
        $grade = static::assign($average);
        $scale = static::loadScale();
        foreach ($scale as $entry) {
            if ((string)($entry['grade'] ?? '') === (string)$grade) {
                $min = $entry['min'] ?? 0;
                if ($min >= 50) return 'PROMOTED';
                return 'REPEATED';
            }
        }
        return ($average >= 50) ? 'PROMOTED' : 'REPEATED';
    }
}
