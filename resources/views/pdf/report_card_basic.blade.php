<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Basic School Terminal Report — {{ is_array($student ?? null) ? ($student['user']['full_name'] ?? 'Student') : ($student->user->full_name ?? 'Student') }}</title>
<style>
* { margin:0; padding:0; }
@page { size:A4 portrait; margin:8mm 8mm 6mm 8mm; }
body { font-family:Arial,Helvetica,sans-serif; font-size:8.5pt; color:#000; background:#fff; line-height:1.2; }

.page { border:2.5pt double #000; padding:4mm 5mm; page-break-after:always; page-break-inside:avoid; }
.page:last-child { page-break-after:avoid; }

table { width:100%; border-collapse:collapse; table-layout:fixed; }

/* ── HEADER ── */
.hdr td { vertical-align:middle; padding:0 3pt; }
.hdr-logo { width:55pt; text-align:center; }
.hdr-logo img { max-width:52pt; max-height:52pt; object-fit:contain; }
.hdr-center { text-align:center; }
.sch-name { font-size:14pt; font-weight:bold; text-transform:uppercase; letter-spacing:1pt; }
.sch-sub { font-size:7.5pt; color:#333; margin-top:1pt; }
.rpt-title { font-size:10pt; font-weight:bold; text-transform:uppercase; margin-top:3pt; letter-spacing:0.5pt; border-top:1pt solid #000; border-bottom:1pt solid #000; padding:2pt 0; }
.rpt-sub { font-size:7.5pt; color:#444; margin-top:2pt; }

hr.d { border:none; border-top:1.5pt solid #000; margin:3pt 0; }
hr.dt { border:none; border-top:0.5pt solid #888; margin:2.5pt 0; }

/* ── INFO GRID ── */
.info td { font-size:8pt; padding:2pt 1pt; vertical-align:bottom; }
.lbl { font-weight:bold; white-space:nowrap; }
.val { border-bottom:0.8pt solid #000; display:inline-block; width:95%; padding:0 2pt 1pt; font-weight:bold; text-transform:uppercase; font-size:8.5pt; }

/* ── SUBJECTS TABLE ── */
.st th { border:0.7pt solid #000; background:#d0d0d0; padding:3pt 2pt; text-align:center; font-size:7.5pt; font-weight:bold; line-height:1.2; }
.st td { border:0.7pt solid #000; padding:2.5pt 2pt; text-align:center; vertical-align:middle; height:14pt; font-size:8pt; }
.st td.sn { text-align:left; padding-left:4pt; font-weight:bold; }
.st tr.tot td { font-weight:bold; background:#efefef; border-top:1.5pt solid #000; padding:3pt; }

/* ── LOWER SECTION: 3 columns ── */
.lower { margin-top:4pt; }
.lower-inner { display:table; width:100%; }

/* ── KEY SECTION ── */
.key-box { border:0.8pt solid #000; padding:3pt; margin-bottom:4pt; }
.key-title { font-size:8pt; font-weight:bold; text-align:center; text-transform:uppercase; border-bottom:0.5pt solid #888; margin-bottom:2pt; padding-bottom:1pt; }
.key-tbl th { border:0.5pt solid #888; background:#e8e8e8; padding:1.5pt 2pt; text-align:center; font-size:7pt; font-weight:bold; }
.key-tbl td { border:0.5pt solid #888; padding:1.5pt 2pt; text-align:center; font-size:7pt; }

/* ── CONDUCT BLOCK ── */
.cond-box { border:0.8pt solid #000; padding:3pt; margin-bottom:4pt; }
.cond-title { font-size:8pt; font-weight:bold; text-align:center; text-transform:uppercase; border-bottom:0.5pt solid #888; margin-bottom:2pt; padding-bottom:1pt; }
.cond-row { display:flex; justify-content:space-between; font-size:7.5pt; margin-bottom:1.5pt; }
.cond-lbl { font-weight:bold; }
.cond-val { border-bottom:0.6pt solid #555; min-width:40pt; display:inline-block; }

/* ── REMARKS ── */
.rmk td { font-size:8pt; padding:2.5pt 1pt; vertical-align:bottom; }
.rmk-lbl { font-weight:bold; white-space:nowrap; }
.rmk-line { border-bottom:0.8pt solid #000; display:inline-block; width:95%; padding:0 2pt 1pt; font-size:8pt; font-style:italic; }

/* ── SIGNATURES ── */
.sig td { text-align:center; padding:0 3pt; vertical-align:top; font-size:7.5pt; }
.sig-line { border-top:0.8pt dotted #555; width:85%; margin:16pt auto 2pt; }

.footer { text-align:center; font-size:6.5pt; font-style:italic; font-weight:bold; border-top:0.5pt solid #ccc; padding-top:2pt; margin-top:3pt; }
</style>
</head>
<body>
@php
    use App\Services\GradingScale;

    $getVal = function($obj, $key, $default='') {
        if ($obj===null) return $default;
        if (is_array($obj)) return $obj[$key]??$default;
        if (is_object($obj)) return $obj->$key??$default;
        return $default;
    };
    if (isset($data) && is_array($data)) {
        foreach ($data as $k=>$v) { if (!isset($$k)) $$k=$v; }
    }

    $studentName = '';
    if (isset($student)) {
        $userObj = $getVal($student,'user');
        $studentName = $getVal($userObj,'full_name',$getVal($student,'full_name',''));
    }
    $programName = isset($program) ? $getVal($program,'name','') : '';
    $sbaPct  = $sba_percentage  ?? $quiz_percentage  ?? 30;
    $examPct = $exam_percentage ?? 70;

    $rawCourses = $courses ?? [];
    if (is_object($rawCourses) && method_exists($rawCourses,'all')) $rawCourses=$rawCourses->all();

    $totalMarks=0; $totalCourses=0;
    foreach ($rawCourses as $row) { $totalMarks+=(float)$getVal($row,'aggregate',0); $totalCourses++; }
    $totalMarks = round($totalMarks,1);

    $pos = $overall_position??'—';
    $tot = $total_students_in_exam??'—';
    $avg = round((float)($overall_average??0),2);

    $attempt       = $attempt??1;
    $presentDays   = $present_days??null;
    $schoolDays    = $school_days??null;
    $conductVal    = $conduct_value??'';
    $attitudeVal   = $attitude_value??'';
    $interestVal   = $interest_value??'';
    $classTeachRmk = $class_teacher_remark??'';
    $promotedTo    = $promoted_to??'';
    $overallGrade  = $overall_grade??GradingScale::assign($avg);
    $overallResult = $result??($avg>=50?'PROMOTED':'REPEATED');
    $schoolMotto   = $schoolMotto??'Learners Today, Leaders Tomorrow';

    $ordinal = function($n): string {
        if (!is_numeric($n)||intval($n)<=0) return (string)$n;
        $n=intval($n); $m=$n%100;
        if ($m>=11&&$m<=13) return $n.'th';
        switch($n%10){ case 1:return $n.'st'; case 2:return $n.'nd'; case 3:return $n.'rd'; default:return $n.'th'; }
    };

    $examDateStr='';
    if (!empty($exam_date)) { try { $examDateStr=\Carbon\Carbon::parse($exam_date)->format('d F, Y'); } catch(\Throwable $e){} }

    // Basic/Primary grade key
    $basicKey = [
        ['range'=>'80 – 100','grade'=>'1','interp'=>'Outstanding'],
        ['range'=>'70 – 79', 'grade'=>'2','interp'=>'Excellent'],
        ['range'=>'60 – 69', 'grade'=>'3','interp'=>'Very Good'],
        ['range'=>'50 – 59', 'grade'=>'4','interp'=>'Good'],
        ['range'=>'40 – 49', 'grade'=>'5','interp'=>'Average'],
        ['range'=>'30 – 39', 'grade'=>'6','interp'=>'Below Average'],
        ['range'=>'0  – 29', 'grade'=>'7','interp'=>'Weak'],
    ];

    $conductOptions = ['Excellent','Very Good','Good','Fair','Poor'];
@endphp

<div class="page">

{{-- HEADER --}}
<table class="hdr">
    <tr>
        <td class="hdr-logo">
            @if(!empty($siteLogoData))<img src="{{ $siteLogoData }}" alt="Logo">
            @elseif(!empty($logoBase64))<img src="{{ $logoBase64 }}" alt="Logo">
            @endif
        </td>
        <td class="hdr-center">
            <div class="sch-name">{{ $schoolName??config('app.name','School') }}</div>
            @if(!empty($schoolSubtitle))<div class="sch-sub">{{ $schoolSubtitle }}</div>@endif
            @if(!empty($schoolAddress))<div class="sch-sub">{{ $schoolAddress }}</div>@endif
            <div class="rpt-title">BASIC SCHOOL TERMINAL REPORT CARD</div>
            @if($examDateStr)<div class="rpt-sub">Academic Year {{ date('Y') }} &bull; Term {{ $attempt }} &bull; {{ $examDateStr }}</div>@endif
        </td>
        <td class="hdr-logo">
            @if(!empty($siteLogoData))<img src="{{ $siteLogoData }}" alt="Logo">
            @elseif(!empty($logoBase64))<img src="{{ $logoBase64 }}" alt="Logo">
            @endif
        </td>
    </tr>
</table>
<hr class="d">

{{-- STUDENT INFO --}}
<table class="info">
    <tr>
        <td style="width:17%"><span class="lbl">Pupil's Name:</span></td>
        <td colspan="5"><span class="val">{{ strtoupper($studentName) }}</span></td>
    </tr>
    <tr>
        <td><span class="lbl">Class / Form:</span></td>
        <td style="width:30%"><span class="val">{{ strtoupper($programName) }}</span></td>
        <td style="width:7%"><span class="lbl">Year:</span></td>
        <td style="width:12%"><span class="val">{{ date('Y') }}</span></td>
        <td style="width:7%"><span class="lbl">Term:</span></td>
        <td style="width:14%"><span class="val">{{ $attempt }}</span></td>
    </tr>
    <tr>
        <td><span class="lbl">Class Size:</span></td>
        <td><span class="val">{{ $tot }}</span></td>
        <td><span class="lbl">Position:</span></td>
        <td><span class="val">{{ $ordinal($pos) }}</span></td>
        <td><span class="lbl">Average:</span></td>
        <td><span class="val">{{ $avg>0 ? number_format($avg,1).'%' : '' }}</span></td>
    </tr>
</table>
<hr class="dt">

{{-- SUBJECTS TABLE --}}
<table class="st" style="margin-top:2pt">
    <thead>
        <tr>
            <th style="width:32%;text-align:left;padding-left:4pt;">SUBJECT</th>
            <th style="width:11%">CLASS<br>SCORE<br>({{ $sbaPct }}%)</th>
            <th style="width:11%">EXAM<br>SCORE<br>({{ $examPct }}%)</th>
            <th style="width:10%">TOTAL<br>(100%)</th>
            <th style="width:8%">GRADE</th>
            <th style="width:8%">POSITION</th>
            <th style="width:20%;font-size:7pt;">TEACHER'S REMARKS</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rawCourses as $row)
        @php
            $cs  = round((float)$getVal($row,'class_score',$getVal($row,'quiz_score',0)),1);
            $es  = round((float)$getVal($row,'exam_score',0),1);
            $agg = round((float)$getVal($row,'aggregate',0),1);
            $gr  = $getVal($row,'grade',GradingScale::assign($agg));
            $pos2= $getVal($row,'position','');
            $rmk = $agg>=80?'Outstanding':($agg>=70?'Excellent':($agg>=60?'Very Good':($agg>=50?'Good':($agg>=40?'Average':'Needs Improvement'))));
            $cObj= $getVal($row,'course');
            $cNm = $getVal($cObj,'name','—');
        @endphp
        <tr>
            <td class="sn">{{ strtoupper($cNm) }}</td>
            <td>{{ $cs>0?number_format($cs,1):'' }}</td>
            <td>{{ $es>0?number_format($es,1):'' }}</td>
            <td><strong>{{ $agg>0?number_format($agg,1):'' }}</strong></td>
            <td><strong>{{ $gr }}</strong></td>
            <td>{{ $ordinal($pos2) }}</td>
            <td style="text-align:left;padding-left:3pt;font-size:7pt;">{{ $rmk }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="padding:6pt;text-align:center;color:#888;font-style:italic;">No scores recorded.</td></tr>
        @endforelse
        @for($i=count($rawCourses);$i<10;$i++)
        <tr><td class="sn">&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        @endfor
        <tr class="tot">
            <td class="sn" style="text-align:left;padding-left:4pt;">TOTAL / AGGREGATE</td>
            <td colspan="2"></td>
            <td><strong>{{ $totalMarks>0?number_format($totalMarks,1):'' }}</strong></td>
            <td colspan="3" style="text-align:left;padding-left:4pt;font-size:7.5pt;">
                Grade: <strong>{{ $overallGrade }}</strong> &nbsp;&nbsp; Result: <strong>{{ $overallResult }}</strong>
            </td>
        </tr>
    </tbody>
</table>

{{-- LOWER: Grade Key | Conduct | Attendance --}}
<table style="margin-top:5pt;">
    <tr>
        {{-- Grade Key --}}
        <td style="width:35%;vertical-align:top;padding-right:4pt;">
            <div class="key-box">
                <div class="key-title">Grading Key</div>
                <table class="key-tbl">
                    <thead><tr><th>Marks (%)</th><th>Grade</th><th>Remark</th></tr></thead>
                    <tbody>
                        @foreach($basicKey as $k)
                        <tr><td>{{ $k['range'] }}</td><td><strong>{{ $k['grade'] }}</strong></td><td style="text-align:left;padding-left:2pt;">{{ $k['interp'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="text-align:center;font-size:6.5pt;font-style:italic;font-weight:bold;margin-top:3pt;">
                &ldquo;{{ $schoolMotto }}&rdquo;
            </div>
        </td>

        {{-- Conduct & Attendance --}}
        <td style="width:65%;vertical-align:top;padding-left:4pt;border-left:0.8pt solid #000;">
            <div class="cond-box">
                <div class="cond-title">Conduct &amp; Character Assessment</div>
                @foreach([
                    ['Conduct / Behaviour', $conductVal],
                    ['Attitude to Work', $attitudeVal],
                    ['Interest in Studies', $interestVal],
                ] as [$lbl,$val])
                <table style="margin-bottom:2pt;"><tr>
                    <td style="width:40%;font-size:7.5pt;font-weight:bold;">{{ $lbl }}:</td>
                    <td>
                        @foreach($conductOptions as $opt)
                        <span style="font-size:6.5pt;margin-right:4pt;">
                            <span style="border:0.5pt solid #333;display:inline-block;width:7pt;height:7pt;text-align:center;line-height:7pt;font-size:7pt;">{{ strtolower($val??'')==strtolower($opt)?'✓':'' }}</span>
                            {{ $opt }}
                        </span>
                        @endforeach
                    </td>
                </tr></table>
                @endforeach
            </div>

            <table class="rmk" style="margin-top:3pt;">
                <tr>
                    <td style="width:30%"><span class="rmk-lbl">Days Present:</span></td>
                    <td style="width:20%"><span class="rmk-line">{{ $presentDays??'' }}</span></td>
                    <td style="width:10%;text-align:center"><span class="rmk-lbl">of</span></td>
                    <td style="width:40%"><span class="rmk-line">{{ $schoolDays??'' }}</span></td>
                </tr>
                <tr>
                    <td><span class="rmk-lbl">Promoted To:</span></td>
                    <td colspan="3"><span class="rmk-line">{{ $promotedTo }}</span></td>
                </tr>
                <tr>
                    <td colspan="4"><span class="rmk-lbl">Class Teacher's Remarks:</span></td>
                </tr>
                <tr>
                    <td colspan="4"><span class="rmk-line">{{ $classTeachRmk }}</span></td>
                </tr>
            </table>

            {{-- Personality traits (checkboxes) --}}
            @if(isset($attrDefs) && count($attrDefs)>0)
            <div style="margin-top:3pt;border:0.5pt solid #ccc;padding:2pt;">
                <div style="font-size:7pt;font-weight:bold;text-align:center;margin-bottom:2pt;text-transform:uppercase;">Personality Development</div>
                <table style="font-size:6.5pt;">
                    <thead><tr><th style="border:0.5pt solid #ccc;background:#e8e8e8;padding:1pt 2pt;width:50%;text-align:left;">Attribute</th><th style="border:0.5pt solid #ccc;background:#e8e8e8;padding:1pt;width:17%;text-align:center;">Very Good</th><th style="border:0.5pt solid #ccc;background:#e8e8e8;padding:1pt;width:17%;text-align:center;">Average</th><th style="border:0.5pt solid #ccc;background:#e8e8e8;padding:1pt;width:16%;text-align:center;">Poor</th></tr></thead>
                    <tbody>
                    @foreach($attrDefs as $idx=>$attr)
                    @php
                        $attrId=$getVal($attr,'id');
                        $saved=(is_object($studentRatings??null)&&method_exists($studentRatings,'get'))?($studentRatings->get($attrId)?->rating??null):null;
                        $isVG=$saved&&in_array(strtolower($saved),['very good','good']);
                        $isAvg=$saved&&strtolower($saved)==='average';
                        $isWeak=$saved&&in_array(strtolower($saved),['weak / poor','poor','weak']);
                    @endphp
                    <tr>
                        <td style="border:0.5pt solid #ccc;padding:1pt 2pt;text-align:left;">{{ $getVal($attr,'name') }}</td>
                        <td style="border:0.5pt solid #ccc;padding:1pt;text-align:center;">{{ $isVG?'✓':'' }}</td>
                        <td style="border:0.5pt solid #ccc;padding:1pt;text-align:center;">{{ $isAvg?'✓':'' }}</td>
                        <td style="border:0.5pt solid #ccc;padding:1pt;text-align:center;">{{ $isWeak?'✓':'' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </td>
    </tr>
</table>

{{-- SIGNATURES --}}
<table class="sig" style="margin-top:5pt;">
    <tr>
        <td style="width:33%"><div class="sig-line"></div><strong>CLASS TEACHER</strong><br>Name: ..................................<br>Date: ..................................</td>
        <td style="width:34%"><div class="sig-line"></div><strong>HEAD TEACHER / PRINCIPAL</strong><br>Name: ..................................<br>Date: ..................................</td>
        <td style="width:33%">
            <div style="width:55pt;height:55pt;border:1pt dashed #aaa;margin:0 auto 3pt;border-radius:50%;text-align:center;font-size:6pt;padding-top:20pt;">SCHOOL<br>STAMP</div>
        </td>
    </tr>
</table>

<div class="footer">{{ $schoolName??config('app.name') }} &bull; Basic School Terminal Report &bull; Term {{ $attempt }}, {{ date('Y') }}</div>

</div>
</body>
</html>
