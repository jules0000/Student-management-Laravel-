<div class="space-y-8">
    <div class="space-y-3">
        <h2 class="text-2xl font-bold tracking-tight">Grades & Advising</h2>
        <p class="text-sm text-zinc-600">
            Add grades for the subjects you teach. If you are also an adviser, you can enroll and grade students in your program.
        </p>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject</label>
                <select
                    wire:model="selectedSubjectId"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                    <option value="">Select subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}{{ $subject->code ? " ($subject->code)" : '' }}</option>
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
                        <option value="{{ $term->id }}">{{ $term->name }}{{ $term->school_year ? " ($term->school_year)" : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($subjects->isEmpty())
            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                <p class="text-sm text-zinc-700">
                    No subjects found for your instructor account. Ask the admin to add subjects.
                </p>
            </div>
        @elseif(! $selectedSubjectId || ! $selectedTermId)
            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                <p class="text-sm text-zinc-700">
                    Select a subject and a term to start enrolling students.
                </p>
            </div>
        @else
            {{-- Enrollment requests (Adviser) --}}
            <div class="space-y-4">
                <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                    <div class="p-6 space-y-3">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold">Course Roster</h3>
                            <p class="text-sm text-zinc-600">
                                All students enrolled in this subject/term (pending and approved).
                            </p>
                        </div>

                        @if($courseEnrollments->isEmpty())
                            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                                <p class="text-sm text-zinc-700">
                                    No enrollments yet for this subject/term.
                                </p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[720px]">
                                    <thead>
                                    <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Enrollment Status</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200">
                                    @foreach($courseEnrollments as $enrollment)
                                        @php
                                            $student = $enrollment->student;
                                        @endphp
                                        @continue(!$student)
                                        <tr class="hover:bg-zinc-50 transition-colors">
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
                                            <td class="px-6 py-3">
                                                <span class="text-sm text-zinc-600">
                                                    {{ $enrollment->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        @if($isAdviser)
                            <h3 class="text-sm font-semibold">Enroll Students</h3>
                            <p class="text-sm text-zinc-600">
                                Select students in your subject's program. Enrollments are approved instantly for adviser-instructors.
                            </p>
                        @else
                            <h3 class="text-sm font-semibold">Request Enrollment</h3>
                            <p class="text-sm text-zinc-600">Choose your advisees to enroll in this subject/term. Program chair will approve.</p>
                        @endif
                    </div>
                    <button
                        type="button"
                        wire:click="requestEnrollment"
                        class="px-6 py-2.5 bg-zinc-50 border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-100 transition-all active:scale-[0.98] shadow-sm">
                        {{ $isAdviser ? 'Enroll' : 'Request' }}
                    </button>
                </div>

                @if($enrollmentStudents->isEmpty())
                    <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                        <p class="text-sm text-zinc-700">
                            @if($isAdviser)
                                No students found for the selected subject/program.
                            @else
                                You don’t have any advisees yet. Ask the admin to assign you as an adviser for students.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[720px]">
                                <thead>
                                <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Select</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Status</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                @foreach($enrollmentStudents as $student)
                                    @php
                                        $status = $enrollmentStatusByStudentId[(int)$student->id] ?? null;
                                    @endphp
                                    <tr class="hover:bg-zinc-50 transition-colors">
                                        <td class="px-6 py-3">
                                            <input
                                                type="checkbox"
                                                value="{{ $student->id }}"
                                                wire:model="enrollmentStudentIds"
                                                class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/20">
                                        </td>
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
                                        <td class="px-6 py-3">
                                            <span class="text-sm text-zinc-600">
                                                {{ $status ?? 'Not requested' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Grade entry (Instructor) --}}
            <div class="pt-4 space-y-4 border-t border-zinc-200">
                <div>
                    <h3 class="text-sm font-semibold">Enter Grades</h3>
                    <p class="text-sm text-zinc-600">
                        @if($isAdviser)
                            Only students approved for this subject/term. Grades are published immediately.
                        @else
                            Only students approved by the program chair for this subject/term.
                        @endif
                    </p>
                    <p class="text-xs text-zinc-500 mt-1">
                        Final Grade = 30% Prelim + 30% Midterm + 40% Final
                    </p>
                </div>

                @if($gradeStudents->isEmpty())
                    <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                        <p class="text-sm text-zinc-700">
                            @if($isAdviser)
                                No approved enrollments yet for this subject/term.
                            @else
                                No approved enrollments yet. Request enrollment and wait for program chair approval.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                            <div class="p-6 space-y-3">
                                <div class="space-y-1">
                                    <h3 class="text-sm font-semibold">Approved Students</h3>
                                    <p class="text-sm text-zinc-600">
                                        Tap <span class="font-semibold">Grade</span> to enter Prelim, Midterm, and Final grades for a student.
                                    </p>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse min-w-[720px]">
                                        <thead>
                                        <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Final Grade</th>
                                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200">
                                        @foreach($gradeStudents as $student)
                                            @php
                                                $studentId = (int) $student->id;
                                                $finalToShow = $storedFinalGrades[$studentId] ?? null;
                                            @endphp
                                            <tr class="hover:bg-zinc-50 transition-colors {{ $gradingStudentId === $studentId ? 'bg-zinc-50' : '' }}">
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
                                                <td class="px-6 py-3">
                                                    <span class="text-sm font-semibold text-zinc-900">
                                                        @if($finalToShow !== null)
                                                            {{ number_format((float) $finalToShow, 2) }}
                                                        @else
                                                            —
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="px-6 py-3 text-right">
                                                    <button
                                                        type="button"
                                                        wire:click="startGrading({{ $studentId }})"
                                                        class="px-4 py-2 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                                                        Grade
                                                    </button>
                                                    <a
                                                        href="{{ route('students.details', ['student_id' => $studentId, 'subject_id' => $selectedSubjectId, 'term_id' => $selectedTermId]) }}"
                                                        class="inline-flex items-center px-4 py-2 ml-2 bg-white border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 transition-all active:scale-[0.98]">
                                                        Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if($gradingStudentId)
                            @php
                                $activeId = (int) $gradingStudentId;
                                $activeStudent = $gradeStudents->firstWhere('id', $activeId);

                                $prelim = $prelimGrades[$activeId] ?? null;
                                $midterm = $midtermGrades[$activeId] ?? null;
                                $finalExam = $finalExamGrades[$activeId] ?? null;

                                if ($prelim === '') $prelim = null;
                                if ($midterm === '') $midterm = null;
                                if ($finalExam === '') $finalExam = null;

                                $computedFinal = null;
                                if ($prelim !== null && $midterm !== null && $finalExam !== null) {
                                    $computedFinal = ((float) $prelim * 0.30) + ((float) $midterm * 0.30) + ((float) $finalExam * 0.40);
                                }
                                $finalToShow = $computedFinal ?? ($storedFinalGrades[$activeId] ?? null);
                            @endphp

                            <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                                <div class="p-6 space-y-4">
                                    <div class="flex items-start justify-between gap-4 flex-wrap">
                                        <div class="space-y-1">
                                            <h3 class="text-sm font-semibold">Grade Student</h3>
                                            <p class="text-sm text-zinc-600">
                                                {{ $activeStudent?->first_name }} {{ $activeStudent?->last_name }} · {{ $activeStudent?->section }}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            wire:click="$set('gradingStudentId', null)"
                                            class="px-4 py-2 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                                            Close
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Prelim</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                inputmode="decimal"
                                                wire:model.defer="prelimGrades.{{ $activeId }}"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                        </div>

                                        <div class="space-y-1">
                                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Midterm</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                inputmode="decimal"
                                                wire:model.defer="midtermGrades.{{ $activeId }}"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                        </div>

                                        <div class="space-y-1">
                                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Final</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                inputmode="decimal"
                                                wire:model.defer="finalExamGrades.{{ $activeId }}"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 flex-wrap pt-3 border-t border-zinc-200">
                                        <div class="space-y-1">
                                            <p class="text-xs text-zinc-500">Final Grade Preview</p>
                                            <p class="text-lg font-semibold text-zinc-900">
                                                @if($finalToShow !== null)
                                                    {{ number_format((float) $finalToShow, 2) }}
                                                @else
                                                    —
                                                @endif
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            wire:click="save({{ $activeId }})"
                                            class="px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                                            Save Grades
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                                <p class="text-sm text-zinc-700">
                                    Select a student by tapping <span class="font-semibold">Grade</span> above to enter their grades.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Attendance/Classroom setup (geolocation) --}}
    <div class="pt-6 space-y-4 border-t border-zinc-200">
        <div class="space-y-1">
            <h3 class="text-sm font-semibold">Attendance: Classroom & Session Setup</h3>
            <p class="text-sm text-zinc-600">
                Capture your classroom geolocation, then schedule a class session for the selected subject/term.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Classroom</label>
                <select
                    wire:model="selectedClassroomId"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                >
                    <option value="">New classroom</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}">
                            {{ $classroom->room_label }} ({{ $classroom->radius_m }}m)
                        </option>
                    @endforeach
                </select>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Room Label</label>
                    <input
                        type="text"
                        wire:model.defer="classroomRoomLabel"
                        placeholder="e.g., Room 101"
                        @if($selectedClassroomId) readonly @endif
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                    >
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <button
                        type="button"
                        onclick="instructorCaptureClassroomLocation()"
                        class="px-4 py-2 bg-zinc-50 border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-100 transition-all active:scale-[0.98] shadow-sm"
                    >
                        Capture Classroom Location
                    </button>
                    <span class="text-xs text-zinc-500" id="instructorClassroomGeoStatus"></span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Latitude</label>
                        <input
                            id="instructor-classroom-lat"
                            type="text"
                            wire:model="classroomLatitude"
                            readonly
                            class="w-full px-3 py-2.5 bg-zinc-100 border border-zinc-200 rounded-xl text-sm text-zinc-700 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Longitude</label>
                        <input
                            id="instructor-classroom-lng"
                            type="text"
                            wire:model="classroomLongitude"
                            readonly
                            class="w-full px-3 py-2.5 bg-zinc-100 border border-zinc-200 rounded-xl text-sm text-zinc-700 focus:outline-none"
                        >
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Radius (meters)</label>
                    <input
                        type="number"
                        wire:model.defer="classroomRadiusM"
                        min="5"
                        max="5000"
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                    >
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Session Start</label>
                <input
                    type="datetime-local"
                    wire:model.defer="sessionStartAt"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                >
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Duration (minutes)</label>
                <input
                    type="number"
                    wire:model.defer="sessionDurationMinutes"
                    min="1"
                    max="600"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                >
            </div>

            <div class="space-y-2 md:col-span-2">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Repeat Weekdays</label>
                    <span class="text-xs text-zinc-500">
                        If none selected, it uses the weekday of “Session Start”.
                    </span>
                </div>

                <div class="flex flex-wrap gap-3">
                    @php
                        $weekdayOptions = [
                            ['iso' => 1, 'label' => 'Mon'],
                            ['iso' => 2, 'label' => 'Tue'],
                            ['iso' => 3, 'label' => 'Wed'],
                            ['iso' => 4, 'label' => 'Thu'],
                            ['iso' => 5, 'label' => 'Fri'],
                            ['iso' => 6, 'label' => 'Sat'],
                            ['iso' => 7, 'label' => 'Sun'],
                        ];
                    @endphp
                    @foreach($weekdayOptions as $opt)
                        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 font-semibold">
                            <input
                                type="checkbox"
                                value="{{ $opt['iso'] }}"
                                wire:model.defer="sessionRepeatWeekdays"
                                class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/20"
                            >
                            {{ $opt['label'] }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="space-y-1 md:col-span-2">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Repeat for (weeks)</label>
                <input
                    type="number"
                    wire:model.defer="sessionRepeatWeeks"
                    min="1"
                    max="52"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                >
            </div>
        </div>

        @if($attendanceSetupMessage)
            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4 text-sm text-zinc-700">
                {{ $attendanceSetupMessage }}
            </div>
        @endif

        <div class="flex items-center justify-end gap-3 flex-wrap">
            <button
                type="button"
                wire:click="createClassSession"
                {{ (! $selectedSubjectId || ! $selectedTermId) ? 'disabled' : '' }}
                class="px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Create Class Session
            </button>
        </div>
    </div>

    <script>
        (function () {
            if (window.__instructorClassroomGeoCaptureInstalled) return;
            window.__instructorClassroomGeoCaptureInstalled = true;

            window.instructorCaptureClassroomLocation = function () {
                const statusEl = document.getElementById('instructorClassroomGeoStatus');
                const latInput = document.getElementById('instructor-classroom-lat');
                const lngInput = document.getElementById('instructor-classroom-lng');

                if (!navigator.geolocation) {
                    if (statusEl) statusEl.textContent = 'Geolocation is not supported by this browser.';
                    return;
                }

                if (statusEl) statusEl.textContent = 'Capturing location...';

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;

                        if (latInput) {
                            latInput.value = lat;
                            latInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        if (lngInput) {
                            lngInput.value = lng;
                            lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        if (statusEl) statusEl.textContent = 'Location captured.';
                    },
                    function (err) {
                        if (statusEl) statusEl.textContent = err && err.message ? err.message : 'Unable to capture location.';
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            };
        })();
    </script>
</div>

