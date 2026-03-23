<?php

namespace App\Livewire;

use App\Models\ClassSession;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Instructor;
use App\Models\Subject;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Term;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class InstructorPortal extends Component
{
    public ?int $selectedSubjectId = null;
    public ?int $selectedTermId = null;

    public bool $isAdviser = false;
    private bool $supportsSubjectIsActive = false;
    private bool $supportsGradeComponents = false;
    private bool $supportsGradeStatus = false;
    private bool $supportsGradeApprovalFields = false;

    public string $activeTab = 'roster';
    public string $rosterSearch = '';
    public string $studentDrawerTab = 'grades';

    /** @var array<int, string|float|null> */
    public array $prelimGrades = [];
    /** @var array<int, string|float|null> */
    public array $midtermGrades = [];
    /** @var array<int, string|float|null> */
    public array $finalExamGrades = [];
    /**
     * Stores the already-approved/computed final grade (the `grades.grade` column)
     * so the UI can show it even if components are blank.
     *
     * @var array<int, string|float|null>
     */
    public array $storedFinalGrades = [];

    /** @var int[] student ids selected for enrollment */
    public array $enrollmentStudentIds = [];

    public ?int $syncedSubjectId = null;
    public ?int $syncedTermId = null;

    /**
     * When set, the UI shows the student drawer for this student.
     *
     * Grade editing is still restricted to approved enrollments in `save()`.
     */
    public ?int $gradingStudentId = null;

    // Attendance/classroom setup (geolocation-based)
    /** @var int|null Selected classroom to use for new sessions */
    public $selectedClassroomId = null;
    public string $classroomRoomLabel = '';
    public ?float $classroomLatitude = null;
    public ?float $classroomLongitude = null;
    public int $classroomRadiusM = 100;

    public ?string $sessionStartAt = null; // datetime-local value (client)
    public int $sessionDurationMinutes = 60;
    /**
     * ISO weekday numbers: 1=Mon ... 7=Sun.
     * If empty, recurrence defaults to the weekday of `sessionStartAt`.
     *
     * @var int[]
     */
    public array $sessionRepeatWeekdays = [];

    /**
     * Generate sessions for the next N weeks starting from `sessionStartAt`.
     */
    public int $sessionRepeatWeeks = 8;

    // Edit Scheduled Sessions (Attendance tab)
    public ?int $editingClassSessionId = null;
    public ?string $editingClassSessionStartAt = null; // datetime-local value
    public int $editingClassSessionDurationMinutes = 60;
    public string $editingClassSessionError = '';

    public string $attendanceSetupMessage = '';

    /**
     * Schema flags must refresh on every Livewire request (hydration), not only in mount(),
     * otherwise save() and other actions see supportsGradeStatus=false and omit status/approval columns.
     */
    public function boot(): void
    {
        $this->supportsSubjectIsActive = Schema::hasColumn('subjects', 'is_active');
        $this->supportsGradeComponents = Schema::hasColumn('grades', 'prelim')
            && Schema::hasColumn('grades', 'midterm')
            && Schema::hasColumn('grades', 'final_exam');
        $this->supportsGradeStatus = Schema::hasColumn('grades', 'status');
        $this->supportsGradeApprovalFields = Schema::hasColumn('grades', 'approved_by_program_chair_id')
            && Schema::hasColumn('grades', 'approved_at');
    }

    public function mount(): void
    {
        $this->initialiseDefaults();
        $this->syncExistingGrades();
    }

    private function initialiseDefaults(): void
    {
        $instructor = auth('instructor')->user();
        $this->isAdviser = (bool) ($instructor?->advisees()->exists());

        $subjectsQuery = $instructor?->subjects();
        if ($this->supportsSubjectIsActive && $subjectsQuery) {
            $subjectsQuery->where('is_active', true);
        }

        $subjects = $subjectsQuery?->orderBy('id')->get() ?? collect();
        $terms = Term::query()->orderBy('id')->get();

        $this->selectedSubjectId = $subjects->first()?->id;
        $this->selectedTermId = $terms->first()?->id;

        // If the instructor already captured classrooms before, select the first one by default.
        if ($instructor) {
            $existingClassroom = Classroom::query()
                ->where('instructor_id', $instructor->id)
                ->orderBy('room_label')
                ->first();

            if ($existingClassroom) {
                $this->selectedClassroomId = $existingClassroom->id;
                $this->classroomRoomLabel = (string) $existingClassroom->room_label;
                $this->classroomLatitude = $existingClassroom->latitude !== null ? (float) $existingClassroom->latitude : null;
                $this->classroomLongitude = $existingClassroom->longitude !== null ? (float) $existingClassroom->longitude : null;
                $this->classroomRadiusM = (int) ($existingClassroom->radius_m ?? 100);
            }
        }
    }

    public function updatedSelectedClassroomId($value): void
    {
        $instructor = auth('instructor')->user();
        if (! $instructor) {
            return;
        }

        $selectedId = $value ? (int) $value : null;

        if ($selectedId === null) {
            // New classroom: clear input fields.
            $this->selectedClassroomId = null;
            $this->classroomRoomLabel = '';
            $this->classroomLatitude = null;
            $this->classroomLongitude = null;
            $this->classroomRadiusM = 100;
            return;
        }

        $classroom = Classroom::query()
            ->where('instructor_id', $instructor->id)
            ->whereKey($selectedId)
            ->first();

        if (! $classroom) {
            $this->selectedClassroomId = null;
            return;
        }

        $this->classroomRoomLabel = (string) $classroom->room_label;
        $this->classroomLatitude = $classroom->latitude !== null ? (float) $classroom->latitude : null;
        $this->classroomLongitude = $classroom->longitude !== null ? (float) $classroom->longitude : null;
        $this->classroomRadiusM = (int) ($classroom->radius_m ?? 100);
    }

    public function updatedSelectedSubjectId(): void
    {
        $this->gradingStudentId = null;
        $this->rosterSearch = '';
        $this->studentDrawerTab = 'grades';
        $this->attendanceSetupMessage = '';
        $this->sessionStartAt = null;
        $this->sessionDurationMinutes = 60;
        $this->sessionRepeatWeekdays = [];
        $this->sessionRepeatWeeks = 8;
        $this->syncExistingGrades();
    }

    public function updatedSelectedTermId(): void
    {
        $this->gradingStudentId = null;
        $this->rosterSearch = '';
        $this->studentDrawerTab = 'grades';
        $this->attendanceSetupMessage = '';
        $this->sessionStartAt = null;
        $this->sessionDurationMinutes = 60;
        $this->sessionRepeatWeekdays = [];
        $this->sessionRepeatWeeks = 8;
        $this->syncExistingGrades();
    }

    private function syncExistingGrades(): void
    {
        $instructor = auth('instructor')->user();
        $approvedStudentIds = $this->getApprovedStudentIds();

        if (! $this->selectedSubjectId || ! $this->selectedTermId || empty($approvedStudentIds)) {
            $this->prelimGrades = [];
            $this->midtermGrades = [];
            $this->finalExamGrades = [];
            $this->storedFinalGrades = [];
            $this->gradingStudentId = null;
            return;
        }

        $existing = Grade::query()
            ->where('subject_id', $this->selectedSubjectId)
            ->where('term_id', $this->selectedTermId)
            ->whereIn('student_id', $approvedStudentIds)
            ->get();

        $this->prelimGrades = [];
        $this->midtermGrades = [];
        $this->finalExamGrades = [];
        $this->storedFinalGrades = [];
        foreach ($existing as $grade) {
            $studentId = (int) $grade->student_id;
            $this->storedFinalGrades[$studentId] = $grade->grade;

            if ($this->supportsGradeComponents) {
                $this->prelimGrades[$studentId] = $grade->prelim;
                $this->midtermGrades[$studentId] = $grade->midterm;
                $this->finalExamGrades[$studentId] = $grade->final_exam;
            }
        }

        $this->syncedSubjectId = $this->selectedSubjectId;
        $this->syncedTermId = $this->selectedTermId;

        // If the user had a student selected for grading, it must still be
        // approved for the newly selected subject/term.
        if ($this->gradingStudentId !== null && ! in_array($this->gradingStudentId, $approvedStudentIds, true)) {
            $this->gradingStudentId = null;
        }
    }

    /**
     * Students selected for grading = those approved for this subject/term.
     *
     * @return int[]
     */
    private function getApprovedStudentIds(): array
    {
        if (! $this->selectedSubjectId || ! $this->selectedTermId) {
            return [];
        }
        // Course roster: all approved enrollments for the selected subject/term.
        return StudentSubjectEnrollment::query()
            ->where('subject_id', $this->selectedSubjectId)
            ->where('term_id', $this->selectedTermId)
            ->where('status', 'approved')
            ->pluck('student_id')
            ->all();
    }

    /**
     * Compare stored vs computed final grades without string float quirks ("83.00" vs "83").
     */
    private function finalGradeMeaningfullyChanged($existing, $incoming): bool
    {
        if ($existing === null && $incoming === null) {
            return false;
        }
        if ($existing === null || $incoming === null) {
            return true;
        }

        return round((float) $existing, 2) !== round((float) $incoming, 2);
    }

    /**
     * Compare grade component values while handling nulls and numeric strings safely.
     */
    private function gradeValueMeaningfullyChanged($existing, $incoming): bool
    {
        if ($existing === null && $incoming === null) {
            return false;
        }

        if ($existing === null || $incoming === null) {
            return true;
        }

        return round((float) $existing, 2) !== round((float) $incoming, 2);
    }

    public function startGrading(int $studentId): void
    {
        $instructor = auth('instructor')->user();

        abort_if(! $this->selectedSubjectId || ! $this->selectedTermId, 422);

        // Enforce: instructor can only grade subjects they teach.
        $ownsSubjectQuery = $instructor->subjects()->whereKey($this->selectedSubjectId);
        if ($this->supportsSubjectIsActive) {
            $ownsSubjectQuery->where('is_active', true);
        }
        $ownsSubject = $ownsSubjectQuery->exists();
        abort_if(! $ownsSubject, 403);

        // Allow opening the drawer for any student in this subject/term roster.
        // Editing grades is still restricted to approved enrollments in `save()`.
        $existsInRoster = StudentSubjectEnrollment::query()
            ->where('subject_id', $this->selectedSubjectId)
            ->where('term_id', $this->selectedTermId)
            ->where('student_id', $studentId)
            ->exists();
        abort_if(! $existsInRoster, 404);

        $this->gradingStudentId = $studentId;
        $this->studentDrawerTab = 'grades';
    }

    public function save(?int $studentId = null): void
    {
        $instructor = auth('instructor')->user();

        abort_if(! $this->selectedSubjectId || ! $this->selectedTermId, 422);

        // Enforce: instructor can only grade subjects they teach.
        $ownsSubjectQuery = $instructor->subjects()->whereKey($this->selectedSubjectId);
        if ($this->supportsSubjectIsActive) {
            $ownsSubjectQuery->where('is_active', true);
        }
        $ownsSubject = $ownsSubjectQuery->exists();
        abort_if(! $ownsSubject, 403);

        $approvedStudentIds = $this->getApprovedStudentIds();
        abort_if(empty($approvedStudentIds), 422);

        if ($studentId !== null) {
            abort_if(! in_array($studentId, $approvedStudentIds, true), 403);
            $studentIds = [$studentId];
        } else {
            $studentIds = $approvedStudentIds;
        }

        $weights = [
            'prelim' => 0.30,
            'midterm' => 0.30,
            'final' => 0.40,
        ];

        // Normalize + validate inputs for all approved students.
        foreach ($studentIds as $studentId) {
            $prelim = $this->prelimGrades[$studentId] ?? null;
            $midterm = $this->midtermGrades[$studentId] ?? null;
            $finalExam = $this->finalExamGrades[$studentId] ?? null;

            if ($prelim === '' || $prelim === null) {
                $this->prelimGrades[$studentId] = null;
            }
            if ($midterm === '' || $midterm === null) {
                $this->midtermGrades[$studentId] = null;
            }
            if ($finalExam === '' || $finalExam === null) {
                $this->finalExamGrades[$studentId] = null;
            }

            $this->validate([
                'prelimGrades.' . $studentId => ['nullable', 'numeric', 'min:0', 'max:100'],
                'midtermGrades.' . $studentId => ['nullable', 'numeric', 'min:0', 'max:100'],
                'finalExamGrades.' . $studentId => ['nullable', 'numeric', 'min:0', 'max:100'],
            ]);
        }

        foreach ($studentIds as $studentId) {
            $prelim = $this->prelimGrades[$studentId] ?? null;
            $midterm = $this->midtermGrades[$studentId] ?? null;
            $finalExam = $this->finalExamGrades[$studentId] ?? null;

            $existing = Grade::query()
                ->where('student_id', $studentId)
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->first();

            // If all components are blank, keep the previously stored final grade.
            if ($prelim === null && $midterm === null && $finalExam === null && $existing) {
                $gradeValue = $existing->grade;
                $prelim = $existing->prelim;
                $midterm = $existing->midterm;
                $finalExam = $existing->final_exam;
            } else {
                if ($prelim !== null && $midterm !== null && $finalExam !== null) {
                    $gradeValue = round(((float) $prelim * $weights['prelim']) + ((float) $midterm * $weights['midterm']) + ((float) $finalExam * $weights['final']), 2);
                } else {
                    // Only compute when all components are present; otherwise keep the previous final grade (if any).
                    $gradeValue = $existing?->grade;
                }
            }

            $gradeData = [
                // `grade` is the computed final grade students see.
                'grade' => $gradeValue,
                'remarks' => null,
            ];

            if ($this->supportsGradeComponents) {
                $gradeData['prelim'] = $prelim;
                $gradeData['midterm'] = $midterm;
                $gradeData['final_exam'] = $finalExam;
            }

            if ($this->supportsGradeStatus) {
                $existingStatus = $existing
                    ? strtolower(trim((string) ($existing->status ?? '')))
                    : '';
                $finalChanged = $existing && $this->finalGradeMeaningfullyChanged($existing->grade, $gradeValue);
                $prelimChanged = $this->supportsGradeComponents && $existing
                    ? $this->gradeValueMeaningfullyChanged($existing->prelim, $prelim)
                    : false;
                $midtermChanged = $this->supportsGradeComponents && $existing
                    ? $this->gradeValueMeaningfullyChanged($existing->midterm, $midterm)
                    : false;
                $finalExamChanged = $this->supportsGradeComponents && $existing
                    ? $this->gradeValueMeaningfullyChanged($existing->final_exam, $finalExam)
                    : false;

                $hasGradeChange = ! $existing || $finalChanged || $prelimChanged || $midtermChanged || $finalExamChanged;

                // Any instructor-entered grade change must go through chair approval.
                if ($hasGradeChange) {
                    $gradeData['status'] = 'pending';
                    if ($this->supportsGradeApprovalFields) {
                        $gradeData['approved_by_program_chair_id'] = null;
                        $gradeData['approved_at'] = null;
                    }
                } elseif ($existingStatus === 'approved') {
                    $gradeData['status'] = 'approved';
                    if ($this->supportsGradeApprovalFields) {
                        $gradeData['approved_by_program_chair_id'] = $existing->approved_by_program_chair_id;
                        $gradeData['approved_at'] = $existing->approved_at;
                    }
                } else {
                    $gradeData['status'] = 'pending';
                    if ($this->supportsGradeApprovalFields) {
                        $gradeData['approved_by_program_chair_id'] = null;
                        $gradeData['approved_at'] = null;
                    }
                }
            }

            Grade::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $this->selectedSubjectId,
                    'term_id' => $this->selectedTermId,
                ],
                $gradeData
            );

            $this->storedFinalGrades[$studentId] = $gradeValue;
        }

        $this->dispatch('saved');
    }

    public function requestEnrollment(): void
    {
        $instructor = auth('instructor')->user();

        abort_if(! $this->selectedSubjectId || ! $this->selectedTermId, 422);
        abort_if(empty($this->enrollmentStudentIds), 422);

        // Enforce: instructor can only enroll in subjects they teach.
        $ownsSubjectQuery = $instructor->subjects()->whereKey($this->selectedSubjectId);
        if ($this->supportsSubjectIsActive) {
            $ownsSubjectQuery->where('is_active', true);
        }
        $ownsSubject = $ownsSubjectQuery->exists();
        abort_if(! $ownsSubject, 403);

        $subject = Subject::query()->whereKey($this->selectedSubjectId)->first();
        abort_if(! $subject, 404);

        if ($this->isAdviser) {
            // Adviser-instructor can enroll only their advisees (in the subject's program if set).
            $adviseesQuery = $instructor->advisees();
            if (! empty($subject->program_id)) {
                $adviseesQuery->where('program_id', $subject->program_id);
            }

            $allowedStudentIds = $adviseesQuery->pluck('students.id')->all();
            $selected = array_values(array_intersect($this->enrollmentStudentIds, $allowedStudentIds));
            abort_if(empty($selected), 403);

            foreach ($selected as $studentId) {
                StudentSubjectEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $this->selectedSubjectId,
                        'term_id' => $this->selectedTermId,
                    ],
                    [
                        'requested_by_instructor_id' => $instructor->id,
                        'status' => 'approved',
                    ],
                );
            }
        } else {
            // Regular instructor: can only request enrollment for their advisees.
            $adviseeIds = $instructor->advisees()->pluck('students.id')->all();
            $selected = array_values(array_intersect($this->enrollmentStudentIds, $adviseeIds));
            abort_if(empty($selected), 403);

            foreach ($selected as $studentId) {
                StudentSubjectEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $this->selectedSubjectId,
                        'term_id' => $this->selectedTermId,
                    ],
                    [
                        'requested_by_instructor_id' => $instructor->id,
                        'status' => 'pending',
                    ],
                );
            }
        }

        $this->enrollmentStudentIds = [];
        $this->dispatch('saved');
    }

    public function createClassSession(): void
    {
        $instructor = auth('instructor')->user();

        abort_if(! $this->selectedSubjectId || ! $this->selectedTermId, 422);

        // Enforce: instructor can only schedule sessions for subjects they teach.
        $ownsSubjectQuery = $instructor->subjects()->whereKey($this->selectedSubjectId);
        if ($this->supportsSubjectIsActive) {
            $ownsSubjectQuery->where('is_active', true);
        }
        $ownsSubject = $ownsSubjectQuery->exists();
        abort_if(! $ownsSubject, 403);

        $selectedClassroomId = $this->selectedClassroomId ? (int) $this->selectedClassroomId : null;
        $creatingNewClassroom = $selectedClassroomId === null;

        $rules = [
            'sessionStartAt' => ['required', 'date'],
            'sessionDurationMinutes' => ['required', 'integer', 'min:1', 'max:600'],
            'sessionRepeatWeeks' => ['required', 'integer', 'min:1', 'max:52'],
            'sessionRepeatWeekdays' => ['array'],
            'sessionRepeatWeekdays.*' => ['integer', 'between:1,7'],
        ];

        if ($creatingNewClassroom) {
            $rules += [
                'classroomRoomLabel' => ['required', 'string', 'max:255'],
                'classroomLatitude' => ['required', 'numeric', 'min:-90', 'max:90'],
                'classroomLongitude' => ['required', 'numeric', 'min:-180', 'max:180'],
                'classroomRadiusM' => ['required', 'integer', 'min:5', 'max:5000'],
            ];
        }

        $this->validate($rules);

        $startAt = \Carbon\Carbon::parse((string) $this->sessionStartAt);
        $selectedWeekdays = array_values(array_unique(array_map(
            static fn ($d) => (int) $d,
            is_array($this->sessionRepeatWeekdays) ? $this->sessionRepeatWeekdays : []
        )));

        if (empty($selectedWeekdays)) {
            // Keep prior behavior: when no recurrence is selected, schedule only the start weekday.
            $selectedWeekdays = [(int) $startAt->dayOfWeekIso]; // 1=Mon ... 7=Sun
        }

        $selectedWeekdays = array_values(array_filter(
            $selectedWeekdays,
            static fn (int $d) => $d >= 1 && $d <= 7
        ));

        if (empty($selectedWeekdays)) {
            $selectedWeekdays = [(int) $startAt->dayOfWeekIso];
        }

        if ($creatingNewClassroom) {
            $classroom = Classroom::query()->create([
                'instructor_id' => $instructor->id,
                'room_label' => $this->classroomRoomLabel,
                'latitude' => $this->classroomLatitude,
                'longitude' => $this->classroomLongitude,
                'radius_m' => $this->classroomRadiusM,
            ]);
            $this->selectedClassroomId = $classroom->id;
        } else {
            $classroom = Classroom::query()
                ->where('instructor_id', $instructor->id)
                ->whereKey($selectedClassroomId)
                ->firstOrFail();

            // Instructor may optionally recapture and adjust the classroom geo/radius.
            $update = [];
            if ($this->classroomRoomLabel !== '') {
                $update['room_label'] = $this->classroomRoomLabel;
            }
            if ($this->classroomLatitude !== null) {
                $update['latitude'] = $this->classroomLatitude;
            }
            if ($this->classroomLongitude !== null) {
                $update['longitude'] = $this->classroomLongitude;
            }
            if ($this->classroomRadiusM !== null) {
                $update['radius_m'] = $this->classroomRadiusM;
            }

            if (! empty($update)) {
                $classroom->update($update);
            }
        }

        $limitAt = $startAt->copy()->addDays($this->sessionRepeatWeeks * 7 - 1);
        $createdCount = 0;
        $skippedCount = 0;

        for ($cursor = $startAt->copy(); $cursor->lte($limitAt); $cursor->addDay()) {
            if (! in_array((int) $cursor->dayOfWeekIso, $selectedWeekdays, true)) {
                continue;
            }

            $instanceStartAt = $cursor->copy();
            $instanceEndAt = $instanceStartAt->copy()->addMinutes($this->sessionDurationMinutes);

            $alreadyExists = ClassSession::query()
                ->where('instructor_id', $instructor->id)
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->where('classroom_id', $classroom->id)
                ->where('start_at', $instanceStartAt)
                ->exists();

            if ($alreadyExists) {
                $skippedCount++;
                continue;
            }

            ClassSession::query()->create([
                'instructor_id' => $instructor->id,
                'subject_id' => $this->selectedSubjectId,
                'term_id' => $this->selectedTermId,
                'classroom_id' => $classroom->id,
                'start_at' => $instanceStartAt,
                'end_at' => $instanceEndAt,
            ]);

            $createdCount++;
        }

        $this->attendanceSetupMessage = $createdCount > 0
            ? 'Class session(s) created. Students can now mark attendance at the scheduled times.'
            : 'No new class sessions were created.';
        if ($skippedCount > 0) {
            $this->attendanceSetupMessage .= " {$skippedCount} duplicate(s) skipped.";
        }

        // Keep classroom geo for reuse; only reset when creating a brand-new classroom.
        if ($creatingNewClassroom) {
            $this->classroomLatitude = null;
            $this->classroomLongitude = null;
        }
        $this->sessionStartAt = null;
        $this->sessionDurationMinutes = 60;

        $this->dispatch('saved');
    }

    public function editClassSession(int $sessionId): void
    {
        $instructor = auth('instructor')->user();
        abort_if(! $instructor, 401);

        $session = ClassSession::query()
            ->where('instructor_id', $instructor->id)
            ->whereKey($sessionId)
            ->firstOrFail();

        $this->editingClassSessionId = (int) $session->id;
        $this->editingClassSessionStartAt = $session->start_at
            ? $session->start_at->format('Y-m-d\\TH:i')
            : null;

        if ($session->end_at) {
            $this->editingClassSessionDurationMinutes = max(
                1,
                (int) $session->start_at->diffInMinutes($session->end_at)
            );
        } else {
            $this->editingClassSessionDurationMinutes = 60;
        }

        $this->editingClassSessionError = '';
    }

    public function cancelEditClassSession(): void
    {
        $this->editingClassSessionId = null;
        $this->editingClassSessionStartAt = null;
        $this->editingClassSessionDurationMinutes = 60;
        $this->editingClassSessionError = '';
    }

    public function saveEditedClassSession(): void
    {
        $instructor = auth('instructor')->user();
        abort_if(! $instructor, 401);
        abort_if(! $this->editingClassSessionId, 422);

        $this->validate([
            'editingClassSessionStartAt' => ['required', 'date'],
            'editingClassSessionDurationMinutes' => ['required', 'integer', 'min:1', 'max:600'],
        ]);

        $session = ClassSession::query()
            ->where('instructor_id', $instructor->id)
            ->whereKey($this->editingClassSessionId)
            ->firstOrFail();

        $newStartAt = \Carbon\Carbon::parse((string) $this->editingClassSessionStartAt);
        $newEndAt = $newStartAt->copy()->addMinutes($this->editingClassSessionDurationMinutes);

        // Prevent exact duplicates for the same instructor/classroom/time.
        $duplicate = ClassSession::query()
            ->where('instructor_id', $instructor->id)
            ->where('subject_id', $session->subject_id)
            ->where('term_id', $session->term_id)
            ->where('classroom_id', $session->classroom_id)
            ->where('start_at', $newStartAt)
            ->where('id', '!=', $session->id)
            ->exists();

        if ($duplicate) {
            $this->editingClassSessionError = 'A session with the same start time already exists. Please choose another time.';
            return;
        }

        $session->update([
            'start_at' => $newStartAt,
            'end_at' => $newEndAt,
        ]);

        $this->attendanceSetupMessage = 'Scheduled session updated successfully.';
        $this->cancelEditClassSession();
        $this->dispatch('saved');
    }

    public function render()
    {
        $instructor = auth('instructor')->user();

        $subjects = $instructor->subjects()
            ->orderBy('name')
            ->when($this->supportsSubjectIsActive, fn ($q) => $q->where('is_active', true))
            ->get();
        $terms = Term::query()->orderBy('id')->get();
        $classrooms = Classroom::query()
            ->where('instructor_id', $instructor->id)
            ->orderBy('room_label')
            ->get();

        $this->isAdviser = (bool) $instructor?->advisees()->exists();

        $enrollmentStudents = collect();
        if ($this->isAdviser) {
            $selectedSubject = $subjects->firstWhere('id', $this->selectedSubjectId);

            $enrollmentStudentsQuery = $instructor->advisees();
            if (! empty($selectedSubject?->program_id)) {
                $enrollmentStudentsQuery->where('program_id', $selectedSubject->program_id);
            }

            $enrollmentStudents = $enrollmentStudentsQuery
                ->orderBy('section')
                ->orderBy('last_name')
                ->get();
        } else {
            // Advisees = students linked to this instructor (regular enrollment request UI)
            $enrollmentStudents = $instructor->advisees()->orderBy('section')->orderBy('last_name')->get();
        }

        $enrollmentStatusByStudentId = [];
        $rosterStatusByStudentId = [];
        $gradeStudents = collect();
        $courseEnrollments = collect();
        $availableEnrollmentStudents = collect();
        $enrolledEnrollmentStudents = collect();
        $studentDrawerStudent = null;
        $studentDrawerEnrollmentStatus = null;
        $studentDrawerAttendanceLogs = collect();
        $attendanceSessions = collect();

        if ($this->selectedSubjectId && $this->selectedTermId) {
            // Full course roster (pending + approved). Instructors can see everything in their taught subject.
            $courseEnrollments = StudentSubjectEnrollment::query()
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->with(['student'])
                ->orderByDesc('id')
                ->get();

            if ($this->rosterSearch !== '') {
                $needle = mb_strtolower(trim($this->rosterSearch));
                $courseEnrollments = $courseEnrollments->filter(function ($e) use ($needle) {
                    $s = $e->student;
                    if (! $s) return false;
                    $haystack = mb_strtolower(
                        trim(($s->first_name ?? '').' '.($s->last_name ?? '').' '.($s->section ?? '').' '.(string) $s->id)
                    );
                    return str_contains($haystack, $needle);
                })->values();
            } else {
                $courseEnrollments = $courseEnrollments->values();
            }

            $rosterStatusByStudentId = $courseEnrollments
                ->pluck('status', 'student_id')
                ->toArray();

            // For the enrollment UI (advising-class roster), show status only for this instructor's advisees.
            if (! empty($enrollmentStudents)) {
                $candidateIds = $enrollmentStudents->pluck('id')->all();

                $enrollmentStatusByStudentId = StudentSubjectEnrollment::query()
                    ->where('subject_id', $this->selectedSubjectId)
                    ->where('term_id', $this->selectedTermId)
                    ->whereIn('student_id', $candidateIds)
                    ->pluck('status', 'student_id')
                    ->toArray();
            }

            // For the grading UI (course roster), show all students approved in this subject/term.
            $gradeStudents = StudentSubjectEnrollment::query()
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->where('status', 'approved')
                ->with(['student'])
                ->get()
                ->map(fn ($e) => $e->student)
                ->filter()
                ->values();
        }

        // Enrollment dual panel:
        // - Available = advisees not yet enrolled in this subject/term
        // - Enrolled = advisees with an enrollment record for this subject/term (pending/approved)
        $enrolledStudentIds = array_keys($enrollmentStatusByStudentId);
        $enrolledEnrollmentStudents = $enrollmentStudents
            ->whereIn('id', $enrolledStudentIds)
            ->values();
        $availableEnrollmentStudents = $enrollmentStudents
            ->whereNotIn('id', $enrolledStudentIds)
            ->values();

        if ($this->gradingStudentId && $this->selectedSubjectId && $this->selectedTermId) {
            $drawerEnrollment = StudentSubjectEnrollment::query()
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->where('student_id', $this->gradingStudentId)
                ->with(['student'])
                ->first();

            $studentDrawerStudent = $drawerEnrollment?->student ?? null;
            $studentDrawerEnrollmentStatus = $drawerEnrollment?->status ?? null;

            $studentDrawerAttendanceLogs = \App\Models\AttendanceLog::query()
                ->where('student_id', $this->gradingStudentId)
                ->whereHas('classSession', function ($q) use ($instructor) {
                    $q->where('instructor_id', $instructor->id)
                        ->where('subject_id', $this->selectedSubjectId)
                        ->where('term_id', $this->selectedTermId);
                })
                ->with(['classSession', 'classSession.classroom', 'classSession.subject', 'classSession.term'])
                ->orderByDesc('marked_at')
                ->get();
        }

        if ($this->selectedSubjectId && $this->selectedTermId) {
            $attendanceSessions = ClassSession::query()
                ->where('instructor_id', $instructor->id)
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->with(['classroom', 'subject', 'term'])
                ->orderByDesc('id')
                ->get();
        }

        // Load defaults if needed
        if (! $this->selectedSubjectId || ! $this->selectedTermId) {
            $this->initialiseDefaults();
        }

        // Only sync from DB when the user changes subject/term (avoid wiping typed input)
        if ($this->selectedSubjectId !== $this->syncedSubjectId || $this->selectedTermId !== $this->syncedTermId) {
            $this->syncExistingGrades();
        }

        return view('livewire.instructor-portal-tabs', [
            'subjects' => $subjects,
            'terms' => $terms,
            'enrollmentStudents' => $enrollmentStudents,
            'isAdviser' => $this->isAdviser,
            'gradeStudents' => $gradeStudents,
            'enrollmentStatusByStudentId' => $enrollmentStatusByStudentId,
            'courseEnrollments' => $courseEnrollments,
            'classrooms' => $classrooms,

            'rosterStatusByStudentId' => $rosterStatusByStudentId,
            'availableEnrollmentStudents' => $availableEnrollmentStudents,
            'enrolledEnrollmentStudents' => $enrolledEnrollmentStudents,

            'studentDrawerStudent' => $studentDrawerStudent,
            'studentDrawerEnrollmentStatus' => $studentDrawerEnrollmentStatus,
            'studentDrawerAttendanceLogs' => $studentDrawerAttendanceLogs,
            'attendanceSessions' => $attendanceSessions,

            'supportsGradeComponents' => $this->supportsGradeComponents,
            'supportsGradeStatus' => $this->supportsGradeStatus,
            'supportsGradeApprovalFields' => $this->supportsGradeApprovalFields,
        ]);
    }
}

