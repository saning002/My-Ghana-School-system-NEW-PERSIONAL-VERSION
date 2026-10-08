@extends('layouts.app')
@section('title','Student Report')
@section('subtitle',$student->user->full_name ?? 'Student')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

<div class="flex items-center gap-3 mb-2">
    <a href="{{ route('lecturer.student-reports.index', ['attempt'=>$attempt]) }}"
       class="text-sm text-slate-500 hover:text-slate-700 font-semibold flex items-center gap-1">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-sm font-bold text-slate-700">{{ $student->user->full_name }}</span>
</div>

<form method="POST" action="{{ route('lecturer.student-reports.save', $student) }}">
    @csrf
    <input type="hidden" name="program_id" value="{{ $program->id }}">
    <input type="hidden" name="attempt"    value="{{ $attempt }}">

    {{-- Conduct / Interest / Remarks --}}
    <div class="card p-5 space-y-4">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="fas fa-star text-amber-500"></i> Conduct, Attitude & Remarks
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Conduct</label>
                <input list="conduct-opts" name="conduct" value="{{ old('conduct',$report->conduct??'') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="e.g. Excellent">
                <datalist id="conduct-opts">
                    @foreach(['Excellent','Very Good','Good','Fair','Poor'] as $o)<option value="{{ $o }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Attitude</label>
                <input list="attitude-opts" name="attitude" value="{{ old('attitude',$report->attitude??'') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="e.g. Very Good">
                <datalist id="attitude-opts">
                    @foreach(['Excellent','Very Good','Good','Fair','Poor','Needs Improvement'] as $o)<option value="{{ $o }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Interest</label>
                <input list="interest-opts" name="interest" value="{{ old('interest',$report->interest??'') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="e.g. Keen">
                <datalist id="interest-opts">
                    @foreach(['Very Keen','Keen','Average','Low'] as $o)<option value="{{ $o }}">@endforeach
                </datalist>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Class / Form Teacher's Remarks</label>
            <textarea name="class_teacher_remark" rows="2"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none"
                placeholder="e.g. Promoted to next class. Keep it up!">{{ old('class_teacher_remark',$report->class_teacher_remark??'') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Promoted To</label>
            <input type="text" name="promoted_to" value="{{ old('promoted_to',$report->promoted_to??'') }}"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                placeholder="e.g. Class 4, Next Level...">
        </div>
    </div>

    {{-- Personality Ratings --}}
    @if($attributes->isNotEmpty())
    <div class="card p-5">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
            <i class="fas fa-user-check text-indigo-500"></i> Personality Development Ratings
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-2 text-left w-8">#</th>
                        <th class="py-2 text-left">Attribute</th>
                        @foreach(['Very Good','Good','Average','Weak / Poor'] as $col)
                        <th class="py-2 text-center px-3">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($attributes as $attr)
                    @php $saved = $existingRatings->get($attr->id)?->rating ?? null; @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 text-xs text-slate-400">{{ $loop->iteration }}</td>
                        <td class="py-2.5 font-semibold text-slate-700">{{ $attr->name }}</td>
                        @foreach(['Very Good','Good','Average','Weak / Poor'] as $rating)
                        <td class="py-2.5 text-center px-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="ratings[{{ $attr->id }}]" value="{{ $rating }}"
                                    {{ $saved === $rating ? 'checked' : '' }}
                                    class="w-4 h-4 accent-yellow-500 cursor-pointer">
                            </label>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="flex gap-3 pb-6">
        <button type="submit"
            class="btn-gold px-8 py-3 rounded-2xl text-sm font-bold shadow active:scale-95 inline-flex items-center gap-2">
            <i class="fas fa-save text-xs"></i> Save Report
        </button>
        <a href="{{ route('lecturer.student-reports.index', ['attempt'=>$attempt]) }}"
           class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-sm font-bold transition-colors">
            Cancel
        </a>
    </div>
</form>

</div>
@endsection
