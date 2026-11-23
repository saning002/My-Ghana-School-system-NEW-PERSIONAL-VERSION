@props(['name' => 'student_id', 'placeholder' => 'Search by name or ID...', 'required' => false])
@php $uid = 'ss_' . uniqid(); @endphp

<div x-data="studentSearch('{{ $uid }}')" @program-change.window="setProgram($event.detail)" class="relative space-y-2">

    {{-- Programme filter --}}
    <select
        @change="setProgram($event.target.value)"
        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
        <option value="">All Programmes</option>
        @foreach(\App\Models\Program::orderBy('sequence')->get() as $prog)
        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
        @endforeach
    </select>

    {{-- Search input --}}
    <div class="relative">
        <input
            type="text"
            x-model="query"
            @input.debounce.250ms="search()"
            @focus="if(query.length >= 1 || programId) search(); showDropdown = true;"
            @keydown.arrow-down.prevent="highlightNext()"
            @keydown.arrow-up.prevent="highlightPrevious()"
            @keydown.enter.prevent="selectHighlighted()"
            @keydown.escape="showDropdown = false"
            :placeholder="selectedName ? selectedName + ' — click × to change' : '{{ $placeholder }}'"
            :class="selectedId ? 'border-green-400 bg-green-50 text-green-800 font-semibold' : 'border-gray-200 bg-white'"
            class="w-full px-4 py-3 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 pr-10"
            autocomplete="off"
            :readonly="selectedId !== ''"
        />

        {{-- Clear button --}}
        <button x-show="selectedId" type="button" @click="clearSelection()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-red-400 hover:text-red-600 transition-colors"
            title="Clear selection">
            <i class="fas fa-times-circle"></i>
        </button>

        {{-- Search icon when no selection --}}
        <span x-show="!selectedId" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none">
            <i class="fas fa-search text-sm"></i>
        </span>
    </div>

    {{-- The critical hidden input — set by JS not Alpine x-model --}}
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $uid }}_hidden"
           value=""
           {{ $required ? 'required' : '' }} />

    {{-- Dropdown results --}}
    <div
        x-show="showDropdown && (results.length > 0 || (query.length >= 1 && !loading))"
        @click.away="showDropdown = false"
        x-cloak
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-64 overflow-y-auto"
        style="top:100%">

        <template x-if="loading">
            <div class="px-4 py-3 text-sm text-gray-400 text-center">
                <i class="fas fa-spinner fa-spin mr-2"></i> Searching…
            </div>
        </template>

        <template x-if="!loading && results.length === 0 && query.length >= 1">
            <div class="px-4 py-3 text-sm text-gray-400 text-center">
                <i class="fas fa-search mr-2"></i> No students found
            </div>
        </template>

        <template x-for="(student, index) in results" :key="student.id">
            <div
                @click="selectStudent(student)"
                :class="highlightedIndex === index ? 'bg-yellow-50 border-l-4 border-yellow-400' : 'hover:bg-gray-50'"
                class="px-4 py-3 cursor-pointer border-b border-gray-50 last:border-b-0 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 border-2 border-yellow-100 bg-yellow-50 flex items-center justify-center font-bold text-sm text-yellow-700">
                        <template x-if="student.photo_url">
                            <img :src="student.photo_url" class="w-full h-full object-cover" :alt="student.full_name">
                        </template>
                        <template x-if="!student.photo_url">
                            <span x-text="student.full_name.charAt(0).toUpperCase()"></span>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate" x-text="student.full_name"></p>
                        <p class="text-xs text-gray-400 flex items-center gap-1">
                            <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-gray-600" x-text="student.student_id"></span>
                            <span>·</span>
                            <span x-text="student.program"></span>
                        </p>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-sm" x-show="selectedId == student.id"></i>
                </div>
            </div>
        </template>
    </div>

    {{-- Selected confirmation badge --}}
    <div x-show="selectedName" x-cloak
         class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-xl text-sm">
        <i class="fas fa-check-circle text-green-600 flex-shrink-0"></i>
        <span class="font-semibold text-green-800" x-text="selectedName"></span>
        <span class="text-green-500 text-xs" x-text="selectedStudentId ? '(' + selectedStudentId + ')' : ''"></span>
    </div>
</div>

<script>
function studentSearch(uid) {
    return {
        query: '',
        results: [],
        selectedId: '',
        selectedName: '',
        selectedStudentId: '',
        showDropdown: false,
        loading: false,
        highlightedIndex: -1,
        programId: '',
        hiddenInput: null,

        init() {
            // Get the hidden input by its unique ID
            this.hiddenInput = document.getElementById(uid + '_hidden');
        },

        async search() {
            if (this.query.length < 1 && !this.programId) {
                this.results = [];
                this.showDropdown = false;
                return;
            }
            this.loading = true;
            try {
                const url = new URL(`{{ route('admin.students.search') }}`, window.location.origin);
                if (this.query.length >= 1) url.searchParams.set('q', this.query);
                if (this.programId)         url.searchParams.set('program_id', this.programId);
                const r = await fetch(url.href);
                this.results = await r.json();
                this.showDropdown = true;
                this.highlightedIndex = -1;
            } catch (e) {
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        async setProgram(programId) {
            this.programId = programId || '';
            this.clearSelection();
            await this.search();
        },

        selectStudent(student) {
            this.selectedId      = student.id;
            this.selectedName    = student.full_name;
            this.selectedStudentId = student.student_id;
            this.query           = '';
            this.results         = [];
            this.showDropdown    = false;
            this.highlightedIndex = -1;
            // Set the hidden input value directly — reliable, no Alpine x-model needed
            if (this.hiddenInput) {
                this.hiddenInput.value = student.id;
            }
        },

        clearSelection() {
            this.selectedId      = '';
            this.selectedName    = '';
            this.selectedStudentId = '';
            this.query           = '';
            this.results         = [];
            this.highlightedIndex = -1;
            if (this.hiddenInput) this.hiddenInput.value = '';
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.results[this.highlightedIndex]) {
                this.selectStudent(this.results[this.highlightedIndex]);
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.results.length - 1) this.highlightedIndex++;
        },
        highlightPrevious() {
            if (this.highlightedIndex > 0) this.highlightedIndex--;
        },
    };
}
</script>
