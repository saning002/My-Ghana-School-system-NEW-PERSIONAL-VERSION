@extends('layouts.app')
@section('title', 'Report Card')
@section('subtitle', $student->user->full_name . ' — ' . $program->name)

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ── Action Bar ── --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <a href="{{ route('admin.exams.pdf', [$student, $program]) }}?attempt={{ $currentAttempt }}" target="_blank"
           class="btn-gold inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('admin.exams.excel', [$student, $program]) }}?attempt={{ $currentAttempt }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border-2 transition-colors"
           style="border-color:#D4A017;color:#78520a;background:#fef9c3">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <button onclick="window.print()"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-colors">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.exams.create', ['program_id' => $program->id, 'student_id' => $student->id, 'attempt' => $currentAttempt]) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-yellow-200 text-gray-700 rounded-xl text-sm font-semibold transition-colors hover:bg-yellow-50">
            <i class="fas fa-pen" style="color:#78520a"></i> Edit Scores
        </a>
        <a href="{{ route('admin.exams.index') }}" class="ml-auto text-sm text-gray-500 hover:text-gray-700 font-medium">← Back</a>
    </div>

    {{-- ── Attempt Tabs ── --}}
    @if($attempts->count() > 1)
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="text-xs font-semibold text-gray-500 mr-1">Attempt:</span>
        @foreach($attempts as $att)
            <a href="{{ route('admin.exams.show', [$student, $program]) }}?attempt={{ $att }}"
               class="px-4 py-1.5 rounded-full text-xs font-bold border transition-colors
                      {{ $att == $currentAttempt ? 'border-amber-500 text-amber-800' : 'border-gray-200 text-gray-500 bg-white hover:bg-yellow-50' }}"
               style="{{ $att == $currentAttempt ? 'background:#fde68a' : '' }}">
                Attempt {{ $att }}
            </a>
        @endforeach
        @php $newAttempt = $attempts->max() + 1; @endphp
        <a href="{{ route('admin.exams.create', ['program_id' => $program->id, 'student_id' => $student->id, 'attempt' => $newAttempt]) }}"
           class="ml-2 px-4 py-1.5 rounded-full text-xs font-bold border border-dashed border-amber-400 text-amber-700 bg-white hover:bg-yellow-50 transition-colors">
            <i class="fas fa-plus mr-1"></i> New Attempt
        </a>
    </div>
    @endif

    @if(!$has_scores)
    <div class="card p-12 text-center">
        <i class="fas fa-file-alt text-gray-200 text-5xl mb-4 block"></i>
        <p class="text-gray-500 font-semibold">No scores recorded yet.</p>
        <p class="text-gray-400 text-sm mt-1">Enter exam scores for this student and program first.</p>
        <a href="{{ route('admin.exams.create') }}?program_id={{ $program->id }}"
           class="btn-gold inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl text-sm font-semibold">
            <i class="fas fa-pen"></i> Enter Scores
        </a>
    </div>
    @else

    {{-- ══════════════════════════════════════════════════════════════
         REPORT CARD — physical card replica
    ══════════════════════════════════════════════════════════════ --}}
    <div id="report-card"
         class="overflow-hidden rounded-lg shadow-xl print:shadow-none print:rounded-none"
         style="background:#FFD700;border:2.5px solid #000;font-family:Arial,sans-serif">

        {{-- ── TOP HEADER ── --}}
        <div style="padding:16px 20px 0">
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="width:90px;vertical-align:middle;text-align:center;padding-right:12px">
                            <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'School Logo' }}"
                                 style="width:82px;height:82px;border-radius:50%;background:#000;padding:3px;object-fit:contain;display:block;margin:0 auto">
                        </td>
                        <td style="vertical-align:middle;text-align:center">
                            <div style="font-size:17px;font-weight:900;text-transform:uppercase;letter-spacing:0.5px;text-decoration:underline;margin-bottom:3px;color:#000;line-height:1.2">
                                {{ $schoolName ?? 'School Name' }}
                        </div>
                        <div style="font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;color:#000">
                            {{ $schoolSubtitle ?? '' }}
                        </div>
                        <div style="font-size:11px;font-weight:600;color:#000;margin-bottom:3px">
                            {{ $schoolSubtitle ? '(' . $schoolSubtitle . ')' : '' }}
                        </div>
                        <div style="font-size:11px;font-weight:700;color:#000;margin-bottom:2px">
                            Regional Campus: {{ strtoupper($student->churchBranch?->name ?? 'HO- BRANCH-V/R') }}, Ghana
                        </div>
                        <div style="font-size:10px;font-weight:500;color:#000">
                            {{ $siteContactEmail ?? 'contact@school.edu' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ── THICK DIVIDER ── --}}
        <div style="border-top:3px solid #000;margin:12px 20px 0"></div>

        {{-- ── TRANSCRIPT TITLE ── --}}
        <div style="padding:8px 20px 0;text-align:center">
            <div style="font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:0.5px;color:#000;margin-bottom:2px">
                Academic Transcript Result Sheet
            </div>
            <div style="font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:0.3px;color:#000;margin-bottom:5px">
                Certificate Semester Examination
            </div>
            <div style="font-size:14px;font-weight:900;text-transform:uppercase;letter-spacing:0.8px;color:#7B3F00;margin-bottom:10px">
                {{ strtoupper($program->name) }}
            </div>
        </div>

        {{-- ── STUDENT INFO ── --}}
        <div style="padding:0 20px 8px">
            <table style="width:100%;border-collapse:collapse;margin-bottom:4px">
                <tr>
                    <td style="padding:3px 8px 3px 0;font-size:12px;font-weight:700;white-space:nowrap;vertical-align:bottom;width:50%">
                        <span style="font-weight:900;text-transform:uppercase">Name: </span>
                        <span style="display:inline-block;border-bottom:1.5px solid #000;min-width:200px;padding:0 4px 1px;font-weight:700;text-transform:uppercase">
                            {{ strtoupper($student->user->full_name) }}
                        </span>
                    </td>
                    <td style="padding:3px 0;font-size:12px;font-weight:700;white-space:nowrap;vertical-align:bottom">
                        <span style="font-weight:900;text-transform:uppercase">Reg No: </span>
                        <span style="display:inline-block;border-bottom:1.5px solid #000;min-width:160px;padding:0 4px 1px;font-weight:700;text-transform:uppercase">
                            {{ strtoupper($student->student_id) }}
                        </span>
                    </td>
                </tr>
            </table>
            <div style="font-size:12px;font-weight:700;margin-bottom:10px">
                <span style="font-weight:900;text-transform:uppercase">Campus: </span>
                <span style="display:inline-block;border-bottom:1.5px solid #000;min-width:300px;padding:0 4px 1px;font-weight:700;text-transform:uppercase">
                    {{ strtoupper($student->churchBranch?->name ?? '—') }}
                </span>
            </div>
            <div style="text-align:center;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:0.3px;color:#000;margin-bottom:1px">
                General Semester Certificate Courses
            </div>
            <div style="text-align:center;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:0.3px;color:#000;margin-bottom:8px">
                Analytical Reference Chart
            </div>
        </div>

        {{-- ── SCORES TABLE ── --}}
        <div style="padding:0 20px">
            <table style="width:100%;border-collapse:collapse;border:2px solid #000">
                <thead>
                    <tr style="background:#FFD700">
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:5%">&nbsp;</th>
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:26%">Course</th>
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:9%">CA</th>
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:9%">Scores</th>
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:13%">Course<br>Aggregate</th>
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:13%">Course<br>Position</th>
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:12%">%<br>Average</th>
                        <th style="border:1.5px solid #000;padding:7px 4px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;width:13%">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $i => $row)
                    @php
                        $pos    = (int) $row['position'];
                        $suffix = $pos === 1 ? 'ST' : ($pos === 2 ? 'ND' : ($pos === 3 ? 'RD' : 'TH'));
                        $showAvg = ($i === (int)(count($courses) / 2));
                    @endphp
                    <tr style="background:#FFD700">
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:13px;font-weight:900;color:#000">{{ chr(65+$i) }}</td>
                        <td style="border:1.5px solid #000;padding:8px 6px;text-align:center;font-size:11px;font-weight:900;text-transform:uppercase;color:#000;line-height:1.3">{{ strtoupper($row['course']->name) }}</td>
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:12px;font-weight:700;color:#000">{{ $row['quiz_score'] > 0 ? $row['quiz_score'] : '' }}</td>
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:12px;font-weight:700;color:#000">{{ $row['exam_score'] > 0 ? $row['exam_score'] : '' }}</td>
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:12px;font-weight:900;color:#000">{{ $row['aggregate'] }}</td>
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:12px;font-weight:700;color:#000">{{ $pos }}<sup style="font-size:9px">{{ $suffix }}</sup></td>
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:12px;font-weight:700;color:#000">{{ $showAvg ? $overall_average.'%' : '' }}</td>
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:13px;font-weight:900;color:#000">{{ $row['grade'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── FOOTER ── --}}
        <div style="border-top:3px solid #000;margin:12px 20px 0;padding:10px 0 18px">
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    {{-- LEFT: Grading Scale --}}
                    <td style="width:36%;vertical-align:top;padding-right:16px">
                        <div style="font-size:11px;font-weight:900;text-decoration:underline;text-transform:uppercase;margin-bottom:5px;color:#000">Scale of Grading:</div>
                        @foreach(['A1 = 80–100 (Distinction)','A2 = 70–79 (Upper Division)','A3 = 60–69 (Lower Division)','B1 = 50–59 (Credit)','B2 = 40–49 (Pass)','F  = 0–39  (Fail)'] as $g)
                        <div style="font-size:11px;font-weight:700;color:#000;margin-bottom:3px">{{ $g }}</div>
                        @endforeach
                    </td>
                    {{-- RIGHT: Results + Totals --}}
                    <td style="width:64%;vertical-align:top">
                        @foreach([['Result:', $result], ['Grade:', $overall_grade_description], ['Remarks:', $remarks]] as [$lbl, $val])
                        <table style="width:100%;border-collapse:collapse;margin-bottom:6px">
                            <tr>
                                <td style="font-size:12px;font-weight:900;text-transform:uppercase;white-space:nowrap;width:85px;vertical-align:bottom;padding-right:4px">{{ $lbl }}</td>
                                <td style="font-size:12px;font-weight:700;border-bottom:1.5px solid #000;padding:0 4px 1px;vertical-align:bottom;text-transform:uppercase;line-height:1.5">{{ $val }}</td>
                            </tr>
                        </table>
                        @endforeach
                        <table style="width:100%;border-collapse:collapse;border:1.5px solid #000;margin-top:4px">
                            <tr>
                                @foreach([[$total_courses,'Total Score'],[$overall_aggregate,'Aggregate'],[$overall_average.'%','Average'],['&nbsp;','Placement']] as [$v,$l])
                                <td style="border:1.5px solid #000;text-align:center;padding:7px 4px;width:25%">
                                    <div style="font-size:14px;font-weight:900;color:#000;line-height:1.2">{!! $v !!}</div>
                                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:#000;margin-top:2px">{{ $l }}</div>
                                </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

    </div>{{-- end #report-card --}}
    @endif
</div>

<style>
@media print {
    header, nav, aside, .bottom-nav,
    [class*="lg:pl-"],
    .flex.flex-wrap.items-center.gap-3.mb-6 { display:none!important; }
    body { background:#FFD700!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    #report-card { box-shadow:none!important; border:none!important; margin:0!important; }
    .max-w-3xl { max-width:100%!important; margin:0!important; padding:0!important; }
}
</style>
@endsection
