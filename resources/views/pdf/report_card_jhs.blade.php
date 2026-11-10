<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JHS Terminal Report — {{ is_array($student ?? null) ? ($student['user']['full_name'] ?? 'Student') : ($student->user->full_name ?? 'Student') }}</title>
<style>
* { margin:0; padding:0; }
@page { size:A4 portrait; margin:8mm 8mm 6mm 8mm; }
body { font-family:Arial,Helvetica,sans-serif; font-size:8.5pt; color:#000; background:#fff; line-height:1.2; }

.page { border:2pt solid #000; padding:4mm 5mm; page-break-after:always; page-break-inside:avoid; }
.page:last-child { page-break-after:avoid; }
table { width:100%; border-collapse:collapse; table-layout:fixed; }

/* ── HEADER ── */
.hdr td { vertical-align:middle; }
.hdr-logo { width:55pt; text-align:center; }
.hdr-logo img { max-width:52pt; max-height:52pt; object-fit:contain; }
.hdr-center { text-align:center; padding:0 5pt; }
.sch-name { font-size:14.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.5pt; }
.sch-sub { font-size:7.5pt; color:#333; margin-top:1pt; }
.rpt-title-wrap { margin-top:3pt; border:1.2pt solid #000; padding:2pt 6pt; }
.rpt-title { font-size:10pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.5pt; }
.rpt-sub { font-size:7.5pt; color:#444; margin-top:1pt; }

hr.d  { border:none; border-top:1.5pt solid #000; margin:3pt 0; }
hr.dt { border:none; border-top:0.5pt solid #888; margin:2.5pt 0; }

/* ── INFO ── */
.info td { font-size:8pt; padding:2pt 1pt; vertical-align:bottom; }
.lbl { font-weight:bold; white-space:nowrap; }
.val { border-bottom:0.8pt solid #000; display:inline-block; width:95%; padding:0 2pt 1pt; font-weight:bold; text-transform:uppercase; font-size:8.5pt; }

/* ── SUBJECTS TABLE — JHS style ── */
/* Columns: Subject | CA Score (30%) | Exam Score (70%) | Total (100%) | Grade | Position | Remark */
.st th { border:0.7pt solid #000; background:#c8c8c8; padding:3pt 2pt; text-align:center; font-size:7.5pt; font-weight:bold; line-height:1.2; }
.st td { border:0.7pt solid #000; padding:2.5pt 2pt; text-align:center; vertical-align:middle; height:14pt; font-size:8pt; }
.st td.sn { text-align:left; padding-left:4pt; font-weight:bold; }
.st tr.tot td { font-weight:bold; background:#efefef; border-top:1.5pt solid #000; padding:3pt; }

/* ── BECE GRADE KEY ── */
.bece-key th { border:0.5pt solid #888; background:#e8e8e8; padding:1.8pt 2pt; text-align:center; font-size:7.2pt; font-weight:bold; }
.bece-key td { border:0.5pt solid #888; padding:1.5pt 2pt; text-align:center; font-size:7.2pt; }

/* ── REMARKS ── */
.rmk td { font-size:8pt; padding:2.5pt 1pt; vertical-align:bottom; }
.rmk-lbl { font-weight:bold; white-space:nowrap; }
.rmk-line { border-bottom:0.8pt solid #000; display:inline-block; width:95%; padding:0 2pt 1pt; font-size:8pt; }

/* ── PERSONALITY ── */
.pd-tbl th { border:0.5pt solid #888; background:#e8e8e8; padding:2pt 1pt; text-align:center; font-weight:bold; font-size:7pt; line-height:1.15; }
.pd-tbl td { border:0.5pt solid #888; padding:1.5pt 1pt; vertical-align:middle; height:12pt; font-size:7.2pt; }
.pd-tbl td.an { text-align:left; padding-left:3pt; }
.pd-tbl td.rc { text-align:center; font-weight:bold; }

/* ── SIGNATURES ── */
.sig td { text-align:center; padding:0 3pt; vertical-align:top; font-size:7.5pt; }
.sig-line { border-top:0.8pt dotted #555; width:85%; margin:16pt auto 2pt; }

.footer { text-align:center; font-size:6.5pt; font-style:italic; font-weight:bold; border-top:0.5pt solid #ccc; padding-top:2pt; margin-top:3pt; }
</style>
</head>
<body>
@php
    use App\Services\GradingScale;

    $getVal = function($obj,$key,$default='') {
        if ($obj===null) return $default;
        if (is_array($obj)) return $obj[$key]??$default;
        if (is_object($obj)) return $obj->$key??$default;
        return $default;
    };
    if (isset($data)&&is_array($data)) { foreach($data as $k=>$v){ if(!isset($$k)) $$k=$v; } }

    $studentName='';
    if(isset($student)){ $userObj=$getVal($student,'user'); $studentName=$getVal($userObj,'full_name',$getVal($student,'full_name','')); }
    $programName = isset($program)?$getVal($program,'name',''):'';
    $sbaPct  = $sba_percentage??$quiz_percentage??30;
    $examPct = $exam_percentage??70;

    $rawCourses=$courses??[];
    if(is_object($rawCourses)&&method_exists($rawCourses,'all')) $rawCourses=$rawCourses->all();

    $totalMarks=0;
    foreach($rawCourses as $row) $totalMarks+=(float)$getVal($row,'aggregate',0);
    $totalMarks=round($totalMarks,1);

    $pos=$overall_position??'—';
    $tot=$total_students_in_exam??'—';
    $avg=round((float)($overall_average??0),2);
    $attempt=$attempt??1;
    $presentDays=$present_days??null;
    $schoolDays=$school_days??null;
    $conductVal=$conduct_value??'';
    $attitudeVal=$attitude_value??'';
    $interestVal=$interest_value??'';
    $classTeachRmk=$class_teacher_remark??'';
    $promotedTo=$promoted_to??'';
    $overallGrade=$overall_grade??GradingScale::assign($avg);
    $overallResult=$result??($avg>=50?'PROMOTED':'REPEATED');
    $schoolMotto=$schoolMotto??'Learners Today, Leaders Tomorrow';

    $ordinal=function($n): string {
        if(!is_numeric($n)||intval($n)<=0) return (string)$n;
        $n=intval($n); $m=$n%100;
        if($m>=11&&$m<=13) return $n.'th';
        switch($n%10){ case 1:return $n.'st'; case 2:return $n.'nd'; case 3:return $n.'rd'; default:return $n.'th'; }
    };

    $examDateStr='';
    if(!empty($exam_date)){ try{ $examDateStr=\Carbon\Carbon::parse($exam_date)->format('d F, Y'); }catch(\Throwable $e){} }

    // BECE/JHS Grade Scale (Ghana standard A1-F9)
    $jhsScale = [
        ['range'=>'80 – 100','grade'=>'A1','interp'=>'Excellent'],
        ['range'=>'70 – 79', 'grade'=>'B2','interp'=>'Very Good'],
        ['range'=>'60 – 69', 'grade'=>'B3','interp'=>'Good'],
        ['range'=>'55 – 59', 'grade'=>'C4','interp'=>'Credit'],
        ['range'=>'50 – 54', 'grade'=>'C5','interp'=>'Credit'],
        ['range'=>'45 – 49', 'grade'=>'C6','interp'=>'Credit'],
        ['range'=>'40 – 44', 'grade'=>'D7','interp'=>'Pass'],
        ['range'=>'35 – 39', 'grade'=>'E8','interp'=>'Pass'],
        ['range'=>'0  – 34', 'grade'=>'F9','interp'=>'Fail'],
    ];
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
            <div class="rpt-title-wrap">
                <div class="rpt-title">JHS Terminal Report Card</div>
                @if($examDateStr)<div class="rpt-sub">Academic Year {{ date('Y') }} &bull; Term {{ $attempt }} &bull; {{ $examDateStr }}</div>@endif
            </div>
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
        <td style="width:17%"><span class="lbl">Student's Name:</span></td>
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
        <td><span class="lbl">Average %:</span></td>
        <td><span class="val">{{ $avg>0?number_format($avg,1).'%':'' }}</span></td>
    </tr>
</table>
<hr class="dt">

{{-- SUBJECTS TABLE --}}
<table class="st" style="margin-top:2pt">
    <thead>
        <tr>
            <th style="width:30%;text-align:left;padding-left:4pt;">SUBJECT</th>
            <th style="width:11%">CLASS<br>SCORE<br>({{ $sbaPct }}%)</th>
            <th style="width:11%">EXAM<br>SCORE<br>({{ $examPct }}%)</th>
            <th style="width:9%">TOTAL<br>(100%)</th>
            <th style="width:8%">GRADE</th>
            <th style="width:8%">POS.</th>
            <th style="width:23%;font-size:7pt;">REMARKS</th>
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
            $rmk = $agg>=80?'Excellent':($agg>=70?'Very Good':($agg>=60?'Good':($agg>=55?'Credit':($agg>=40?'Pass':'Fail'))));
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
            <td class="sn" style="padding-left:4pt;">AGGREGATE / TOTAL</td>
            <td colspan="2"></td>
            <td><strong>{{ $totalMarks>0?number_format($totalMarks,1):'' }}</strong></td>
            <td colspan="3" style="text-align:left;padding-left:4pt;font-size:7.5pt;">
                Grade: <strong>{{ $overallGrade }}</strong> &nbsp;&nbsp;
                Result: <strong>{{ $overallResult }}</strong>
            </td>
        </tr>
    </tbody>
</table>

{{-- LOWER SECTION --}}
<table style="margin-top:5pt;">
    <tr>
        {{-- LEFT: BECE Grade Key --}}
        <td style="width:35%;vertical-align:top;padding-right:5pt;">
            <table style="font-size:7.5pt;font-weight:bold;text-align:center;margin-bottom:2pt;"><tr><td colspan="3" style="border:0.8pt solid #000;background:#c8c8c8;padding:2pt;">BECE GRADING SCALE</td></tr></table>
            <table class="bece-key">
                <thead><tr><th>Mark (%)</th><th>Grade</th><th>Interpretation</th></tr></thead>
                <tbody>
                    @foreach($jhsScale as $gs)
                    <tr><td>{{ $gs['range'] }}</td><td><strong>{{ $gs['grade'] }}</strong></td><td style="text-align:left;padding-left:2pt;">{{ $gs['interp'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:5pt;text-align:center;">
                <p style="font-size:7pt;font-weight:bold;margin-bottom:2pt;">SCHOOL STAMP</p>
                <div style="width:55pt;height:55pt;border:1pt dashed #aaa;margin:0 auto;border-radius:50%;"></div>
            </div>
            <div style="margin-top:4pt;text-align:center;font-size:6.5pt;font-style:italic;font-weight:bold;">
                &ldquo;{{ $schoolMotto }}&rdquo;
            </div>
        </td>

        {{-- RIGHT: Personality Dev + Conduct --}}
        <td style="width:65%;vertical-align:top;padding-left:5pt;border-left:1pt solid #000;">
            <div style="font-size:7.5pt;font-weight:bold;text-transform:uppercase;text-decoration:underline;text-align:center;margin-bottom:2pt;">
                Personality Development
            </div>
            <table class="pd-tbl">
                <thead>
                    <tr>
                        <th style="width:50%;text-align:left;padding-left:3pt;">ATTRIBUTES</th>
                        <th class="rc" style="width:17%;">Very Good /<br>Good</th>
                        <th class="rc" style="width:17%;">Average</th>
                        <th class="rc" style="width:16%;">Weak /<br>Poor</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($attrDefs) && count($attrDefs)>0)
                    @foreach($attrDefs as $idx=>$attr)
                    @php
                        $attrId=$getVal($attr,'id');
                        $saved=(is_object($studentRatings??null)&&method_exists($studentRatings,'get'))?($studentRatings->get($attrId)?->rating??null):null;
                        $isVG=$saved&&in_array(strtolower($saved),['very good','good']);
                        $isAvg=$saved&&strtolower($saved)==='average';
                        $isWeak=$saved&&in_array(strtolower($saved),['weak / poor','poor','weak']);
                    @endphp
                    <tr>
                        <td class="an">{{ $idx+1 }}. {{ $getVal($attr,'name') }}</td>
                        <td class="rc">{{ $isVG?'✓':'' }}</td>
                        <td class="rc">{{ $isAvg?'✓':'' }}</td>
                        <td class="rc">{{ $isWeak?'✓':'' }}</td>
                    </tr>
                    @endforeach
                    @for($i=count($attrDefs);$i<12;$i++)
                    <tr><td class="an">{{ $i+1 }}.</td><td class="rc"></td><td class="rc"></td><td class="rc"></td></tr>
                    @endfor
                    @else
                    @foreach(['Conduct','Neatness','Tolerance','Sociable','Initiative','Dependability','Honesty','Interest in Practical Work','Leadership','Co-operation','Observation','Interest in Studies'] as $i=>$an)
                    <tr><td class="an">{{ $i+1 }}. {{ $an }}</td><td class="rc"></td><td class="rc"></td><td class="rc"></td></tr>
                    @endforeach
                    @endif
                </tbody>
            </table>

            <table class="rmk" style="margin-top:3pt;">
                <tr>
                    <td style="width:25%"><span class="rmk-lbl">Attendance:</span></td>
                    <td style="width:17%"><span class="rmk-line">{{ $presentDays??'' }}</span></td>
                    <td style="width:7%;text-align:center"><span class="rmk-lbl">of</span></td>
                    <td style="width:17%"><span class="rmk-line">{{ $schoolDays??'' }}</span></td>
                    <td style="width:12%;text-align:right"><span class="rmk-lbl">Promoted:</span></td>
                    <td><span class="rmk-line">{{ $promotedTo }}</span></td>
                </tr>
                <tr>
                    <td style="width:25%"><span class="rmk-lbl">Conduct:</span></td>
                    <td><span class="rmk-line">{{ $conductVal }}</span></td>
                    <td colspan="2" style="text-align:right;padding-right:3pt"><span class="rmk-lbl">Attitude:</span></td>
                    <td colspan="2"><span class="rmk-line">{{ $attitudeVal }}</span></td>
                </tr>
                <tr>
                    <td colspan="2"><span class="rmk-lbl">Class Teacher's Remarks:</span></td>
                    <td colspan="4"><span class="rmk-line">{{ $classTeachRmk }}</span></td>
                </tr>
                <tr>
                    <td colspan="2"><span class="rmk-lbl">Head Teacher's Signature:</span></td>
                    <td colspan="4"><span class="rmk-line">&nbsp;</span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- SIGNATURES --}}
<table class="sig" style="margin-top:5pt;">
    <tr>
        <td style="width:34%"><div class="sig-line"></div><strong>CLASS TEACHER</strong><br>Name: ..................................<br>Date: ..................................</td>
        <td style="width:33%"><div class="sig-line"></div><strong>HEAD TEACHER / PRINCIPAL</strong><br>Name: ..................................<br>Date: ..................................</td>
        <td style="width:33%"><div class="sig-line"></div><strong>PARENT / GUARDIAN</strong><br>Name: ..................................<br>Date: ..................................</td>
    </tr>
</table>

<div class="footer">{{ $schoolName??config('app.name') }} &bull; JHS Terminal Report Card &bull; Term {{ $attempt }}, {{ date('Y') }}</div>

</div>
</body>
</html>
