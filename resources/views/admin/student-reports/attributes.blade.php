@extends('layouts.app')
@section('title','Report Attributes')
@section('subtitle','Manage personality development, conduct and attitude attributes for report cards')

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

{{-- Add new attribute --}}
<div class="card p-5">
    <h3 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-2">
        <i class="fas fa-plus-circle text-amber-500"></i> Add New Attribute
    </h3>
    <form method="POST" action="{{ route('admin.student-reports.attributes.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Attribute Name *</label>
            <input type="text" name="name" required value="{{ old('name') }}"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                placeholder="e.g. Neatness, Honesty...">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Type</label>
            <select name="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="personality">Personality</option>
                <option value="conduct">Conduct</option>
                <option value="attitude">Attitude</option>
                <option value="interest">Interest</option>
            </select>
        </div>
        <div class="w-24">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $attributes->count() + 1) }}"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
        </div>
        <button type="submit" class="btn-gold px-5 py-2.5 rounded-xl text-sm font-bold shadow active:scale-95 whitespace-nowrap">
            <i class="fas fa-plus mr-1 text-xs"></i> Add Attribute
        </button>
    </form>
</div>

{{-- Existing attributes --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-slate-800">
            Current Attributes
            <span class="ml-1 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5">{{ $attributes->count() }}</span>
        </h3>
        <p class="text-xs text-slate-400">These appear on report cards under Personality Development</p>
    </div>

    @if($attributes->isEmpty())
    <div class="py-12 text-center text-slate-400">
        <i class="fas fa-list-check text-3xl mb-2 block"></i>
        <p class="text-sm font-semibold">No attributes defined yet. Add your first one above.</p>
    </div>
    @else
    <div class="divide-y divide-slate-50">
        @foreach($attributes as $attr)
        @php
            $typeColors = [
                'personality' => 'bg-violet-100 text-violet-700',
                'conduct'     => 'bg-blue-100 text-blue-700',
                'attitude'    => 'bg-emerald-100 text-emerald-700',
                'interest'    => 'bg-amber-100 text-amber-700',
            ];
        @endphp
        <div class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition-colors" x-data="{ editing: false }">
            {{-- Sort handle --}}
            <span class="text-slate-300 text-sm w-6 text-center font-mono shrink-0">{{ $attr->sort_order }}</span>

            {{-- Name / edit form --}}
            <div class="flex-1 min-w-0">
                <div x-show="!editing" class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-800">{{ $attr->name }}</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $typeColors[$attr->type] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst($attr->type) }}
                    </span>
                    @if(! $attr->is_active)
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">Hidden</span>
                    @endif
                </div>
                <form x-show="editing" x-cloak method="POST"
                      action="{{ route('admin.student-reports.attributes.update', $attr) }}"
                      class="flex flex-wrap gap-2 items-end">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $attr->name }}" required
                        class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 flex-1 min-w-[140px]">
                    <select name="type" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        @foreach(['personality','conduct','attitude','interest'] as $t)
                        <option value="{{ $t }}" {{ $attr->type === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="sort_order" value="{{ $attr->sort_order }}" min="0"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm w-16 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $attr->is_active ? 'checked' : '' }} class="rounded accent-emerald-500">
                        Active
                    </label>
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition-colors">Save</button>
                    <button type="button" @click="editing=false" class="px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl">Cancel</button>
                </form>
            </div>

            {{-- Actions --}}
            <div x-show="!editing" class="flex gap-1.5 shrink-0">
                <button @click="editing=true" type="button"
                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-yellow-50 hover:text-yellow-700 transition-colors">
                    <i class="fas fa-pen text-[10px]"></i>
                </button>
                <form method="POST" action="{{ route('admin.student-reports.attributes.destroy', $attr) }}"
                      onsubmit="return confirm('Delete {{ addslashes($attr->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-500 transition-colors">
                        <i class="fas fa-trash text-[10px]"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Quick guide --}}
<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
    <p class="font-bold mb-2"><i class="fas fa-info-circle mr-1 text-amber-500"></i> How this works</p>
    <ul class="list-disc list-inside space-y-1 text-xs leading-relaxed">
        <li>These attributes appear in the <strong>Personality Development</strong> section of every report card</li>
        <li>Teachers select a rating (<strong>Very Good / Good / Average / Weak / Poor</strong>) for each student per attempt</li>
        <li>Admin fills in <strong>Conduct, Attitude, Interest, Class Teacher Remarks, Head Teacher Remarks</strong></li>
        <li>All values are automatically printed on the PDF report card</li>
        <li>Go to <a href="{{ route('admin.student-reports.index') }}" class="underline font-bold text-amber-700">Student Reports</a> to fill in ratings per student</li>
    </ul>
</div>

</div>
@endsection
