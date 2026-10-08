<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the website_programs table with default academic programs
 * matching the school's actual offerings (pulled from the programs table
 * if it has data, otherwise uses sensible defaults).
 *
 * Also seeds academics page settings into site_settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Only seed if table is empty
        if (DB::table('website_programs')->count() > 0) {
            return;
        }

        $now      = now()->toDateTimeString();
        $programs = [];

        // Try to pull real program names from the school's programs table
        try {
            $schoolPrograms = DB::table('programs')->orderBy('sequence')->get();
            foreach ($schoolPrograms as $idx => $prog) {
                // level must not be null — use a safe 30-char slug of the name
                $level = substr(strtolower(preg_replace('/[^a-z0-9]/i', '', str_replace(' ', '', $prog->name))), 0, 30);
                if (empty($level)) $level = 'program' . ($idx + 1);
                $programs[] = [
                    'name'        => $prog->name,
                    'level'       => $level,
                    'age_range'   => 'All Ages',
                    'description' => $prog->name . ' — a comprehensive program rooted in academic excellence and Kingdom values.',
                    'curriculum'  => 'Covers biblical studies, academic subjects and practical skills aligned with Kingdom values.',
                    'schedule'    => 'Monday – Friday, 7:30 AM – 3:30 PM. Evening classes available on request.',
                    'icon'        => '🎓',
                    'color'       => 'indigo',
                    'order'       => $idx + 1,
                    'is_active'   => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        } catch (\Throwable $e) {
            // programs table may not exist — use defaults
        }

        // Fallback defaults if no school programs found
        if (empty($programs)) {
            $defaults = [
                [
                    'name'        => 'Day Care',
                    'level'       => 'daycare',
                    'age_range'   => 'Ages 1 – 2',
                    'description' => 'A safe, nurturing environment for our youngest learners. We provide structured play, early sensory activities, and consistent care routines.',
                    'curriculum'  => 'Sensory play, music & movement, story time, social skill development, and age-appropriate exploration activities.',
                    'schedule'    => 'Monday – Friday, 7:00 AM – 5:00 PM. Extended care available.',
                    'icon'        => '👶',
                    'color'       => 'rose',
                    'order'       => 1,
                ],
                [
                    'name'        => 'Nursery',
                    'level'       => 'nursery',
                    'age_range'   => 'Ages 2 – 3',
                    'description' => 'Building foundations through play-based learning, language development, and social interactions in a warm, supportive setting.',
                    'curriculum'  => 'Early literacy, numeracy concepts, arts & crafts, outdoor play, and language-rich storytelling sessions.',
                    'schedule'    => 'Monday – Friday, 7:30 AM – 4:30 PM. Half-day options available.',
                    'icon'        => '🌱',
                    'color'       => 'emerald',
                    'order'       => 2,
                ],
                [
                    'name'        => 'Preschool',
                    'level'       => 'preschool',
                    'age_range'   => 'Ages 3 – 4',
                    'description' => 'Preparing curious minds with foundational academic concepts, creative expression, and social-emotional development.',
                    'curriculum'  => 'Pre-reading, pre-writing, basic mathematics, science exploration, music, physical education, and problem-solving activities.',
                    'schedule'    => 'Monday – Friday, 7:30 AM – 3:30 PM.',
                    'icon'        => '📚',
                    'color'       => 'blue',
                    'order'       => 3,
                ],
                [
                    'name'        => 'Kindergarten',
                    'level'       => 'kindergarten',
                    'age_range'   => 'Ages 4 – 6',
                    'description' => 'Our flagship program develops confident, capable learners through a rich blend of structured academics and child-led discovery.',
                    'curriculum'  => 'Reading, writing, mathematics, science, social studies, ICT basics, physical education, arts and values education.',
                    'schedule'    => 'Monday – Friday, 7:30 AM – 3:00 PM. Saturday enrichment available.',
                    'icon'        => '🎒',
                    'color'       => 'indigo',
                    'order'       => 4,
                ],
            ];
            foreach ($defaults as $d) {
                $programs[] = array_merge($d, [
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('website_programs')->insert($programs);

        // Also update site_settings academics text if it exists and is empty
        try {
            $site = DB::table('site_settings')->first();
            if ($site) {
                $updates = [];
                if (empty($site->academics_hero_title ?? null)) {
                    $updates['academics_hero_title'] = 'Our <em>Academic Programs</em>';
                }
                if (empty($site->academics_hero_subtitle ?? null)) {
                    $updates['academics_hero_subtitle'] = 'Carefully designed programs for every stage of learning, rooted in Kingdom values and academic excellence.';
                }
                if (empty($site->academics_eyebrow ?? null)) {
                    $updates['academics_eyebrow'] = 'What We Offer';
                }
                if (empty($site->academics_section_title ?? null)) {
                    $updates['academics_section_title'] = 'Programs for Every Learner';
                }
                if (empty($site->academics_section_subtitle ?? null)) {
                    $updates['academics_section_subtitle'] = 'From nursery to kindergarten — every program is thoughtfully designed to develop the whole child.';
                }
                if (! isset($site->report_card_orientation)) {
                    $updates['report_card_orientation'] = 'landscape';
                }
                if (! empty($updates)) {
                    DB::table('site_settings')->where('id', $site->id)->update($updates);
                }
            }
        } catch (\Throwable $e) {
            // column may not exist yet — handled by migration 2026_09_06_000001
        }
    }

    public function down(): void
    {
        DB::table('website_programs')->delete();
    }
};
