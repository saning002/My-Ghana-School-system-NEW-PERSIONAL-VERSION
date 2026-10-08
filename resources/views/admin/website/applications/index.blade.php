@extends('layouts.app')
@section('title','Admission Applications')

@section('content')
<div class="flex gap-3 mb-6 flex-wrap">
  <a href="{{ route('admin.website.applications.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ !request('status') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600' }}">All</a>
  @foreach(['pending'=>'yellow','reviewing'=>'blue','accepted'=>'green','rejected'=>'red'] as $s=>$c)
  <a href="{{ route('admin.website.applications.index',['status'=>$s]) }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ request('status')===$s ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($s) }}</a>
  @endforeach
</div>
<div class="card overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
      <tr><th class="px-5 py-3 text-left font-semibold">Child</th><th class="px-5 py-3 text-left font-semibold">Program</th><th class="px-5 py-3 text-left font-semibold">Parent</th><th class="px-5 py-3 text-left font-semibold">Status</th><th class="px-5 py-3 text-left font-semibold">Date</th><th class="px-5 py-3 text-center font-semibold">View</th></tr>
    </thead>
    <tbody class="divide-y divide-gray-50">
      @forelse($applications as $app)
      @php $b = $app->status_badge; @endphp
      <tr class="hover:bg-gray-50">
        <td class="px-5 py-4 font-semibold text-gray-800">{{ $app->child_full_name }}</td>
        <td class="px-5 py-4 text-gray-600">{{ $app->program_label }}</td>
        <td class="px-5 py-4 text-gray-600">{{ $app->parent_name }}<br><span class="text-xs text-gray-400">{{ $app->parent_phone }}</span></td>
        <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $b['color'] }}-100 text-{{ $b['color'] }}-700">{{ $b['label'] }}</span></td>
        <td class="px-5 py-4 text-gray-400 text-xs">{{ $app->created_at->format('M j, Y') }}</td>
        <td class="px-5 py-4 text-center"><a href="{{ route('admin.website.applications.show',$app) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100"><i class="fas fa-eye"></i></a></td>
      </tr>
      @empty<tr><td colspan="6" class="py-12 text-center text-gray-400">No applications yet.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-4 border-t">{{ $applications->links() }}</div>
</div>
@endsection
