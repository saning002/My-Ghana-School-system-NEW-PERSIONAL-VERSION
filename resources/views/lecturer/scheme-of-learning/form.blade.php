@extends('layouts.app')
@section('title', isset($entry) ? 'Edit Scheme Entry' : 'Add Scheme Entry')
@section('subtitle', 'Weekly topic plan')

@section('content')
@php $isEdit = isset($entry); @endphp
<div class="max-w-3xl mx-auto">
<form method="POST"
      action="{{ $isEdit ? route('lecturer.scheme-of-learning.update', $entry) : route('lecturer.scheme-of-learning.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-4 border-b bg-yellow-50">
            <h3 class="text-sm font-bold text-yellow-900"><i class="fas fa-book-open text-yellow-600 mr-1.5"></i>{{ $isEdit ? 'Edit Entry' : 'New Week Entry' }}</h3>
        </div>
        <div class="px-5 py-5 space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program *</label>
                    <select name="program_id" id="program_id" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="">— Select —</option>
                        @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ old('program_id',$isEdit?$entry->program_id:request('program_id'))==$p->id?'selected':'' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Course *</label>
                    <select name="course_id" id="course_id" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="">— Select —</option>
                        @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ old('course_id',$isEdit?$entry->course_id:request('course_id'))==$c->id?'selected':'' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Academic Year *</label>
                    <select name="academic_year" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ old('academic_year',$isEdit?$entry->academic_year:request('academic_year',$years[1]))==$yr?'selected':'' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Week Number *</label>
                    <input type="number" name="week_number" min="1" max="52" required
                        value="{{ old('week_number',$isEdit?$entry->week_number:'') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Term / Semester</label>
                    <input type="text" name="term" placeholder="e.g. Term 1"
                        value="{{ old('term',$isEdit?$entry->term:'') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Topic *</label>
                <input type="text" name="topic" required placeholder="Main topic for this week"
                    value="{{ old('topic',$isEdit?$entry->topic:'') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Subtopics</label>
                <textarea name="subtopics" rows="3" placeholder="Subtopics covered this week..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">{{ old('subtopics',$isEdit?$entry->subtopics:'') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Learning Objectives</label>
                <textarea name="learning_objectives" rows="3" placeholder="By end of week, students should be able to..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">{{ old('learning_objectives',$isEdit?$entry->learning_objectives:'') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Teaching Methods</label>
                    <input type="text" name="teaching_methods" placeholder="e.g. Lecture, Group Work"
                        value="{{ old('teaching_methods',$isEdit?$entry->teaching_methods:'') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Resources</label>
                    <input type="text" name="resources" placeholder="e.g. Textbook Chapter 3"
                        value="{{ old('resources',$isEdit?$entry->resources:'') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Assessment Type</label>
                    <select name="assessment_type"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="">— None —</option>
                        @foreach(['Quiz','Test','Class Exercise','Group Work','Project','Assignment','Observation'] as $at)
                        <option value="{{ $at }}" {{ old('assessment_type',$isEdit?$entry->assessment_type:'')==$at?'selected':'' }}>{{ $at }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Remarks</label>
                <textarea name="remarks" rows="2" placeholder="Additional notes..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">{{ old('remarks',$isEdit?$entry->remarks:'') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn-gold px-7 py-3 rounded-xl text-sm font-bold shadow active:scale-95">
            <i class="fas fa-save mr-1.5"></i> {{ $isEdit ? 'Save Changes' : 'Add Entry' }}
        </button>
        <a href="{{ route('lecturer.scheme-of-learning.index') }}"
           class="px-6 py-3 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-semibold transition-colors">Cancel</a>
    </div>
</form>
</div>

@push('scripts')
<script>
document.getElementById('program_id')?.addEventListener('change', function() {
    const pid = this.value;
    const sel = document.getElementById('course_id');
    sel.innerHTML = '<option value="">— Loading... —</option>';
    if (!pid) { sel.innerHTML = '<option value="">— Select —</option>'; return; }
    fetch(`/lecturer/scheme-of-learning/courses?program_id=${pid}`)
        .then(r => r.json())
        .then(courses => {
            sel.innerHTML = '<option value="">— Select Course —</option>';
            courses.forEach(c => sel.innerHTML += `<option value="${c.id}">${c.name}</option>`);
        });
});
</script>
@endpush
@endsection
