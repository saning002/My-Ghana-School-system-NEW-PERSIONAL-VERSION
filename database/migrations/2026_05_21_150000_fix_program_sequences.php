<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Canonical sequence mapping for the default five programs.
        $mapping = [
            'Pre-College Course Program' => 1,
            'First Semester Course Program' => 2,
            'Second Semester Course Program' => 3,
            'Third Semester Course Program' => 4,
            'Third Semester Practical Course Program' => 5,
        ];

        foreach ($mapping as $name => $seq) {
            DB::table('programs')->where('name', $name)->update(['sequence' => $seq]);
        }

        // Ensure any other programs get unique increasing sequence numbers after the mapped ones.
        $mappedIds = DB::table('programs')->whereIn('name', array_keys($mapping))->pluck('id')->toArray();
        $start = max($mapping) + 1;

        $others = DB::table('programs')
            ->whereNotIn('id', $mappedIds)
            ->orderBy('id')
            ->get();

        foreach ($others as $program) {
            DB::table('programs')->where('id', $program->id)->update(['sequence' => $start]);
            $start++;
        }
    }

    public function down(): void
    {
        // Revert to the previous (incorrect) state if needed: set all sequences to 1.
        DB::table('programs')->update(['sequence' => 1]);
    }
};
