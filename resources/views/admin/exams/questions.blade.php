@extends('layouts.app')
@section('title','Exam Questions')
@section('subtitle','Review exam question documents uploaded by teachers')

@section('content')
<div class="space-y-5">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="card p-5">
    <form method="GET" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program</label>
            <select name="program_id" onchange="this.form.submit()" class="field-input w-full">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Course</label>
            <select name="course_id" onchange="this.form.submit()" class="field-input w-full" {{ $courses->isEmpty()?'disabled':'' }}>
                <option value="">All Courses</option>
                @foreach($courses as $c)
                <option value="{{ $c->id }}" {{ request('course_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Teacher</label>
            <select name="lecturer_id" onchange="this.form.submit()" class="field-input w-full">
                <option value="">All Teachers</option>
                @foreach($lecturers as $l)
                <option value="{{ $l->id }}" {{ request('lecturer_id')==$l->id?'selected':'' }}>{{ $l->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Academic Year</label>
            <select name="academic_year" onchange="this.form.submit()" class="field-input w-full">
                <option value="">All Years</option>
                @foreach($years as $yr)
                <option value="{{ $yr }}" {{ request('academic_year')===$yr?'selected':'' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Term</label>
            <input type="text" name="term" value="{{ request('term') }}" class="field-input w-full" placeholder="e.g. Term 1" onchange="this.form.submit()">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Type</label>
            <select name="document_type" onchange="this.form.submit()" class="field-input w-full">
                <option value="">All Types</option>
                <option value="pdf"  {{ request('document_type')==='pdf' ?'selected':'' }}>📄 PDF</option>
                <option value="word" {{ request('document_type')==='word'?'selected':'' }}>📝 Word</option>
            </select>
        </div>
    </form>
    @if(request()->hasAny(['program_id','course_id','lecturer_id','academic_year','term','document_type']))
    <div class="mt-3">
        <a href="{{ route('admin.exam-questions.index') }}" class="text-xs font-bold text-slate-500 hover:text-red-500 transition-colors">
            <i class="fas fa-times mr-1"></i> Clear filters
        </a>
    </div>
    @endif
</div>

{{-- ── Stats bar ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    @php
        $total    = $documents->total();
        $pdfs     = \App\Models\ExamQuestion::when(request('program_id'), fn($q)=>$q->where('program_id',request('program_id')))->where('document_type','pdf')->count();
        $words    = \App\Models\ExamQuestion::when(request('program_id'), fn($q)=>$q->where('program_id',request('program_id')))->where('document_type','word')->count();
        $approved = \App\Models\ExamQuestion::where('is_approved',true)->count();
    @endphp
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-slate-800">{{ $total }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Total Docs</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-red-600">{{ $pdfs }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">PDF Files</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-blue-600">{{ $words }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Word Files</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-emerald-600">{{ $approved }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Approved</p>
    </div>
</div>

{{-- ── Documents list ───────────────────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
            <i class="fas fa-folder-open text-slate-400"></i>
            Exam Question Documents
            <span class="rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5">{{ $documents->total() }}</span>
        </h3>
    </div>

    @if($documents->isEmpty())
    <div class="py-16 text-center text-slate-400">
        <i class="fas fa-file-circle-question text-4xl mb-3 block"></i>
        <p class="text-sm font-semibold">No exam question documents uploaded yet.</p>
        <p class="text-xs text-slate-400 mt-1">Teachers upload documents from their <strong>Exams → Question Upload</strong> section.</p>
    </div>
    @else

    {{-- Desktop table --}}
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50">
                    <th class="px-4 py-3 text-left w-10"></th>
                    <th class="px-4 py-3 text-left">Document</th>
                    <th class="px-4 py-3 text-left">Course</th>
                    <th class="px-4 py-3 text-left">Class</th>
                    <th class="px-4 py-3 text-left">Teacher</th>
                    <th class="px-4 py-3 text-left">Year / Term</th>
                    <th class="px-4 py-3 text-left">Size</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($documents as $doc)
                <tr class="hover:bg-slate-50/70 transition-colors" x-data="{ showComment: false }">
                    <td class="px-4 py-3">
                        <i class="fas {{ $doc->icon }} text-xl"></i>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-bold text-slate-800">{{ $doc->title }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $doc->original_filename }}</p>
                        @if($doc->notes)
                        <p class="text-[11px] text-slate-500 mt-0.5 italic">{{ Str::limit($doc->notes, 60) }}</p>
                        @endif
                        {{-- Admin feedback (if set) --}}
                        @if($doc->notes && str_contains($doc->notes, '[Admin'))
                        <div class="mt-1.5 rounded-lg bg-violet-50 border border-violet-200 px-2.5 py-1.5 text-[11px] text-violet-800">
                            <i class="fas fa-comment text-[9px] mr-1 text-violet-500"></i>
                            {{ $doc->notes }}
                        </div>
                        @endif
                        {{-- Inline comment form --}}
                        <div x-show="showComment" x-cloak class="mt-2">
                            <form method="POST" action="{{ route('admin.exam-questions.comment', $doc) }}" class="flex gap-1.5">
                                @csrf
                                <input type="text" name="comment" placeholder="Add feedback for the teacher..."
                                    class="flex-1 text-xs px-2.5 py-1.5 border border-violet-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-300 bg-white">
                                <button type="submit" class="px-3 py-1.5 bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold rounded-lg whitespace-nowrap transition-colors">
                                    Save
                                </button>
                            </form>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ $doc->course->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ $doc->program->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ $doc->lecturer->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">
                        {{ $doc->academic_year }}
                        @if($doc->term)<br><span class="text-slate-400">{{ $doc->term }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $doc->file_size_label }}</td>
                    <td class="px-4 py-3">
                        @if($doc->is_approved)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5">
                            <i class="fas fa-check text-[8px]"></i> Approved
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5">
                            <i class="fas fa-clock text-[8px]"></i> Pending
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- View PDF inline --}}
                            @if($doc->document_type === 'pdf')
                            <a href="{{ route('admin.exam-questions.view', $doc) }}" target="_blank"
                               title="View PDF"
                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @endif
                            {{-- Download --}}
                            <a href="{{ route('admin.exam-questions.download', $doc) }}"
                               title="Download"
                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                <i class="fas fa-download text-xs"></i>
                            </a>
                            {{-- Approve / Unapprove toggle --}}
                            <form method="POST" action="{{ route('admin.exam-questions.approve', $doc) }}">
                                @csrf
                                <button type="submit"
                                    title="{{ $doc->is_approved ? 'Mark as pending' : 'Approve' }}"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors
                                        {{ $doc->is_approved ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' }}">
                                    <i class="fas {{ $doc->is_approved ? 'fa-rotate-left' : 'fa-check' }} text-xs"></i>
                                </button>
                            </form>
                            {{-- Comment toggle --}}
                            <button @click="showComment = !showComment" type="button" title="Add feedback"
                                :class="showComment ? 'bg-violet-200 text-violet-700' : 'bg-violet-50 text-violet-600 hover:bg-violet-100'"
                                class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors">
                                <i class="fas fa-comment text-xs"></i>
                            </button>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.exam-questions.destroy', $doc) }}"
                                  onsubmit="return confirm('Delete this document permanently?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="lg:hidden divide-y divide-slate-50">
        @foreach($documents as $doc)
        <div class="flex items-start gap-3 px-4 py-4 hover:bg-slate-50 transition-colors">
            <i class="fas {{ $doc->icon }} text-2xl shrink-0 mt-0.5"></i>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-bold text-slate-800">{{ $doc->title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $doc->course->name ?? '—' }} · {{ $doc->program->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $doc->lecturer->full_name ?? '—' }} · {{ $doc->academic_year }}</p>
                    </div>
                    @if($doc->is_approved)
                    <span class="shrink-0 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5">Approved</span>
                    @else
                    <span class="shrink-0 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5">Pending</span>
                    @endif
                </div>
                <div class="flex gap-2 mt-2">
                    <a href="{{ route('admin.exam-questions.download', $doc) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold">
                        <i class="fas fa-download text-[9px]"></i> Download
                    </a>
                    @if($doc->document_type === 'pdf')
                    <a href="{{ route('admin.exam-questions.view', $doc) }}" target="_blank"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">
                        <i class="fas fa-eye text-[9px]"></i> View
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($documents->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">
        {{ $documents->links() }}
    </div>
    @endif
    @endif
</div>

</div>

@push('styles')
<style>
.field-input { padding:9px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px; background:#f8fafc; outline:none; transition:all .15s; }
.field-input:focus { border-color:#eab308; box-shadow:0 0 0 3px rgba(234,179,8,.12); background:#fff; }
</style>
@endpush
@endsection
