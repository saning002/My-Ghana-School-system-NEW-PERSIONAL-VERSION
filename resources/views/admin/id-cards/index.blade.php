@extends('layouts.app')
@section('title', 'Student ID Cards')
@section('subtitle', 'Generate and print student ID cards')

@section('content')

<div class="page-hero-banner mb-5">
    <div class="hero-left">
        <h1><i class="fas fa-id-card mr-2" style="color:var(--brand)"></i>Student ID Cards</h1>
        <p>Select students and generate printable ID cards — front and back, 4 per A4 page</p>
    </div>
    <div class="hero-actions">
        <button type="button" onclick="printSelected()" class="btn-primary-action"
                id="print-btn" disabled>
            <i class="fas fa-print"></i> Generate Cards
        </button>
    </div>
</div>

@if(session('error'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold mb-5">
    <i class="fas fa-times-circle text-red-500 flex-shrink-0"></i> {{ session('error') }}
</div>
@endif

@if($incompleteCount > 0)
<div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 mb-5 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 flex-shrink-0"></i>
    <div>
        <p class="font-bold text-amber-800 text-sm">{{ $incompleteCount }} student(s) have incomplete profiles</p>
        <p class="text-xs text-amber-700 mt-1">Their cards will still print but with missing fields shown as dashes. To fix, update the student's profile with their full name, photo, and programme before generating cards.</p>
    </div>
</div>
@endif

{{-- Filter bar --}}
<div class="sc mb-5">
    <div class="sc-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Search by name or ID</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-input"
                       placeholder="Type student name or ID...">
            </div>
            <div style="min-width:180px;">
                <label class="form-label">Programme</label>
                <select name="program_id" class="form-input">
                    <option value="">All Programmes</option>
                    @foreach($programs as $p)
                    <option value="{{ $p->id }}" {{ (string)($programId ?? '') === (string)$p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary-action">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.id-cards.index') }}" class="btn-secondary-action" style="color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

{{-- Selection summary bar --}}
<div class="sc mb-5 sticky top-20" style="z-index:50;">
    <div class="sc-body d-flex align-items-center gap-3" style="padding:.75rem 1.25rem;">
        <button type="button" onclick="selectAll()" class="btn-secondary-action" style="color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;font-size:.75rem;">
            <i class="fas fa-check-double"></i> Select All
        </button>
        <button type="button" onclick="deselectAll()" class="btn-secondary-action" style="color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;font-size:.75rem;">
            <i class="fas fa-times"></i> Deselect All
        </button>
        <span class="ml-auto text-sm font-bold" id="selection-count" style="color:var(--brand);">
            0 students selected
        </span>
    </div>
</div>

{{-- Student list --}}
<form id="card-form" action="{{ route('admin.id-cards.print') }}" method="POST" target="_blank">
@csrf

@if($students->isEmpty())
<div class="sc">
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-id-card"></i></div>
        <p class="empty-state-title">No students found</p>
        <p class="empty-state-sub">Adjust your search or filter to find students.</p>
    </div>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($students as $student)
    @php
        $hasPhoto    = !empty($student->photo);
        $hasName     = !empty($student->user?->full_name);
        $hasId       = !empty($student->student_id);
        $hasProgram  = !empty($student->program?->name);
        $isComplete  = $hasPhoto && $hasName && $hasId;
    @endphp
    <label class="sc p-4 cursor-pointer transition-all hover:shadow-md"
           style="display:flex;gap:.9rem;align-items:center;border:2px solid #f1f5f9;"
           onclick="toggleCard({{ $student->id }}, this)">
        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
               class="sr-only" id="stu_{{ $student->id }}"
               onchange="toggleCard({{ $student->id }}, this.closest('label'))">

        {{-- Photo --}}
        <div style="width:48px;height:48px;border-radius:.6rem;flex-shrink:0;overflow:hidden;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
            @if($hasPhoto)
            <img src="{{ Storage::disk('public')->url($student->photo) }}" alt="Photo"
                 style="width:100%;height:100%;object-fit:cover;">
            @else
            <i class="fas fa-user" style="color:#cbd5e1;font-size:1.2rem;"></i>
            @endif
        </div>

        {{-- Info --}}
        <div style="flex:1;min-width:0;">
            <p class="font-bold text-sm text-slate-800 truncate">
                {{ $student->user?->full_name ?? 'Unknown' }}
                @if(!$isComplete)
                <i class="fas fa-exclamation-circle text-amber-400 ml-1" title="Incomplete profile"></i>
                @endif
            </p>
            <p class="text-xs text-slate-500 truncate">{{ $student->student_id ?? 'No ID' }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $student->program?->name ?? '—' }}</p>
        </div>

        {{-- Checkbox indicator --}}
        <div id="check_{{ $student->id }}" style="width:22px;height:22px;border-radius:50%;border:2px solid #e2e8f0;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s;">
        </div>
    </label>
    @endforeach
</div>

<div class="mt-5">{{ $students->links() }}</div>
@endif
</form>

@push('scripts')
<script>
function toggleCard(id, label) {
    var cb = document.getElementById('stu_' + id);
    var indicator = document.getElementById('check_' + id);
    if (cb.checked) {
        indicator.style.background = 'var(--brand)';
        indicator.style.borderColor = 'var(--brand)';
        indicator.innerHTML = '<i class="fas fa-check" style="color:#fff;font-size:.6rem;"></i>';
        label.style.borderColor = 'var(--brand)';
    } else {
        indicator.style.background = '';
        indicator.style.borderColor = '#e2e8f0';
        indicator.innerHTML = '';
        label.style.borderColor = '#f1f5f9';
    }
    updateCount();
}

function selectAll() {
    document.querySelectorAll('input[name="student_ids[]"]').forEach(function(cb) {
        cb.checked = true;
        toggleCard(cb.value, cb.closest('label'));
    });
}

function deselectAll() {
    document.querySelectorAll('input[name="student_ids[]"]').forEach(function(cb) {
        cb.checked = false;
        toggleCard(cb.value, cb.closest('label'));
    });
}

function updateCount() {
    var count = document.querySelectorAll('input[name="student_ids[]"]:checked').length;
    document.getElementById('selection-count').textContent = count + ' student' + (count !== 1 ? 's' : '') + ' selected';
    document.getElementById('print-btn').disabled = count === 0;
}

function printSelected() {
    var form = document.getElementById('card-form');
    form.submit();
}

// Init
updateCount();
</script>
@endpush

@endsection
