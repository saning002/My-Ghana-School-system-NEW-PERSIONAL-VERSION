@extends('layouts.app')
@section('title', 'Application — '.$application->child_full_name)

@section('content')
<div class="max-w-3xl space-y-6">

  {{-- Status bar --}}
  @php $badge = $application->status_badge; @endphp
  <div class="card p-5 flex items-center justify-between gap-4 flex-wrap">
    <div class="flex items-center gap-3">
      <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-{{ $badge['color'] }}-100 text-{{ $badge['color'] }}-700">{{ $badge['label'] }}</span>
      <span class="text-sm text-gray-500">Submitted {{ $application->created_at->format('F j, Y') }}</span>
    </div>
    <form method="POST" action="{{ route('admin.website.applications.status', $application) }}" class="flex items-center gap-3 flex-wrap">
      @csrf @method('PATCH')
      <select name="status" class="px-3 py-2 border border-gray-200 rounded-xl text-sm">
        @foreach(['pending','reviewing','accepted','rejected'] as $s)
        <option value="{{ $s }}" {{ $application->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn-gold px-5 py-2 rounded-xl text-sm font-bold">Update Status</button>
    </form>
  </div>

  {{-- Child info --}}
  <div class="card overflow-hidden">
    <div class="px-6 py-4 bg-blue-50 border-b">
      <h3 class="font-bold text-blue-900 text-sm"><i class="fas fa-child text-blue-500 mr-2"></i>Child's Information</h3>
    </div>
    <div class="px-6 py-5 grid grid-cols-2 gap-4 text-sm">
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Full Name</p><p class="font-semibold text-gray-800">{{ $application->child_full_name }}</p></div>
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Date of Birth</p><p class="text-gray-700">{{ $application->child_dob->format('F j, Y') }}</p></div>
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Gender</p><p class="text-gray-700">{{ $application->child_gender === 'M' ? 'Male' : 'Female' }}</p></div>
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Program</p><p class="font-semibold text-gray-800">{{ $application->program_label }}</p></div>
      @if($application->previous_school)
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Previous School</p><p class="text-gray-700">{{ $application->previous_school }}</p></div>
      @endif
      @if($application->special_needs)
      <div class="col-span-2"><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Special Needs / Medical Notes</p><p class="text-gray-700">{{ $application->special_needs }}</p></div>
      @endif
    </div>
  </div>

  {{-- Parent info --}}
  <div class="card overflow-hidden">
    <div class="px-6 py-4 bg-green-50 border-b">
      <h3 class="font-bold text-green-900 text-sm"><i class="fas fa-user text-green-500 mr-2"></i>Parent / Guardian</h3>
    </div>
    <div class="px-6 py-5 grid grid-cols-2 gap-4 text-sm">
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Name</p><p class="font-semibold text-gray-800">{{ $application->parent_name }}</p></div>
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Relationship</p><p class="text-gray-700">{{ $application->relationship }}</p></div>
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Email</p><p><a href="mailto:{{ $application->parent_email }}" class="text-blue-600">{{ $application->parent_email }}</a></p></div>
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Phone</p><p><a href="tel:{{ $application->parent_phone }}" class="text-blue-600">{{ $application->parent_phone }}</a></p></div>
      <div class="col-span-2"><p class="text-xs text-gray-400 uppercase font-semibold mb-1">Address</p><p class="text-gray-700">{{ $application->address }}</p></div>
      @if($application->how_did_you_hear)
      <div><p class="text-xs text-gray-400 uppercase font-semibold mb-1">How They Heard</p><p class="text-gray-700">{{ $application->how_did_you_hear }}</p></div>
      @endif
    </div>
  </div>

  {{-- Notes --}}
  <div class="card overflow-hidden">
    <div class="px-6 py-4 bg-yellow-50 border-b">
      <h3 class="font-bold text-yellow-900 text-sm"><i class="fas fa-sticky-note text-yellow-500 mr-2"></i>Admin Notes</h3>
    </div>
    <form method="POST" action="{{ route('admin.website.applications.status', $application) }}" class="px-6 py-5 space-y-4">
      @csrf @method('PATCH')
      <input type="hidden" name="status" value="{{ $application->status }}">
      <textarea name="notes" rows="4" placeholder="Add internal notes about this application…"
        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm resize-none">{{ $application->notes }}</textarea>
      <button type="submit" class="btn-gold px-6 py-2.5 rounded-xl text-sm font-bold"><i class="fas fa-save mr-2"></i>Save Notes</button>
    </form>
  </div>

  <div class="flex items-center justify-between">
    <a href="{{ route('admin.website.applications.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200">
      <i class="fas fa-arrow-left mr-2"></i>Back to Applications
    </a>
    <form method="POST" action="{{ route('admin.website.applications.destroy', $application) }}" onsubmit="return confirm('Permanently delete this application?')">
      @csrf @method('DELETE')
      <button class="px-5 py-2.5 rounded-xl text-sm font-bold text-red-700 bg-red-50 hover:bg-red-100">
        <i class="fas fa-trash mr-2"></i>Delete
      </button>
    </form>
  </div>

</div>
@endsection
