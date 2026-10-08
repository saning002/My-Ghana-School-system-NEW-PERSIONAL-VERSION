@extends('staff-portal.layout')
@section('title','Students')
@section('subtitle', $branchId ? 'Your branch students' : 'All students')

@section('content')
<div class="space-y-5">

<div class="card p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                <option value="">All Programs</option>
                @foreach($programs as $p)<option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Status</label>
            <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(['active','graduated','suspended','withdrawn'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach
            </select>
        </div>
    </form>
</div>

{{-- Desktop table (hidden on mobile) --}}
<div class="card overflow-hidden hidden lg:block">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-slate-800">Students ({{ $students->total() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 text-left">Student</th>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">Program</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-left">Admission</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($students as $student)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
                            </div>
                            <span class="font-bold text-slate-800">{{ $student->user->full_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $student->student_id }}</td>
                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $student->program?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold
                            {{ $student->status==='active'?'bg-emerald-100 text-emerald-700':($student->status==='graduated'?'bg-blue-100 text-blue-700':'bg-red-100 text-red-700') }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $student->admission_date?->format('M d, Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-10 text-sm text-slate-400">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $students->links() }}</div>
    @endif
</div>

{{-- Mobile cards (hidden on desktop) --}}
<div class="lg:hidden space-y-3">
    <div class="px-1 mb-2">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Students ({{ $students->total() }})</h3>
    </div>
    @forelse($students as $student)
    <div class="card p-4">
        <div class="flex items-start gap-3">
            <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold shrink-0 shadow-sm">
                {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-bold text-slate-900 text-sm leading-tight">{{ $student->user->full_name }}</p>
                        <p class="text-[11px] font-mono text-slate-500 mt-0.5">{{ $student->student_id }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold
                        {{ $student->status==='active'?'bg-emerald-100 text-emerald-700':($student->status==='graduated'?'bg-blue-100 text-blue-700':'bg-red-100 text-red-700') }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 pt-3 border-t border-slate-100">
                    <div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Program</p>
                        <p class="text-xs text-slate-700 font-medium mt-0.5 truncate">{{ $student->program?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Admission</p>
                        <p class="text-xs text-slate-700 font-medium mt-0.5">{{ $student->admission_date?->format('M d, Y') ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-8 text-center">
        <i class="fas fa-user-graduate text-3xl text-slate-200 mb-2 block"></i>
        <p class="text-sm font-semibold text-slate-400">No students found.</p>
    </div>
    @endforelse
    @if($students->hasPages())
    <div class="pt-2">{{ $students->links() }}</div>
    @endif
</div>

</div>
@endsection
