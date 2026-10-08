<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rewrite existing student IDs from KMC/PC/001 → KMC/KA/PC/001
     * by inserting the branch code after KMC/.
     *
     * Only rewrites IDs that match the old 3-segment format KMC/XX/NNN.
     * IDs already in the new 4-segment format KMC/XX/XX/NNN are skipped.
     */
    public function up(): void
    {
        $students = DB::table('students')
            ->join('church_branches', 'students.church_branch_id', '=', 'church_branches.id')
            ->select(
                'students.id',
                'students.student_id',
                'church_branches.code as branch_code',
                'church_branches.name as branch_name'
            )
            ->get();

        foreach ($students as $student) {
            $sid = $student->student_id;

            // Skip if already in new format (4 segments: KMC/XX/XX/NNN)
            if (substr_count($sid, '/') >= 3) {
                continue;
            }

            // Only reformat old-style KMC/XX/NNN (3 segments)
            if (substr_count($sid, '/') !== 2) {
                continue;
            }

            // Derive branch code
            $branchCode = $student->branch_code
                ? strtoupper($student->branch_code)
                : strtoupper(substr($student->branch_name, 0, 2));

            // Split: KMC / PC / 001  →  KMC / KA / PC / 001
            $parts   = explode('/', $sid);          // ['KMC', 'PC', '001']
            $newId   = $parts[0] . '/' . $branchCode . '/' . $parts[1] . '/' . $parts[2];

            DB::table('students')
                ->where('id', $student->id)
                ->update(['student_id' => $newId]);
        }
    }

    public function down(): void
    {
        // Reverse: KMC/KA/PC/001 → KMC/PC/001
        $students = DB::table('students')
            ->select('id', 'student_id')
            ->get();

        foreach ($students as $student) {
            $sid = $student->student_id;

            // Only reverse 4-segment IDs
            if (substr_count($sid, '/') !== 3) {
                continue;
            }

            $parts = explode('/', $sid);   // ['KMC', 'KA', 'PC', '001']
            $oldId = $parts[0] . '/' . $parts[2] . '/' . $parts[3];

            DB::table('students')
                ->where('id', $student->id)
                ->update(['student_id' => $oldId]);
        }
    }
};
