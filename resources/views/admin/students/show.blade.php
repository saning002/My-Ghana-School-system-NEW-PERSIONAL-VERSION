@extends('layouts.app')
@section('title','Student Profile')
@section('subtitle', $student->user->full_name)

@section('content')
<div class="max-w-4xl mx-auto space-y-4" x-data="{tab:'info'}">

    {{-- Profile Header --}}
    <div class="card overflow-hidden border-0 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)]">
        <div class="px-6 py-8 relative" style="background:linear-gradient(135deg, #0B1121, #1A2642); overflow:hidden;">
            <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-500 rounded-full blur-[80px] opacity-10 translate-x-1/2 -translate-y-1/2"></div>
            <div class="relative z-10 flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 border-2 border-white/10 shadow-2xl relative group">
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors z-10"></div>
                    @if($student->photo)
                        <img src="{{ filter_var($student->photo, FILTER_VALIDATE_URL) ? $student->photo : route('admin.students.photo', $student) }}" class="w-full h-full object-cover relative z-0" alt="{{ $student->user->full_name }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white text-3xl font-extrabold relative z-0" style="background:linear-gradient(135deg, #D4A017, #b8860b)">
                            {{ strtoupper(substr($student->user->full_name,0,1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-extrabold text-white tracking-tight leading-tight">{{ $student->user->full_name }}</h2>
                    <p class="text-yellow-400/80 text-sm font-medium mt-1 tracking-wide">{{ $student->student_id }} <span class="mx-2 opacity-50">·</span> {{ $student->program->name??'—' }}</p>
                </div>
                <span class="px-4 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-wider flex-shrink-0 shadow-sm border
                    {{ $student->status==='active'?'bg-emerald-500/10 text-emerald-300 border-emerald-500/20':
                       ($student->status==='graduated'?'bg-blue-500/10 text-blue-300 border-blue-500/20':
                       ($student->status==='manifestation'?'bg-yellow-500/10 text-yellow-300 border-yellow-500/20':
                       ($student->status==='withdrawn'?'bg-gray-500/10 text-gray-300 border-gray-500/20':
                       'bg-red-500/10 text-red-300 border-red-500/20'))) }}">
                    {{ ucfirst($student->status) }}
                </span>
            </div>
        </div>

        {{-- Quick info --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 bg-white divide-x divide-y sm:divide-y-0 divide-slate-100">
            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors"><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Email</p><p class="text-[13px] font-semibold text-slate-800 truncate">{{ $student->user->email }}</p></div>
            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors"><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Phone</p><p class="text-[13px] font-semibold text-slate-800">{{ $student->user->phone??'—' }}</p></div>
            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors"><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Ministry Branch</p><p class="text-[13px] font-semibold text-slate-800 truncate">{{ $student->churchBranch->name??'—' }}</p></div>
            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors"><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Admitted</p><p class="text-[13px] font-semibold text-slate-800">{{ \Carbon\Carbon::parse($student->admission_date)->format('M d, Y') }}</p></div>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-wrap gap-3">
            <a href="{{ route('admin.students.edit',$student) }}" class="btn-gold flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-bold shadow-[0_2px_10px_rgba(212,160,23,0.2)]">
                <i class="fas fa-pen text-xs"></i> Edit Profile
            </a>
            {{-- Approve button — only shown when student not yet approved --}}
            @if(! $student->is_approved)
            <form method="POST" action="{{ route('admin.students.approve', $student) }}"
                  onsubmit="return confirm('Approve {{ $student->user->full_name }}? This will remove their NEW badge.')">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:-translate-y-0.5 transition-all shadow-sm border border-emerald-200/50">
                    <i class="fas fa-check text-xs"></i> Approve
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('admin.students.promote',$student) }}"
                  onsubmit="return confirm('Promote {{ $student->user->full_name }} to the next program?')">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-bold bg-yellow-50 text-yellow-700 hover:bg-yellow-100 hover:-translate-y-0.5 transition-all shadow-sm border border-yellow-200/50">
                    <i class="fas fa-arrow-up text-xs"></i> Promote
                </button>
            </form>
            @php
                $canDemote = \App\Models\PromotionHistory::where('to_program_id', $student->program_id)->exists();
            @endphp
            @if($canDemote)
            <form method="POST" action="{{ route('admin.students.demote',$student) }}"
                  onsubmit="return confirm('Demote {{ $student->user->full_name }} to the previous program?')">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-bold bg-red-50 text-red-700 hover:bg-red-100 hover:-translate-y-0.5 transition-all shadow-sm border border-red-200/50">
                    <i class="fas fa-arrow-down text-xs"></i> Demote
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('admin.students.reset-password', $student) }}"
                  onsubmit="return confirm('Reset {{ $student->user->full_name }}\\'s portal password? They will receive a temporary password.')">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-bold bg-orange-50 text-orange-700 hover:bg-orange-100 hover:-translate-y-0.5 transition-all shadow-sm border border-orange-200/50">
                    <i class="fas fa-key text-xs"></i> Reset Password
                </button>
            </form>
            <a href="{{ route('admin.students.index') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300 rounded-xl text-[13px] font-bold shadow-sm transition-all hover:-translate-y-0.5">
                ← Back to List
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 p-1 rounded-2xl bg-slate-100 border border-slate-200/60 shadow-inner overflow-x-auto">
        @foreach(['info'=>'Personal Info','reports'=>'Report Cards','attendance'=>'Attendance','fees'=>'Fees'] as $key=>$label)
        <button @click="tab='{{ $key }}'"
                :class="tab==='{{ $key }}' ? 'bg-white text-slate-800 shadow-sm border-slate-200/60' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'"
                class="flex-1 min-w-[120px] py-2.5 rounded-xl text-[13px] font-bold transition-all border border-transparent tracking-tight">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Personal Info Tab --}}
    <div x-show="tab==='info'" class="card p-6 grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-4 border border-slate-200/60 shadow-sm">
        @foreach([
            'Full Name'       => $student->user->full_name,
            'Email'           => $student->user->email,
            'Phone'           => $student->user->phone ?? '—',
            'Address'         => $student->user->address ?? '—',
            'Date of Birth'   => $student->user->date_of_birth?->format('M d, Y') ?? '—',
            'Gender'          => $student->user->gender ? ucfirst($student->user->gender) : '—',
            'Marital Status'  => $student->user->marital_status ? ucfirst($student->user->marital_status) : '—',
            'Nationality'     => $student->user->nationality ?? '—',
            'Student ID'      => $student->student_id,
            'Program'         => $student->program->name ?? '—',
            'Ministry Branch' => $student->churchBranch->name ?? '—',
            'Admission Date'  => \Carbon\Carbon::parse($student->admission_date)->format('M d, Y'),
            'Qualifications'  => $student->qualifications ?? '—',
            'Native Town'     => $student->native_town ?? '—',
            'Enrollment Year' => $student->enrollment_year ?? '—',
            'Study Mode'      => $student->study_mode ? ucwords(str_replace('_', ' ', $student->study_mode)) : '—',
            'Profession'      => $student->profession ?? '—',
            'Status'          => ucfirst($student->status),
        ] as $label=>$value)
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 transition-colors hover:border-slate-200">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $label }}</p>
            <p class="text-[14px] font-semibold text-slate-800">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    {{-- Report Cards Tab --}}
    <div x-show="tab==='reports'" class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h3 class="text-sm font-bold" style="color:#78520a">Report Cards by Program</h3>
        </div>
        <div class="divide-y divide-yellow-50">
            @foreach($programs as $program)
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef9c3">
                    <i class="fas fa-file-alt" style="color:#78520a"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-ink truncate">{{ $program->name }}</p>
                    <p class="text-xs text-gray-400">Seq. {{ $program->sequence }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.exams.show',[$student,$program]) }}" class="text-xs px-3 py-1.5 rounded-lg font-semibold" style="background:#fef9c3;color:#78520a">View</a>
                    <a href="{{ route('admin.exams.pdf',[$student,$program]) }}" target="_blank" class="btn-gold text-xs px-3 py-1.5 rounded-lg font-semibold">PDF</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Attendance Tab --}}
    <div x-show="tab==='attendance'" class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold" style="color:#78520a">Attendance Records</h3>
                <div class="flex gap-3 text-xs">
                    <span class="font-semibold text-green-700">{{ $attendanceStats['present'] }} Present</span>
                    <span class="font-semibold text-red-600">{{ $attendanceStats['total'] - $attendanceStats['present'] }} Absent</span>
                    <span class="font-bold" style="color:#78520a">{{ $attendanceStats['rate'] }}%</span>
                </div>
            </div>
        </div>
        <div class="divide-y divide-yellow-50 max-h-72 overflow-y-auto">
            @forelse($student->attendances->sortByDesc('date') as $att)
            <div class="flex items-center gap-3 px-5 py-3">
                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $att->status==='present'?'bg-green-500':'bg-red-400' }}"></span>
                <p class="text-sm text-ink flex-1 truncate">{{ $att->course->name??'—' }}</p>
                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</p>
                <span class="text-xs font-bold {{ $att->status==='present'?'text-green-600':'text-red-500' }}">{{ ucfirst($att->status) }}</span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No attendance records.</div>
            @endforelse
        </div>
    </div>

    {{-- Fees Tab --}}
    <div x-show="tab==='fees'" class="space-y-4">
        <div class="grid grid-cols-3 gap-3">
            <div class="card p-4 text-center" style="border-top:3px solid #D4A017">
                <p class="text-xs text-gray-500 mb-1">Total Fees</p>
                <p class="text-xl font-bold" style="color:#78520a">GH₵ {{ number_format($fees['total'],2) }}</p>
                @if(isset($fees['exam_total']))
                  <p class="text-[10px] text-gray-400 mt-1 font-semibold">Prog: GH₵{{ number_format($fees['program_total'],0) }} | Exam: GH₵{{ number_format($fees['exam_total'],0) }}</p>
                @endif
            </div>
            <div class="card p-4 text-center" style="border-top:3px solid #16a34a">
                <p class="text-xs text-gray-500 mb-1">Amount Paid</p>
                <p class="text-xl font-bold text-green-700">GH₵ {{ number_format($fees['paid'],2) }}</p>
            </div>
            <div class="card p-4 text-center" style="border-top:3px solid #dc2626">
                <p class="text-xs text-gray-500 mb-1">Balance</p>
                <p class="text-xl font-bold {{ $fees['balance']>0?'text-red-600':'text-green-600' }}">GH₵ {{ number_format($fees['balance'],2) }}</p>
            </div>
        </div>
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
                <h3 class="text-sm font-bold" style="color:#78520a">Payment History</h3>
            </div>
            <div class="divide-y divide-yellow-50">
                @forelse($student->payments->sortByDesc('payment_date') as $payment)
                <div class="flex items-center gap-4 px-5 py-3.5">
                    <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-coins text-green-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-green-700">GH₵ {{ number_format($payment->amount_paid,2) }}</p>
                        <p class="text-xs text-gray-400">{{ $payment->payment_date->format('M d, Y') }}{{ $payment->notes?' · '.$payment->notes:'' }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.fees.destroy',$payment) }}" onsubmit="return confirm('Remove this payment?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No payments recorded.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Promotion History --}}
    @if($promotionHistory->count() > 0)
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h3 class="text-sm font-bold" style="color:#78520a">Promotion History</h3>
        </div>
        <div class="divide-y divide-yellow-50">
            @foreach($promotionHistory->sortByDesc('promoted_at') as $history)
            <div class="flex items-center gap-3 px-5 py-3.5">
                <i class="fas fa-arrow-up text-xs" style="color:#D4A017"></i>
                <div class="flex-1 text-xs text-ink">
                    <span class="font-semibold">{{ $history->fromProgram->name ?? '—' }}</span>
                    <span class="text-gray-400 mx-2">→</span>
                    <span class="font-semibold">{{ $history->toProgram?->name ?? 'Graduated' }}</span>
                </div>
                <p class="text-xs text-gray-400">{{ $history->promoted_at->format('M d, Y') }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
