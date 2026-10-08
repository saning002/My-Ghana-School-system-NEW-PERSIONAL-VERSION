@extends('layouts.app')
@section('title','Edit Student Report')
@section('subtitle',$student->user->full_name ?? 'Student Report')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

<div class="flex items-center gap-3 mb-2">
    <a href="{{ route('admin.student-reports.index', ['program_id'=>$program->id,'attempt'=>$attempt]) }}"
       class="text-sm text-slate-500 hover:text-slate-700 font-semibold flex items-center gap-1">
        <i class="fas fa-arrow-left text-xs"></i> Back to List
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-sm font-bold text-slate-700">{{ $student->user->full_name }}</span>
</div>

<form method="POST" action="{{ route('admin.student-reports.save', $student) }}">
    @csrf

    <input type="hidden" name="program_id" value="{{ $program->id }}">
    <input type="hidden" name="attempt"    value="{{ $attempt }}">

    {{-- Conduct / Attitude / Interest --}}
    <div class="card p-5 space-y-4">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="fas fa-star text-amber-500"></i>
            Conduct, Attitude & Interest
        </h3>

        @php
            $conductOptions  = ['Excellent','Very Good','Good','Fair','Poor'];
            $attitudeOptions = ['Excellent','Very Good','Good','Fair','Poor','Needs Improvement'];
            $interestOptions = ['Very Keen','Keen','Average','Low'];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Conduct</label>
                <input list="conduct-list" name="conduct"
                    value="{{ old('conduct', $report->conduct ?? '') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="e.g. Excellent">
                <datalist id="conduct-list">
                    @foreach($conductOptions as $o)<option value="{{ $o }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Attitude</label>
                <input list="attitude-list" name="attitude"
                    value="{{ old('attitude', $report->attitude ?? '') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="e.g. Very Good">
                <datalist id="attitude-list">
                    @foreach($attitudeOptions as $o)<option value="{{ $o }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Interest</label>
                <input list="interest-list" name="interest"
                    value="{{ old('interest', $report->interest ?? '') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="e.g. Keen">
                <datalist id="interest-list">
                    @foreach($interestOptions as $o)<option value="{{ $o }}">@endforeach
                </datalist>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Class / Form Teacher's Remarks</label>
            <textarea name="class_teacher_remark" rows="2"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none"
                placeholder="e.g. Promoted to the next class. Keep it up!">{{ old('class_teacher_remark', $report->class_teacher_remark ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Head Teacher's Remarks</label>
            <textarea name="head_teacher_remark" rows="2"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none"
                placeholder="e.g. Well done. Continue to work hard.">{{ old('head_teacher_remark', $report->head_teacher_remark ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Promoted To</label>
            <input type="text" name="promoted_to"
                value="{{ old('promoted_to', $report->promoted_to ?? '') }}"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                placeholder="e.g. Class 4, Next Level, JHS 2...">
        </div>
    </div>

    {{-- Personality Development Ratings --}}
    <div class="card p-5">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
            <i class="fas fa-user-check text-indigo-500"></i>
            Personality Development Ratings
        </h3>

        @if($attributes->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-8 text-center text-slate-400">
            <p class="text-sm font-semibold">No attributes defined.</p>
            <a href="{{ route('admin.student-reports.attributes') }}" class="text-xs text-indigo-600 font-bold mt-1 inline-block">
                <i class="fas fa-plus mr-1"></i>Add Attributes First
            </a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-2 text-left w-8">#</th>
                        <th class="py-2 text-left">Attribute</th>
                        @foreach(['Very Good','Good','Average','Weak / Poor'] as $col)
                        <th class="py-2 text-center px-3">{{ $col }}</th>
                        @endforeach
                        <th class="py-2 text-center px-2">Clear</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($attributes as $attr)
                    @php
                        $saved = $existingRatings->get($attr->id)?->rating ?? null;
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 text-xs text-slate-400">{{ $loop->iteration }}</td>
                        <td class="py-2.5 font-semibold text-slate-700">{{ $attr->name }}</td>
                        @foreach(['Very Good','Good','Average','Weak / Poor'] as $rating)
                        <td class="py-2.5 text-center px-3">
                            <label class="cursor-pointer inline-flex items-center justify-center">
                                <input type="radio"
                                    name="ratings[{{ $attr->id }}]"
                                    value="{{ $rating }}"
                                    {{ $saved === $rating ? 'checked' : '' }}
                                    class="w-4 h-4 accent-yellow-500 cursor-pointer">
                            </label>
                        </td>
                        @endforeach
                        <td class="py-2.5 text-center px-2">
                            <label class="cursor-pointer text-slate-300 hover:text-red-400 transition-colors" title="Clear rating">
                                <input type="radio" name="ratings[{{ $attr->id }}]" value="" {{ !$saved ? 'checked' : '' }} class="sr-only">
                                <i class="fas fa-times text-xs"></i>
                            </label>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="flex gap-3 pb-6">
        <button type="submit"
            class="btn-gold px-8 py-3 rounded-2xl text-sm font-bold shadow active:scale-95 inline-flex items-center gap-2">
            <i class="fas fa-save text-xs"></i> Save Report
        </button>
        <a href="{{ route('admin.student-reports.index', ['program_id'=>$program->id,'attempt'=>$attempt]) }}"
           class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-sm font-bold transition-colors">
            Cancel
        </a>
    </div>

</form>
</div>
@endsection
