@extends('layouts.app')
@section('title','Scheme of Learning')
@section('subtitle','Weekly teaching plan per course and program')

@section('content')
<div class="space-y-5" x-data="{ mode: '{{ request('mode','entries') }}' }">

{{-- ── Filters ────────────────────────────────────────────────────────────── --}}
<div class="card p-5">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <input type="hidden" name="mode" :value="mode">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program *</label>
            <select name="program_id" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">— Select Program —</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Course *</label>
            <select name="course_id" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                {{ !$selectedProgram?'disabled':'' }}>
                <option value="">— Select Course —</option>
                @if($selectedProgram)
                <option value="all" {{ request('course_id')==='all'?'selected':'' }}>📚 All Courses</option>
                @endif
                @foreach($courses as $c)
                <option value="{{ $c->id }}" {{ request('course_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Academic Year</label>
            <select name="academic_year" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                @foreach($years as $yr)
                <option value="{{ $yr }}" {{ $selectedYear==$yr?'selected':'' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </div>
        {{-- Term filter (optional) --}}
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Term / Semester</label>
            <input type="text" name="term" value="{{ request('term') }}" placeholder="e.g. Term 1"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                onchange="this.form.submit()">
        </div>
    </form>
</div>

@if($selectedProgram && ($selectedCourse || $isAllCourses))

{{-- ── Dual-Mode Tab Bar ───────────────────────────────────────────────────── --}}
<div class="flex gap-1 p-1 rounded-2xl bg-slate-100 border border-slate-200 shadow-inner">
    <button @click="mode='entries'"
        :class="mode==='entries'
            ? 'bg-white text-slate-900 shadow border border-slate-200/70'
            : 'text-slate-500 hover:text-slate-700 hover:bg-white/50'"
        class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold transition-all">
        <i class="fas fa-list-ul text-xs"></i>
        <span>Create Entries</span>
        <span class="ml-1 rounded-full bg-yellow-100 text-yellow-800 text-[10px] font-extrabold px-2 py-0.5">
            {{ $entries->count() }}
        </span>
    </button>
    <button @click="mode='pdf'"
        :class="mode==='pdf'
            ? 'bg-white text-slate-900 shadow border border-slate-200/70'
            : 'text-slate-500 hover:text-slate-700 hover:bg-white/50'"
        class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold transition-all">
        <i class="fas fa-file-pdf text-xs text-red-500"></i>
        <span>Upload Full PDF</span>
        @php $hasPdf = $entries->whereNotNull('pdf_path')->first(); @endphp
        @if($hasPdf)
        <span class="ml-1 rounded-full bg-red-100 text-red-700 text-[10px] font-extrabold px-2 py-0.5">PDF</span>
        @endif
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODE A — CREATE ENTRIES (weekly topic-by-topic plan)
     ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="mode==='entries'" x-cloak>
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b bg-slate-50">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800">{{ $selectedProgram->name }} — {{ $isAllCourses ? 'All Courses' : ($selectedCourse?->name ?? 'Course') }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Academic Year {{ $selectedYear }}
                    @if(request('term')) · {{ request('term') }} @endif
                    · {{ $entries->count() }} week entries
                </p>
            </div>
            @if(! $isAllCourses && $selectedCourse)
            <a href="{{ route('admin.scheme-of-learning.create', ['program_id'=>$selectedProgram->id,'course_id'=>$selectedCourse->id,'academic_year'=>$selectedYear]) }}"
               class="btn-gold inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold shadow active:scale-95">
                <i class="fas fa-plus text-xs"></i> Add Week Entry
            </a>
            @elseif($isAllCourses)
            <a href="{{ route('admin.scheme-of-learning.create', ['program_id'=>$selectedProgram->id,'academic_year'=>$selectedYear]) }}"
               class="btn-gold inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold shadow active:scale-95">
                <i class="fas fa-plus text-xs"></i> Add Week Entry
            </a>
            @endif
        </div>

        @if($entries->isEmpty())
        <div class="py-16 text-center">
            <i class="fas fa-book-open text-4xl text-slate-200 mb-3 block"></i>
            <p class="text-slate-500 font-semibold text-sm">No entries yet for {{ $isAllCourses ? 'this program' : 'this course' }}.</p>
            <p class="text-xs text-slate-400 mt-1">You can add individual week entries here, or switch to <strong>Upload Full PDF</strong> to upload the entire scheme as a PDF.</p>
            @if(! $isAllCourses && $selectedCourse)
            <a href="{{ route('admin.scheme-of-learning.create', ['program_id'=>$selectedProgram->id,'course_id'=>$selectedCourse->id,'academic_year'=>$selectedYear]) }}"
               class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 btn-gold rounded-xl text-sm font-bold">
                <i class="fas fa-plus text-xs"></i> Add First Week Entry
            </a>
            @elseif($isAllCourses)
            <a href="{{ route('admin.scheme-of-learning.create', ['program_id'=>$selectedProgram->id,'academic_year'=>$selectedYear]) }}"
               class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 btn-gold rounded-xl text-sm font-bold">
                <i class="fas fa-plus text-xs"></i> Add First Week Entry
            </a>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50">
                        @if($isAllCourses)
                        <th class="px-4 py-3 text-left">Course</th>
                        @endif
                        <th class="px-4 py-3 text-left w-14">Week</th>
                        <th class="px-4 py-3 text-left">Topic</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Subtopics</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Objectives</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Methods</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Assessment</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Lecturer</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($entries as $entry)
                    <tr class="hover:bg-slate-50 transition-colors">
                        @if($isAllCourses)
                        <td class="px-4 py-3 font-semibold text-slate-700 text-xs whitespace-nowrap">
                            {{ $entry->course?->name ?? '—' }}
                        </td>
                        @endif
                        <td class="px-4 py-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-yellow-100 text-yellow-800 text-xs font-extrabold">
                                {{ $entry->week_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-bold text-slate-800 max-w-[200px]">
                            <div class="flex items-center gap-2">
                                {{ $entry->topic }}
                                @if($entry->pdf_path)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 text-red-500 px-2 py-0.5 text-[10px] font-bold border border-red-100 shrink-0">
                                    <i class="fas fa-file-pdf text-[8px]"></i> PDF
                                </span>
                                @endif
                            </div>
                            @if($entry->term)
                            <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $entry->term }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 hidden md:table-cell max-w-[180px] text-xs">{{ Str::limit($entry->subtopics, 80) }}</td>
                        <td class="px-4 py-3 text-slate-600 hidden lg:table-cell max-w-[180px] text-xs">{{ Str::limit($entry->learning_objectives, 80) }}</td>
                        <td class="px-4 py-3 text-slate-600 hidden lg:table-cell text-xs">{{ $entry->teaching_methods }}</td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($entry->assessment_type)
                            <span class="rounded-full bg-indigo-100 text-indigo-700 px-2.5 py-0.5 text-[11px] font-bold">{{ $entry->assessment_type }}</span>
                            @else<span class="text-slate-300 text-xs">—</span>@endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 hidden lg:table-cell">{{ $entry->lecturer?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($entry->pdf_path)
                                <a href="{{ route('admin.scheme-of-learning.pdf', $entry) }}" target="_blank" title="View PDF"
                                   class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                                    <i class="fas fa-file-pdf text-[10px]"></i>
                                </a>
                                <a href="{{ route('admin.scheme-of-learning.pdf', $entry) }}?download=1" title="Download PDF"
                                   class="w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                    <i class="fas fa-download text-[10px]"></i>
                                </a>
                                @endif
                                <a href="{{ route('admin.scheme-of-learning.edit', $entry) }}"
                                   class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-yellow-50 hover:text-yellow-700 transition-colors">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.scheme-of-learning.destroy', $entry) }}"
                                      onsubmit="return confirm('Delete week {{ $entry->week_number }} entry?')">
                                    @csrf @method('DELETE')
                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODE B — UPLOAD FULL PDF SCHEME
     Admin uploads one PDF that covers the entire term/year scheme.
     Lecturers view and download it from their portal.
     ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="mode==='pdf'" x-cloak>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Upload panel --}}
        <div class="card p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                    <i class="fas fa-file-upload text-red-500 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800">Upload Full Scheme PDF</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Upload one PDF covering the complete term or year scheme.</p>
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 mb-4 text-xs text-amber-800 font-medium">
                <i class="fas fa-info-circle mr-1 text-amber-500"></i>
                This PDF is <strong>separate</strong> from the weekly entries above. Lecturers will see it as a downloadable scheme document in their portal.
            </div>

            {{-- Create a dummy entry to attach the PDF to, scoped to week 0 --}}
            <form method="POST"
                  action="{{ route('admin.scheme-of-learning.store') }}"
                  enctype="multipart/form-data"
                  class="space-y-4">
                @csrf
                {{-- Hidden fields — course + program already selected --}}
                <input type="hidden" name="program_id"   value="{{ $selectedProgram->id }}">
                @if(! $isAllCourses && $selectedCourse)
                <input type="hidden" name="course_id"    value="{{ $selectedCourse->id }}">
                @endif
                <input type="hidden" name="academic_year" value="{{ $selectedYear }}">
                <input type="hidden" name="week_number"  value="0">
                <input type="hidden" name="topic"        value="[Full Scheme PDF]">
                <input type="hidden" name="_redirect_mode" value="pdf">

                @if($isAllCourses)
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Select Course *</label>
                    <select name="course_id" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                        <option value="">— Select Course —</option>
                        @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Term / Semester</label>
                    <input type="text" name="term" placeholder="e.g. Term 1, Full Year"
                        value="{{ request('term') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Description / Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="Optional notes about this scheme document..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                </div>

                <div class="rounded-xl border-2 border-dashed border-red-200 bg-red-50/30 p-5 text-center" id="pdf-drop-zone">
                    <i class="fas fa-file-pdf text-3xl text-red-400 mb-2 block"></i>
                    <p class="text-sm font-bold text-slate-700 mb-1">Drop PDF here or click to browse</p>
                    <p class="text-xs text-slate-400 mb-3">PDF only · Max 20MB</p>
                    <input type="file" name="pdf_file" accept=".pdf" required id="pdf-upload-input"
                        class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-500 file:text-white hover:file:bg-red-600 transition-colors cursor-pointer"
                        onchange="updatePdfLabel(this)">
                    <p id="pdf-file-label" class="text-xs text-emerald-600 font-semibold mt-2 hidden"></p>
                    @error('pdf_file')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-400 hover:to-rose-500 text-white text-sm font-bold py-3 shadow transition-all active:scale-95">
                    <i class="fas fa-upload mr-2"></i> Upload Scheme PDF
                </button>
            </form>
        </div>

        {{-- Existing PDFs panel --}}
        <div class="card p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-folder-open text-slate-500 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800">Uploaded PDFs</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Lecturers can view and download these from their portal.</p>
                </div>
            </div>

            @php
                $pdfEntries = $entries->whereNotNull('pdf_path')->sortByDesc('created_at');
            @endphp

            @if($pdfEntries->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-12 text-center">
                <i class="fas fa-file-pdf text-3xl text-slate-200 mb-2 block"></i>
                <p class="text-sm font-semibold text-slate-400">No PDFs uploaded yet.</p>
                <p class="text-xs text-slate-400 mt-1">Upload a PDF using the form on the left.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($pdfEntries as $pe)
                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:border-red-200 hover:bg-red-50/20 transition-colors">
                    <i class="fas fa-file-pdf text-red-500 text-xl shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate">
                            @if($isAllCourses && $pe->course) [{{ $pe->course->name }}] @endif
                            {{ $pe->term ?: 'Full Scheme' }}
                            @if($pe->topic !== '[Full Scheme PDF]') · {{ $pe->topic }} @endif
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            Uploaded {{ $pe->created_at?->diffForHumans() }}
                            @if($pe->remarks) · {{ Str::limit($pe->remarks, 60) }} @endif
                        </p>
                    </div>
                    <div class="flex gap-1.5 shrink-0">
                        <a href="{{ route('admin.scheme-of-learning.pdf', $pe) }}" target="_blank"
                           title="View PDF"
                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                        <a href="{{ route('admin.scheme-of-learning.pdf', $pe) }}?download=1"
                           title="Download PDF"
                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                            <i class="fas fa-download text-xs"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.scheme-of-learning.destroy', $pe) }}"
                              onsubmit="return confirm('Delete this PDF entry?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

@elseif($selectedProgram)
<div class="card p-10 text-center text-slate-400">
    <i class="fas fa-book text-3xl mb-2 block"></i>
    <p class="text-sm font-semibold">Select a course to view the scheme of learning.</p>
</div>
@else
<div class="card p-10 text-center text-slate-400">
    <i class="fas fa-graduation-cap text-3xl mb-2 block"></i>
    <p class="text-sm font-semibold">Select a program and course to get started.</p>
</div>
@endif

</div>

@push('scripts')
<script>
function updatePdfLabel(input) {
    const label = document.getElementById('pdf-file-label');
    if (input.files && input.files[0]) {
        const size = (input.files[0].size / 1024 / 1024).toFixed(2);
        label.textContent = '✓ ' + input.files[0].name + ' (' + size + ' MB)';
        label.classList.remove('hidden');
    }
}
</script>
@endpush
@endsection
