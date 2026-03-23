<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
        <div>
            <p class="text-sm text-zinc-600">
                Update student login credentials and adviser assignment.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]">
                <thead>
                    <tr class="bg-zinc-100/50 border-b border-zinc-200">
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Adviser</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($students as $student)
                        <tr class="group hover:bg-zinc-50 transition-colors">
                            <td class="px-6 py-3">
                                <div class="font-bold text-sm text-zinc-900">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">
                                    {{ $student->section }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-zinc-600">
                                {{ $student->email ?? 'Not set' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-zinc-600">
                                {{ optional($student->advisers->first())->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        wire:click="edit({{ $student->id }})"
                                        class="p-2 text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition-all">
                                        Set / Update
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
                        {{ $editing ? 'Set Student Credentials' : 'Set Student Credentials' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-zinc-700">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Email</label>
                            <input
                                type="email"
                                wire:model="form.email"
                                class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                            @error('form.email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if(! auth('program_chair')->check())
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Adviser (Instructor)</label>
                                <select
                                    wire:model="form.instructor_id"
                                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                    <option value="">—</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.instructor_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @else
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Adviser (Instructor)</label>
                                <div class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm text-zinc-600">
                                    Adviser assignment is managed by Admin in this page,
                                    and by Program Chair in Instructor Details.
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Password</label>
                            <input
                                type="password"
                                wire:model="form.password"
                                class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                            @error('form.password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Confirm Password</label>
                            <input
                                type="password"
                                wire:model="form.password_confirmation"
                                class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                            @error('form.password_confirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="px-6 py-2.5 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-8 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                            Save Credentials
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

