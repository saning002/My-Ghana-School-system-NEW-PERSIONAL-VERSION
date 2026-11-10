@extends('layouts.app')
@section('title', 'ID Cards')
@section('subtitle', 'Generate and print student and staff ID cards')

@section('content')

<div class="page-hero-banner mb-5">
    <div class="hero-left">
        <h1><i class="fas fa-id-card mr-2" style="color:var(--brand)"></i>ID Cards</h1>
        <p>Select students or staff and generate printable ID cards — front &amp; back, 4 per A4 page</p>
    </div>
    <div class="hero-actions">
        <button type="button" onclick="submitForm()" class="btn-primary-action"
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
        <p class="font-bold text-amber-800 text-sm">{{ $incompleteCount }} {{ $cardType === 'staff' ? 'staff member(s)' : 'student(s)' }} have incomplete profiles</p>
        <p class="text-xs text-amber-700 mt-1">Their cards will still print but with missing fields shown as dashes.</p>
    </div>
</div>
@endif

{{-- Card type tabs --}}
<div class="sc mb-5">
    <div class="sc-body" style="padding:.75rem 1.25rem;">
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('admin.id-cards.index', ['card_type'=>'student']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm transition-all
                      {{ $cardType === 'student' ? 'text-white' : 'text-slate-500 bg-slate-50 border border-slate-200 hover:border-[var(--brand)]' }}"
               style="{{ $cardType === 'student' ? 'background:var(--brand);' : '' }}">
                <i class="fas fa-user-graduate"></i> Students
            </a>
            <a href="{{ route('admin.id-cards.index', ['card_type'=>'staff']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm transition-all
                      {{ $cardType === 'staff' ? 'text-white' : 'text-slate-500 bg-slate-50 border border-slate-200 hover:border-[var(--brand)]' }}"
               style="{{ $cardType === 'staff' ? 'background:var(--brand);' : '' }}">
                <i class="fas fa-id-badge"></i> Teachers &amp; Staff
            </a>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<div class="sc mb-5">
    <div class="sc-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="card_type" value="{{ $cardType }}">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Search by name{{ $cardType === 'student' ? ' or ID' : '' }}</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-input"
                       placeholder="Type name{{ $cardType === 'student' ? ' or student ID' : '' }}...">
            </div>
            @if($cardType === 'student')
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
            @endif
            <button type="submit" class="btn-primary-action"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.id-cards.index', ['card_type'=>$cardType]) }}"
               class="btn-secondary-action" style="color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;">
               <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

{{-- Selection bar --}}
<div class="sc mb-5 sticky top-20" style="z-index:50;">
    <div class="sc-body" style="padding:.75rem 1.25rem;display:flex;align-items:center;gap:.75rem;">
        <button type="button" onclick="selectAll()"
            class="btn-secondary-action" style="color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;font-size:.75rem;">
            <i class="fas fa-check-double"></i> Select All
        </button>
        <button type="button" onclick="deselectAll()"
            class="btn-secondary-action" style="color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;font-size:.75rem;">
            <i class="fas fa-times"></i> Deselect All
        </button>
        <span class="ml-auto text-sm font-bold" id="sel-count" style="color:var(--brand);">0 selected</span>
    </div>
</div>

{{-- Card form --}}
<form id="card-form" action="{{ route('admin.id-cards.print') }}" method="POST" target="_blank">
@csrf
<input type="hidden" name="card_type" value="{{ $cardType }}">

@php $list = $cardType === 'staff' ? $staff : $students; @endphp

@if($list->isEmpty())
<div class="sc">
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-id-card"></i></div>
        <p class="empty-state-title">No {{ $cardType === 'staff' ? 'staff members' : 'students' }} found</p>
        <p class="empty-state-sub">Adjust your search or filter.</p>
    </div>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($list as $person)
    @php
        if ($cardType === 'staff') {
            $personId   = $person->id;
            $personName = $person->full_name ?? 'Unknown';
            $personSub  = ucfirst(str_replace('_',' ',$person->role ?? 'Staff'));
            $personSub2 = $person->email ?? '';
            $photoPath  = $person->photo ?? null;
            $isComplete = !empty($person->full_name);
        } else {
            $personId   = $person->id;
            $personName = $person->user?->full_name ?? 'Unknown';
            $personSub  = $person->student_id ?? 'No ID';
            $personSub2 = $person->program?->name ?? '—';
            $photoPath  = $person->photo ?? null;
            $isComplete = !empty($person->user?->full_name) && !empty($person->student_id) && !empty($person->photo);
        }
        $photoUrl = $photoPath ? Storage::disk('public')->url($photoPath) : null;
    @endphp
    <label class="sc p-4 cursor-pointer transition-all hover:shadow-md"
           style="display:flex;gap:.9rem;align-items:center;border:2px solid #f1f5f9;"
           onclick="toggleCard('{{ $personId }}', this)">
        <input type="checkbox" name="card_ids[]" value="{{ $personId }}"
               class="sr-only" id="p_{{ $personId }}"
               onchange="toggleCard('{{ $personId }}', this.closest('label'))">
        <div style="width:48px;height:48px;border-radius:.6rem;flex-shrink:0;overflow:hidden;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
            @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="" style="width:100%;height:100%;object-fit:cover;">
            @else
            <i class="fas fa-user" style="color:#cbd5e1;font-size:1.2rem;"></i>
            @endif
        </div>
        <div style="flex:1;min-width:0;">
            <p class="font-bold text-sm text-slate-800 truncate">
                {{ $personName }}
                @if(!$isComplete)<i class="fas fa-exclamation-circle text-amber-400 ml-1" title="Incomplete"></i>@endif
            </p>
            <p class="text-xs text-slate-500 truncate">{{ $personSub }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $personSub2 }}</p>
        </div>
        <div id="chk_{{ $personId }}" style="width:22px;height:22px;border-radius:50%;border:2px solid #e2e8f0;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s;"></div>
    </label>
    @endforeach
</div>
<div class="mt-5">{{ $list->links() }}</div>
@endif
</form>

@push('scripts')
<script>
function toggleCard(id, label) {
    var cb = document.getElementById('p_' + id);
    var ind = document.getElementById('chk_' + id);
    if (cb.checked) {
        ind.style.background = 'var(--brand)';
        ind.style.borderColor = 'var(--brand)';
        ind.innerHTML = '<i class="fas fa-check" style="color:#fff;font-size:.6rem;"></i>';
        label.style.borderColor = 'var(--brand)';
    } else {
        ind.style.background = '';
        ind.style.borderColor = '#e2e8f0';
        ind.innerHTML = '';
        label.style.borderColor = '#f1f5f9';
    }
    updateCount();
}
function selectAll() {
    document.querySelectorAll('input[name="card_ids[]"]').forEach(function(cb) {
        cb.checked = true;
        toggleCard(cb.value, cb.closest('label'));
    });
}
function deselectAll() {
    document.querySelectorAll('input[name="card_ids[]"]').forEach(function(cb) {
        cb.checked = false;
        toggleCard(cb.value, cb.closest('label'));
    });
}
function updateCount() {
    var c = document.querySelectorAll('input[name="card_ids[]"]:checked').length;
    document.getElementById('sel-count').textContent = c + ' selected';
    document.getElementById('print-btn').disabled = c === 0;
}
function submitForm() {
    document.getElementById('card-form').submit();
}
updateCount();
</script>
@endpush
@endsection
