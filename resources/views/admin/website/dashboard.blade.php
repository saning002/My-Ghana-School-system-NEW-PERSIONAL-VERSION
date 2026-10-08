@extends('layouts.app')
@section('title','Website CMS')
@section('subtitle','Manage your school website content')

@section('content')
@php $programsCount = \App\Models\Website\WebsiteProgram::count(); @endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
  @foreach([
    ['Blog Posts',      $postsCount,     'fa-newspaper',       '#2563eb', route('admin.website.blog.index')],
    ['Published',       $publishedCount, 'fa-check-circle',    '#16a34a', route('admin.website.blog.index')],
    ['Upcoming Events', $eventsCount,    'fa-calendar',        '#d97706', route('admin.website.events.index')],
    ['Gallery Images',  $galleryCount,   'fa-images',          '#7c3aed', route('admin.website.gallery.index')],
    ['Staff Members',   $staffCount,     'fa-users',           '#0891b2', route('admin.website.staff.index')],
    ['Programs',        $programsCount,  'fa-graduation-cap',  '#059669', route('admin.website.programs.index')],
    ['Unread Messages', $unreadMessages, 'fa-envelope',        '#dc2626', route('admin.website.messages.index')],
    ['Pending Apps',    $pendingApps,    'fa-file-alt',        '#ea580c', route('admin.website.applications.index')],
  ] as [$label,$count,$icon,$color,$link])
  <a href="{{ $link }}" class="card p-5 flex items-center gap-4 hover:shadow-lg transition-all">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:{{ $color }}20">
      <i class="fas {{ $icon }} text-lg" style="color:{{ $color }}"></i>
    </div>
    <div><p class="text-2xl font-bold text-gray-900">{{ $count }}</p><p class="text-xs text-gray-500">{{ $label }}</p></div>
  </a>
  @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
  {{-- Quick links --}}
  <div class="card p-6">
    <h3 class="font-bold text-gray-800 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 gap-3">
      @foreach([
        ['New Blog Post',      'fa-plus',              'admin.website.blog.create',      '#2563eb'],
        ['New Event',          'fa-calendar-plus',     'admin.website.events.create',    '#d97706'],
        ['Upload Photos',      'fa-upload',            'admin.website.gallery.index',    '#7c3aed'],
        ['Add Staff',          'fa-user-plus',         'admin.website.staff.create',     '#0891b2'],
        ['Add Program',        'fa-graduation-cap',    'admin.website.programs.create',  '#059669'],
        ['Manage Programs',    'fa-list-alt',          'admin.website.programs.index',   '#059669'],
        ['Site Settings',      'fa-cog',               'admin.website.site-settings',    '#374151'],
        ['View Website',       'fa-external-link-alt', 'website.home',                   '#16a34a'],
      ] as [$label,$icon,$route,$color])
      <a href="{{ route($route) }}" @if($route==='website.home') target="_blank" @endif
         class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:shadow-sm transition-all text-sm font-semibold text-gray-700 hover:border-gray-300">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:{{ $color }}15">
          <i class="fas {{ $icon }} text-sm" style="color:{{ $color }}"></i>
        </div>{{ $label }}
      </a>
      @endforeach
    </div>
  </div>

  {{-- Recent messages --}}
  <div class="card p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-gray-800">Recent Messages</h3>
      <a href="{{ route('admin.website.messages.index') }}" class="text-xs text-blue-600 font-semibold">View all →</a>
    </div>
    @forelse($recentMessages as $msg)
    <a href="{{ route('admin.website.messages.show', $msg) }}" class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 rounded-lg px-2 -mx-2 transition-colors">
      <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-700 text-xs font-bold">{{ strtoupper(substr($msg->name,0,1)) }}</div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <p class="text-sm font-semibold text-gray-800 truncate">{{ $msg->name }}</p>
          @if(!$msg->is_read)<span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>@endif
        </div>
        <p class="text-xs text-gray-500 truncate">{{ $msg->subject }}</p>
      </div>
      <span class="text-xs text-gray-400 flex-shrink-0">{{ $msg->created_at->diffForHumans(null,true) }}</span>
    </a>
    @empty<p class="text-sm text-gray-400 text-center py-4">No messages yet.</p>
    @endforelse
  </div>
</div>

{{-- Recent applications --}}
@if($recentApps->count())
<div class="card p-6">
  <div class="flex items-center justify-between mb-4">
    <h3 class="font-bold text-gray-800">Recent Applications</h3>
    <a href="{{ route('admin.website.applications.index') }}" class="text-xs text-blue-600 font-semibold">View all →</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-xs text-gray-500 uppercase border-b border-gray-100">
        <th class="pb-3 font-semibold">Child</th><th class="pb-3 font-semibold">Program</th><th class="pb-3 font-semibold">Parent</th><th class="pb-3 font-semibold">Status</th><th class="pb-3 font-semibold">Date</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-50">
        @foreach($recentApps as $app)
        <tr class="hover:bg-gray-50">
          <td class="py-3 font-semibold text-gray-800">{{ $app->child_full_name }}</td>
          <td class="py-3 text-gray-600">{{ $app->program_label }}</td>
          <td class="py-3 text-gray-600">{{ $app->parent_name }}</td>
          <td class="py-3">
            @php $badge = $app->status_badge; @endphp
            <span class="px-2 py-1 rounded-full text-xs font-bold bg-{{ $badge['color'] }}-100 text-{{ $badge['color'] }}-700">{{ $badge['label'] }}</span>
          </td>
          <td class="py-3 text-gray-400 text-xs">{{ $app->created_at->format('M j, Y') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
