<?php

namespace App\Livewire;

use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StudentCredentialsTable extends Component
{
    public bool $showModal = false;
    public ?Student $editing = null;

    public array $form = [
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'instructor_id' => null,
    ];

    private function isChairMode(): bool
    {
        return auth('program_chair')->check();
    }

    /**
     * @return int[]
     */
    private function getChairProgramIds(): array
    {
        if (! $this->isChairMode()) {
            return [];
        }

        return auth('program_chair')->user()?->programs()->pluck('programs.id')->all() ?? [];
    }

    public function create(): void
    {
        // Admin can only set credentials for existing students (student records are managed elsewhere).
        $this->editing = null;
        $this->showModal = true;

        $this->form = [
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'instructor_id' => null,
        ];
    }

    public function edit(int $id): void
    {
        $student = Student::findOrFail($id);

        if ($this->isChairMode()) {
            $programIds = $this->getChairProgramIds();
            abort_if(! in_array((int) ($student->program_id ?? -1), $programIds, true), 403);
        }

        $this->editing = $student;

        $this->form = [
            'email' => $student->email ?? '',
            'password' => '',
            'password_confirmation' => '',
            'instructor_id' => optional($student->advisers()->first())->id,
        ];

        $this->showModal = true;
    }

    protected function rules(): array
    {
        $emailRule = Rule::unique('students', 'email')
            ->ignore($this->editing?->id);

        $instructorRule = ['nullable', 'exists:instructors,id'];
        if ($this->isChairMode()) {
            $programIds = $this->getChairProgramIds();
            $allowedInstructorIds = Instructor::query()
                ->whereHas('subjects', function ($query) use ($programIds) {
                    $query->whereIn('program_id', $programIds);
                })
                ->pluck('id')
                ->all();

            $instructorRule = ['nullable', Rule::in($allowedInstructorIds)];
        }

        return [
            'form.email' => ['required', 'email', 'max:255', $emailRule],
            'form.password' => [$this->editing ? 'nullable' : 'required', 'string', 'min:8'],
            'form.password_confirmation' => ['nullable', 'same:form.password'],
            'form.instructor_id' => $instructorRule,
        ];
    }

    public function save(): void
    {
        // If Admin is only changing adviser assignment, they may leave password fields blank.
        // Normalize empty strings to null so validation doesn't fail on `password_confirmation`.
        if ($this->editing) {
            if (trim((string) ($this->form['password'] ?? '')) === '') {
                $this->form['password'] = null;
                $this->form['password_confirmation'] = null;
            }
        }

        $validated = $this->validate($this->rules())['form'];
        $validated['email'] = strtolower(trim($validated['email']));

        if ($this->editing) {
            $update = [
                'email' => $validated['email'],
                'instructor_id' => $validated['instructor_id'], // no-op if column doesn't exist
            ];

            if (! empty($validated['password'])) {
                $update['password'] = Hash::make($validated['password']);
            }

            $this->editing->update([
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : $this->editing->password,
            ]);

            // Only Admin should manage adviser assignments from /settings.
            // Program Chair assigns advisers from the Instructor Details page.
            if (! $this->isChairMode()) {
                // Update adviser assignment pivot
                $this->editing->advisers()->detach();
                if (! empty($validated['instructor_id'])) {
                    $this->editing->advisers()->attach((int) $validated['instructor_id']);
                }
            }
        }

        $this->showModal = false;
        $this->editing = null;
        $this->dispatch('saved');
    }

    public function render()
    {
        $programIds = $this->getChairProgramIds();
        $isChairMode = $this->isChairMode();

        $studentsQuery = Student::query()->orderByDesc('id');
        $instructorsQuery = Instructor::query()->orderBy('name');

        if ($isChairMode && ! empty($programIds)) {
            $studentsQuery->whereIn('program_id', $programIds);

            $instructorsQuery->whereHas('subjects', function ($query) use ($programIds) {
                $query->whereIn('program_id', $programIds);
            });
        }

        return view('livewire.student-credentials-table', [
            'students' => $studentsQuery->get(),
            'instructors' => $instructorsQuery->get(),
        ]);
    }
}

