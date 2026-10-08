@extends('layouts.app')
@section('title','Exam Questions')
@section('subtitle','Upload exam question documents for admin review')

@section('content')
<div class="max-w-4xl mx-auto space-y-5" x-data="{ showForm: {{ $errors->any() ? 'true' : 'false' }} }">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

{{-- ── Upload Form ─────────────────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <button @click="showForm = !showForm"
        class="w-full flex items-center justify-between px-5 py-4 bg-slate-50 hover:bg-slate-100 transition-colors text-left border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-upload text-red-500 text-xs"></i>
            </div>
            <p class="text-sm font-extrabold text-slate-800">Upload Exam Question Document</p>
        </div>
        <i class="fas text-slate-400 text-xs" :class="showForm ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </button>

    <div x-show="showForm" x-cloak class="p-5">
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 mb-4 text-xs text-amber-800 font-medium">
            <i class="fas fa-info-circle mr-1 text-amber-500"></i>
            Upload PDF or Word (.doc/.docx) files. Admin will review and can download or print them from the Exams section.
        </div>

        <form method="POST" action="{{ route('lecturer.exam-questions.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Course / Subject *</label>
                    <select name="course_id" required class="field-input w-full">
                        <option value="">— Select Course —</option>
                        @foreach($assignments as $a)
                        <option value="{{ $a->course_id }}" {{ old('course_id')==$a->course_id?'selected':'' }}>
                            {{ $a->course->name }} ({{ $a->program->name ?? '' }})
                        </option>
                        @endforeach
                    </select>
                    @error('course_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Document Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="field-input w-full" placeholder="e.g. End of Term Exam 2026, Mid-Term Questions">
                    @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Academic Year</label>
                    <select name="academic_year" class="field-input w-full">
                        @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ old('academic_year', date('Y').'/'.(date('Y')+1))===$yr?'selected':'' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Term</label>
                    <input type="text" name="term" value="{{ old('term') }}" class="field-input w-full" placeholder="e.g. Term 1, Mid-Term">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Notes</label>
                <textarea name="notes" rows="2" class="field-input w-full resize-none"
                    placeholder="Any notes for the admin about this document...">{{ old('notes') }}</textarea>
            </div>

            <div class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-5" id="doc-drop-zone">
                <div class="text-center">
                    <i class="fas fa-file-upload text-3xl text-slate-300 mb-2 block"></i>
                    <p class="text-sm font-bold text-slate-700 mb-1">Select PDF or Word document</p>
                    <p class="text-xs text-slate-400 mb-3">PDF, DOC, DOCX · Max 20MB</p>
                    <input type="file" name="document" accept=".pdf,.doc,.docx" required id="doc-input"
                        class="mx-auto block text-sm text-slate-500 file:mr-3 file:py-2 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-white hover:file:bg-slate-700 transition-colors cursor-pointer"
                        onchange="updateDocLabel(this)">
                    <p id="doc-file-label" class="text-xs text-emerald-600 font-semibold mt-2 hidden"></p>
                    @error('document')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <button type="submit"
                class="btn-gold px-6 py-2.5 rounded-xl text-sm font-bold shadow active:scale-95 inline-flex items-center gap-2">
                <i class="fas fa-upload text-xs"></i> Upload Document
            </button>
        </form>
    </div>
</div>

{{-- ── My Uploaded Documents ───────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
            <i class="fas fa-folder-open text-slate-400"></i> My Uploaded Documents
            <span class="rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5">{{ $documents->total() }}</span>
        </h3>
        <form method="GET" class="flex gap-2">
            <select name="course_id" onchange="this.form.submit()"
                class="text-xs rounded-lg border border-slate-200 px-2 py-1.5 bg-white focus:outline-none">
                <option value="">All Courses</option>
                @foreach($assignments as $a)
                <option value="{{ $a->course_id }}" {{ request('course_id')==$a->course_id?'selected':'' }}>
                    {{ $a->course->name }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($documents->isEmpty())
    <div class="py-12 text-center text-slate-400">
        <i class="fas fa-file-upload text-3xl mb-2 block"></i>
        <p class="text-sm font-semibold">No documents uploaded yet.</p>
    </div>
    @else
    <div class="divide-y divide-slate-50">
        @foreach($documents as $doc)
        <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
            <i class="fas {{ $doc->icon }} text-2xl shrink-0"></i>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ $doc->title }}</p>
                    @if($doc->is_approved)
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Approved</span>
                    @else
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">Pending Review</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3 text-[11px] text-slate-500 mt-0.5">
                    <span>{{ $doc->course->name ?? '—' }}</span>
                    @if($doc->term)<span>{{ $doc->term }}</span>@endif
                    <span>{{ $doc->academic_year }}</span>
                    <span>{{ $doc->file_size_label }}</span>
                    <span>{{ $doc->created_at->diffForHumans() }}</span>
                </div>
                @if($doc->notes)
                <p class="text-[10px] text-slate-400 mt-0.5 italic">{{ Str::limit($doc->notes, 80) }}</p>
                @endif
            </div>
            <div class="flex gap-1.5 shrink-0">
                @if($doc->document_type === 'pdf')
                <a href="{{ route('lecturer.exam-questions.view', $doc) }}" target="_blank"
                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
                   title="View PDF">
                    <i class="fas fa-eye text-xs"></i>
                </a>
                @endif
                <a href="{{ route('lecturer.exam-questions.download', $doc) }}"
                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                   title="Download">
                    <i class="fas fa-download text-xs"></i>
                </a>
                <form method="POST" action="{{ route('lecturer.exam-questions.destroy', $doc) }}"
                      onsubmit="return confirm('Delete this document?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-400 hover:bg-red-100 transition-colors"
                        title="Delete">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @if($documents->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">{{ $documents->links() }}</div>
    @endif
    @endif
</div>

</div>

@push('styles')
<style>
.field-input { padding:10px 14px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px; background:#f8fafc; outline:none; transition:all .15s; }
.field-input:focus { border-color:#eab308; box-shadow:0 0 0 3px rgba(234,179,8,.12); background:#fff; }
</style>
@endpush

@push('scripts')
<script>
function updateDocLabel(input) {
    const label = document.getElementById('doc-file-label');
    if (input.files && input.files[0]) {
        const size = (input.files[0].size / 1024 / 1024).toFixed(2);
        label.textContent = '✓ ' + input.files[0].name + ' (' + size + ' MB)';
        label.classList.remove('hidden');
    }
}
</script>
@endpush
@endsection
