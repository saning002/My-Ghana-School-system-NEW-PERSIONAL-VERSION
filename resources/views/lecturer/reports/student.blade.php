@extends('layouts.app')
@section('title','Student Report Card')
@section('subtitle', $student->user->full_name . ' — ' . $program->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Report Card Preview</h2>
            <p class="text-sm text-gray-500 mt-1">Review the report card for {{ $student->user->full_name }} in {{ $program->name }}.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors">
                <i class="fas fa-print"></i> Print
            </button>
            @if(\App\Models\Setting::lecturerCan('reports'))
            <a href="{{ route('lecturer.exams.report-card', ['student_id'=>$student->id,'program_id'=>$program->id,'attempt'=>$attempt]) }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors shadow-sm">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            @endif
        </div>
    </div>

    <div class="card p-6">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <div class="rounded-3xl border border-gray-100 bg-gray-50 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400 mb-3">Student</p>
                <p class="text-lg font-semibold text-gray-900">{{ $student->user->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $student->student_id }}</p>
            </div>
            <div class="rounded-3xl border border-gray-100 bg-gray-50 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400 mb-3">Program</p>
                <p class="text-lg font-semibold text-gray-900">{{ $program->name }}</p>
                <p class="text-sm text-gray-500">{{ $program->duration }} months</p>
            </div>
            <div class="rounded-3xl border border-gray-100 bg-gray-50 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400 mb-3">Attempt</p>
                <p class="text-lg font-semibold text-gray-900">{{ $attempt }}</p>
                <p class="text-sm text-gray-500">Average: {{ number_format($overall_average, 2) }}%</p>
            </div>
        </div>

        <div class="table-wrap overflow-x-auto mb-6">
            <table class="min-w-full text-left text-sm text-gray-600 border border-gray-200 rounded-3xl overflow-hidden">
                <thead class="bg-yellow-50 text-xs uppercase tracking-[0.16em] text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Course</th>
                        <th class="px-4 py-3 text-center">Quiz</th>
                        <th class="px-4 py-3 text-center">Exam</th>
                        <th class="px-4 py-3 text-center">Aggregate</th>
                        <th class="px-4 py-3 text-center">Grade</th>
                        <th class="px-4 py-3 text-center">Position</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($courses as $row)
                        <tr>
                            <td class="px-4 py-4 font-medium text-gray-900">{{ $row['course']->name }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['quiz_score'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['exam_score'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['aggregate'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['grade'] }}</td>
                            <td class="px-4 py-4 text-center">{{ $row['position'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400">No scores available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-3xl border border-gray-100 p-5 bg-gray-50">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400 mb-4">Summary</p>
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex justify-between"><span>Overall Aggregate</span><span class="font-semibold">{{ $overall_aggregate }}</span></div>
                    <div class="flex justify-between"><span>Average</span><span class="font-semibold">{{ number_format($overall_average, 2) }}%</span></div>
                    <div class="flex justify-between"><span>Grade</span><span class="font-semibold">{{ $overall_grade }}</span></div>
                    <div class="flex justify-between"><span>Remarks</span><span class="font-semibold">{{ $remarks }}</span></div>
                </div>
            </div>
            <div class="rounded-3xl border border-gray-100 p-5 bg-gray-50">
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400 mb-4">Rank & credits</p>
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex justify-between"><span>Class Position</span><span class="font-semibold">{{ $overall_position }}</span></div>
                    <div class="flex justify-between"><span>Students</span><span class="font-semibold">{{ $total_students_in_exam }}</span></div>
                    <div class="flex justify-between"><span>SGPA</span><span class="font-semibold">{{ number_format($overall_sgpa, 2) }}</span></div>
                    <div class="flex justify-between"><span>CGPA</span><span class="font-semibold">{{ number_format($cgpa, 2) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
