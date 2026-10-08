@extends('layouts.app')
@section('title','Fees & Payments')
@section('subtitle', auth()->user()->isBranchAdmin() ? 'Manage student payments for ' . (auth()->user()->branch->name ?? 'your branch') : 'Manage program fees and student payments across branches')

@section('content')

<style>
    .premium-card {
        background: linear-gradient(135deg, #ffffff 0%, #fffcf5 100%);
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(15, 23, 42, 0.05);
    }
    
    .premium-card:hover {
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.12), 0 6px 12px rgba(15, 23, 42, 0.06);
        transform: translateY(-3px);
    }
    
    .summary-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafb 100%);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        padding: 16px 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    
    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, currentColor, transparent);
    }
    
    .summary-card.billed::before { background: linear-gradient(90deg, #D4A017, transparent); }
    .summary-card.collected::before { background: linear-gradient(90deg, #16a34a, transparent); }
    .summary-card.outstanding::before { background: linear-gradient(90deg, #dc2626, transparent); }
    
    .summary-icon {
        font-size: 22px;
        margin-bottom: 8px;
        opacity: 0.8;
    }
    
    .summary-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    
    .summary-amount {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
    }
    
    .premium-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .premium-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
    
    .premium-btn:active {
        transform: translateY(0);
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    
    .section-header-icon {
        font-size: 24px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    
    .section-header h2 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }
    
    .section-header p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }
    
    .student-row {
        transition: all 0.18s ease;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 8px;
        background: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .student-row:hover {
        background: #f9fafb;
        border-color: rgba(0, 0, 0, 0.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    
    .action-btn:hover {
        transform: scale(1.1);
    }
    
    .modal-overlay {
        animation: fadeIn 0.2s ease;
    }
    
    .modal-content {
        animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .input-field {
        position: relative;
        overflow: hidden;
    }
    
    .input-field input,
    .input-field select {
        background: white;
        border: 1.5px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .input-field input:focus,
    .input-field select:focus {
        border-color: currentColor;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.04);
    }
    
    .badge-custom {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1e40af;
    }
    
    .payment-item {
        padding: 12px;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-radius: 10px;
        transition: all 0.2s ease;
        border: 1px solid rgba(217, 119, 6, 0.1);
    }
    
    .payment-item:hover {
        background: linear-gradient(135deg, #fde68a, #fcd34d);
        transform: translateX(4px);
    }
</style>

{{-- ══ FEE MODE TOGGLE ═══════════════════════════════════════════════════════ --}}
@php $feeMode   = $feeMode   ?? 'program_fees'; @endphp
@php $dailyRate = $dailyRate ?? 5; @endphp

<div class="premium-card overflow-hidden mb-6">
    <div class="px-5 py-4 border-b flex items-center gap-3"
         style="background:linear-gradient(135deg,{{ $feeMode==='daily_fees' ? '#f0fdf4,#dcfce7' : '#fef9c3,#fef3c7' }})">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center"
             style="background:{{ $feeMode==='daily_fees' ? '#16a34a' : '#D4A017' }}">
            <i class="fas {{ $feeMode==='daily_fees' ? 'fa-calendar-check' : 'fa-coins' }} text-white text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold" style="color:{{ $feeMode==='daily_fees' ? '#166534' : '#78520a' }}">
                Fee Mode:
                <span class="ml-1">{{ $feeMode==='daily_fees' ? '⚡ Daily Fees' : '📋 Program / Term Fees' }}</span>
            </h3>
            <p class="text-xs" style="color:{{ $feeMode==='daily_fees' ? '#166534aa' : '#78520aaa' }}">
                {{ $feeMode==='daily_fees'
                    ? 'Students are charged GH₵ '.$dailyRate.' per school day. Go to Daily Fees to mark payments.'
                    : 'Students are charged per program or term. Set fees per program below.' }}
            </p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.fees.mode') }}" class="px-5 py-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">

            {{-- Mode selector --}}
            <div class="sm:col-span-1">
                <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-2">Fee Collection Mode</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="fee_mode" value="program_fees"
                               class="sr-only peer" {{ $feeMode==='program_fees' ? 'checked' : '' }}>
                        <span class="flex flex-col items-center gap-1 rounded-xl border-2 border-gray-200 bg-gray-50 p-3 text-center transition-all
                                     peer-checked:border-yellow-500 peer-checked:bg-yellow-50">
                            <i class="fas fa-coins text-lg text-yellow-500"></i>
                            <span class="text-xs font-bold text-gray-700 leading-tight">Program<br>Fees</span>
                            <span class="text-[10px] text-gray-400">Per term</span>
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="fee_mode" value="daily_fees"
                               class="sr-only peer" {{ $feeMode==='daily_fees' ? 'checked' : '' }}>
                        <span class="flex flex-col items-center gap-1 rounded-xl border-2 border-gray-200 bg-gray-50 p-3 text-center transition-all
                                     peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                            <i class="fas fa-calendar-check text-lg text-emerald-500"></i>
                            <span class="text-xs font-bold text-gray-700 leading-tight">Daily<br>Fees</span>
                            <span class="text-[10px] text-gray-400">Per day</span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Daily rate --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-2">
                    Daily Rate (GH₵)
                    <span class="font-normal text-gray-400 normal-case ml-1">— only used in Daily Fees mode</span>
                </label>
                <div class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus-within:ring-2 focus-within:ring-emerald-400">
                    <span class="text-gray-500 font-bold text-sm">GH₵</span>
                    <input type="number" name="daily_fee_rate" min="0" max="9999" step="0.01"
                           value="{{ $dailyRate }}"
                           class="flex-1 border-0 p-0 text-sm font-bold text-gray-800 focus:ring-0 focus:outline-none bg-transparent">
                    <span class="text-[11px] text-gray-400">/ student / day</span>
                </div>
            </div>

            {{-- Save + shortcut --}}
            <div class="flex flex-col gap-2">
                <button type="submit"
                    class="w-full premium-btn py-3 rounded-xl text-sm font-bold text-white shadow-sm"
                    style="background:linear-gradient(135deg,#D4A017,#b8860b)">
                    <i class="fas fa-save mr-1.5"></i> Save Fee Mode
                </button>
                @if($feeMode === 'daily_fees')
                <a href="{{ route('admin.daily-fees.index') }}"
                   class="w-full text-center py-2.5 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                    <i class="fas fa-external-link-alt mr-1"></i> Go to Daily Fees →
                </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- ══ NOTE: If daily fees mode is active, program fees below are ignored ════ --}}
@if($feeMode === 'daily_fees')
<div class="flex items-start gap-3 px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 mb-4">
    <i class="fas fa-info-circle text-emerald-500 mt-0.5 shrink-0"></i>
    <p class="text-xs text-emerald-800 leading-relaxed">
        <strong>Daily Fees mode is active.</strong> Students are charged <strong>GH₵ {{ number_format($dailyRate,2) }}</strong> per school day.
        Use the <a href="{{ route('admin.daily-fees.index') }}" class="underline font-bold">Daily Fees</a> page to mark who paid each day, manage scholarships/exemptions, and view reports.
        Program fees below are not used for billing in this mode.
    </p>
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
    <div class="summary-card billed">
        <div style="color: #D4A017;">
            <div class="summary-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <p class="summary-label" style="color: #78520a;">{{ auth()->user()->isBranchAdmin() ? 'Branch' : 'Total' }} Billed</p>
            <p class="summary-amount" style="color: #78520a;">GH₵ {{ number_format($global['billed'],2) }}</p>
        </div>
    </div>
    <div class="summary-card collected">
        <div style="color: #16a34a;">
            <div class="summary-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <p class="summary-label" style="color: #166534;">{{ auth()->user()->isBranchAdmin() ? 'Branch' : 'Total' }} Collected</p>
            <p class="summary-amount" style="color: #166534;">GH₵ {{ number_format($global['collected'],2) }}</p>
        </div>
    </div>
    <div class="summary-card outstanding">
        <div style="color: #dc2626;">
            <div class="summary-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <p class="summary-label" style="color: #991b1b;">{{ auth()->user()->isBranchAdmin() ? 'Branch' : 'Total' }} Outstanding</p>
            <p class="summary-amount" style="color: #991b1b;">GH₵ {{ number_format($global['outstanding'],2) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="premium-card overflow-hidden">
        <div class="px-4 py-4" style="background: linear-gradient(135deg, #fef9c3, #fef3c7); border-bottom: 1px solid rgba(217, 119, 6, 0.1);">
            <h3 class="text-base font-bold mb-1" style="color:#78520a;">
                <i class="fas fa-download mr-2"></i>Download Fees Template
            </h3>
            <p class="text-xs text-yellow-700">Export student data for bulk fee updates</p>
        </div>
        <div class="px-4 py-4 grid grid-cols-1 gap-3">
            <form method="GET" action="{{ route('admin.fees.template') }}">
                <div class="input-field mb-4">
                    <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Student Type</label>
                    <select name="student_type" class="w-full px-4 py-3 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" style="border-color: #fcd34d;">
                        <option value="all" {{ request('student_type') === 'all' ? 'selected' : '' }}>All students</option>
                        <option value="precollege" {{ request('student_type') === 'precollege' ? 'selected' : '' }}>Pre-College students</option>
                        <option value="first_semester" {{ request('student_type') === 'first_semester' ? 'selected' : '' }}>First Semester students</option>
                    </select>
                </div>
                <button type="submit" class="w-full premium-btn inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl text-sm hover:from-indigo-700 hover:to-indigo-800">
                    <i class="fas fa-download mr-2"></i> Download Template
                </button>
            </form>
            <p class="text-xs text-gray-500 text-center italic mt-2">Matches exact sheet layout for seamless import</p>
        </div>
    </div>
    
    <div class="premium-card overflow-hidden">
        <div class="px-4 py-4" style="background: linear-gradient(135deg, #dcfce7, #d1fae5); border-bottom: 1px solid rgba(22, 163, 74, 0.1);">
            <h3 class="text-base font-bold mb-1" style="color:#166534;">
                <i class="fas fa-upload mr-2"></i>Import Fees Sheet
            </h3>
            <p class="text-xs text-green-700">Bulk upload fee payments at once</p>
        </div>
        <form method="POST" action="{{ route('admin.fees.import') }}" enctype="multipart/form-data" class="px-4 py-4 grid grid-cols-1 gap-3">
            @csrf
            <div class="input-field">
                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Select File</label>
                <input type="file" name="fees_file" accept=".xlsx,.xls,.csv" required
                    class="w-full px-4 py-3 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" style="border-color: #a7f3d0;">
                @error('fees_file')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full premium-btn inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl text-sm hover:from-emerald-700 hover:to-emerald-800">
                <i class="fas fa-upload mr-2"></i> Import Fees
            </button>
            <p class="text-xs text-gray-500 text-center italic">Supports .xlsx, .xls, .csv formats</p>
        </form>
    </div>
</div>

{{-- Super Admin Branch Breakdown --}}
@if(auth()->user()->isSuperAdmin() && !empty($branchSummaries))
<div class="premium-card overflow-hidden mb-6">
    <div class="px-4 py-4" style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border-bottom: 1px solid rgba(0, 0, 0, 0.05);">
        <h3 class="text-base font-bold mb-1">
            <i class="fas fa-building mr-2" style="color: #6366f1;"></i>Branch Breakdown
        </h3>
        <p class="text-xs text-gray-600">Overview of all active campus branches</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 2px solid rgba(0, 0, 0, 0.08);">
                    <th class="px-4 py-3 font-bold text-gray-800 text-xs uppercase tracking-wider">Branch</th>
                    <th class="px-4 py-3 font-bold text-gray-800 text-xs uppercase tracking-wider">Location</th>
                    <th class="px-4 py-3 font-bold text-gray-800 text-xs uppercase tracking-wider text-right">Billed</th>
                    <th class="px-4 py-3 font-bold text-gray-800 text-xs uppercase tracking-wider text-right">Collected</th>
                    <th class="px-4 py-3 font-bold text-gray-800 text-xs uppercase tracking-wider text-right">Outstanding</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($branchSummaries as $bs)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="px-4 py-3 font-semibold text-gray-900">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full" style="background: linear-gradient(135deg, #D4A017, #fbbf24);"></span>
                            {{ $bs['branch']->name }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 font-medium">{{ $bs['branch']->location ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-900 font-semibold text-right">GH₵ {{ number_format($bs['summary']['billed'], 2) }}</td>
                    <td class="px-4 py-3 text-green-700 font-semibold text-right">GH₵ {{ number_format($bs['summary']['collected'], 2) }}</td>
                    <td class="px-4 py-3 text-red-700 font-bold text-right">GH₵ {{ number_format($bs['summary']['outstanding'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    {{-- Program Fees Config --}}
    <div class="premium-card overflow-hidden">
        <div class="px-4 py-4" style="background: linear-gradient(135deg, #fef9c3, #fef3c7); border-bottom: 1px solid rgba(217, 119, 6, 0.1);">
            <h3 class="text-base font-bold mb-1" style="color:#78520a;">
                <i class="fas fa-sliders-h mr-2"></i>Program Fee Configuration
            </h3>
            <p class="text-xs text-yellow-700">Set default fees per program</p>
        </div>
        <div class="divide-y divide-yellow-50">
            @foreach($programs as $program)
            <form method="POST" action="{{ route('admin.fees.set-fee') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 py-3 hover:bg-yellow-50/40 transition-colors">
                @csrf
                <input type="hidden" name="program_id" value="{{ $program->id }}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $program->name }}</p>
                    <p class="text-xs text-gray-500 font-medium">Level {{ $program->sequence }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-gray-600 uppercase">Prog.:</span>
                        <input type="number" name="amount" step="0.01" min="0"
                            value="{{ $program->branchFee?->amount ?? 0 }}"
                            class="w-20 px-2 py-1.5 border border-yellow-300 rounded-lg text-xs text-right focus:outline-none focus:ring-2 bg-white font-semibold"
                            placeholder="0.00">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-gray-600 uppercase">Exam:</span>
                        <input type="number" name="exam_fee" step="0.01" min="0"
                            value="{{ $program->branchFee?->exam_fee ?? 0 }}"
                            class="w-20 px-2 py-1.5 border border-yellow-300 rounded-lg text-xs text-right focus:outline-none focus:ring-2 bg-white font-semibold"
                            placeholder="0.00">
                    </div>
                    <button type="submit" class="premium-btn px-3 py-1.5 rounded-lg text-xs bg-yellow-600 text-white hover:bg-yellow-700">Save</button>
                </div>
            </form>
            @endforeach
        </div>
    </div>

    {{-- Record Payment --}}
    <div class="premium-card overflow-hidden">
        <div class="px-4 py-4" style="background: linear-gradient(135deg, #fef9c3, #fef3c7); border-bottom: 1px solid rgba(217, 119, 6, 0.1);">
            <h3 class="text-base font-bold mb-1" style="color:#78520a;">
                <i class="fas fa-coins mr-2"></i>Record New Payment
            </h3>
            <p class="text-xs text-yellow-700">Log individual student fee payments</p>
        </div>
        <form method="POST" action="{{ route('admin.fees.store') }}" class="px-4 py-4 space-y-3">
            @csrf
            <div class="input-field">
                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Student *</label>
                <x-student-search name="student_id" placeholder="Search by name or ID..." :required="true" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="input-field">
                    <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Amount *</label>
                    <input type="number" name="amount_paid" step="0.01" min="0.01" required
                        class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-white"
                        placeholder="0.00">
                </div>
                <div class="input-field">
                    <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-white">
                </div>
            </div>
            <div class="input-field">
                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Notes</label>
                <input type="text" name="notes"
                    class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-white"
                    placeholder="Optional note...">
            </div>
            <button type="submit" class="w-full premium-btn py-3 rounded-xl text-sm bg-gradient-to-r from-yellow-600 to-yellow-700 text-white hover:from-yellow-700 hover:to-yellow-800">
                <i class="fas fa-plus mr-2"></i> Record Payment
            </button>
        </form>
    </div>
</div>

{{-- Set Custom Fees Section --}}
<div class="premium-card overflow-hidden mb-6">
    <div class="px-4 py-4" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-bottom: 1px solid rgba(30, 64, 175, 0.1);">
        <h3 class="text-base font-bold mb-1" style="color: #1e40af;">
            <i class="fas fa-percent mr-2"></i>Set Custom Fees for Selected Students
        </h3>
        <p class="text-xs text-blue-700">Override program default with student-specific fees</p>
    </div>
    <form method="POST" action="{{ route('admin.fees.set-bulk') }}" class="px-4 py-4 space-y-3">
        @csrf
        <input type="hidden" name="student_ids[]" value="">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="input-field">
                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Program Fee (GH₵) *</label>
                <input type="number" name="amount" step="0.01" min="0" required
                    class="w-full px-4 py-3 border-1.5 border-blue-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    placeholder="0.00">
                @error('amount')
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-field">
                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Exam Fee (GH₵)</label>
                <input type="number" name="exam_fee" step="0.01" min="0"
                    class="w-full px-4 py-3 border border-blue-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    placeholder="0.00">
                @error('exam_fee')
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-field">
                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Notes</label>
                <input type="text" name="notes"
                    class="w-full px-4 py-3 border border-blue-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    placeholder="Scholarship, Sponsor...">
                @error('notes')
                    <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="submitCustomFeeForm()" class="flex-1 premium-btn px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm hover:from-blue-700 hover:to-blue-800">
                <i class="fas fa-dollar-sign mr-2"></i> Set Custom Fee
            </button>
            <button type="button" onclick="clearCustomFeeForm()" class="px-4 py-3 premium-btn bg-gray-200 text-gray-700 rounded-xl text-sm hover:bg-gray-300">
                <i class="fas fa-times mr-2"></i> Clear
            </button>
        </div>
        <p class="text-xs text-gray-600 text-center italic">✓ Select students above · ✓ Enter amounts · ✓ Click Set Custom Fee</p>
    </form>
</div>

{{-- Student Payment History --}}
<div class="section-header">
    <div class="section-header-icon bg-yellow-100" style="color: #D4A017;">
        <i class="fas fa-history"></i>
    </div>
    <div>
        <h2>Student Payment History</h2>
        <p>View and manage fees for all enrolled students</p>
    </div>
</div>

<form id="bulk-reset-form" method="POST" action="{{ route('admin.fees.reset-bulk') }}">
    @csrf
    <div class="premium-card overflow-hidden mb-6">
        <div class="px-4 py-4" style="background: linear-gradient(135deg, #fef9c3, #fef3c7); border-bottom: 1px solid rgba(217, 119, 6, 0.1);">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold" style="color:#78520a;">
                        <i class="fas fa-list mr-2"></i>Student Records
                    </h3>
                    <p class="text-xs text-yellow-700 mt-1">{{ $students->total() }} total student(s) · Page {{ $students->currentPage() }} of {{ $students->lastPage() }}</p>
                    <p class="text-xs text-gray-600 mt-1">Select students by ticking the checkbox on the left of each row, or use “Select all on this page”.</p>
                </div>
                <div id="bulk-actions" class="hidden flex items-center gap-2 flex-wrap justify-end">
                    <button type="button" onclick="selectAllVisibleStudents()" class="premium-btn px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700 flex items-center gap-1.5">
                        <i class="fas fa-check-square"></i> Select all
                    </button>
                    <span id="selected-count" class="text-xs font-bold text-gray-700 bg-gray-100 px-3 py-1.5 rounded-lg">0 selected</span>
                    <button type="button" onclick="document.getElementById('bulk-reset-form').submit()" class="premium-btn px-4 py-2 bg-red-600 text-white rounded-xl text-xs font-semibold hover:bg-red-700 flex items-center gap-1.5">
                        <i class="fas fa-redo-alt"></i> Reset
                    </button>
                    <button type="button" onclick="document.querySelectorAll('input[name=\"student_ids[]\"]:checked').forEach(cb => cb.checked = false); updateBulkActions();" class="premium-btn px-4 py-2 bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-400">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        
        <div class="px-4 py-4 space-y-2 max-h-[600px] overflow-y-auto">
            @forelse($students as $student)
            @php
                try {
                    $calc    = app(\App\Services\FeeCalculationService::class)->calculate($student);
                    $fee     = $calc['total'] ?? 0;
                    $paid    = $calc['paid'] ?? 0;
                    $balance = $calc['balance'] ?? 0;
                } catch (\Throwable $e) {
                    $calc = ['total' => 0, 'paid' => 0, 'balance' => 0];
                    $fee = 0;
                    $paid = 0;
                    $balance = 0;
                    \Log::error('View calculation error for student ' . ($student->id ?? 'unknown') . ': ' . $e->getMessage());
                }
            @endphp
            <div class="student-row">
                <div class="flex items-start gap-4 mb-3">
                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox w-5 h-5 rounded cursor-pointer mt-1" onchange="updateBulkActions()" style="accent-color: #D4A017;">
                    
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm text-white flex-shrink-0" style="background: linear-gradient(135deg, #D4A017, #f59e0b); box-shadow: 0 4px 8px rgba(212, 160, 23, 0.2);">
                        {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $student->user->full_name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <i class="fas fa-graduation-cap mr-1" style="color: #D4A017;"></i>
                            {{ $student->program->name ?? 'N/A' }}
                        </p>
                        @if(isset($student->customFee) && $student->customFee)
                        <p class="text-xs mt-1.5">
                            <span class="badge-custom">Custom Fee Set</span>
                        </p>
                        @endif
                    </div>
                    
                    <div class="text-right flex-shrink-0 min-w-max">
                        <p class="text-xs text-gray-500 font-semibold">Balance Due</p>
                        <p class="text-lg font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            GH₵ {{ number_format($balance, 2) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $paid > 0 ? 'Paid: GH₵ ' . number_format($paid, 2) : 'No payments' }}</p>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex gap-1.5 flex-shrink-0">
                        @if($student->payments->count() > 0)
                        <form method="POST" action="{{ route('admin.fees.reset-student', $student) }}" onsubmit="return confirm('Reset all {{ $student->payments->count() }} payment(s) for {{ $student->user->full_name }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn bg-orange-100 text-orange-600 hover:bg-orange-200" title="Reset payments">
                                <i class="fas fa-redo-alt"></i>
                            </button>
                        </form>
                        @endif
                        
                        <button type="button" onclick="openSetFeeModal({{ $student->id }}, '{{ addslashes($student->user->full_name) }}', {{ isset($student->customFee) && $student->customFee ? $student->customFee->amount : 0 }}, {{ isset($student->customFee) && $student->customFee ? $student->customFee->exam_fee : 0 }}, '{{ isset($student->customFee) && $student->customFee ? addslashes($student->customFee->notes ?? '') : '' }}')" class="action-btn bg-blue-100 text-blue-600 hover:bg-blue-200" title="Set custom fee">
                            <i class="fas fa-money-bill-wave"></i>
                        </button>
                        
                        @if(isset($student->customFee) && $student->customFee)
                        <form method="POST" action="{{ route('admin.fees.clear-student', $student) }}" onsubmit="return confirm('Clear custom fee for {{ $student->user->full_name }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn bg-red-100 text-red-600 hover:bg-red-200" title="Clear custom fee">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                
                {{-- Payment History --}}
                @if($student->payments->count() > 0)
                <div class="space-y-2 ml-14">
                    @foreach($student->payments->sortByDesc('payment_date')->take(2) as $payment)
                    <div class="payment-item">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-yellow-900">GH₵ {{ number_format($payment->amount_paid, 2) }}</p>
                                <p class="text-xs text-yellow-700 mt-0.5">
                                    <i class="fas fa-calendar mr-1"></i>{{ $payment->payment_date->format('M d, Y') }}
                                    @if($payment->notes)
                                    <span class="ml-1.5">·</span> <span class="ml-1.5 italic">{{ $payment->notes }}</span>
                                    @endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.fees.destroy', $payment) }}" onsubmit="return confirm('Remove this payment record?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn w-7 h-7 bg-red-100/50 text-red-500 hover:bg-red-200" title="Delete payment">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                    @if($student->payments->count() > 2)
                    <p class="text-xs text-gray-500 ml-1 italic">+{{ $student->payments->count() - 2 }} more payment(s)</p>
                    @endif
                </div>
                @else
                <p class="text-xs text-gray-400 italic ml-14">No payment records</p>
                @endif
            </div>
            @empty
            <div class="py-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4" style="display: block;"></i>
                <p class="text-gray-500 font-medium">No students found</p>
            </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if($students->hasPages())
        <div class="px-4 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</form>

<script>
function selectAllVisibleStudents() {
    document.querySelectorAll('input[name="student_ids[]"]').forEach(cb => {
        cb.checked = true;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('input[name="student_ids[]"]:checked');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (checkboxes.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = checkboxes.length + ' selected';
        
        const customFeeStudentIds = document.querySelectorAll('form[action="{{ route("admin.fees.set-bulk") }}"] input[name="student_ids[]"]');
        customFeeStudentIds.forEach(field => field.remove());
        checkboxes.forEach(checkbox => {
            const newField = document.createElement('input');
            newField.type = 'hidden';
            newField.name = 'student_ids[]';
            newField.value = checkbox.value;
            document.querySelector('form[action="{{ route("admin.fees.set-bulk") }}"]').appendChild(newField);
        });
    } else {
        bulkActions.classList.add('hidden');
        const customFeeStudentIds = document.querySelectorAll('form[action="{{ route("admin.fees.set-bulk") }}"] input[name="student_ids[]"]');
        customFeeStudentIds.forEach(field => field.remove());
    }
}

function submitCustomFeeForm() {
    const checkboxes = document.querySelectorAll('input[name="student_ids[]"]:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one student');
        return;
    }
    const form = document.querySelector('form[action="{{ route("admin.fees.set-bulk") }}"]');
    if (form.amount.value === '' || parseFloat(form.amount.value) < 0) {
        alert('Please enter a valid program fee amount');
        return;
    }
    if (form.submit) {
        form.submit();
    }
}

function clearCustomFeeForm() {
    document.querySelectorAll('input[name="student_ids[]"]:checked').forEach(cb => cb.checked = false);
    document.querySelector('form[action="{{ route("admin.fees.set-bulk") }}"]').reset();
    updateBulkActions();
}

function openSetFeeModal(studentId, fullName, currentAmount, currentExamFee, currentNotes) {
    const html = `
        <div id="fee-modal-${studentId}" class="modal-overlay fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="modal-content bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="px-6 py-5 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-white">Set Custom Fee</h3>
                        <p class="text-sm text-blue-100 mt-0.5">${fullName}</p>
                    </div>
                    <button onclick="document.getElementById('fee-modal-${studentId}').remove()" class="text-blue-200 hover:text-white text-xl transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.fees.set-student', '') }}/${studentId}" class="p-6 space-y-4">
                    @csrf
                    <div class="input-field">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Program Fee (GH₵) *</label>
                        <input type="number" name="amount" step="0.01" min="0" required value="${currentAmount}"
                            class="w-full px-4 py-3 border border-blue-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>
                    <div class="input-field">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Exam Fee (GH₵)</label>
                        <input type="number" name="exam_fee" step="0.01" min="0" value="${currentExamFee}"
                            class="w-full px-4 py-3 border border-blue-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>
                    <div class="input-field">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Notes</label>
                        <input type="text" name="notes" value="${currentNotes}"
                            class="w-full px-4 py-3 border border-blue-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            placeholder="e.g., Scholarship holder">
                    </div>
                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="submit" class="flex-1 premium-btn px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm hover:from-blue-700 hover:to-blue-800">
                            <i class="fas fa-save mr-2"></i> Save Fee
                        </button>
                        <button type="button" onclick="document.getElementById('fee-modal-${studentId}').remove()" class="px-4 py-3 premium-btn bg-gray-200 text-gray-700 rounded-xl text-sm hover:bg-gray-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById(`fee-modal-${studentId}`);
    if (existingModal) {
        existingModal.remove();
    }
    
    const div = document.createElement('div');
    div.innerHTML = html;
    document.body.appendChild(div.firstElementChild);
}

document.getElementById('bulk-reset-form')?.addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="student_ids[]"]:checked');
    if (checkboxes.length > 0) {
        const confirmed = confirm('Reset fees for ' + checkboxes.length + ' student(s)? This will delete all their payment records.');
        if (!confirmed) {
            e.preventDefault();
        }
    }
});
</script>

@endsection
