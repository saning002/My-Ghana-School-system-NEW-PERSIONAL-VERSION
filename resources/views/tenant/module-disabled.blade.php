<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Not Available</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="w-20 h-20 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-lock text-amber-500 text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Module Not Enabled</h1>
        <p class="text-slate-500 mb-1">
            <strong>{{ ucwords(str_replace('_', ' ', $module ?? '')) }}</strong> is not part of
            <strong>{{ $tenant->name ?? 'this school' }}</strong>'s current plan.
        </p>
        <p class="text-slate-400 text-sm mb-8">
            Contact your school management to request access to this feature.
        </p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.dashboard') }}"
           class="inline-flex items-center gap-2 bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold text-sm hover:bg-slate-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>
        <div class="mt-10 pt-6 border-t border-slate-200">
            <p class="text-xs text-slate-300">
                Powered by <span class="font-semibold">SaningTech Solutions</span>
            </p>
        </div>
    </div>
</body>
</html>
