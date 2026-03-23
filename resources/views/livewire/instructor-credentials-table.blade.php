<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="text-sm text-zinc-600">
            Create/update instructor login credentials and the subjects they teach.
        </div>
        <button
            wire:click="create"
            class="px-6 py-3 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10 w-full sm:w-auto">
            Add Instructor
        </button>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]">
                <thead>
                    <tr class="bg-zinc-100/50 border-b border-zinc-200">
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Subjects</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($instructors as $instructor)
                        <tr class="group hover:bg-zinc-50 transition-colors">
                            <td class="px-6 py-3 font-bold text-zinc-900">{{ $instructor->name }}</td>
                            <td class="px-6 py-3 text-sm text-zinc-600 truncate max-w-[220px]">{{ $instructor->email }}</td>
                            <td class="px-6 py-3 text-sm text-zinc-600">
                                {{ $instructor->subjects()->count() }} subject(s)
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="edit({{ $instructor->id }})"
                                        class="p-2 text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition-all">
                                        Edit
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $instructor->id }})"
                                        class="p-2 text-red-500/70 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-zinc-400 text-sm">No instructors found.</td>
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
                    <h2 class="text-lg font-semibold">{{ $editing ? 'Edit Instructor' : 'Add New Instructor' }}</h2>
                    <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-zinc-700">✕</button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Name</label>
                        <input type="text" wire:model="form.name"
                               class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                        @error('form.name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Email</label>
                        <input type="email" wire:model="form.email"
                               class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                        @error('form.email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Password</label>
                            <input type="password" wire:model="form.password"
                                   class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                            @error('form.password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Confirm Password</label>
                            <input type="password" wire:model="form.password_confirmation"
                                   class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                            @error('form.password_confirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">
                            Subjects (comma separated)
                        </label>
                        <textarea wire:model="form.subjects_csv"
                                  class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm min-h-[90px]"></textarea>
                        @error('form.subjects_csv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                class="px-6 py-2.5 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-8 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                            Save Instructor
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
                <h3 class="text-lg font-semibold mb-2">Delete Instructor?</h3>
                <p class="text-sm text-zinc-600 mb-4">This can’t be undone.</p>
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

