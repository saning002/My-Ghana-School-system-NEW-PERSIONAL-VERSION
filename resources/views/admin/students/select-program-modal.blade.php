{{-- Program selector modal — shown when the uploaded file has no Program column --}}
<div id="selectProgramModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4">
        <h2 class="text-lg font-extrabold text-slate-800 mb-2">Select Program for Import</h2>
        <p class="text-sm text-slate-500 mb-5">
            Your file doesn't have a Program column. Select the program to assign to all imported students.
        </p>

        @if(session('select_program_for_import'))
        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
            @csrf
            {{-- Pass the system-tmp path so the controller can re-read it --}}
            <input type="hidden" name="sheet_temp_path" value="{{ session('select_program_for_import')['sheet_temp_path'] ?? '' }}">

            <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-2.5 text-xs text-amber-800">
                <i class="fas fa-file-excel text-amber-500 mr-1"></i>
                File: <strong>{{ session('select_program_for_import')['file'] ?? 'uploaded file' }}</strong>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Select Program *</label>
                <select name="default_program_id" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    <option value="">— Choose a program —</option>
                    @foreach(session('select_program_for_import')['programs'] as $prog)
                    <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.students.index') }}"
                    class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-center text-sm font-semibold transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-sm font-bold shadow transition-all active:scale-95">
                    <i class="fas fa-upload mr-1.5 text-xs"></i> Import
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
