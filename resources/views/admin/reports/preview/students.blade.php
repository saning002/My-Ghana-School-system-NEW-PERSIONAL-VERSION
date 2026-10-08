<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Report — KMC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
        }
    </style>
</head>
<body class="bg-white text-gray-900 p-6">

    {{-- Header --}}
    <div class="text-center mb-6 border-b-2 pb-4" style="border-color:#D4A017">
        @if(!empty($siteLogoData))
            <img src="{{ $siteLogoData }}" alt="{{ $schoolName ?? 'Logo' }}" class="w-16 h-16 object-contain mx-auto mb-3">
        @else
            <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'Logo' }}" class="w-16 h-16 object-contain mx-auto mb-3">
        @endif
        <h1 class="text-xl font-bold" style="color:#78520a">{{ strtoupper($schoolName ?? 'School Name') }}</h1>
        <p class="text-sm text-gray-600">{{ strtoupper($schoolSubtitle ?? '') }}</p>
        <h2 class="text-lg font-bold mt-2" style="color:#D4A017">STUDENTS REPORT</h2>
        <p class="text-xs text-gray-500">Generated: {{ now()->format('F d, Y \a\t H:i') }}</p>
    </div>

    {{-- Action Buttons --}}
    <div class="no-print flex gap-3 mb-5 justify-end">
        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#D4A017">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ url()->previous() }}" class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($students->isEmpty())
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-users text-5xl mb-4 block"></i>
        <p class="text-lg font-medium">No data found for the selected filters.</p>
    </div>
    @else
    <p class="text-sm text-gray-500 mb-3">{{ $students->count() }} student(s) found</p>
    <table class="w-full border-collapse text-sm">
        <thead>
            <tr style="background:#D4A017;color:white">
                <th class="border border-gray-300 px-3 py-2 text-left">Student ID</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Full Name</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Program</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Ministry Branch</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Admission Date</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $student)
            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-yellow-50' }}">
                <td class="border border-gray-200 px-3 py-2 font-mono text-xs">{{ $student->student_id }}</td>
                <td class="border border-gray-200 px-3 py-2 font-semibold">{{ $student->user->full_name ?? '—' }}</td>
                <td class="border border-gray-200 px-3 py-2">{{ $student->program->name ?? '—' }}</td>
                <td class="border border-gray-200 px-3 py-2">{{ $student->churchBranch->name ?? '—' }}</td>
                <td class="border border-gray-200 px-3 py-2">{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('M d, Y') : '—' }}</td>
                <td class="border border-gray-200 px-3 py-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $student->status==='active'?'bg-emerald-100 text-emerald-700':($student->status==='graduated'?'bg-blue-100 text-blue-700':($student->status==='manifestation'?'bg-amber-100 text-amber-700':'bg-red-100 text-red-700')) }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="mt-6 pt-4 border-t text-xs text-gray-400 text-center no-print">
        {{ $schoolName ?? 'School' }} {{ $schoolSubtitle ? ' — '.$schoolSubtitle : '' }} — Confidential Report
    </div>
</body>
</html>
