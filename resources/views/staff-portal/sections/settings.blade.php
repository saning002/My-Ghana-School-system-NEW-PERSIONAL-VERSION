@extends('staff-portal.layout')
@section('title','Settings')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">School Settings</h2>
    <span class="text-xs text-slate-400">View only — contact admin to make changes</span>
</div>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase w-1/3">Setting</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Value</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($settings as $key => $value)
            @if(!str_contains($key,'password') && !str_contains($key,'secret') && !str_contains($key,'token'))
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $key }}</td>
                <td class="px-4 py-3 text-slate-700 text-sm">{{ is_array($value) ? json_encode($value) : $value }}</td>
            </tr>
            @endif
            @empty
            <tr><td colspan="2" class="px-4 py-12 text-center text-slate-400">No settings found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
