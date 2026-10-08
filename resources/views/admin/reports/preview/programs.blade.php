<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Performance Report — KMC</title>
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

    <div class="text-center mb-6 border-b-2 pb-4" style="border-color:#D4A017">
        @if(!empty($siteLogoData))
            <img src="{{ $siteLogoData }}" alt="{{ $schoolName ?? 'Logo' }}" class="w-16 h-16 object-contain mx-auto mb-3">
        @else
            <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'Logo' }}" class="w-16 h-16 object-contain mx-auto mb-3">
        @endif
        <h1 class="text-xl font-bold" style="color:#78520a">{{ strtoupper($schoolName ?? 'School Name') }}</h1>
        <p class="text-sm text-gray-600">{{ strtoupper($schoolSubtitle ?? '') }}</p>
        <h2 class="text-lg font-bold mt-2" style="color:#D4A017">PROGRAM PERFORMANCE REPORT</h2>
        <p class="text-xs text-gray-500">Generated: {{ now()->format('F d, Y \a\t H:i') }}</p>
    </div>

    <div class="no-print flex gap-3 mb-5 justify-end">
        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#D4A017">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ url()->previous() }}" class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($programs->isEmpty())
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-graduation-cap text-5xl mb-4 block"></i>
        <p class="text-lg font-medium">No program data found.</p>
    </div>
    @else
    <table class="w-full border-collapse text-sm">
        <thead>
            <tr style="background:#D4A017;color:white">
                <th class="border border-gray-300 px-3 py-2 text-left">Program</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Seq.</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Total Students</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Active</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Graduated</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Suspended</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Courses</th>
            </tr>
        </thead>
        <tbody>
            @foreach($programs as $i => $program)
            @php
                $active    = $program->students->where('status','active')->count();
                $graduated = $program->students->where('status','graduated')->count();
                $suspended = $program->students->where('status','suspended')->count();
            @endphp
            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-yellow-50' }}">
                <td class="border border-gray-200 px-3 py-2 font-semibold">{{ $program->name }}</td>
                <td class="border border-gray-200 px-3 py-2 text-center">{{ $program->sequence }}</td>
                <td class="border border-gray-200 px-3 py-2 text-center font-bold">{{ $program->students_count }}</td>
                <td class="border border-gray-200 px-3 py-2 text-center text-emerald-700 font-semibold">{{ $active }}</td>
                <td class="border border-gray-200 px-3 py-2 text-center text-blue-700 font-semibold">{{ $graduated }}</td>
                <td class="border border-gray-200 px-3 py-2 text-center text-red-600 font-semibold">{{ $suspended }}</td>
                <td class="border border-gray-200 px-3 py-2 text-center">{{ $program->courses->count() }}</td>
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
