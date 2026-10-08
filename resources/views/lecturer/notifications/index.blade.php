@extends('layouts.app')

@section('title', 'Class Notifications')
@section('subtitle', 'Send announcements to students and review administrative notices')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="card border border-amber-200/60 bg-gradient-to-r from-amber-50 to-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Communication Center</p>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Class Notifications</h2>
                <p class="text-xs text-slate-600 mt-0.5">Broadcast announcements to students and read messages sent from administration.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/60 bg-white px-3.5 py-1.5 text-xs font-bold text-amber-800 shadow-sm shrink-0">
                <i class="fas fa-bell text-amber-500"></i> Notifications Studio
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Left Side: Compose Notification Form --}}
        <div class="lg:col-span-2">
            <div class="card border border-slate-200 bg-white overflow-hidden sticky top-20 shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/80 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900">Send Notification</h3>
                </div>

                <form method="POST" action="{{ route('lecturer.notifications.store') }}" class="p-5 space-y-4">
                    @csrf

                    <div x-data="{ audience: '{{ old('audience', 'all') }}' }">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Audience *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="audience" value="all" x-model="audience" class="sr-only peer" checked>
                                <div class="p-3 rounded-xl border border-slate-200 text-center transition-all peer-checked:border-amber-500 peer-checked:bg-amber-50/60 peer-checked:text-amber-900">
                                    <i class="fas fa-users text-sm block mb-1 text-amber-600"></i>
                                    <span class="text-xs font-bold block">All Students</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="audience" value="individual" x-model="audience" class="sr-only peer">
                                <div class="p-3 rounded-xl border border-slate-200 text-center transition-all peer-checked:border-amber-500 peer-checked:bg-amber-50/60 peer-checked:text-amber-900">
                                    <i class="fas fa-user text-sm block mb-1 text-amber-600"></i>
                                    <span class="text-xs font-bold block">Individual</span>
                                </div>
                            </label>
                        </div>

                        <div class="mt-3" x-show="audience === 'individual'" x-cloak>
                            <x-student-search name="student_id" placeholder="Search student by name or ID..." :required="false" />
                        </div>
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Notification Type *</label>
                        <select name="type" id="type" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all">
                            <option value="general">📢 General Announcement</option>
                            <option value="attendance">📅 Attendance Notice</option>
                            <option value="result">📋 Exam Result Notice</option>
                            <option value="fee">💰 Fee Reminder</option>
                            <option value="emergency">🚨 Emergency Alert</option>
                        </select>
                    </div>

                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Title *</label>
                        <input type="text" name="title" id="title" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all" placeholder="e.g. Test Scheduled for Friday">
                    </div>

                    <div>
                        <label for="message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Message *</label>
                        <textarea name="message" id="message" rows="4" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all resize-none" placeholder="Write notification message content here..."></textarea>
                    </div>

                    <button type="submit" class="btn-gold w-full py-3 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane text-xs"></i> Send Notification
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Side: Notification Feed Tabs --}}
        <div class="lg:col-span-3 space-y-4" x-data="{ tab: 'sent' }">
            {{-- Tabs Header --}}
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                <button @click="tab='sent'" :class="tab==='sent' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Sent by You
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20" :class="tab==='sent' ? 'text-white' : 'text-slate-700 bg-slate-200'">{{ $notifications->total() }}</span>
                </button>
                <button @click="tab='inbox'" :class="tab==='inbox' ? 'bg-purple-800 text-white shadow-sm' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                    <i class="fas fa-inbox"></i> From Admin
                    <span class="px-2 py-0.5 rounded-full text-[10px]" :class="tab==='inbox' ? 'bg-white/20 text-white' : 'bg-purple-200 text-purple-800'">{{ $adminNotifications->total() }}</span>
                </button>
            </div>

            {{-- Sent Tab Content --}}
            <div x-show="tab==='sent'" class="space-y-3">
                @if($notifications->isEmpty())
                    <div class="card p-12 text-center text-slate-400">
                        <i class="fas fa-bell-slash text-4xl mb-3 text-slate-300"></i>
                        <p class="text-sm font-bold text-slate-600">No notifications sent yet.</p>
                    </div>
                @else
                    @foreach($notifications as $notification)
                        @php
                            $tc = [
                                'general'    => ['icon' => 'fa-bullhorn', 'bg' => 'bg-blue-50 text-blue-700 border-blue-100', 'label' => 'General'],
                                'attendance' => ['icon' => 'fa-clipboard-check', 'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-100', 'label' => 'Attendance'],
                                'result'     => ['icon' => 'fa-file-alt', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => 'Result'],
                                'fee'        => ['icon' => 'fa-coins', 'bg' => 'bg-amber-50 text-amber-700 border-amber-100', 'label' => 'Fee'],
                                'emergency'  => ['icon' => 'fa-exclamation-triangle', 'bg' => 'bg-red-50 text-red-700 border-red-100', 'label' => 'Emergency'],
                            ][$notification->type] ?? ['icon' => 'fa-bell', 'bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Notice'];
                        @endphp
                        <div class="card p-4 border border-slate-200 bg-white hover:border-amber-200 transition-all space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $tc['bg'] }}">
                                        <i class="fas {{ $tc['icon'] }}"></i> {{ $tc['label'] }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                        {{ $notification->audience === 'all' ? 'All Students' : 'Individual' }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400 shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $notification->title }}</h4>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $notification->message }}</p>
                        </div>
                    @endforeach

                    @if($notifications->hasPages())
                        <div class="pt-2">{{ $notifications->links() }}</div>
                    @endif
                @endif
            </div>

            {{-- Inbox Tab Content --}}
            <div x-show="tab==='inbox'" class="space-y-3" x-cloak>
                @if($adminNotifications->isEmpty())
                    <div class="card p-12 text-center text-slate-400">
                        <i class="fas fa-inbox text-4xl mb-3 text-slate-300"></i>
                        <p class="text-sm font-bold text-slate-600">No admin notifications received.</p>
                    </div>
                @else
                    @foreach($adminNotifications as $msg)
                        <div class="card p-4 border border-slate-200 bg-white space-y-2 {{ !$msg->is_read ? 'border-l-4 border-l-purple-600' : '' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">{{ $msg->title }}</span>
                                    @if(!$msg->is_read)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">New</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400 shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $msg->message }}</p>
                            <p class="text-[11px] text-slate-400 pt-1">From: {{ $msg->sender->full_name ?? 'Administration' }}</p>
                        </div>
                    @endforeach

                    @if($adminNotifications->hasPages())
                        <div class="pt-2">{{ $adminNotifications->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
