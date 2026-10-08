@props(['name' => 'student_id', 'placeholder' => 'Search by name, ID, or reg number...', 'required' => false])

<div x-data="studentSearch()" @program-change.window="setProgram($event.detail)" class="relative">
    <input 
        type="text" 
        x-model="query"
        @input.debounce.300ms="search()"
        @focus="showDropdown = true"
        @keydown.arrow-down.prevent="highlightNext()"
        @keydown.arrow-up.prevent="highlightPrevious()"
        @keydown.enter.prevent="selectHighlighted()"
        @keydown.escape="showDropdown = false"
        placeholder="{{ $placeholder }}"
        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
        autocomplete="off"
    />
    
    <input type="hidden" name="{{ $name }}" x-model="selectedId" {{ $required ? 'required' : '' }} />
    
    <!-- Dropdown -->
    <div 
        x-show="showDropdown && (results.length > 0 || (query.length >= 2 && !loading))"
        @click.away="showDropdown = false"
        x-cloak
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-64 overflow-y-auto"
    >
        <template x-if="loading">
            <div class="px-4 py-3 text-sm text-gray-400 text-center">
                <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
            </div>
        </template>
        
        <template x-if="!loading && results.length === 0 && query.length >= 2">
            <div class="px-4 py-3 text-sm text-gray-400 text-center">
                <i class="fas fa-search mr-2"></i> No students found
            </div>
        </template>
        
        <template x-for="(student, index) in results" :key="student.id">
            <div 
                @click="selectStudent(student)"
                :class="highlightedIndex === index ? 'bg-primary-50' : 'hover:bg-gray-50'"
                class="px-4 py-3 cursor-pointer border-b border-gray-50 last:border-b-0 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 border border-amber-100">
                        <template x-if="student.photo">
                            <img :src="student.photo_url" class="w-full h-full object-cover" :alt="student.full_name">
                        </template>
                        <template x-if="!student.photo">
                            <div class="w-full h-full bg-amber-100 flex items-center justify-center font-bold text-xs text-amber-700"
                                 x-text="student.full_name.charAt(0).toUpperCase()"></div>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate" x-text="student.full_name"></p>
                        <p class="text-xs text-gray-400">
                            <span class="font-mono" x-text="student.student_id"></span>
                            <span class="mx-1">·</span>
                            <span x-text="student.program"></span>
                        </p>
                    </div>
                    <i class="fas fa-check text-primary-500 text-xs ml-2" x-show="selectedId === student.id"></i>
                </div>
            </div>
        </template>
    </div>
    
    <!-- Selected student display -->
    <div x-show="selectedName" x-cloak class="mt-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-check-circle text-green-600 text-sm"></i>
            <span class="text-sm font-semibold text-green-800" x-text="selectedName"></span>
        </div>
        <button type="button" @click="clearSelection()" class="text-red-500 hover:text-red-700 text-xs">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<script>
function studentSearch() {
    return {
        query: '',
        results: [],
        selectedId: '',
        selectedName: '',
        showDropdown: false,
        loading: false,
        highlightedIndex: -1,
        programId: '',
        
        async search() {
            if (!this.programId && this.query.length < 2) {
                this.results = [];
                this.showDropdown = false;
                return;
            }

            this.loading = true;
            try {
                const url = new URL(`{{ route('admin.students.search') }}`, window.location.origin);
                if (this.query.length >= 2) {
                    url.searchParams.set('q', this.query);
                }
                if (this.programId) {
                    url.searchParams.set('program_id', this.programId);
                }

                const response = await fetch(url.href);
                this.results = await response.json();
                this.showDropdown = this.results.length > 0;
                this.highlightedIndex = -1;
            } catch (error) {
                console.error('Search error:', error);
                this.results = [];
                this.showDropdown = false;
            } finally {
                this.loading = false;
            }
        },

        async setProgram(programId) {
            this.programId = programId || '';
            this.clearSelection();
            if (this.programId) {
                await this.search();
            } else {
                this.results = [];
                this.showDropdown = false;
            }
        },

        selectStudent(student) {
            this.selectedId = student.id;
            this.selectedName = student.full_name;
            this.query = '';
            this.results = [];
            this.showDropdown = false;
            this.highlightedIndex = -1;
        },
        
        clearSelection() {
            this.selectedId = '';
            this.selectedName = '';
            this.query = '';
            this.results = [];
            this.highlightedIndex = -1;
        },
        
        highlightNext() {
            if (this.highlightedIndex < this.results.length - 1) {
                this.highlightedIndex++;
            }
        },
        
        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        
        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.results.length) {
                this.selectStudent(this.results[this.highlightedIndex]);
            }
        }
    }
}
</script>
