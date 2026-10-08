<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bulk Terminal Reports</title>
<style>
* { margin: 0; padding: 0; }
@page {
    size: A4 portrait;
    margin: 8mm 8mm 6mm 8mm;
}
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5pt;
    color: #000;
    background: #fff;
    line-height: 1.2;
    margin: 0;
    padding: 0;
}

/* ── OUTER BORDER ── */
.page {
    border: 2pt solid #000;
    padding: 4mm 5mm;
    page-break-after: always;
    page-break-inside: avoid;
    margin: 0;
}
.page:last-child {
    page-break-after: avoid;
}

/* ── ALL TABLES FULL WIDTH ── */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin: 0;
    padding: 0;
}

/* ── HEADER ── */
.hdr { width: 100%; margin-bottom: 3pt; }
.hdr td { vertical-align: middle; }
.hdr-logo { width: 55pt; text-align: center; }
.hdr-logo img { max-width: 50pt; max-height: 50pt; object-fit: contain; }
.hdr-center { text-align: center; padding: 0 6pt; }
.school-name { font-size: 15pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; line-height: 1.15; }
.school-address { font-size: 8pt; color: #333; margin-top: 1.5pt; }
.report-title { font-size: 10.5pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; margin-top: 3pt; letter-spacing: 0.5pt; }
.report-sub { font-size: 8pt; margin-top: 1.5pt; color: #444; }

.divider { border: none; border-top: 1.5pt solid #000; margin: 3.5pt 0; }
.divider-thin { border: none; border-top: 0.6pt solid #777; margin: 3pt 0; }

/* ── STUDENT INFO TABLE ── */
.info-tbl { width: 100%; margin-bottom: 3pt; font-size: 8.5pt; }
.info-tbl td { padding: 2pt 2pt; vertical-align: bottom; }
.lbl { font-weight: bold; white-space: nowrap; font-size: 8pt; }
.val {
    border-bottom: 0.8pt solid #000;
    font-weight: bold;
    display: inline-block;
    width: 96%;
    padding: 0 2pt 1pt;
    text-transform: uppercase;
    font-size: 8.5pt;
}

/* ── SUBJECTS TABLE ── */
.subj-tbl { width: 100%; margin-top: 3pt; font-size: 8pt; }
.subj-tbl th {
    border: 0.7pt solid #000;
    background: #e8e8e8;
    padding: 3pt 2pt;
    text-align: center;
    font-size: 7.5pt;
    font-weight: bold;
    line-height: 1.15;
}
.subj-tbl td {
    border: 0.7pt solid #000;
    padding: 2.2pt 2pt;
    text-align: center;
    vertical-align: middle;
    height: 13pt;
    font-size: 8pt;
}
.subj-tbl td.s-name { text-align: left; padding-left: 4pt; font-weight: bold; }
.subj-tbl tr.total-row td {
    font-weight: bold;
    background: #f0f0f0;
    border-top: 1.5pt solid #000;
    padding: 3pt 2pt;
    font-size: 8.5pt;
}

/* ── REMARKS SECTION ── */
.rmk-tbl { width: 100%; margin-top: 4pt; font-size: 8pt; }
.rmk-tbl td { padding: 2.5pt 1pt; vertical-align: bottom; }
.rmk-label { font-weight: bold; white-space: nowrap; font-size: 8pt; }
.rmk-line {
    border-bottom: 0.8pt solid #000;
    display: inline-block;
    width: 96%;
    padding: 0 2pt 1pt;
    font-style: italic;
    font-size: 8pt;
}

/* ── TWO-COLUMN BOTTOM LAYOUT ── */
.bottom-layout-tbl { width: 100%; margin-top: 4pt; border-top: 1pt solid #000; padding-top: 4pt; }
.bottom-layout-tbl td.col-l { width: 40%; vertical-align: top; padding-right: 5pt; }
.bottom-layout-tbl td.col-r { width: 60%; vertical-align: top; border-left: 1pt solid #000; padding-left: 5pt; }

/* ── GRADING TABLE ── */
.grade-tbl { width: 100%; font-size: 7.2pt; }
.grade-tbl th, .grade-tbl td { border: 0.6pt solid #000; padding: 1.8pt 2pt; text-align: center; }
.grade-tbl thead th { background: #e8e8e8; font-weight: bold; font-size: 7.2pt; }
.grade-tbl th.hdr { background: #c8c8c8; font-size: 8pt; font-weight: bold; padding: 2.5pt 2pt; }

/* ── PERSONALITY TABLE ── */
.pd-title { font-size: 8pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; text-align: center; margin-bottom: 3pt; }
.pd-tbl { width: 100%; font-size: 7.2pt; }
.pd-tbl th { border: 0.6pt solid #000; background: #e8e8e8; padding: 2pt 1pt; text-align: center; font-weight: bold; line-height: 1.15; font-size: 7pt; }
.pd-tbl td { border: 0.6pt solid #000; padding: 1.5pt 1pt; vertical-align: middle; height: 12pt; }
.pd-tbl td.a-name { text-align: left; padding-left: 3pt; font-size: 7.2pt; }
.pd-tbl td.rc { text-align: center; font-size: 8pt; font-weight: bold; }

/* ── SIGNATURE SECTION ── */
.sig-tbl { width: 100%; margin-top: 6pt; font-size: 7.5pt; }
.sig-tbl td { text-align: center; padding: 0 4pt; vertical-align: top; }
.sig-line { border-top: 0.8pt dotted #555; width: 85%; margin: 16pt auto 2pt; }

/* ── FOOTER ── */
.footer-box { width: 100%; margin-top: 4pt; text-align: center; font-size: 7pt; font-style: italic; font-weight: bold; border-top: 0.5pt solid #ccc; padding-top: 3pt; }
</style>
</head>
<body>

@php
    use App\Services\GradingScale;

    // Helper function to safely read array or object properties
    $getVal = function($obj, $key, $default = '') {
        if ($obj === null) return $default;
        if (is_array($obj)) return $obj[$key] ?? $default;
        if (is_object($obj)) return $obj->$key ?? $default;
        return $default;
    };

    // Ordinal helper (PHP 7.4+ safe, no match expression)
    $ordinal = function($n): string {
        if (!is_numeric($n) || intval($n) <= 0) return (string)$n;
        $n = intval($n);
        $mod100 = $n % 100;
        if ($mod100 >= 11 && $mod100 <= 13) {
            return $n . 'th';
        }
        switch ($n % 10) {
            case 1:  return $n . 'st';
            case 2:  return $n . 'nd';
            case 3:  return $n . 'rd';
            default: return $n . 'th';
        }
    };

    // ── Attribute definitions (fetched ONCE outside the loop) ─────────────
    $attrDefs = $attrDefs ?? collect();
    if (empty($attrDefs) || (is_object($attrDefs) && method_exists($attrDefs, 'isEmpty') && $attrDefs->isEmpty())) {
        $defaults = [
            'Courtesy','Neatness','Tolerance / Temperament','Sociable',
            'Imaginative & Creative / Ability','Initiative','Dependability','Honesty',
            'Interest in Practical Work','Leadership Quality',
            'Understanding of Lessons','Interest in Studies',
            'Observation of Objects','Co-operation with Mates',
        ];
        $attrDefs = collect(array_map(fn($n,$i)=>(object)['id'=>$i+1,'name'=>$n], $defaults, array_keys($defaults)));
    }

    // ── System's Grading Scale (dynamically reflecting system configuration) ─────────────
    $scaleRaw = GradingScale::getScale();
    $gradeScale = [];
    $scCount = count($scaleRaw);
    for ($i = 0; $i < $scCount; $i++) {
        $entry = $scaleRaw[$i];
        $min = $entry['min'] ?? 0;
        $max = $entry['max'] ?? ($i === 0 ? 100 : (($scaleRaw[$i-1]['min'] ?? 100) - 1));
        $gradeScale[] = [
            'range'  => $min . ' – ' . $max,
            'grade'  => (string) ($entry['grade'] ?? ''),
            'interp' => (string) ($entry['description'] ?? $entry['interp'] ?? ''),
        ];
    }

    $schoolMotto = $schoolMotto ?? 'Learners Today, Leaders Tomorrow';
@endphp

@foreach($cards as $card)
@php
    $cData = is_array($card) && isset($card['data']) && is_array($card['data']) ? $card['data'] : (is_array($card) ? $card : []);

    $student = $getVal($card, 'student', $getVal($cData, 'student'));
    $program = $getVal($card, 'program', $getVal($cData, 'program'));
    $courses = $getVal($card, 'courses', $getVal($cData, 'courses', []));

    $studentName = '';
    if ($student) {
        $userObj = $getVal($student, 'user');
        $studentName = $getVal($userObj, 'full_name', $getVal($student, 'full_name', ''));
    }

    $programName = '';
    if ($program) {
        $programName = $getVal($program, 'name', '');
    }

    $cardLogo = $getVal($card, 'logoBase64', $getVal($cData, 'logoBase64', $siteLogoData ?? null));
    $cardAttempt = $getVal($card, 'attempt', $getVal($cData, 'attempt', $attempt ?? 1));

    $sbaPct  = $getVal($card, 'sba_percentage', $getVal($cData, 'sba_percentage', $getVal($cData, 'quiz_percentage', 50)));
    $examPct = $getVal($card, 'exam_percentage', $getVal($cData, 'exam_percentage', 50));

    $rawCourses = $courses;
    if (is_object($rawCourses) && method_exists($rawCourses, 'all')) {
        $rawCourses = $rawCourses->all();
    }

    $totalMarks   = 0;
    foreach ($rawCourses as $row) {
        $aggVal = (float) $getVal($row, 'aggregate', 0);
        $totalMarks += $aggVal;
    }
    $totalMarks = round($totalMarks, 1);

    $pos = $getVal($card, 'overall_position', $getVal($cData, 'overall_position', '—'));
    $tot = $getVal($card, 'total_students_in_exam', $getVal($cData, 'total_students_in_exam', '—'));
    $avg = round((float) $getVal($card, 'overall_average', $getVal($cData, 'overall_average', 0)), 2);

    $presentDays   = $getVal($card, 'present_days', $getVal($cData, 'present_days', null));
    $schoolDays    = $getVal($card, 'school_days', $getVal($cData, 'school_days', null));
    $conductVal    = $getVal($card, 'conduct_value', $getVal($cData, 'conduct_value', $getVal($card, 'conduct', '')));
    $attitudeVal   = $getVal($card, 'attitude_value', $getVal($cData, 'attitude_value', $getVal($card, 'attitude', '')));
    $interestVal   = $getVal($card, 'interest_value', $getVal($cData, 'interest_value', $getVal($card, 'interest', '')));
    $classTeachRmk = $getVal($card, 'class_teacher_remark', $getVal($cData, 'class_teacher_remark', ''));
    $promotedTo    = $getVal($card, 'promoted_to', $getVal($cData, 'promoted_to', $getVal($card, 'remarks', '')));

    $overallGrade  = $getVal($card, 'overall_grade', $getVal($cData, 'overall_grade', GradingScale::assign($avg)));
    $overallResult = $getVal($card, 'result', $getVal($cData, 'result', ($avg >= 50 ? 'PROMOTED' : 'REPEATED')));
    $overallRemark = $getVal($card, 'remarks', $getVal($cData, 'remarks', ''));

    // Student personality ratings
    $studentRatings = $getVal($card, 'studentRatings', $getVal($cData, 'studentRatings', collect()));

    // Exam date
    $cardExamDate = $getVal($card, 'exam_date', $getVal($cData, 'exam_date'));
    $examDateStr = '';
    if (!empty($cardExamDate)) {
        try { $examDateStr = \Carbon\Carbon::parse($cardExamDate)->format('d F, Y'); } catch(\Throwable $e){}
    }
@endphp

<div class="page">

{{-- ══ HEADER ══ --}}
<table class="hdr">
    <tr>
        <td class="hdr-logo">
            @if(!empty($siteLogoData))<img src="{{ $siteLogoData }}" alt="Logo">
            @elseif(!empty($cardLogo))<img src="{{ $cardLogo }}" alt="Logo">
            @endif
        </td>
        <td class="hdr-center">
            <div class="school-name">{{ $schoolName ?? config('app.name','School') }}</div>
            @if(!empty($schoolSubtitle))<div class="school-address">{{ $schoolSubtitle }}</div>@endif
            @if(!empty($schoolAddress))<div class="school-address">{{ $schoolAddress }}</div>@endif
            <div class="report-title">TERMINAL REPORT — {{ strtoupper($programName) }}</div>
            @if($examDateStr)<div class="report-sub">Academic Year {{ date('Y') }} &bull; Term {{ $cardAttempt }} &bull; {{ $examDateStr }}</div>@endif
        </td>
        <td class="hdr-logo">
            @if(!empty($siteLogoData))<img src="{{ $siteLogoData }}" alt="Logo">
            @elseif(!empty($cardLogo))<img src="{{ $cardLogo }}" alt="Logo">
            @endif
        </td>
    </tr>
</table>
<hr class="divider">

{{-- ══ STUDENT INFO ══ --}}
<table class="info-tbl">
    <tr>
        <td style="width:17%;"><span class="lbl">Name of Student:</span></td>
        <td colspan="5"><span class="val">{{ strtoupper($studentName) }}</span></td>
    </tr>
    <tr>
        <td style="width:17%;"><span class="lbl">Class / Programme:</span></td>
        <td style="width:33%;"><span class="val">{{ strtoupper($programName) }}</span></td>
        <td style="width:8%;"><span class="lbl">Year:</span></td>
        <td style="width:14%;"><span class="val">{{ date('Y') }}</span></td>
        <td style="width:10%;"><span class="lbl">Term:</span></td>
        <td style="width:18%;"><span class="val">{{ $cardAttempt }}</span></td>
    </tr>
    <tr>
        <td><span class="lbl">No. On Roll:</span></td>
        <td><span class="val">{{ $tot }}</span></td>
        <td><span class="lbl">Position:</span></td>
        <td><span class="val">{{ $ordinal($pos) }}</span></td>
        <td><span class="lbl">Average:</span></td>
        <td><span class="val">{{ $avg > 0 ? number_format($avg,1).'%' : '' }}</span></td>
    </tr>
    <tr>
        <td><span class="lbl">Next Term Begins:</span></td>
        <td><span class="val"></span></td>
        <td colspan="2"><span class="lbl">Date of Vacation:</span></td>
        <td colspan="2"><span class="val"></span></td>
    </tr>
</table>
<hr class="divider-thin">

{{-- ══ SUBJECTS TABLE ══ --}}
<table class="subj-tbl">
    <thead>
        <tr>
            <th style="width:30%;text-align:left;padding-left:4pt;">SUBJECT</th>
            <th style="width:10%;">CLASS<br>SCORE<br>({{ $sbaPct }}%)</th>
            <th style="width:10%;">EXAMS<br>SCORE<br>({{ $examPct }}%)</th>
            <th style="width:10%;">TOTAL<br>SCORE<br>(100%)</th>
            <th style="width:9%;">POSITION<br>IN<br>SUBJECT</th>
            <th style="width:7%;">GRADE</th>
            <th style="width:24%;font-size:7pt;">REMARKS<br><span style="font-weight:normal;">(Strengths/Weakness)</span></th>
        </tr>
    </thead>
    <tbody>
        @forelse($rawCourses as $row)
        @php
            $cs   = round((float) $getVal($row, 'class_score', $getVal($row, 'quiz_score', 0)), 1);
            $es   = round((float) $getVal($row, 'exam_score', 0), 1);
            $agg  = round((float) $getVal($row, 'aggregate', 0), 1);
            $gr   = $getVal($row, 'grade', GradingScale::assign($agg));
            $rmk  = $agg >= 80 ? 'Excellent' : ($agg >= 70 ? 'Very Good' : ($agg >= 60 ? 'Good' : ($agg >= 50 ? 'Average' : 'Needs Improvement')));
            $subP = $getVal($row, 'position', '');

            $cObj = $getVal($row, 'course');
            $cName = $getVal($cObj, 'name', '—');
        @endphp
        <tr>
            <td class="s-name">{{ strtoupper($cName) }}</td>
            <td>{{ $cs > 0 ? number_format($cs, 1) : '' }}</td>
            <td>{{ $es > 0 ? number_format($es, 1) : '' }}</td>
            <td><strong>{{ $agg > 0 ? number_format($agg, 1) : '' }}</strong></td>
            <td>{{ $ordinal($subP) }}</td>
            <td><strong>{{ $gr }}</strong></td>
            <td style="font-size:7.2pt;text-align:left;padding-left:3pt;">{{ $rmk }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="padding:6pt;text-align:center;color:#888;font-style:italic;">No scores recorded for this term.</td></tr>
        @endforelse

        {{-- Pad to at least 9 rows --}}
        @for($i = count($rawCourses); $i < 9; $i++)
        <tr><td class="s-name">&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        @endfor

        {{-- TOTAL ROW --}}
        <tr class="total-row">
            <td class="s-name" style="text-align:left;padding-left:4pt;">TOTAL SCORE</td>
            <td colspan="2"></td>
            <td><strong>{{ $totalMarks > 0 ? number_format($totalMarks, 1) : '' }}</strong></td>
            <td colspan="3" style="text-align:left;padding-left:4pt;font-size:7.5pt;">
                Overall Grade: <strong>{{ $overallGrade }}</strong>
                &nbsp;&nbsp; Result: <strong>{{ $overallResult }}</strong>
            </td>
        </tr>
    </tbody>
</table>

{{-- ══ REMARKS SECTION ══ --}}
<table class="rmk-tbl">
    <tr>
        <td style="width:13%;"><span class="rmk-label">Attendance:</span></td>
        <td style="width:13%;"><span class="rmk-line">{{ $presentDays ?? '' }}</span></td>
        <td style="width:6%;text-align:center;"><span class="rmk-label">out of</span></td>
        <td style="width:13%;"><span class="rmk-line">{{ $schoolDays ?? '' }}</span></td>
        <td style="width:16%;text-align:right;padding-right:3pt;"><span class="rmk-label">Promoted to:</span></td>
        <td style="width:39%;"><span class="rmk-line">{{ $promotedTo }}</span></td>
    </tr>
    <tr>
        <td><span class="rmk-label">Conduct:</span></td>
        <td><span class="rmk-line">{{ $conductVal }}</span></td>
        <td colspan="2" style="text-align:right;padding-right:3pt;"><span class="rmk-label">Attitude:</span></td>
        <td><span class="rmk-line">{{ $attitudeVal }}</span></td>
        <td>
            <span class="rmk-label" style="margin-right:2pt;">Interest:</span>
            <span class="rmk-line" style="width:70%;">{{ $interestVal }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2"><span class="rmk-label">Class/Form Teacher's Remarks:</span></td>
        <td colspan="4"><span class="rmk-line">{{ $classTeachRmk }}</span></td>
    </tr>
    <tr>
        <td colspan="2"><span class="rmk-label">Head Teacher's Signature:</span></td>
        <td colspan="4"><span class="rmk-line">&nbsp;</span></td>
    </tr>
</table>

{{-- ══ BOTTOM: Grade Scale (Left) + Personality Development (Right) ══ --}}
<table class="bottom-layout-tbl">
    <tr>
        {{-- LEFT: Grading system --}}
        <td class="col-l">
            <table class="grade-tbl">
                <thead>
                    <tr><th colspan="3" class="hdr">GRADING SYSTEM</th></tr>
                    <tr>
                        <th style="width:38%;">MARKS (%)</th>
                        <th style="width:22%;">GRADE</th>
                        <th style="width:40%;">INTERPRETATION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gradeScale as $gs)
                    <tr>
                        <td>{{ $gs['range'] }}</td>
                        <td><strong>{{ $gs['grade'] }}</strong></td>
                        <td style="text-align:left;padding-left:3pt;">{{ $gs['interp'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- School stamp --}}
            <div style="margin-top:5pt;text-align:center;">
                <p style="font-size:7pt;font-weight:bold;margin-bottom:2pt;">SCHOOL'S STAMP</p>
                <div style="width:60pt;height:60pt;border:1pt dashed #aaa;margin:0 auto;border-radius:50%;"></div>
            </div>

            <div style="margin-top:5pt;text-align:center;font-size:7pt;font-style:italic;font-weight:bold;">
                &ldquo;{{ $schoolMotto }}&rdquo;
            </div>
        </td>

        {{-- RIGHT: Personality Development --}}
        <td class="col-r">
            <div class="pd-title">PERSONALITY DEVELOPMENT</div>
            <table class="pd-tbl">
                <thead>
                    <tr>
                        <th style="width:55%;text-align:left;padding-left:3pt;">ATTRIBUTES</th>
                        <th class="rc" style="width:15%;">Very<br>Good</th>
                        <th class="rc" style="width:15%;">Average</th>
                        <th class="rc" style="width:15%;">Weak/<br>Poor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attrDefs as $idx => $attr)
                    @php
                        $attrId = $getVal($attr, 'id');
                        $attrName = $getVal($attr, 'name');
                        $saved  = (is_object($studentRatings) && method_exists($studentRatings, 'get')) ? ($studentRatings->get($attrId)?->rating ?? null) : null;
                        $isVG   = $saved && in_array(strtolower($saved), ['very good','good']);
                        $isAvg  = $saved && strtolower($saved) === 'average';
                        $isWeak = $saved && in_array(strtolower($saved), ['weak / poor','poor','weak']);
                    @endphp
                    <tr>
                        <td class="a-name">{{ $idx + 1 }}. {{ $attrName }}</td>
                        <td class="rc">{{ $isVG   ? '✓' : '' }}</td>
                        <td class="rc">{{ $isAvg  ? '✓' : '' }}</td>
                        <td class="rc">{{ $isWeak ? '✓' : '' }}</td>
                    </tr>
                    @endforeach
                    @for($i = count($attrDefs); $i < 14; $i++)
                    <tr>
                        <td class="a-name">{{ $i + 1 }}.</td>
                        <td class="rc"></td><td class="rc"></td><td class="rc"></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </td>
    </tr>
</table>

{{-- ══ SIGNATURE BLOCKS ══ --}}
<table class="sig-tbl">
    <tr>
        <td style="width:33.33%;">
            <div class="sig-line"></div>
            <strong>CLASS TEACHER</strong><br>
            Name: ..................................<br>
            Date: ..................................
        </td>
        <td style="width:33.33%;">
            <div class="sig-line"></div>
            <strong>HEAD TEACHER / PRINCIPAL</strong><br>
            Name: ..................................<br>
            Date: ..................................
        </td>
        <td style="width:33.34%;">
            <div class="sig-line"></div>
            <strong>DIRECTOR / MANAGEMENT</strong><br>
            Name: ..................................<br>
            Date: ..................................
        </td>
    </tr>
</table>

<div class="footer-box">
    {{ $schoolName ?? config('app.name') }} &bull; Terminal Report Card &bull; Term {{ $cardAttempt }}, {{ date('Y') }}
</div>

</div>{{-- end .page --}}
@endforeach

</body>
</html>