@extends('layouts.app')
@section('title','Scheme of Learning')
@section('subtitle','Your weekly teaching plan')

@section('content')
<div class="space-y-5">

<form method="GET" class="card p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program</label>
        <select name="program_id" onchange="this.form.submit()"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <option value="">— Select Program —</option>
            @foreach($programs as $p)
            <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Course</label>
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
    @if($selectedProgram && $selectedCourse)
    <a href="{{ route('lecturer.scheme-of-learning.create', ['program_id'=>$selectedProgram->id,'course_id'=>$selectedCourse->id,'academic_year'=>$selectedYear]) }}"
       class="btn-gold inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold shadow active:scale-95">
        <i class="fas fa-plus text-xs"></i> Add Entry
    </a>
    @endif
</form>

@if($selectedProgram && ($selectedCourse || $isAllCourses))
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">{{ $selectedProgram->name }} — {{ $isAllCourses ? 'All Courses' : $selectedCourse->name }}</h3>
        <p class="text-xs text-slate-500 mt-0.5">{{ $selectedYear }} · {{ $entries->count() }} entries</p>
    </div>
    @if($entries->isEmpty())
    <div class="py-14 text-center text-slate-400">
        <i class="fas fa-book-open text-3xl mb-2 block"></i>
        <p class="text-sm font-semibold">No entries yet. Add your first week plan.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b bg-slate-50">
                    <th class="px-4 py-3 text-left w-16">Week</th>
                    <th class="px-4 py-3 text-left">Topic</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Subtopics</th>
                    <th class="px-4 py-3 text-left hidden lg:table-cell">Objectives</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Assessment</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($entries as $entry)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-yellow-100 text-yellow-800 text-xs font-extrabold">{{ $entry->week_number }}</span>
                    </td>
                    <td class="px-4 py-3 font-bold text-slate-800">
                        <div class="flex items-center gap-2">
                            {{ $entry->topic }}
                            @if($entry->pdf_path)
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 text-red-500 px-2 py-0.5 text-[10px] font-bold border border-red-100">
                                <i class="fas fa-file-pdf text-[8px]"></i> PDF
                            </span>
                            @endif
                        </div>
                        @if($entry->term)<div class="text-[10px] text-slate-400 font-normal">{{ $entry->term }}</div>@endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 hidden md:table-cell max-w-[180px]">{{ Str::limit($entry->subtopics, 80) }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500 hidden lg:table-cell max-w-[180px]">{{ Str::limit($entry->learning_objectives, 80) }}</td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        @if($entry->assessment_type)
                        <span class="rounded-full bg-indigo-100 text-indigo-700 px-2.5 py-0.5 text-[11px] font-bold">{{ $entry->assessment_type }}</span>
                        @else<span class="text-slate-300 text-xs">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- PDF: view / download (admin-uploaded) --}}
                            @if($entry->pdf_path)
                            <a href="{{ route('lecturer.scheme-of-learning.pdf', $entry) }}"
                               target="_blank"
                               title="View PDF"
                               class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                                <i class="fas fa-file-pdf text-[10px]"></i>
                            </a>
                            <a href="{{ route('lecturer.scheme-of-learning.pdf', $entry) }}?download=1"
                               title="Download PDF"
                               class="w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                <i class="fas fa-download text-[10px]"></i>
                            </a>
                            @endif
                            <a href="{{ route('lecturer.scheme-of-learning.edit', $entry) }}"
                               class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-yellow-50 hover:text-yellow-700 text-slate-500 transition-colors">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            <form method="POST" action="{{ route('lecturer.scheme-of-learning.destroy', $entry) }}"
                                  onsubmit="return confirm('Delete this entry?')">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500 transition-colors">
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
@else
<div class="card p-12 text-center text-slate-400">
    <i class="fas fa-graduation-cap text-3xl mb-2 block"></i>
    <p class="text-sm font-semibold">Select a program and course above to view or add entries.</p>
</div>
@endif
</div>
@endsection
