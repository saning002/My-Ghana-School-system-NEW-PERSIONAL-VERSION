@extends('layouts.app')
@section('title', 'Message Templates')
@section('subtitle', 'Create reusable message drafts with smart placeholders')

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────── --}}
<div class="page-hero-banner mb-6">
    <div class="hero-left">
        <h1><i class="fas fa-file-alt mr-2" style="color:var(--brand)"></i>Message Templates</h1>
        <p>Write a message once — reuse it every time you send a notification</p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('admin.notifications.index') }}" class="btn-secondary-action">
            <i class="fas fa-arrow-left"></i> Back to Notifications
        </a>
    </div>
</div>

{{-- ── Flash ─────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-green-50 border border-green-200 text-green-800 text-sm font-semibold mb-5">
    <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold mb-5">
    <i class="fas fa-times-circle text-red-500"></i> {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

    {{-- ════════════════════════════════════════════════════════════════════
         LEFT — Create / Edit form
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="xl:col-span-2">
        <div class="sc" x-data="templateForm()" id="template-form-card">
            <div class="sc-hd">
                <div>
                    <p class="sc-title" x-text="editId ? 'Edit Template' : 'New Template'"></p>
                    <p class="sc-sub">Use <span class="font-bold" style="color:var(--brand)">{placeholders}</span> for dynamic content</p>
                </div>
                <button type="button" x-show="editId" @click="resetForm()"
                        class="text-xs text-slate-400 hover:text-red-500 font-semibold flex items-center gap-1 transition-colors">
                    <i class="fas fa-times"></i> Cancel edit
                </button>
            </div>

            <div class="sc-body space-y-4">
                <form method="POST"
                      action="{{ route('admin.notifications.templates.store') }}"
                      id="tpl-form">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="tpl-method">

                    {{-- Template name --}}
                    <div>
                        <label class="form-label">Template Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" x-model="form.name" required
                               class="form-input"
                               placeholder="e.g. End of Term Reminder">
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="form-label">Category <span class="text-red-400">*</span></label>
                        <select name="category" x-model="form.category" required class="form-input">
                            <option value="general">General Announcement</option>
                            <option value="attendance">Attendance</option>
                            <option value="fee">Fee Reminder</option>
                            <option value="results">Results / Report Card</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    {{-- Default subject / title --}}
                    <div>
                        <label class="form-label">Default Subject / Title <span class="text-red-400">*</span></label>
                        <input type="text" name="title" x-model="form.title" required
                               class="form-input"
                               placeholder="e.g. Your ward's attendance update">
                    </div>

                    {{-- Message body --}}
                    <div>
                        <label class="form-label">Message Body <span class="text-red-400">*</span></label>
                        <textarea name="body" x-model="form.body" id="tpl-body" rows="6" required
                                  class="form-input resize-none"
                                  @input="syncBody($event)"
                                  placeholder="Dear {parent_name}, {student_name} of {class}..."></textarea>
                        <div class="flex justify-between mt-1">
                            <p class="text-xs text-slate-400">Click a placeholder below to insert it at cursor</p>
                            <p class="text-xs font-bold"
                               :class="form.body.length > 2800 ? 'text-red-500' : 'text-slate-400'"
                               x-text="form.body.length + ' / 3000'"></p>
                        </div>
                    </div>

                    {{-- Placeholder buttons ─────────────────────────────── --}}
                    <div>
                        <p class="form-label mb-2">Insert Placeholder</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(\App\Models\NotificationTemplate::placeholders() as $tag => $desc)
                            <button type="button"
                                    title="{{ $desc }}"
                                    onclick="insertPlaceholder('{{ $tag }}')"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold
                                           border border-dashed transition-all
                                           hover:border-solid hover:-translate-y-0.5"
                                    style="background:var(--primary-pale);color:var(--brand);border-color:var(--brand)">
                                {{ $tag }}
                            </button>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Hover any tag to see what it inserts. Placeholders are replaced with real data when the message is sent.
                        </p>
                    </div>

                    {{-- Preview ─────────────────────────────────────────── --}}
                    <div x-show="form.body.length > 0" x-cloak>
                        <p class="form-label mb-2">Live Preview <span class="text-slate-400 font-normal normal-case">(placeholders shown as-is)</span></p>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 leading-relaxed whitespace-pre-wrap break-words"
                             x-text="form.body"></div>
                    </div>

                    <button type="submit"
                            class="btn-primary-action w-full justify-center py-3 rounded-xl text-sm font-bold">
                        <i class="fas fa-save mr-1.5"></i>
                        <span x-text="editId ? 'Update Template' : 'Save Template'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         RIGHT — Saved templates list
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="xl:col-span-3">
        <div class="sc">
            <div class="sc-hd">
                <div>
                    <p class="sc-title">Saved Templates</p>
                    <p class="sc-sub">{{ $templates->count() }} template{{ $templates->count() !== 1 ? 's' : '' }} saved</p>
                </div>
                {{-- Category filter --}}
                <select id="cat-filter" onchange="filterTemplates(this.value)"
                        class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 text-slate-600 focus:outline-none focus:border-[var(--brand)]">
                    <option value="">All categories</option>
                    <option value="general">General</option>
                    <option value="attendance">Attendance</option>
                    <option value="fee">Fee</option>
                    <option value="results">Results</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            @if($templates->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-file-alt"></i></div>
                <p class="empty-state-title">No templates yet</p>
                <p class="empty-state-sub">Create your first template using the form on the left.</p>
            </div>
            @else
            <div class="divide-y divide-slate-50" id="templates-list">
                @foreach($templates as $tpl)
                @php
                    $catMeta = [
                        'general'    => ['bg'=>'#ede9fe','color'=>'#7c3aed','label'=>'General'],
                        'attendance' => ['bg'=>'#cffafe','color'=>'#0891b2','label'=>'Attendance'],
                        'fee'        => ['bg'=>'#fef3c7','color'=>'#d97706','label'=>'Fee'],
                        'results'    => ['bg'=>'#dcfce7','color'=>'#16a34a','label'=>'Results'],
                        'custom'     => ['bg'=>'#f1f5f9','color'=>'#475569','label'=>'Custom'],
                    ][$tpl->category] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>ucfirst($tpl->category)];
                @endphp
                <div class="px-5 py-4 hover:bg-slate-50/80 transition-all tpl-row"
                     data-category="{{ $tpl->category }}">
                    <div class="flex items-start gap-3">
                        {{-- Category pill --}}
                        <span class="mt-0.5 flex-shrink-0 text-xs font-bold px-2.5 py-1 rounded-full"
                              style="background:{{ $catMeta['bg'] }};color:{{ $catMeta['color'] }}">
                            {{ $catMeta['label'] }}
                        </span>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $tpl->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                <i class="fas fa-heading mr-1 opacity-50"></i>{{ $tpl->title }}
                            </p>
                            {{-- Body preview --}}
                            <p class="text-xs text-slate-400 mt-1.5 leading-relaxed line-clamp-2">
                                {{ Str::limit($tpl->body, 120) }}
                            </p>
                            {{-- Placeholders detected in this template --}}
                            @php
                                preg_match_all('/\{[a-z_]+\}/', $tpl->body, $found);
                                $foundTags = array_unique($found[0] ?? []);
                            @endphp
                            @if(!empty($foundTags))
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($foundTags as $tag)
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded"
                                      style="background:var(--primary-pale);color:var(--brand)">
                                    {{ $tag }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            {{-- Use in notification --}}
                            <a href="{{ route('admin.notifications.index', ['template_id' => $tpl->id]) }}"
                               title="Use in notification"
                               class="action-btn action-btn-view">
                                <i class="fas fa-paper-plane" style="font-size:.72rem"></i>
                            </a>
                            {{-- Edit --}}
                            <button type="button"
                                    title="Edit template"
                                    onclick="editTemplate({{ $tpl->id }}, {{ json_encode($tpl->name) }}, '{{ $tpl->category }}', {{ json_encode($tpl->title) }}, {{ json_encode($tpl->body) }})"
                                    class="action-btn action-btn-edit">
                                <i class="fas fa-pen" style="font-size:.72rem"></i>
                            </button>
                            {{-- Delete --}}
                            <form method="POST"
                                  action="{{ route('admin.notifications.templates.destroy', $tpl) }}"
                                  onsubmit="return confirm('Delete template \'{{ addslashes($tpl->name) }}\'?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete" class="action-btn action-btn-delete">
                                    <i class="fas fa-trash" style="font-size:.72rem"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── Placeholder reference card ──────────────────────────────── --}}
        <div class="sc mt-5">
            <div class="sc-hd">
                <div>
                    <p class="sc-title">Placeholder Reference</p>
                    <p class="sc-sub">Every tag you can use and what it inserts</p>
                </div>
                <span class="sc-pill"><i class="fas fa-tags"></i> {{ count(\App\Models\NotificationTemplate::placeholders()) }} tags</span>
            </div>
            <div class="sc-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach(\App\Models\NotificationTemplate::placeholders() as $tag => $desc)
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <code class="text-xs font-bold flex-shrink-0 mt-0.5"
                              style="color:var(--brand)">{{ $tag }}</code>
                        <span class="text-xs text-slate-500 leading-relaxed">{{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-700">
                    <i class="fas fa-lightbulb mr-1 text-amber-500"></i>
                    <strong>Tip:</strong> If a student's profile is missing a field (e.g. no parent name saved),
                    the placeholder shows a fallback like "Parent/Guardian". Always keep student records complete
                    for the best results.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Alpine component ─────────────────────────────────────────────────────
function templateForm() {
    return {
        editId: null,
        form: {
            name: '',
            category: 'general',
            title: '',
            body: '',
        },

        resetForm() {
            this.editId = null;
            this.form   = { name:'', category:'general', title:'', body:'' };

            // Reset the form action back to store route
            const formEl = document.getElementById('tpl-form');
            if (formEl) formEl.action = '{{ route('admin.notifications.templates.store') }}';

            const methodInput = document.getElementById('tpl-method');
            if (methodInput) methodInput.value = 'POST';

            // Clear the raw textarea too
            const ta = document.getElementById('tpl-body');
            if (ta) { ta.value = ''; }
            document.getElementById('tpl-body')?.focus();
        },

        // Keep body in sync when user types directly in the textarea
        syncBody(e) {
            this.form.body = e.target.value;
        }
    };
}

// ── Edit — fill form from row data ────────────────────────────────────────
function editTemplate(id, name, category, title, body) {
    const formEl = document.getElementById('tpl-form');
    if (!formEl) return;

    // Set the form action to the update URL with the ID
    formEl.action = '{{ url('admin/notifications/templates') }}/' + id;

    // Set _method to PUT for Laravel route model binding
    const methodInput = document.getElementById('tpl-method');
    if (methodInput) methodInput.value = 'PUT';

    // Find the Alpine component that owns the form card
    const alpineEl = formEl.querySelector('[x-data]') ?? formEl;
    const comp = Alpine.$data(alpineEl);

    if (comp) {
        comp.editId        = id;
        comp.form.name     = name;
        comp.form.category = category;
        comp.form.title    = title;
        comp.form.body     = body;
    }

    // Also set the raw textarea value so it matches before Alpine re-renders
    const ta = document.getElementById('tpl-body');
    if (ta) {
        ta.value = body;
        ta.dispatchEvent(new Event('input', { bubbles: true }));
    }

    formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(() => document.getElementById('tpl-body')?.focus(), 300);
}

// ── Insert placeholder at textarea cursor ─────────────────────────────────
function insertPlaceholder(tag) {
    const ta = document.getElementById('tpl-body');
    if (!ta) return;

    const start = ta.selectionStart ?? ta.value.length;
    const end   = ta.selectionEnd   ?? ta.value.length;
    const newVal = ta.value.slice(0, start) + tag + ta.value.slice(end);

    // Update the native textarea value
    ta.value = newVal;

    // Sync Alpine x-model by dispatching an input event
    ta.dispatchEvent(new Event('input', { bubbles: true }));

    // Restore focus and cursor position after the tag
    ta.focus();
    requestAnimationFrame(() => {
        ta.selectionStart = start + tag.length;
        ta.selectionEnd   = start + tag.length;
    });
}

// ── Category filter ───────────────────────────────────────────────────────
function filterTemplates(cat) {
    document.querySelectorAll('.tpl-row').forEach(row => {
        row.style.display = (!cat || row.dataset.category === cat) ? '' : 'none';
    });
}
</script>
@endpush

@endsection
