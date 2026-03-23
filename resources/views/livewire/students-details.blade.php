<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Student Details</h2>
            <p class="text-zinc-600 mt-1">
                View student info, adviser, and term-wise grades.
            </p>
        </div>

        @if(auth('admin')->check())
            <a href="{{ route('students.index') }}"
               class="px-5 py-3 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10 w-full sm:w-auto text-center">
                Manage Students
            </a>
        @endif
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3">
            <div class="lg:border-r border-zinc-200">
                <div class="p-4 border-b border-zinc-200">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Students</p>
                </div>

                <div class="max-h-[420px] overflow-y-auto">
                    @foreach($students as $student)
                        <button
                            type="button"
                            wire:click="$set('selectedStudentId', {{ $student->id }})"
                            class="w-full text-left px-4 py-3 border-b border-zinc-100 hover:bg-zinc-50 transition-colors {{ $selectedStudent && $selectedStudent->id === $student->id ? 'bg-zinc-50' : '' }}">
                            <div class="font-semibold text-zinc-900">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </div>
                            <div class="text-xs text-zinc-500 font-bold uppercase tracking-widest mt-0.5">
                                {{ $student->section }}
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-2">
                @if($selectedStudent)
                    <div class="p-5 sm:p-6 border-b border-zinc-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-zinc-100 border border-zinc-200 overflow-hidden flex items-center justify-center">
                                    @if($selectedStudent->photo_url)
                                        <img src="{{ $selectedStudent->photo_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-sm font-bold text-zinc-500">
                                            {{ strtoupper(substr($selectedStudent->first_name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-lg font-bold text-zinc-900">
                                        {{ $selectedStudent->first_name }} {{ $selectedStudent->last_name }}
                                    </p>
                                    <p class="text-sm text-zinc-600">
                                        Section: <span class="font-semibold">{{ $selectedStudent->section }}</span>
                                    </p>
                                    <p class="text-sm text-zinc-600">
                                        Adviser:
                                        <span class="font-semibold">
                                            {{ optional($selectedStudent->advisers->first())->name ?? '—' }}
                                        </span>
                                    </p>
                                    <p class="text-sm text-zinc-600">
                                        Email: <span class="font-semibold">{{ $selectedStudent->email ?? '—' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="rounded-2xl bg-zinc-50 border border-zinc-200 p-4 space-y-1">
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Birthdate</p>
                                <p class="text-sm font-semibold text-zinc-800">
                                    {{ optional($selectedStudent->birthdate)->format('M d, Y') ?? '—' }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-zinc-50 border border-zinc-200 p-4 space-y-1 md:col-span-2">
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Address</p>
                                <p class="text-sm font-semibold text-zinc-800 break-words">
                                    {{ $selectedStudent->address ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Term</label>
                                <select
                                    wire:model="selectedTermId"
                                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                    <option value="">All terms</option>
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}{{ $term->school_year ? " ($term->school_year)" : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject</label>
                                <select
                                    wire:model="selectedSubjectId"
                                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                    <option value="">All subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}{{ $subject->code ? " ($subject->code)" : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @forelse($gradesByTerm as $termId => $termGrades)
                            @php
                                $term = $termGrades->first()->term;
                            @endphp
                            <div class="bg-zinc-50 border border-zinc-200 rounded-3xl overflow-hidden">
                                <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between gap-3 flex-wrap">
                                    <div>
                                        <p class="text-sm font-bold text-zinc-900">
                                            {{ $term?->name ?? ('Term #' . $termId) }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ $term?->school_year ?? '' }}
                                        </p>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-widest bg-white border border-zinc-200 text-zinc-600 px-3 py-1 rounded-full">
                                        Grades
                                    </span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse min-w-[640px]">
                                        <thead>
                                        <tr class="bg-white/70 border-b border-zinc-200">
                                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Subject</th>
                                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Grade</th>
                                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Remarks</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200">
                                        @foreach($termGrades as $grade)
                                            <tr class="hover:bg-white transition-colors">
                                                <td class="px-6 py-3 font-semibold text-zinc-900">{{ $grade->subject?->name ?? '—' }}</td>
                                                <td class="px-6 py-3 text-zinc-700">
                                                    {{ $grade->grade !== null ? number_format((float)$grade->grade, 2) : '—' }}
                                                </td>
                                                <td class="px-6 py-3 text-zinc-600">{{ $grade->remarks ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="bg-zinc-50 border border-zinc-200 rounded-3xl p-6">
                                <p class="text-sm text-zinc-700">
                                    No grades match the selected filters.
                                </p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="p-5 sm:p-6">
                        <p class="text-sm text-zinc-600">Select a student to view details.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

