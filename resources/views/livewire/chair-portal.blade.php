<div class="space-y-8">
    <div class="space-y-3">
        <h2 class="text-2xl font-bold tracking-tight">Program Chair</h2>
        <p class="text-sm text-zinc-600">
            Manage curriculum for your program, approve student enrollments, and approve academic grades.
        </p>
    </div>

    {{-- Curriculum --}}
    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
        <div class="space-y-1">
            <h3 class="text-lg font-semibold tracking-tight">Curriculum (Courses/Subjects)</h3>
            <p class="text-sm text-zinc-600">Create subjects and assign instructors for your program.</p>
        </div>

        <form wire:submit.prevent="createSubject" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject Name</label>
                <input type="text" wire:model="form.subject_name"
                       class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                @error('form.subject_name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject Code</label>
                <input type="text" wire:model="form.subject_code"
                       class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                @error('form.subject_code')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Assign Instructor</label>
                <select wire:model="form.instructor_id"
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                    <option value="">Select instructor</option>
                    @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}">{{ $instructor->name }} ({{ $instructor->email }})</option>
                    @endforeach
                </select>
                @error('form.instructor_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            @if($supportsSubjectIsActive)
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject Active</label>
                    <label class="flex items-center gap-2 px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl cursor-pointer hover:bg-zinc-100 transition-all">
                        <input type="checkbox" wire:model="form.is_active" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/20">
                        <span class="text-sm font-semibold text-zinc-700">{{ $form['is_active'] ? 'Active' : 'Inactive' }}</span>
                    </label>
                    @error('form.is_active')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif

            <div class="md:col-span-3 flex justify-end">
                <button type="submit"
                        class="px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10 w-full md:w-auto">
                    Create Subject
                </button>
            </div>
        </form>

        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="space-y-0.5">
                    <h3 class="text-base font-semibold tracking-tight">Subjects</h3>
                    <p class="text-sm text-zinc-600">Activate/deactivate, assign instructors, and manage subjects.</p>
                </div>

                <button
                    type="button"
                    wire:click="exportSubjects"
                    class="px-4 py-2 bg-zinc-50 border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-100 transition-all active:scale-[0.98] shadow-sm">
                    Export CSV
                </button>
            </div>

            <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                    <tr class="bg-zinc-100/50 border-b border-zinc-200">
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Subject</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Assigned Instructor</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Assign / Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                    @forelse($subjects as $subject)
                        @php
                            $assigned = $subject->instructor?->name ?? '—';
                            $selectedInstructorId = $subjectInstructorSelections[$subject->id] ?? $subject->instructor_id;
                            $selectedInstructorIdInt = $selectedInstructorId ? (int) $selectedInstructorId : 0;
                            $canAssign = $selectedInstructorIdInt > 0;
                        @endphp
                        <tr class="group hover:bg-zinc-50 transition-colors">
                            <td class="px-6 py-3">
                                <div class="font-semibold text-zinc-900">{{ $subject->name }}</div>
                                <div class="text-xs text-zinc-500 font-bold uppercase tracking-widest">{{ $subject->code ?? '' }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm text-zinc-600">
                                {{ $assigned }}
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    @if($supportsSubjectIsActive)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            {{ $subject->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-700' }}">
                                            {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                        </span>

                                        <button
                                            type="button"
                                            wire:click="toggleSubjectActive({{ $subject->id }})"
                                            class="px-2 py-1 text-[11px] rounded-lg font-semibold border {{ $subject->is_active ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' : 'border-zinc-200 text-zinc-700 hover:bg-zinc-50' }}">
                                            {{ $subject->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    @else
                                        <span class="text-sm text-zinc-400">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <select
                                        class="px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm"
                                        wire:model="subjectInstructorSelections.{{ $subject->id }}">
                                        @foreach($instructors as $instructor)
                                            <option value="{{ $instructor->id }}" @selected(($subjectInstructorSelections[$subject->id] ?? $subject->instructor_id) == $instructor->id)>
                                                {{ $instructor->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button
                                        type="button"
                                        wire:click="assignInstructorToSubject({{ $subject->id }}, subjectInstructorSelections['{{ $subject->id }}'])"
                                        class="{{ $canAssign ? 'px-4 py-2 bg-zinc-900 text-white' : 'px-4 py-2 bg-zinc-100 text-zinc-400' }} rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                                        Assign
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="confirmDeleteSubject({{ $subject->id }})"
                                        class="px-3 py-2 bg-red-50 border border-red-200 text-red-700 rounded-xl font-semibold hover:bg-red-100 transition-all active:scale-[0.98]">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-zinc-400 text-sm">No subjects in your program.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        @if($confirmingDeleteSubjectId)
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-900/40">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 overflow-hidden">
                    <h3 class="text-lg font-semibold mb-2">Delete this subject?</h3>
                    <p class="text-sm text-zinc-600 mb-4">
                        This will permanently remove the subject and its enrollments/grades.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="$set('confirmingDeleteSubjectId', null)"
                            class="px-4 py-2.5 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="deleteSubject"
                            class="px-4 py-2.5 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-all active:scale-[0.98] shadow-lg shadow-red-600/10">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Enrollment approvals --}}
    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-4">
        <div class="space-y-1">
            <h3 class="text-lg font-semibold tracking-tight">Approve Student Enrollments</h3>
            <p class="text-sm text-zinc-600">Students can only enroll after adviser requests. Approve to allow grading.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]">
                <thead>
                <tr class="bg-zinc-100/50 border-b border-zinc-200">
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Subject</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Term</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                @forelse($pendingEnrollments as $enrollment)
                    <tr class="hover:bg-zinc-50 transition-colors">
                        <td class="px-6 py-3">
                            <div class="font-semibold text-zinc-900">
                                {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}
                            </div>
                            <div class="text-xs text-zinc-500 font-bold uppercase tracking-widest">{{ $enrollment->student->section }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-600">
                            {{ $enrollment->subject->name ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-600">
                            {{ $enrollment->term->name ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <button
                                type="button"
                                wire:click="approveEnrollment({{ $enrollment->id }})"
                                class="px-4 py-2 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                                Approve
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-zinc-400 text-sm">No pending enrollments.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Grade approvals --}}
    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-4">
        <div class="space-y-1">
            <h3 class="text-lg font-semibold tracking-tight">Approve Grades</h3>
            <p class="text-sm text-zinc-600">Instructors enter grades; approve to publish to students.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]">
                <thead>
                <tr class="bg-zinc-100/50 border-b border-zinc-200">
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Subject</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Term</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Grade</th>
                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                @forelse($pendingGrades as $grade)
                    <tr class="hover:bg-zinc-50 transition-colors">
                        <td class="px-6 py-3">
                            <div class="font-semibold text-zinc-900">
                                {{ $grade->student->first_name }} {{ $grade->student->last_name }}
                            </div>
                            <div class="text-xs text-zinc-500 font-bold uppercase tracking-widest">{{ $grade->student->section }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-600">
                            {{ $grade->subject->name ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-600">
                            {{ $grade->term->name ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-700">
                            {{ $grade->grade !== null ? number_format((float)$grade->grade, 2) : '—' }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <button
                                type="button"
                                wire:click="approveGrade({{ $grade->id }})"
                                class="px-4 py-2 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                                Approve
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-zinc-400 text-sm">No pending grades.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

