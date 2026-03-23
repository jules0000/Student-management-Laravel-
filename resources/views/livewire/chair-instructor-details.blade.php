<div class="space-y-6">
    @if(! $instructor)
        <div class="bg-zinc-50 border border-zinc-200 rounded-3xl p-6 text-sm text-zinc-600">
            Instructor not found.
        </div>
    @else
        <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-2xl font-bold tracking-tight">
                        {{ $instructor->name ?? '—' }}
                    </h2>
                    <p class="text-sm text-zinc-600">
                        {{ $instructor->email ?? '' }}
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $isAdviser = $advisees->count() > 0;
                    @endphp
                    @if($isAdviser)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                            Adviser: {{ $advisees->count() }} student(s)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">
                            Not an adviser
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject</label>
                    <select
                        wire:model="selectedSubjectId"
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                        <option value="">Select subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}{{ $subject->code ? ' (' . $subject->code . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Term</label>
                    <select
                        wire:model="selectedTermId"
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                        <option value="">Select term</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}{{ $term->school_year ? ' (' . $term->school_year . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight">Students & Grades</h3>
                        <p class="text-sm text-zinc-600">Students enrolled in this subject/term, and their grade records.</p>
                    </div>
                </div>

                @if($selectedSubjectId && $selectedTermId && $enrollments->count() === 0)
                    <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4 text-sm text-zinc-700">
                        No students enrolled for this subject/term (within your program scope).
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[820px]">
                            <thead>
                                <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Enrollment</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Grade</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Grade Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach($enrollments as $enrollment)
                                    @php
                                        $student = $enrollment->student;
                                        $grade = $gradesByStudentId[(int) $student->id] ?? null;
                                    @endphp
                                    <tr class="hover:bg-zinc-50 transition-colors">
                                        <td class="px-6 py-3">
                                            <div class="font-semibold text-zinc-900">
                                                {{ $student->first_name }} {{ $student->last_name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">
                                                {{ $student->section }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="text-sm text-zinc-600">{{ $enrollment->status ?? '—' }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-zinc-700">
                                            {{ isset($grade->grade) && $grade->grade !== null ? number_format((float) $grade->grade, 2) : '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            @php
                                        $status = $grade?->status ?? null;
                                            @endphp
                                            @if($status === 'approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                                    approved
                                                </span>
                                            @elseif($status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">
                                                    pending
                                                </span>
                                            @else
                                                <span class="text-sm text-zinc-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight">Advising Class</h3>
                        <p class="text-sm text-zinc-600">Students assigned to this instructor as adviser.</p>
                    </div>
                </div>

                {{-- Adviser assignment controls (Program Chair) --}}
                @if(! empty($candidateStudents))
                    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                        <div class="p-6 space-y-3">
                            <div class="space-y-1">
                                <h4 class="text-sm font-semibold">Set Advisers (This Instructor)</h4>
                                <p class="text-sm text-zinc-600">
                                    Select students in your program(s) to be advised by this instructor.
                                </p>
                            </div>

                            @if($candidateStudents->count() === 0)
                                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4 text-sm text-zinc-700">
                                    No students available in your programs.
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse min-w-[720px]">
                                        <thead>
                                            <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Assign</th>
                                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200">
                                            @foreach($candidateStudents as $student)
                                                <tr class="hover:bg-zinc-50 transition-colors">
                                                    <td class="px-6 py-3 text-right">
                                                        <input
                                                            wire:key="advise-check-{{ $student->id }}"
                                                            type="checkbox"
                                                            value="{{ $student->id }}"
                                                            wire:model="advisingStudentIds"
                                                            class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/20">
                                                    </td>
                                                    <td class="px-6 py-3">
                                                        <div class="font-semibold text-zinc-900">
                                                            {{ $student->first_name }} {{ $student->last_name }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">
                                                            {{ $student->section }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex justify-end gap-3 pt-3 border-t border-zinc-200">
                                    <button
                                        type="button"
                                        wire:click="saveAdvising"
                                        class="px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                                        Save Advisers
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($advisees->count() === 0)
                    <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4 text-sm text-zinc-700">
                        This instructor is not currently assigned as adviser for students in your programs.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[760px]">
                            <thead>
                                <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Enrollment (selected subject/term)</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach($advisees as $student)
                                    @php
                                        $enrollStatus = $adviseesEnrollStatusByStudentId[(int) $student->id] ?? null;
                                        $grade = $adviseesGradesByStudentId[(int) $student->id] ?? null;
                                    @endphp
                                    <tr class="hover:bg-zinc-50 transition-colors">
                                        <td class="px-6 py-3">
                                            <div class="font-semibold text-zinc-900">
                                                {{ $student->first_name }} {{ $student->last_name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">
                                                {{ $student->section }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-zinc-600">
                                            {{ $enrollStatus ?? 'Not enrolled' }}
                                        </td>
                                        <td class="px-6 py-3 text-right text-sm text-zinc-700">
                                            {{ isset($grade->grade) && $grade->grade !== null ? number_format((float) $grade->grade, 2) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

