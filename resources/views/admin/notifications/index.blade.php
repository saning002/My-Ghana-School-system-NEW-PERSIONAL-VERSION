@extends('layouts.app')
@section('title','Notification Center')
@section('subtitle','Send alerts and messages to students and teachers')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    {{-- ── SEND FORM ───────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden sticky top-20"
             x-data="{
                recipientType: '{{ old('recipient_type', 'student') }}',
                audience: '{{ old('audience', 'all') }}'
             }">
            <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
                <h3 class="text-sm font-bold" style="color:#78520a">
                    <i class="fas fa-paper-plane mr-2"></i>Send Notification
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.notifications.store') }}" class="px-5 py-5 space-y-4">
                @csrf

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-xs text-red-700 space-y-1">
                    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
                </div>
                @endif

                {{-- ── Step 1: Who to notify ──────────────────────────── --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Notify *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="recipient_type" value="student"
                                   x-model="recipientType" class="sr-only peer">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-all
                                        peer-checked:border-yellow-500 peer-checked:bg-yellow-50 border-gray-200">
                                <i class="fas fa-user-graduate text-sm" style="color:#D4A017"></i>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Students</p>
                                    <p class="text-xs text-gray-400">Student portal</p>
                                </div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="recipient_type" value="lecturer"
                                   x-model="recipientType" class="sr-only peer">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-all
                                        peer-checked:border-purple-500 peer-checked:bg-purple-50 border-gray-200">
                                <i class="fas fa-chalkboard-user text-sm text-purple-600"></i>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Teachers</p>
                                    <p class="text-xs text-gray-400">Teacher portal</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- ── Step 2: Audience scope ──────────────────────────── --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Send To *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="audience" value="all"
                                   x-model="audience" class="sr-only peer" checked>
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-all
                                        peer-checked:border-yellow-500 peer-checked:bg-yellow-50 border-gray-200">
                                <i class="fas fa-users text-sm" style="color:#D4A017"></i>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">All</p>
                                    <p class="text-xs text-gray-400" x-text="recipientType === 'lecturer' ? 'All teachers' : 'All students'"></p>
                                </div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="audience" value="individual"
                                   x-model="audience" class="sr-only peer">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-all
                                        peer-checked:border-yellow-500 peer-checked:bg-yellow-50 border-gray-200">
                                <i class="fas fa-user text-sm" style="color:#D4A017"></i>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Individual</p>
                                    <p class="text-xs text-gray-400">One person</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Student picker --}}
                    <div class="mt-3" x-show="audience === 'individual' && recipientType === 'student'" x-cloak>
                        <x-student-search name="student_id" placeholder="Search student..." :required="false" />
                    </div>

                    {{-- Lecturer picker --}}
                    <div class="mt-3" x-show="audience === 'individual' && recipientType === 'lecturer'" x-cloak>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Select Teacher *</label>
                        <select name="lecturer_id"
                                class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                            <option value="">— Choose a teacher —</option>
                            @foreach($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                                {{ $lecturer->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ── Type ────────────────────────────────────────────── --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Notification Type *</label>
                    <select name="type" required
                            class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
                        <option value="general">📢 General Announcement</option>
                        <option value="fee">💰 Fee Reminder</option>
                        <option value="emergency">🚨 Emergency Alert</option>
                        <option value="result">📋 Result Notice</option>
                        <option value="attendance">📅 Attendance Notice</option>
                    </select>
                </div>

                {{-- ── Title ───────────────────────────────────────────── --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
                           placeholder="e.g. Important Announcement">
                </div>

                {{-- ── Message ─────────────────────────────────────────── --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Message *</label>
                    <textarea name="message" rows="4" required
                              class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white resize-none"
                              placeholder="Write your message here...">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="btn-gold w-full py-3 rounded-xl text-sm font-bold shadow-sm">
                    <i class="fas fa-paper-plane mr-2"></i> Send Notification
                </button>
            </form>
        </div>
    </div>

    {{-- ── NOTIFICATIONS LIST ──────────────────────────────────────────── --}}
    <div class="lg:col-span-3">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700">
                Sent Notifications
                <span class="ml-2 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-semibold">
                    {{ $notifications->total() }}
                </span>
            </h3>
        </div>

        @if($notifications->isEmpty())
        <div class="card p-12 text-center">
            <i class="fas fa-bell-slash text-gray-200 text-5xl mb-4 block"></i>
            <p class="text-gray-400 font-medium text-sm">No notifications sent yet.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($notifications as $notif)
            @php
                $tc = [
                    'general'    => ['icon'=>'fa-bullhorn',            'color'=>'#6366f1','bg'=>'#ede9fe','label'=>'General'],
                    'fee'        => ['icon'=>'fa-coins',               'color'=>'#d97706','bg'=>'#fef3c7','label'=>'Fee'],
                    'emergency'  => ['icon'=>'fa-exclamation-triangle','color'=>'#dc2626','bg'=>'#fee2e2','label'=>'Emergency'],
                    'result'     => ['icon'=>'fa-file-alt',            'color'=>'#059669','bg'=>'#d1fae5','label'=>'Result'],
                    'attendance' => ['icon'=>'fa-clipboard-check',     'color'=>'#0891b2','bg'=>'#cffafe','label'=>'Attendance'],
                ][$notif->type] ?? ['icon'=>'fa-bell','color'=>'#6b7280','bg'=>'#f3f4f6','label'=>'Notice'];
                $isTeacher = ($notif->recipient_type ?? 'student') === 'lecturer';
            @endphp
            <div class="card p-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:{{ $tc['bg'] }}">
                        <i class="fas {{ $tc['icon'] }}" style="color:{{ $tc['color'] }};font-size:14px"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="text-sm font-bold text-gray-800">{{ $notif->title }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                  style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">
                                {{ $tc['label'] }}
                            </span>
                            {{-- Recipient badge --}}
                            @if($isTeacher)
                                @if($notif->audience === 'all')
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-purple-100 text-purple-700">
                                    <i class="fas fa-chalkboard-user mr-1"></i>All Teachers
                                </span>
                                @else
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-purple-100 text-purple-700">
                                    <i class="fas fa-chalkboard-user mr-1"></i>{{ $notif->lecturer->full_name ?? 'Teacher' }}
                                </span>
                                @endif
                            @else
                                @if($notif->audience === 'all')
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-blue-100 text-blue-700">
                                    <i class="fas fa-users mr-1"></i>All Students
                                </span>
                                @else
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-green-100 text-green-700">
                                    <i class="fas fa-user-graduate mr-1"></i>{{ $notif->student->user->full_name ?? 'Student' }}
                                </span>
                                @endif
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 mb-2 leading-relaxed">{{ $notif->message }}</p>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-400">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $notif->created_at->diffForHumans() }} — by {{ $notif->sender->full_name ?? 'Admin' }}
                            </p>
                            <form method="POST" action="{{ route('admin.notifications.destroy', $notif) }}"
                                  onsubmit="return confirm('Delete this notification?')">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($notifications->hasPages())
        <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
