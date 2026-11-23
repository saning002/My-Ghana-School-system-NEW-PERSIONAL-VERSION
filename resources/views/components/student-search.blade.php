@props(['name' => 'student_id', 'placeholder' => 'Type name or student ID...', 'required' => false])
@php $uid = 'ss' . substr(md5(uniqid()), 0, 8); @endphp

<div id="{{ $uid }}_wrap" style="position:relative;width:100%;">

    {{-- Programme filter --}}
    <select id="{{ $uid }}_prog"
        onchange="ssSetProgram('{{ $uid }}')"
        style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:10px;font-size:.85rem;background:#fff;margin-bottom:6px;outline:none;cursor:pointer;">
        <option value="">— All Programmes —</option>
        @foreach(\App\Models\Program::orderBy('sequence')->get() as $prog)
        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
        @endforeach
    </select>

    {{-- Search input --}}
    <div style="position:relative;">
        <input type="text" id="{{ $uid }}_input"
            placeholder="{{ $placeholder }}"
            oninput="ssSearch('{{ $uid }}')"
            onfocus="ssOpen('{{ $uid }}')"
            onkeydown="ssKey(event,'{{ $uid }}')"
            autocomplete="off"
            style="width:100%;padding:9px 36px 9px 12px;border:1px solid #d1d5db;border-radius:10px;font-size:.85rem;background:#fff;outline:none;box-sizing:border-box;transition:border-color .15s;">
        <span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;">
            <i class="fas fa-search" style="font-size:.78rem;"></i>
        </span>
    </div>

    {{-- Hidden input that actually submits — plain HTML, no JS framework --}}
    <input type="hidden" id="{{ $uid }}_val" name="{{ $name }}" value="" {{ $required ? 'required' : '' }}>

    {{-- Dropdown list --}}
    <div id="{{ $uid }}_drop"
        style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:9999;
               background:#fff;border:1px solid #e5e7eb;border-radius:12px;
               box-shadow:0 10px 36px rgba(0,0,0,.13);max-height:260px;overflow-y:auto;">
    </div>

    {{-- Selected student badge --}}
    <div id="{{ $uid }}_badge"
        style="display:none;margin-top:6px;padding:8px 12px;background:#f0fdf4;
               border:1px solid #bbf7d0;border-radius:10px;
               font-size:.82rem;font-weight:600;color:#166534;
               align-items:center;gap:8px;">
        <i class="fas fa-check-circle" style="color:#16a34a;flex-shrink:0;"></i>
        <span id="{{ $uid }}_badge_name" style="flex:1;"></span>
        <span id="{{ $uid }}_badge_id" style="font-size:.72rem;opacity:.7;"></span>
        <button type="button" onclick="ssClear('{{ $uid }}')"
            style="margin-left:4px;background:none;border:none;cursor:pointer;
                   color:#ef4444;font-size:.75rem;padding:2px 6px;
                   border-radius:6px;border:1px solid #fca5a5;">
            <i class="fas fa-times"></i> Change
        </button>
    </div>
</div>

<script>
(function(){
    // Per-instance state keyed by uid
    if (!window._ss) window._ss = {};
    window._ss['{{ $uid }}'] = {
        programId: '',
        highlighted: -1,
        results: [],
        timer: null,
        searchUrl: '{{ route('admin.students.search') }}'
    };
})();

function ssOpen(uid) {
    var inp = document.getElementById(uid + '_input');
    if (inp && !document.getElementById(uid + '_val').value) {
        ssSearch(uid);
    }
}

function ssSetProgram(uid) {
    var sel = document.getElementById(uid + '_prog');
    window._ss[uid].programId = sel ? sel.value : '';
    ssClear(uid);
    if (window._ss[uid].programId) ssSearch(uid);
}

function ssSearch(uid) {
    var state = window._ss[uid];
    var inp   = document.getElementById(uid + '_input');
    var q     = inp ? inp.value.trim() : '';

    // Don't search if already selected
    if (document.getElementById(uid + '_val').value) return;

    if (!state.programId && q.length < 1) {
        ssHideDrop(uid); return;
    }

    clearTimeout(state.timer);
    state.timer = setTimeout(function() {
        var url = state.searchUrl;
        var params = [];
        if (q.length >= 1)     params.push('q=' + encodeURIComponent(q));
        if (state.programId)   params.push('program_id=' + encodeURIComponent(state.programId));
        if (params.length)     url += '?' + params.join('&');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                state.results    = data;
                state.highlighted = -1;
                ssRenderDrop(uid, data);
            })
            .catch(function(){ ssHideDrop(uid); });
    }, 200);
}

function ssRenderDrop(uid, students) {
    var drop = document.getElementById(uid + '_drop');
    if (!drop) return;

    if (!students || students.length === 0) {
        drop.innerHTML = '<div style="padding:12px 16px;text-align:center;font-size:.82rem;color:#94a3b8;"><i class="fas fa-search" style="margin-right:5px;"></i>No students found</div>';
        drop.style.display = 'block';
        return;
    }

    var html = '';
    students.forEach(function(s, i) {
        var initial = (s.full_name || '?').charAt(0).toUpperCase();
        html += '<div onclick="ssSelect(\'' + uid + '\',' + i + ')" ' +
            'onmouseenter="ssHover(\'' + uid + '\',' + i + ')" ' +
            'id="' + uid + '_item_' + i + '" ' +
            'style="display:flex;align-items:center;gap:11px;padding:10px 14px;cursor:pointer;' +
            'border-bottom:1px solid #f3f4f6;transition:background .1s;">' +
            '<div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#fbbf24,#f59e0b);' +
            'display:flex;align-items:center;justify-content:center;font-weight:800;color:#78350f;' +
            'flex-shrink:0;font-size:.85rem;">' + initial + '</div>' +
            '<div style="flex:1;min-width:0;">' +
            '<div style="font-size:.85rem;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (s.full_name || '') + '</div>' +
            '<div style="font-size:.72rem;color:#94a3b8;margin-top:1px;">' +
            '<span style="background:#f1f5f9;padding:1px 7px;border-radius:6px;font-family:monospace;color:#475569;">' + (s.student_id || '') + '</span>' +
            ' &middot; ' + (s.program || '') +
            '</div></div>' +
            '<i class="fas fa-arrow-right" style="color:#d1d5db;font-size:.65rem;flex-shrink:0;"></i>' +
            '</div>';
    });

    drop.innerHTML = html;
    drop.style.display = 'block';
}

function ssHover(uid, idx) {
    window._ss[uid].highlighted = idx;
    ssHighlight(uid);
}

function ssHighlight(uid) {
    var state = window._ss[uid];
    state.results.forEach(function(_, i) {
        var el = document.getElementById(uid + '_item_' + i);
        if (el) el.style.background = (i === state.highlighted) ? '#fef9c3' : '';
    });
}

function ssSelect(uid, idx) {
    var state   = window._ss[uid];
    var student = state.results[idx];
    if (!student) return;

    // Set the hidden input VALUE — this is what gets submitted
    var hidden = document.getElementById(uid + '_val');
    hidden.value = student.id;

    // Clear search box
    var inp = document.getElementById(uid + '_input');
    if (inp) { inp.value = ''; inp.style.display = 'none'; }

    // Show badge
    var badge = document.getElementById(uid + '_badge');
    badge.style.display = 'flex';
    document.getElementById(uid + '_badge_name').textContent = student.full_name;
    document.getElementById(uid + '_badge_id').textContent   = student.student_id;

    ssHideDrop(uid);
}

function ssKey(e, uid) {
    var state = window._ss[uid];
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        state.highlighted = Math.min(state.highlighted + 1, state.results.length - 1);
        ssHighlight(uid);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        state.highlighted = Math.max(state.highlighted - 1, 0);
        ssHighlight(uid);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (state.highlighted >= 0) ssSelect(uid, state.highlighted);
    } else if (e.key === 'Escape') {
        ssHideDrop(uid);
    }
}

function ssClear(uid) {
    // Clear hidden value
    var hidden = document.getElementById(uid + '_val');
    if (hidden) hidden.value = '';

    // Show input, hide badge
    var inp = document.getElementById(uid + '_input');
    if (inp) { inp.value = ''; inp.style.display = ''; inp.focus(); }

    var badge = document.getElementById(uid + '_badge');
    if (badge) badge.style.display = 'none';

    ssHideDrop(uid);
    if (window._ss[uid].programId) ssSearch(uid);
}

function ssHideDrop(uid) {
    var drop = document.getElementById(uid + '_drop');
    if (drop) drop.style.display = 'none';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!window._ss) return;
    Object.keys(window._ss).forEach(function(uid) {
        var wrap = document.getElementById(uid + '_wrap');
        if (wrap && !wrap.contains(e.target)) ssHideDrop(uid);
    });
});
</script>
