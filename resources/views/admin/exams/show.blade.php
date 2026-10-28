extends('layouts.app')
@section('title', 'Report Card')
@section('subtitle', $student->user->full_name . ' – ' . $program->name)

@section('content'?'admin.exams.pdf', [$student, $program]) }}?attempt={{ $currentAttempt }}" target="_blank"
           class="btn-gold inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('admin.exams.excel', [$student, $program]) }}?attempt={{ $currentAttempt }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border-2 transition-colors"
           style="border-color:var(--brand);color:var(--primary-text);background:var(--primary-pale)">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <button onclick="window.print()"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-colors">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.exams.create', ['program_id' => $program->id, 'student_id' => $student->id, 'attempt' => $currentAttempt]) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-[var(--brand)] text-gray-700 rounded-xl text-sm font-semibold transition-colors hover:bg-[var(--primary-pale)]">
            <i class="fas fa-pen" style="color:var(--primary-text)"></i> Edit Scores
        </a>
        <a href="{{ route('admin.exams.index'?'admin.exams.show', [$student, $program]) }}?attempt={{ $att }}"
               class="px-4 py-1.5 rounded-full text-xs font-bold border transition-colors
                      {{ $att == $currentAttempt ? 'border-amber-500 text-[var(--primary-text)]' : 'border-gray-200 text-gray-500 bg-white hover:bg-[var(--primary-pale)]' }}"
               style="{{ $att == $currentAttempt ? 'background:var(--primary-pale)' : '' }}">
                Attempt {{ $att }}
            </a>
        @endforeach
        @php $newAttempt = $attempts->max() + 1; @endphp
        <a href="{{ route('admin.exams.create', ['program_id' => $program->id, 'student_id' => $student->id, 'attempt' => $newAttempt]) }}"
           class="ml-2 px-4 py-1.5 rounded-full text-xs font-bold border border-dashed border-amber-400 text-[var(--primary-text)] bg-white hover:bg-[var(--primary-pale)] transition-colors">
            <i class="fas fa-plus mr-1"></i> New Attempt
        </a>
    </div>
    @endif

    @if(!$has_scores)
    <div class="card p-12 text-center">
        <i class="fas fa-file-alt text-gray-200 text-5xl mb-4 block"></i>
        <p class="text-gray-500 font-semibold">No scores recorded yet.</p>
        <p class="text-gray-400 text-sm mt-1">Enter exam scores for this student and program first.</p>
        <a href="{{ route('admin.exams.create'?'images/logo.png') }}" alt="{{ $schoolName ?? 'School Logo' }}"
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
                            {{ $siteContactEmail ?? 'contact@school.edu'?'–'?'position'];
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
                        <td style="border:1.5px solid #000;padding:8px 4px;text-align:center;font-size:13px;font-weight:900;color:#000">{{ $row['grade'?'A1 = 80–100 (Distinction)','A2 = 70–79 (Upper Division)','A3 = 60–69 (Lower Division)','B1 = 50–59 (Credit)','B2 = 40–49 (Pass)','F  = 0–39  (Fail)'] as $g)
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
