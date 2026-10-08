@extends('layouts.app')
@section('title','Academic Transcript')
@section('subtitle','Full academic history across all programs and attempts')

@section('content')
<div class="space-y-5">

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div class="sm:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Student *</label>
            <select name="student_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">-- Select Student --</option>
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id')==$s->id?'selected':'' }}>{{ $s->student_id }} — {{ $s->user->full_name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-scroll text-xs"></i> Generate Transcript
        </button>
    </form>
</div>

@if($student && $data)
{{-- Student header --}}
<div class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-violet-50 p-5 flex items-center gap-5">
    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-xl font-extrabold shadow">
        {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
    </div>
    <div class="flex-1">
        <h2 class="text-lg font-extrabold text-slate-900">{{ $student->user->full_name }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">{{ $student->student_id }} &bull; {{ $student->program?->name }}</p>
    </div>
    <a href="{{ route('admin.reports.transcript.pdf', ['student_id'=>$student->id]) }}"
       class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95 shrink-0">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
</div>

@if(empty($data['history']))
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
    <p class="text-sm font-semibold text-slate-400">No academic records found for this student.</p>
</div>
@else

@foreach($data['history'] as $entry)
@php $card = $entry['card']; $gc=['A1'=>'bg-emerald-100 text-emerald-800','A2'=>'bg-emerald-100 text-emerald-700','A3'=>'bg-teal-100 text-teal-800','B1'=>'bg-blue-100 text-blue-800','B2'=>'bg-blue-100 text-blue-700','B3'=>'bg-indigo-100 text-indigo-800','C'=>'bg-amber-100 text-amber-800','F'=>'bg-red-100 text-red-800']; @endphp
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-slate-800 to-slate-700">
        <div class="flex items-center gap-3">
            <span class="text-sm font-extrabold text-white">{{ $entry['program']->name }}</span>
            <span class="rounded-full bg-white/15 text-white/80 text-[10px] font-bold px-2.5 py-0.5">Attempt {{ $entry['attempt'] }}</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-white/60 font-semibold">Avg: <strong class="text-white">{{ $card['overall_average'] }}%</strong></span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $gc[$card['overall_grade']]??'bg-slate-100 text-slate-600' }}">{{ $card['overall_grade'] }}</span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $card['result']==='PASS'?'bg-emerald-100 text-emerald-800':'bg-red-100 text-red-800' }}">{{ $card['result'] }}</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50">
                    <th class="px-5 py-2.5 text-left">Subject</th>
                    <th class="px-4 py-2.5 text-center">Class Score</th>
                    <th class="px-4 py-2.5 text-center">Exam</th>
                    <th class="px-4 py-2.5 text-center">Total</th>
                    <th class="px-4 py-2.5 text-center">Grade</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($card['courses'] as $row)
                <tr>
                    <td class="px-5 py-2.5 font-medium text-slate-800">{{ $row['course']->name }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-600 text-xs">{{ $row['class_score'] }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-600 text-xs">{{ $row['exam_score'] }}</td>
                    <td class="px-4 py-2.5 text-center font-bold text-slate-800">{{ $row['aggregate'] }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $gc[$row['grade']]??'bg-slate-100 text-slate-600' }}">{{ $row['grade'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endif
@endif

</div>
@endsection
