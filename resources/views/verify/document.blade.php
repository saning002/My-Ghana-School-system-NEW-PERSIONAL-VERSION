<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-lg">
        {{-- School branding --}}
        <div class="text-center mb-8">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 border border-white/20 mb-3">
                <i class="fas fa-shield-halved text-2xl text-indigo-300"></i>
            </div>
            <p class="text-sm font-bold text-indigo-300 uppercase tracking-widest">Document Verification</p>
            <p class="text-xs text-slate-400 mt-1">{{ \App\Models\Setting::get('school_name', config('app.name')) }}</p>
        </div>

        @if(!$doc)
        {{-- Not found --}}
        <div class="rounded-3xl bg-white/5 border border-white/10 p-8 text-center">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-red-500/20 border border-red-500/30 mb-4">
                <i class="fas fa-circle-xmark text-2xl text-red-400"></i>
            </div>
            <h2 class="text-lg font-extrabold text-white mb-2">Document Not Found</h2>
            <p class="text-sm text-slate-400">This QR code or verification link does not match any document in our system. It may have been deleted or the link is invalid.</p>
        </div>

        @elseif($doc->status === 'revoked')
        {{-- Revoked --}}
        <div class="rounded-3xl bg-white/5 border border-red-500/30 p-8 text-center">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-red-500/20 border border-red-500/30 mb-4">
                <i class="fas fa-ban text-2xl text-red-400"></i>
            </div>
            <h2 class="text-lg font-extrabold text-red-300 mb-2">Document Revoked</h2>
            <p class="text-sm text-slate-400">This document has been revoked and is no longer valid.</p>
            <div class="mt-4 rounded-xl bg-red-500/10 border border-red-500/20 px-4 py-2 text-xs text-red-300 font-mono break-all">
                {{ $doc->uuid }}
            </div>
        </div>

        @else
        {{-- Valid document --}}
        <div class="rounded-3xl bg-white/5 border border-emerald-400/30 p-8">
            {{-- Valid badge --}}
            <div class="flex items-center justify-center gap-2 mb-6">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 border border-emerald-400/40 px-5 py-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm font-extrabold text-emerald-300 uppercase tracking-wider">Verified — Valid Document</span>
                </div>
            </div>

            {{-- Document details --}}
            <div class="space-y-3">
                @foreach([
                    ['Document',      $doc->title,                                                      'fa-file-lines'],
                    ['Type',          ucwords(str_replace('_',' ',$doc->document_type)),                'fa-tag'],
                    ['Student',       $doc->student?->user?->full_name ?? '—',                          'fa-user-graduate'],
                    ['Admission No.', $doc->student?->student_id ?? '—',                               'fa-id-card'],
                    ['Period',        $doc->period?->full_label ?? '—',                                 'fa-calendar'],
                    ['Issued By',     $doc->generated_by_name,                                         'fa-user-shield'],
                    ['Issued On',     $doc->created_at->format('d M Y \a\t H:i'),                      'fa-clock'],
                    ['Document ID',   $doc->uuid,                                                       'fa-fingerprint'],
                ] as [$label, $value, $icon])
                <div class="flex items-start gap-3 rounded-xl bg-white/5 border border-white/8 px-4 py-3">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-500/20">
                        <i class="fas {{ $icon }} text-xs text-indigo-300"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                        <p class="text-sm font-semibold text-white mt-0.5 break-all">{{ $value }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <p class="text-center text-xs text-slate-500 mt-6">
                This document was officially generated by {{ \App\Models\Setting::get('school_name', config('app.name')) }}.
                If you suspect this document is fraudulent, contact the school directly.
            </p>
        </div>
        @endif

        <p class="text-center text-xs text-slate-600 mt-6">Powered by {{ \App\Models\Setting::get('school_name', config('app.name')) }}</p>
    </div>

</body>
</html>
