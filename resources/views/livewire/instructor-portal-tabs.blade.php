<div class="space-y-6">
    <div class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-zinc-200">
        <div class="p-4 space-y-4">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="space-y-1">
                    <h2 class="text-lg font-bold tracking-tight">Grades & Advising</h2>
                    <p class="text-sm text-zinc-600">Select context, then work via tabs and the student drawer.</p>
                </div>
                <div class="min-w-[220px]">
                    <div class="flex items-center justify-end gap-3">
                        <div wire:loading class="text-xs font-semibold text-zinc-500">Loading...</div>
                        <div wire:loading.remove class="text-xs font-semibold text-zinc-500">
                            @if($selectedSubjectId && $selectedTermId)
                                @if($courseEnrollments->isEmpty())
                                    No data
                                @else
                                    Loaded
                                @endif
                            @else
                                Select subject + term
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject</label>
                    <select
                        wire:model="selectedSubjectId"
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                    >
                        <option value="">Select subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">
                                {{ $subject->name }}{{ $subject->code ? " ($subject->code)" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Term</label>
                    <select
                        wire:model="selectedTermId"
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                    >
                        <option value="">Select term</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">
                                {{ $term->name }}{{ $term->school_year ? " ($term->school_year)" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Tabs</label>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" wire:click="$set('activeTab','roster')" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $activeTab === 'roster' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-zinc-50 text-zinc-700 border-zinc-200 hover:bg-zinc-100' }}">Roster</button>
                        <button type="button" wire:click="$set('activeTab','enrollment')" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $activeTab === 'enrollment' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-zinc-50 text-zinc-700 border-zinc-200 hover:bg-zinc-100' }}">Enrollment</button>
                        <button type="button" wire:click="$set('activeTab','grades')" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $activeTab === 'grades' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-zinc-50 text-zinc-700 border-zinc-200 hover:bg-zinc-100' }}">Grades</button>
                        <button type="button" wire:click="$set('activeTab','attendance')" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $activeTab === 'attendance' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-zinc-50 text-zinc-700 border-zinc-200 hover:bg-zinc-100' }}">Attendance</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB: Roster --}}
    @if($activeTab === 'roster')
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold">Roster</h3>
                    <p class="text-sm text-zinc-600">Click a student to open the drawer.</p>
                </div>
                <div class="relative w-full sm:w-72">
                    <input
                        type="text"
                        wire:model.debounce.300ms="rosterSearch"
                        placeholder="Search by name, section, or ID..."
                        class="pl-3 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm w-full shadow-sm"
                    >
                </div>
            </div>

            @if($subjects->isEmpty())
                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                    <p class="text-sm text-zinc-700">No subjects found for your instructor account.</p>
                </div>
            @elseif(! $selectedSubjectId || ! $selectedTermId)
                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                    <p class="text-sm text-zinc-700">Select a subject and a term to start.</p>
                </div>
            @elseif($courseEnrollments->isEmpty())
                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                    <p class="text-sm text-zinc-700">No students found for this subject and term.</p>
                    <p class="text-xs text-zinc-500 mt-2">Try enrolling students in the Enrollment tab.</p>
                </div>
            @else
                <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[720px]">
                            <thead>
                            <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">ID</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Status</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                            @foreach($courseEnrollments as $enrollment)
                                @php
                                    $student = $enrollment->student;
                                    @endphp
                                @continue(! $student)
                                @php
                                    $sid = (int) $student->id;
                                    $status = $rosterStatusByStudentId[$sid] ?? $enrollment->status ?? null;
                                    $statusKey = $status ? mb_strtolower((string) $status) : '';
                                @endphp
                                <tr class="hover:bg-zinc-50 transition-colors cursor-pointer" wire:click="startGrading({{ $sid }})">
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-sm text-zinc-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                    </td>
                                    <td class="px-6 py-3"><span class="text-sm text-zinc-700 font-semibold">{{ $sid }}</span></td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">{{ $student->section }}</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($statusKey === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Approved</span>
                                        @elseif($statusKey === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">Pending</span>
                                        @elseif($statusKey === 'dropped')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800">Dropped</span>
                                        @else
                                            <span class="text-sm text-zinc-600">{{ ucfirst((string)$status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: Enrollment --}}
    @if($activeTab === 'enrollment')
        <div class="space-y-4">
            <div class="space-y-1">
                <h3 class="text-sm font-semibold">Enrollment</h3>
                <p class="text-sm text-zinc-600">
                    {{ $isAdviser ? 'Select students to enroll (approved instantly).' : 'Select students to request enrollment (await chair approval).' }}
                </p>
            </div>

            @if(! $selectedSubjectId || ! $selectedTermId)
                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                    <p class="text-sm text-zinc-700">Select a subject and term first.</p>
                </div>
            @else
                <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden p-5 space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold">Available Students (select)</h4>
                            @if($availableEnrollmentStudents->isEmpty())
                                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                                    <p class="text-sm text-zinc-700">No available students to enroll for this subject/term.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse min-w-[520px]">
                                        <thead>
                                        <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Select</th>
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200">
                                        @foreach($availableEnrollmentStudents as $student)
                                            <tr class="hover:bg-zinc-50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" value="{{ $student->id }}" wire:model="enrollmentStudentIds" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/20">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="font-bold text-sm text-zinc-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                    <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-0.5">ID: {{ (int)$student->id }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">{{ $student->section }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold">Enrolled Students</h4>
                            @if($enrolledEnrollmentStudents->isEmpty())
                                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                                    <p class="text-sm text-zinc-700">No enrollments yet for this subject/term.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse min-w-[520px]">
                                        <thead>
                                        <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200">
                                        @foreach($enrolledEnrollmentStudents as $student)
                                            @php
                                                $sid = (int)$student->id;
                                                $status = $enrollmentStatusByStudentId[$sid] ?? null;
                                                $statusKey = $status ? mb_strtolower((string)$status) : '';
                                            @endphp
                                            <tr class="hover:bg-zinc-50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <div class="font-bold text-sm text-zinc-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">{{ $student->section }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($statusKey === 'approved')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Approved</span>
                                                    @elseif($statusKey === 'pending')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">Pending</span>
                                                    @else
                                                        <span class="text-sm text-zinc-600">{{ ucfirst((string)$status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 flex-wrap pt-2 border-t border-zinc-200">
                        <button
                            type="button"
                            wire:click="requestEnrollment"
                            @disabled(! $enrollmentStudentIds)
                            class="px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ $isAdviser ? 'Enroll Selected' : 'Request Enrollment' }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: Grades --}}
    @if($activeTab === 'grades')
        <div class="space-y-4">
            <div class="space-y-1">
                <h3 class="text-sm font-semibold">Grades</h3>
                <p class="text-sm text-zinc-600">Only approved enrollments appear here.</p>
            </div>

            @if(! $selectedSubjectId || ! $selectedTermId)
                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                    <p class="text-sm text-zinc-700">Select a subject and term first.</p>
                </div>
            @elseif($gradeStudents->isEmpty())
                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                    <p class="text-sm text-zinc-700">
                        @if($isAdviser)
                            No approved students yet.
                        @else
                            No approved enrollments yet. Request enrollment and wait for program chair approval.
                        @endif
                    </p>
                </div>
            @else
                <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[860px]">
                            <thead>
                            <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Name</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Prelim</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Midterm</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Final</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Final Grade</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Edit</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                            @foreach($gradeStudents as $student)
                                @php
                                    $id = (int)$student->id;
                                    $prelim = $prelimGrades[$id] ?? null;
                                    $midterm = $midtermGrades[$id] ?? null;
                                    $finalExam = $finalExamGrades[$id] ?? null;
                                    $storedFinal = $storedFinalGrades[$id] ?? null;
                                    if ($prelim === '') $prelim = null;
                                    if ($midterm === '') $midterm = null;
                                    if ($finalExam === '') $finalExam = null;
                                    $computedFinal = null;
                                    if ($prelim !== null && $midterm !== null && $finalExam !== null) {
                                        $computedFinal = ((float)$prelim * 0.30) + ((float)$midterm * 0.30) + ((float)$finalExam * 0.40);
                                    }
                                    $finalToShow = $computedFinal ?? $storedFinal;
                                @endphp
                                <tr class="hover:bg-zinc-50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-sm text-zinc-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                        <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-0.5">Section: {{ $student->section }}</div>
                                    </td>
                                    <td class="px-6 py-3"><span class="text-sm text-zinc-700 font-semibold">{{ $prelim !== null ? number_format((float)$prelim, 2) : '—' }}</span></td>
                                    <td class="px-6 py-3"><span class="text-sm text-zinc-700 font-semibold">{{ $midterm !== null ? number_format((float)$midterm, 2) : '—' }}</span></td>
                                    <td class="px-6 py-3"><span class="text-sm text-zinc-700 font-semibold">{{ $finalExam !== null ? number_format((float)$finalExam, 2) : '—' }}</span></td>
                                    <td class="px-6 py-3"><span class="text-sm font-semibold text-zinc-900">{{ $finalToShow !== null ? number_format((float)$finalToShow, 2) : '—' }}</span></td>
                                    <td class="px-6 py-3 text-right">
                                        <button type="button" wire:click="startGrading({{ $id }})" class="px-4 py-2 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: Attendance --}}
    @if($activeTab === 'attendance')
        <div class="space-y-6">
            <div class="space-y-1">
                <h3 class="text-sm font-semibold">Attendance</h3>
                <p class="text-sm text-zinc-600">Setup classroom geo and schedule sessions.</p>
            </div>

            <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden p-5 space-y-6">
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold">A. Classroom Setup</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Room Label</label>
                            <input type="text" wire:model.defer="classroomRoomLabel" placeholder="e.g., Room 101" class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Radius (meters)</label>
                            <input type="number" wire:model.defer="classroomRadiusM" min="5" max="5000" class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl">
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <button type="button" onclick="instructorCaptureClassroomLocation()" class="px-4 py-2 bg-zinc-50 border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-100 transition-all active:scale-[0.98] shadow-sm">
                            Capture Location
                        </button>
                        <span class="text-xs text-zinc-500" id="instructorClassroomGeoStatus"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Latitude</label>
                            <input id="instructor-classroom-lat" type="text" wire:model="classroomLatitude" readonly class="w-full px-3 py-2.5 bg-zinc-100 border border-zinc-200 rounded-xl text-sm text-zinc-700">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Longitude</label>
                            <input id="instructor-classroom-lng" type="text" wire:model="classroomLongitude" readonly class="w-full px-3 py-2.5 bg-zinc-100 border border-zinc-200 rounded-xl text-sm text-zinc-700">
                        </div>
                    </div>
                </div>

                <div class="space-y-3 border-t border-zinc-200 pt-6">
                    <h4 class="text-sm font-semibold">B. Session Scheduler</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Date & Time</label>
                            <input type="datetime-local" wire:model.defer="sessionStartAt" class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Duration (minutes)</label>
                            <input type="number" wire:model.defer="sessionDurationMinutes" min="1" max="600" class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm">
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Repeat Weekdays</label>
                                <span class="text-xs text-zinc-500">
                                    If none selected, it uses the weekday of “Date & Time”.
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

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Repeat for (weeks)</label>
                            <input type="number" wire:model.defer="sessionRepeatWeeks" min="1" max="52" class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm">
                        </div>
                    </div>

                    @if($attendanceSetupMessage)
                        <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4 text-sm text-zinc-700">
                            {{ $attendanceSetupMessage }}
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" wire:click="createClassSession" {{ (! $selectedSubjectId || ! $selectedTermId) ? 'disabled' : '' }} class="px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                            Create Session
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden p-5">
                <div class="space-y-1">
                    <h4 class="text-sm font-semibold">Scheduled Sessions</h4>
                    <p class="text-sm text-zinc-600">Recent sessions for the selected subject/term.</p>
                </div>

                @if($attendanceSessions->isEmpty())
                    <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4 mt-4">
                        <p class="text-sm text-zinc-700">No sessions created yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left border-collapse min-w-[720px]">
                            <thead>
                            <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Start</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Room</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Radius</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Action</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                            @foreach($attendanceSessions as $session)
                                <tr class="hover:bg-zinc-50 transition-colors">
                                    <td class="px-6 py-3">{{ $session->start_at ? $session->start_at->format('M d, Y H:i') : '—' }}</td>
                                    <td class="px-6 py-3"><span class="text-sm font-semibold text-zinc-700">{{ $session->classroom?->room_label ?? '—' }}</span></td>
                                    <td class="px-6 py-3">{{ $session->classroom?->radius_m ?? '—' }} m</td>
                                    <td class="px-6 py-3 text-right">
                                        <button
                                            type="button"
                                            wire:click="editClassSession({{ $session->id }})"
                                            class="px-4 py-2 bg-white border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 transition-all active:scale-[0.98] text-sm"
                                        >
                                            Edit
                                        </button>
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

    {{-- Edit Scheduled Session Modal --}}
    @if($editingClassSessionId)
        <div class="fixed inset-0 z-40 bg-black/40" wire:click="cancelEditClassSession"></div>

        <div class="fixed inset-y-0 right-0 z-50 w-full max-w-xl bg-white shadow-2xl border-l border-zinc-200 overflow-y-auto">
            <div class="p-5 border-b border-zinc-200 space-y-4">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="space-y-1">
                        <p class="text-xs text-zinc-500 font-bold uppercase tracking-widest">Edit Session</p>
                        <h3 class="text-lg font-bold">
                            {{ $editingClassSessionStartAt ? \Carbon\Carbon::parse($editingClassSessionStartAt)->format('M d, Y H:i') : '—' }}
                        </h3>
                        <p class="text-xs text-zinc-500">
                            Duration: <span class="font-semibold text-zinc-700">{{ (int) $editingClassSessionDurationMinutes }} min</span>
                        </p>
                    </div>

                    <button type="button" wire:click="cancelEditClassSession" class="px-3 py-2 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                        X
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Date & Time</label>
                        <input type="datetime-local" wire:model.defer="editingClassSessionStartAt" class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Duration (minutes)</label>
                        <input type="number" wire:model.defer="editingClassSessionDurationMinutes" min="1" max="600" class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm">
                    </div>
                </div>

                @if($editingClassSessionError)
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800">
                        {{ $editingClassSessionError }}
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 flex-wrap">
                    <button type="button" wire:click="cancelEditClassSession" class="px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 transition-all active:scale-[0.98]">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveEditedClassSession" class="px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98]">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Student Drawer --}}
    @if($gradingStudentId)
        <div class="fixed inset-0 z-40 bg-black/40" wire:click="$set('gradingStudentId', null)"></div>

        <div class="fixed inset-y-0 right-0 z-50 w-full max-w-xl bg-white shadow-2xl border-l border-zinc-200 overflow-y-auto">
            <div class="p-5 border-b border-zinc-200 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <p class="text-xs text-zinc-500 font-bold uppercase tracking-widest">Student Drawer</p>
                        <h3 class="text-lg font-bold">
                            {{ $studentDrawerStudent?->first_name ?? '—' }} {{ $studentDrawerStudent?->last_name ?? '' }}
                        </h3>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-semibold text-zinc-600">ID: {{ (int)$gradingStudentId }}</span>
                            @php
                                $drawerStatusKey = $studentDrawerEnrollmentStatus ? mb_strtolower((string)$studentDrawerEnrollmentStatus) : '';
                            @endphp
                            @if($drawerStatusKey === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Approved</span>
                            @elseif($drawerStatusKey === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">Pending</span>
                            @else
                                <span class="text-xs text-zinc-600 font-semibold">{{ $studentDrawerEnrollmentStatus ? ucfirst((string)$studentDrawerEnrollmentStatus) : '—' }}</span>
                            @endif
                        </div>
                    </div>

                    <button type="button" wire:click="$set('gradingStudentId', null)" class="px-3 py-2 rounded-xl font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">X</button>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" wire:click="$set('studentDrawerTab','grades')" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $studentDrawerTab === 'grades' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-zinc-50 text-zinc-700 border-zinc-200 hover:bg-zinc-100' }}">Grades</button>
                    <button type="button" wire:click="$set('studentDrawerTab','attendance')" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $studentDrawerTab === 'attendance' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-zinc-50 text-zinc-700 border-zinc-200 hover:bg-zinc-100' }}">Attendance</button>
                    <button type="button" wire:click="$set('studentDrawerTab','info')" class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $studentDrawerTab === 'info' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-zinc-50 text-zinc-700 border-zinc-200 hover:bg-zinc-100' }}">Info</button>
                </div>
            </div>

            <div class="p-5 space-y-5">
                @if($studentDrawerTab === 'grades')
                    @php
                        $drawerCanEditGrades = ($drawerStatusKey === 'approved');
                        $sid = (int) $gradingStudentId;
                        $prelim = $prelimGrades[$sid] ?? null;
                        $midterm = $midtermGrades[$sid] ?? null;
                        $finalExam = $finalExamGrades[$sid] ?? null;
                        if ($prelim === '') $prelim = null;
                        if ($midterm === '') $midterm = null;
                        if ($finalExam === '') $finalExam = null;
                        $computedFinal = null;
                        if ($prelim !== null && $midterm !== null && $finalExam !== null) {
                            $computedFinal = ((float)$prelim * 0.30) + ((float)$midterm * 0.30) + ((float)$finalExam * 0.40);
                        }
                        $finalToShow = $computedFinal ?? ($storedFinalGrades[$sid] ?? null);
                    @endphp

                    @if(! $drawerCanEditGrades)
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800">
                            Grade editing is available after approval.
                        </div>
                    @endif

                    <div class="bg-white rounded-3xl border border-zinc-200 p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Prelim</label>
                                <input type="number" step="0.01" wire:model="prelimGrades.{{ $sid }}" placeholder="0.00" {{ $drawerCanEditGrades ? '' : 'disabled' }} class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm disabled:bg-zinc-100 disabled:cursor-not-allowed">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Midterm</label>
                                <input type="number" step="0.01" wire:model="midtermGrades.{{ $sid }}" placeholder="0.00" {{ $drawerCanEditGrades ? '' : 'disabled' }} class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm disabled:bg-zinc-100 disabled:cursor-not-allowed">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Final</label>
                                <input type="number" step="0.01" wire:model="finalExamGrades.{{ $sid }}" placeholder="0.00" {{ $drawerCanEditGrades ? '' : 'disabled' }} class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm disabled:bg-zinc-100 disabled:cursor-not-allowed">
                            </div>
                        </div>

                        <div class="flex items-start justify-between gap-4 flex-wrap pt-3 border-t border-zinc-200">
                            <div class="space-y-1">
                                <p class="text-xs text-zinc-500">Final Grade (Auto-calculated)</p>
                                <p class="text-2xl font-bold text-zinc-900">
                                    @if($finalToShow !== null) {{ number_format((float)$finalToShow, 2) }} @else — @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" wire:click="save({{ $sid }})" {{ $drawerCanEditGrades ? '' : 'disabled' }} class="px-5 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed">Save Changes</button>
                                @if($isAdviser && $supportsGradeStatus)
                                    <button type="button" wire:click="save({{ $sid }})" {{ $drawerCanEditGrades ? '' : 'disabled' }} class="px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 disabled:opacity-50 disabled:cursor-not-allowed">Publish</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($studentDrawerTab === 'attendance')
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <h4 class="text-sm font-semibold">Attendance Logs</h4>
                            <span class="text-xs text-zinc-500">{{ $studentDrawerAttendanceLogs->count() }} record(s)</span>
                        </div>

                        @if($studentDrawerAttendanceLogs->isEmpty())
                            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                                <p class="text-sm text-zinc-700">No attendance logs yet.</p>
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach($studentDrawerAttendanceLogs as $log)
                                    @php $statusKey = $log->status ? mb_strtolower((string)$log->status) : ''; @endphp
                                    <div class="bg-white border border-zinc-200 rounded-2xl p-3">
                                        <div class="flex items-center justify-between gap-3 flex-wrap">
                                            <div class="text-sm font-semibold text-zinc-900">{{ $log->marked_at ? $log->marked_at->format('M d, Y H:i') : '—' }}</div>
                                            <div class="text-xs font-bold uppercase tracking-widest text-zinc-600">
                                                @if($statusKey === 'present')
                                                    Present
                                                @elseif($statusKey === 'failed_face')
                                                    Failed Face
                                                @elseif($statusKey === 'failed_face_match')
                                                    Face Mismatch
                                                @elseif($statusKey === 'rejected_location')
                                                    Rejected Location
                                                @else
                                                    {{ ucfirst((string)$log->status) }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-xs text-zinc-600 mt-1">
                                            Room: {{ $log->classSession?->classroom?->room_label ?? '—' }},
                                            Distance: {{ $log->distance_m !== null ? number_format((float)$log->distance_m, 2) . ' m' : '—' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if($studentDrawerTab === 'info')
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold">Student Info</h4>
                        <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4 space-y-2 text-sm text-zinc-700">
                            <p><span class="font-semibold">Name:</span> {{ $studentDrawerStudent?->first_name ?? '—' }} {{ $studentDrawerStudent?->last_name ?? '' }}</p>
                            <p><span class="font-semibold">Section:</span> {{ $studentDrawerStudent?->section ?? '—' }}</p>
                            <p><span class="font-semibold">Enrollment Status:</span> {{ $studentDrawerEnrollmentStatus ? ucfirst((string)$studentDrawerEnrollmentStatus) : '—' }}</p>
                        </div>

                        @if($selectedSubjectId && $selectedTermId)
                            <a href="{{ route('students.details', ['student_id' => (int)$gradingStudentId, 'subject_id' => $selectedSubjectId, 'term_id' => $selectedTermId]) }}" class="inline-flex items-center px-5 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800">
                                View Full Student Portal
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

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

