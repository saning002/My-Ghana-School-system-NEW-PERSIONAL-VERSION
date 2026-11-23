@props(['name' => 'student_id', 'placeholder' => 'Type name or student ID...', 'required' => false])
@php $uid = 'ss' . substr(md5(uniqid()), 0, 8); @endphp

<div id="{{ $uid }}_wrap" style="position:relative;width:100%;">

    {{-- Programme filter --}}
    <select id="{{ $uid }}_prog"
        onchange="document.getElementById('{{ $uid }}_input').value=''; {{ $uid }}_search();"
        style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:10px;font-size:.85rem;background:#fff;margin-bottom:6px;outline:none;cursor:pointer;">
        <option value="">— All Programmes —</option>
        @foreach(\App\Models\Program::orderBy('sequence')->get() as $prog)
        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
        @endforeach
    </select>

    {{-- Search input --}}
    <input type="text" id="{{ $uid }}_input"
        placeholder="{{ $placeholder }}"
        oninput="{{ $uid }}_search()"
        autocomplete="off"
        style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:10px;font-size:.85rem;background:#fff;outline:none;box-sizing:border-box;">

    {{-- Hidden value --}}
    <input type="hidden" id="{{ $uid }}_val" name="{{ $name }}" value="">

    {{-- Dropdown --}}
    <div id="{{ $uid }}_drop"
        style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:9999;
               background:#fff;border:1px solid #e5e7eb;border-radius:12px;
               box-shadow:0 10px 36px rgba(0,0,0,.15);max-height:260px;overflow-y:auto;">
    </div>

    {{-- Selected badge --}}
    <div id="{{ $uid }}_badge" style="display:none;margin-top:6px;padding:8px 12px;
        background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;
        font-size:.82rem;font-weight:600;color:#166534;">
        <i class="fas fa-check-circle" style="color:#16a34a;margin-right:6px;"></i>
        <span id="{{ $uid }}_badge_txt"></span>
        <button type="button" onclick="{{ $uid }}_clear()"
            style="margin-left:8px;background:none;border:1px solid #fca5a5;cursor:pointer;
                   color:#ef4444;font-size:.72rem;padding:1px 8px;border-radius:6px;">
            <i class="fas fa-times"></i> Change
        </button>
    </div>
</div>

<script>
var {{ $uid }}_timer = null;
var {{ $uid }}_results = [];
var {{ $uid }}_url = '{{ route('admin.students.search') }}';

function {{ $uid }}_search() {
    var inp  = document.getElementById('{{ $uid }}_input');
    var prog = document.getElementById('{{ $uid }}_prog');
    var q    = inp ? inp.value.trim() : '';
    var pid  = prog ? prog.value : '';

    if (!q && !pid) {
        document.getElementById('{{ $uid }}_drop').style.display = 'none';
        return;
    }

    clearTimeout({{ $uid }}_timer);
    {{ $uid }}_timer = setTimeout(function() {
        var url = {{ $uid }}_url + '?';
        if (q)   url += 'q='          + encodeURIComponent(q)   + '&';
        if (pid) url += 'program_id=' + encodeURIComponent(pid) + '&';

        fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            {{ $uid }}_results = data;
            {{ $uid }}_render(data);
        })
        .catch(function(err) {
            console.error('Student search error:', err);
            var drop = document.getElementById('{{ $uid }}_drop');
            drop.innerHTML = '<div style="padding:12px;text-align:center;color:#ef4444;font-size:.8rem;">Search failed. Please try again.</div>';
            drop.style.display = 'block';
        });
    }, 250);
}

function {{ $uid }}_render(students) {
    var drop = document.getElementById('{{ $uid }}_drop');
    if (!students || students.length === 0) {
        drop.innerHTML = '<div style="padding:12px 16px;text-align:center;font-size:.82rem;color:#94a3b8;"><i class="fas fa-search"></i> No students found</div>';
        drop.style.display = 'block';
        return;
    }
    var html = '';
    students.forEach(function(s, i) {
        var init = (s.full_name || '?').charAt(0).toUpperCase();
        html += '<div onclick="{{ $uid }}_pick(' + i + ')" style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background=\'#fef9c3\'" onmouseout="this.style.background=\'\'">'
              + '<div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#fbbf24,#f59e0b);display:flex;align-items:center;justify-content:center;font-weight:800;color:#78350f;flex-shrink:0;">' + init + '</div>'
              + '<div style="flex:1;min-width:0;">'
              + '<div style="font-weight:700;font-size:.85rem;color:#1e293b;">' + (s.full_name||'') + '</div>'
              + '<div style="font-size:.72rem;color:#94a3b8;"><span style="background:#f1f5f9;padding:1px 6px;border-radius:5px;font-family:monospace;color:#475569;">' + (s.student_id||'') + '</span> &middot; ' + (s.program||'') + '</div>'
              + '</div><i class="fas fa-check" style="color:#d1d5db;font-size:.65rem;"></i></div>';
    });
    drop.innerHTML = html;
    drop.style.display = 'block';
}

function {{ $uid }}_pick(i) {
    var s = {{ $uid }}_results[i];
    if (!s) return;

    // Set hidden input
    document.getElementById('{{ $uid }}_val').value = s.id;

    // Hide input, show badge
    document.getElementById('{{ $uid }}_input').style.display = 'none';
    document.getElementById('{{ $uid }}_prog').style.display  = 'none';
    document.getElementById('{{ $uid }}_badge_txt').textContent = s.full_name + ' (' + s.student_id + ')';
    document.getElementById('{{ $uid }}_badge').style.display = 'block';
    document.getElementById('{{ $uid }}_drop').style.display  = 'none';
}

function {{ $uid }}_clear() {
    document.getElementById('{{ $uid }}_val').value  = '';
    document.getElementById('{{ $uid }}_input').style.display = '';
    document.getElementById('{{ $uid }}_prog').style.display  = '';
    document.getElementById('{{ $uid }}_input').value = '';
    document.getElementById('{{ $uid }}_badge').style.display = 'none';
    document.getElementById('{{ $uid }}_drop').style.display  = 'none';
    document.getElementById('{{ $uid }}_input').focus();
}

// Close on outside click
document.addEventListener('click', function(e) {
    var wrap = document.getElementById('{{ $uid }}_wrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('{{ $uid }}_drop').style.display = 'none';
    }
});

// Block form submit if no student selected (for required instances)
@if($required)
(function() {
    var val = document.getElementById('{{ $uid }}_val');
    if (!val) return;
    var form = val.closest ? val.closest('form') : null;
    if (!form) return;
    form.addEventListener('submit', function(e) {
        if (!val.value) {
            e.preventDefault();
            var inp = document.getElementById('{{ $uid }}_input');
            if (inp) {
                inp.style.borderColor = '#ef4444';
                inp.placeholder = 'Please search and SELECT a student from the list';
                inp.focus();
                setTimeout(function(){ inp.style.borderColor = '#d1d5db'; }, 3000);
            }
        }
    });
})();
@endif
</script>
