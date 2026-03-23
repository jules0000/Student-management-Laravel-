<div class="space-y-6">
        <div class="flex flex-wrap items-center gap-3">
        <button wire:click="export"
                class="flex items-center gap-2 px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-100 transition-all shadow-sm">
            Export CSV
        </button>

        <label class="flex items-center gap-2 px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-100 transition-all cursor-pointer shadow-sm">
            Import CSV
            <input type="file"
                   class="hidden"
                   wire:model="importFile"
                   wire:change="import"
                   accept=".csv,text/csv">
        </label>

        @error('importFile')
            <p class="text-xs text-red-600 ml-2">{{ $message }}</p>
        @enderror

        @if(!empty($importStatus))
            <p class="text-xs {{ str_contains($importStatus, 'failed') ? 'text-red-600' : 'text-emerald-600' }} ml-2">
                {{ $importStatus }}
            </p>
        @endif

        <div class="flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-1.5 shadow-sm">
            <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Section:</span>
            <select wire:model.live="sectionFilter"
                    class="bg-transparent focus:outline-none text-sm font-bold text-zinc-900 cursor-pointer py-1">
                <option value="All">All Students ({{ \App\Models\Student::count() }})</option>
                @foreach($this->sections as $section)
                    <option value="{{ $section }}">{{ $section }} ({{ $this->sectionCounts[$section] ?? 0 }})</option>
                @endforeach
            </select>
        </div>

        <div class="relative flex-1 min-w-[180px]">
            <input type="text"
                   wire:model.debounce.300ms.live="search"
                   placeholder="Search students..."
                   class="pl-3 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm w-full sm:w-64 text-zinc-900 shadow-sm">
        </div>

        @if($search || $sectionFilter !== 'All')
            <button wire:click="resetFilters"
                    class="text-xs font-bold text-zinc-400 hover:text-zinc-900 uppercase tracking-widest transition-colors">
                Reset
            </button>
        @endif

        <div class="w-full sm:w-auto sm:ml-auto">
            <button wire:click="create"
                    class="flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                Add Student
            </button>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100/50 border-b border-zinc-200">
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Birthdate</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Address</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                @forelse($students as $student)
                    <tr class="group hover:bg-zinc-50 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-zinc-100 border border-zinc-200 overflow-hidden">
                                    @if($student->photo_url)
                                        <img src="{{ $student->photo_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                            <span class="text-xs font-semibold">
                                                {{ strtoupper(substr($student->first_name,0,1)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-bold text-sm leading-tight text-zinc-900">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </p>
                                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mt-0.5">
                                        {{ $student->middle_name ?: '—' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">
                                {{ $student->section }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-600">
                            {{ optional($student->birthdate)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-600 max-w-xs truncate">
                            {{ $student->address }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="edit({{ $student->id }})"
                                        class="p-2 text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition-all">
                                    Edit
                                </button>
                                <button wire:click="confirmDelete({{ $student->id }})"
                                        class="p-2 text-red-500/70 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-zinc-400 text-sm">
                            No students found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-900/40">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-zinc-200">
                    <h2 class="text-lg font-semibold">
                        {{ $editing ? 'Edit Student' : 'Add New Student' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-zinc-700">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <div class="flex flex-col md:flex-row gap-8">
                        <div class="flex flex-col items-center space-y-3">
                            <label class="w-32 h-32 rounded-2xl bg-zinc-100 border-2 border-dashed border-zinc-200 flex flex-col items-center justify-center cursor-pointer hover:bg-zinc-200 transition-all overflow-hidden">
                                @if($editing && $editing->photo_url)
                                    <img src="{{ $editing->photo_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs font-semibold text-zinc-500">Upload Photo</span>
                                @endif
                                <input type="file" class="hidden" wire:model="form.photo" accept="image/*">
                            </label>
                            <p class="text-[10px] text-zinc-400 font-medium uppercase tracking-widest">Student Photo</p>
                        </div>

                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">First Name</label>
                                <input type="text" wire:model="form.first_name"
                                       class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                @error('form.first_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Middle Name</label>
                                <input type="text" wire:model="form.middle_name"
                                       class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Last Name</label>
                                <input type="text" wire:model="form.last_name"
                                       class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                @error('form.last_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Birthdate</label>
                                <input type="date" wire:model="form.birthdate"
                                       class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                @error('form.birthdate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Section</label>
                                <input type="text" wire:model="form.section"
                                       class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                @error('form.section') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Address</label>
                                <textarea wire:model="form.address"
                                          class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm min-h-[80px]"></textarea>
                                @error('form.address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                class="px-6 py-2.5 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-8 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                            Save Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete confirmation --}}
    @if($confirmingDeleteId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-900/40">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6">
                <h3 class="text-lg font-semibold mb-2">Delete Student?</h3>
                <p class="text-sm text-zinc-600 mb-4">
                    This action cannot be undone. The student record will be permanently removed.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('confirmingDeleteId', null)"
                            class="px-4 py-2.5 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="delete"
                            class="px-4 py-2.5 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-all active:scale-[0.98] shadow-lg shadow-red-600/10">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

