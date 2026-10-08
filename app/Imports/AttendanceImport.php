<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AttendanceImport implements ToCollection, WithStartRow
{
    private const WEEK_COUNT = 5;
    private const WEEK_MAP = [
        'thu' => Carbon::THURSDAY,
        'sun' => Carbon::SUNDAY,
        'other' => Carbon::TUESDAY,
    ];

    public function __construct(
        private int $programId,
        private ?int $courseId = null,
        private int $periodId,
        private ?string $courseCode = null,
        private ?string $courseArea = null,
        private ?string $templateDate = null
    ) {}

    public function startRow(): int
    {
        return 7;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $rowCollection) {
                $row = $rowCollection instanceof Collection ? $rowCollection->toArray() : (array) $rowCollection;
                $rowNumber = $index + 7;
                $studentName = trim($row[0] ?? '');

                if ($studentName === '') {
                    if (! $this->hasAttendanceMarkers($row)) {
                        continue;
                    }
                }

                $student = null;
                if ($studentName !== '') {
                    $student = Student::whereHas('user', fn($query) =>
                        $query->whereRaw('LOWER(full_name) = ?', [strtolower($studentName)])
                    )->first();
                }

                if (! $student) {
                    if (! $this->hasAttendanceMarkers($row)) {
                        continue;
                    }

                    throw new \Exception("Row {$rowNumber}: Student not found for '{$studentName}'");
                }

                for ($week = 1; $week <= self::WEEK_COUNT; $week++) {
                    $startIndex = 4 + ($week - 1) * 4;
                    $dayKeys = ['thu', 'sun', 'other', 'count'];
                    foreach ($dayKeys as $offset => $dayKey) {
                        if ($dayKey === 'count') {
                            continue;
                        }

                        $cellValue = trim($row[$startIndex + $offset] ?? '');
                        if ($cellValue === '') {
                            continue;
                        }

                        $status = $this->parseStatus($cellValue);
                        if ($status === null) {
                            throw new \Exception("Row {$rowNumber}: Invalid attendance marker '{$cellValue}' in Week {$week} {$dayKey}");
                        }

                        $date = $this->getWeekDate($week, $dayKey);
                        Attendance::updateOrCreate(
                            [
                                'student_id' => $student->id,
                                'program_id' => $this->programId,
                                'course_id' => $this->courseId,
                                'academic_period_id' => $this->periodId,
                                'date' => $date,
                            ],
                            ['status' => $status]
                        );
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseStatus(string $value): ?string
    {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['✓', 'present', 'p', 'yes', 'y', '1'], true)) {
            return 'present';
        }
        if (in_array($normalized, ['x', 'absent', 'a', 'no', 'n', '0'], true)) {
            return 'absent';
        }

        return null;
    }

    private function hasAttendanceMarkers(array $row): bool
    {
        for ($week = 1; $week <= self::WEEK_COUNT; $week++) {
            $startIndex = 4 + ($week - 1) * 4;
            for ($offset = 0; $offset < 3; $offset++) {
                if ($this->parseStatus(trim($row[$startIndex + $offset] ?? '')) !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getWeekDate(int $week, string $dayKey): string
    {
        $baseDate = Carbon::parse($this->templateDate ?? now()->toDateString())->startOfWeek(Carbon::MONDAY)->addWeeks($week - 1);
        $dayOfWeek = self::WEEK_MAP[$dayKey] ?? Carbon::TUESDAY;

        return $baseDate->copy()->nextOrSame($dayOfWeek)->toDateString();
    }
}
