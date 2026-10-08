@extends('staff-portal.layout')
@section('title','Fees & Payments')
@section('subtitle', $branchId ? 'Your branch fee summary' : 'School-wide fee summary')

@section('content')
<div class="space-y-5">

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    @foreach([
        ['Total Billed',   'GH₵ '.number_format($global['billed']??0,2),   'from-blue-500 to-cyan-600',    'fa-file-invoice'],
        ['Collected',      'GH₵ '.number_format($global['collected']??0,2), 'from-emerald-500 to-teal-600', 'fa-check-circle'],
        ['Outstanding',    'GH₵ '.number_format($global['outstanding']??0,2),'from-rose-500 to-red-600',    'fa-exclamation-circle'],
    ] as [$l,$v,$g,$i])
    <div class="rounded-2xl bg-gradient-to-br {{ $g }} p-5 text-white shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $l }}</p>
            <i class="fas {{ $i }} text-white/50"></i>
        </div>
        <p class="text-xl font-extrabold">{{ $v }}</p>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                <option value="">All Programs</option>
                @foreach($programs as $p)<option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach
            </select>
        </div>
    </form>
</div>

{{-- Desktop table (hidden on mobile) --}}
<div class="card overflow-hidden hidden lg:block">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Student Fee Statements</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 text-left">Student</th>
                <th class="px-4 py-3 text-right">Billed</th>
                <th class="px-4 py-3 text-right">Paid</th>
                <th class="px-4 py-3 text-right">Balance</th>
                <th class="px-4 py-3 text-center">Statement</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($studentFees as $sf)
                @php $s = $sf['student']; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <p class="font-bold text-slate-800">{{ $s->user->full_name }}</p>
                        <p class="text-[11px] text-slate-400 font-mono">{{ $s->student_id }}</p>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700">GH₵ {{ number_format($sf['total']??0,2) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-700">GH₵ {{ number_format($sf['paid']??0,2) }}</td>
                    <td class="px-4 py-3 text-right font-extrabold {{ ($sf['balance']??0)>0?'text-red-600':'text-emerald-700' }}">GH₵ {{ number_format($sf['balance']??0,2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('staff-portal.fees.statement.pdf',['student_id'=>$s->id]) }}"
                           class="inline-flex items-center gap-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-[11px] font-bold px-2.5 py-1.5 transition-colors">
                            <i class="fas fa-file-pdf text-[10px]"></i> PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-10 text-sm text-slate-400">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())<div class="px-5 py-4 border-t border-slate-100">{{ $students->links() }}</div>@endif
</div>

{{-- Mobile cards (hidden on desktop) --}}
<div class="lg:hidden space-y-3">
    <div class="px-1 mb-2">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Student Fee Statements</h3>
    </div>
    @forelse($studentFees as $sf)
    @php $s = $sf['student']; @endphp
    <div class="card p-4">
        <div class="flex items-start gap-3">
            <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold shrink-0 shadow-sm">
                {{ strtoupper(substr($s->user->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900 text-sm leading-tight truncate">{{ $s->user->full_name }}</p>
                        <p class="text-[11px] font-mono text-slate-500 mt-0.5">{{ $s->student_id }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold {{ ($sf['balance']??0)>0?'bg-red-100 text-red-700':'bg-emerald-100 text-emerald-700' }}">
                        {{ ($sf['balance']??0)>0?'Owed':'Settled' }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 pt-3 border-t border-slate-100">
                    <div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Billed</p>
                        <p class="text-xs text-slate-700 font-bold mt-0.5">GH₵ {{ number_format($sf['total']??0,0) }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Paid</p>
                        <p class="text-xs text-emerald-700 font-bold mt-0.5">GH₵ {{ number_format($sf['paid']??0,0) }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Balance</p>
                        <p class="text-xs {{ ($sf['balance']??0)>0?'text-red-700':'text-emerald-700' }} font-bold mt-0.5">GH₵ {{ number_format($sf['balance']??0,0) }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('staff-portal.fees.statement.pdf',['student_id'=>$s->id]) }}"
                       class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 text-[11px] font-bold py-2 transition-colors">
                        <i class="fas fa-file-pdf text-[10px]"></i> Download Statement PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-8 text-center">
        <i class="fas fa-file-invoice text-3xl text-slate-200 mb-2 block"></i>
        <p class="text-sm font-semibold text-slate-400">No students found.</p>
    </div>
    @endforelse
    @if($students->hasPages())<div class="pt-2">{{ $students->links() }}</div>@endif
</div>

</div>
@endsection
