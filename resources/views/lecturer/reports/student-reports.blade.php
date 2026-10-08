@extends('layouts.app')
@section('title','Student Reports')
@section('subtitle','Fill conduct, attitude and personality ratings for your class')

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="{ mode: 'list' }">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

{{-- Attempt selector + mode switcher --}}
<div class="card p-4 flex flex-wrap gap-4 items-center justify-between">
    <form method="GET" class="flex items-center gap-3">
        <label class="text-xs font-bold uppercase tracking-wider text-slate-600">Attempt:</label>
        <select name="attempt" onchange="this.form.submit()"
            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
            @foreach(range(1,5) as $a)
            <option value="{{ $a }}" {{ $attempt==$a?'selected':'' }}>Attempt {{ $a }}</option>
            @endforeach
        </select>
    </form>
    <div class="flex gap-2">
        <button @click="mode='list'" :class="mode==='list'?'btn-gold shadow':'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5">
            <i class="fas fa-list text-[10px]"></i> Fill Individually
        </button>
        <button @click="mode='bulk'" :class="mode==='bulk'?'btn-gold shadow':'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5">
            <i class="fas fa-layer-group text-[10px]"></i> Bulk Fill All
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODE A: INDIVIDUAL LIST
     ═══════════════════════════════════════════════════════════════ --}}
<div x-show="mode==='list'" x-cloak>
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800">{{ $program->name }} — Attempt {{ $attempt }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $students->count() }} students ·
                    <span class="text-emerald-600 font-bold">{{ $reports->count() }} reports filled</span>
                    @if($students->count() > $reports->count())
                    · <span class="text-amber-600 font-bold">{{ $students->count() - $reports->count() }} pending</span>
                    @endif
                </p>
            </div>
        </div>
        @forelse($students as $student)
        @php $rpt = $reports->get($student->id); @endphp
        <a href="{{ route('lecturer.student-reports.edit', [$student, 'attempt'=>$attempt]) }}"
           class="flex items-center gap-4 px-5 py-3.5 hover:bg-yellow-50 transition-colors group border-b border-slate-50 last:border-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-white text-xs font-extrabold shrink-0">
                {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-800 group-hover:text-yellow-700 transition-colors">{{ $student->user->full_name }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ $student->student_id }}</p>
                @if($rpt)
                <p class="text-[10px] text-slate-500 mt-0.5">
                    @if($rpt->conduct)<span class="mr-2">Conduct: <strong>{{ $rpt->conduct }}</strong></span>@endif
                    @if($rpt->class_teacher_remark)<span class="text-emerald-600">Remark saved</span>@endif
                </p>
                @endif
            </div>
            @if($rpt)
            <span class="shrink-0 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5">
                <i class="fas fa-check text-[8px]"></i> Done
            </span>
            @else
            <span class="shrink-0 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5">
                <i class="fas fa-edit text-[8px]"></i> Fill
            </span>
            @endif
            <i class="fas fa-chevron-right text-slate-300 text-xs shrink-0"></i>
        </a>
        @empty
        <div class="py-12 text-center text-slate-400">
            <p class="text-sm font-semibold">No students found in your class.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODE B: BULK FILL — one form for all students at once
     ═══════════════════════════════════════════════════════════════ --}}
<div x-show="mode==='bulk'" x-cloak>

    @php
        $attributes = \App\Models\AttributeDefinition::where('is_active',true)->orderBy('sort_order')->get();
        $ratingOpts = ['Very Good','Good','Average','Weak / Poor'];
        $conductOpts  = ['Excellent','Very Good','Good','Fair','Poor'];
        $attitudeOpts = ['Excellent','Very Good','Good','Fair','Poor'];
        $interestOpts = ['Very Keen','Keen','Average','Low'];
    @endphp

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 font-medium mb-3">
        <i class="fas fa-info-circle mr-1 text-amber-500"></i>
        Bulk fill applies the <strong>same values</strong> to all students at once.
        You can still go back and edit individual students to override specific values.
    </div>

    <form method="POST" action="{{ route('lecturer.student-reports.bulk-save') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="program_id" value="{{ $program->id }}">
        <input type="hidden" name="attempt"    value="{{ $attempt }}">

        {{-- Student checkboxes --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-extrabold text-slate-800">Select Students to Apply To</h3>
                <button type="button" onclick="toggleAllStudents()" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-xl hover:bg-indigo-100 transition-colors">
                    Select / Deselect All
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($students as $student)
                @php $rpt = $reports->get($student->id); @endphp
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-amber-50 transition-colors has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50">
                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                        class="student-bulk-chk rounded accent-amber-500 w-4 h-4 shrink-0" checked>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $student->user->full_name }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $student->student_id }}</p>
                    </div>
                    @if($rpt)
                    <span class="text-[9px] font-bold text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded-full shrink-0">Done</span>
                    @endif
                </label>
                @endforeach
            </div>
        </div>

        {{-- Bulk conduct/attitude/interest/remarks --}}
        <div class="card p-5 space-y-4">
            <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fas fa-star text-amber-500"></i> Conduct, Attitude & Remarks (applied to all selected)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Conduct</label>
                    <input list="bulk-conduct" name="conduct" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="e.g. Good">
                    <datalist id="bulk-conduct">@foreach($conductOpts as $o)<option value="{{ $o }}">@endforeach</datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Attitude</label>
                    <input list="bulk-attitude" name="attitude" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="e.g. Very Good">
                    <datalist id="bulk-attitude">@foreach($attitudeOpts as $o)<option value="{{ $o }}">@endforeach</datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Interest</label>
                    <input list="bulk-interest" name="interest" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="e.g. Keen">
                    <datalist id="bulk-interest">@foreach($interestOpts as $o)<option value="{{ $o }}">@endforeach</datalist>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Class / Form Teacher's Remarks</label>
                <textarea name="class_teacher_remark" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none" placeholder="e.g. Good performance. Keep it up!"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Promoted To</label>
                <input type="text" name="promoted_to" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="e.g. Next Level, Class 4...">
            </div>
        </div>

        {{-- Bulk personality ratings --}}
        @if($attributes->isNotEmpty())
        <div class="card p-5">
            <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                <i class="fas fa-user-check text-indigo-500"></i> Personality Development Ratings (applied to all selected)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <th class="py-2 text-left w-8">#</th>
                            <th class="py-2 text-left">Attribute</th>
                            @foreach($ratingOpts as $col)
                            <th class="py-2 text-center px-3">{{ $col }}</th>
                            @endforeach
                            <th class="py-2 text-center px-2 text-[10px]">Skip</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($attributes as $attr)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 text-xs text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-2.5 font-semibold text-slate-700">{{ $attr->name }}</td>
                            @foreach($ratingOpts as $rating)
                            <td class="py-2.5 text-center px-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="ratings[{{ $attr->id }}]" value="{{ $rating }}"
                                        class="w-4 h-4 accent-yellow-500 cursor-pointer">
                                </label>
                            </td>
                            @endforeach
                            <td class="py-2.5 text-center px-2">
                                <label class="cursor-pointer text-slate-300 hover:text-slate-500" title="Leave unchanged">
                                    <input type="radio" name="ratings[{{ $attr->id }}]" value="" checked class="sr-only">
                                    <i class="fas fa-minus text-xs"></i>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="flex gap-3 pb-4">
            <button type="submit"
                class="btn-gold px-8 py-3 rounded-2xl text-sm font-bold shadow active:scale-95 inline-flex items-center gap-2">
                <i class="fas fa-layer-group text-xs"></i> Apply to Selected Students
            </button>
            <button type="button" @click="mode='list'"
                class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-sm font-bold transition-colors">
                Cancel
            </button>
        </div>
    </form>
</div>

</div>

@push('scripts')
<script>
function toggleAllStudents() {
    const boxes = document.querySelectorAll('.student-bulk-chk');
    const allChecked = [...boxes].every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
}
</script>
@endpush
@endsection
