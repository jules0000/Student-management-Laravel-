<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\Subject;
use App\Models\Program;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class InstructorCredentialsTable extends Component
{
    public bool $showModal = false;
    public ?Instructor $editing = null;
    public ?int $confirmingDeleteId = null;

    public array $form = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'subjects_csv' => '',
    ];

    public function create(): void
    {
        $this->editing = null;
        $this->confirmingDeleteId = null;
        $this->showModal = true;
        $this->form = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'subjects_csv' => '',
        ];
    }

    public function edit(int $id): void
    {
        $this->editing = Instructor::query()->findOrFail($id);
        $this->confirmingDeleteId = null;
        $this->showModal = true;

        $subjects = $this->editing->subjects()->pluck('name')->toArray();

        $this->form = [
            'name' => $this->editing->name,
            'email' => $this->editing->email,
            'password' => '',
            'password_confirmation' => '',
            'subjects_csv' => implode(', ', $subjects),
        ];
    }

    protected function rules(): array
    {
        $emailRule = Rule::unique('instructors', 'email')
            ->ignore($this->editing?->id);

        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => ['required', 'email', 'max:255', $emailRule],
            'form.password' => [$this->editing ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'form.subjects_csv' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules())['form'];

        $defaultProgram = Program::query()->orderBy('id')->first();
        $defaultDepartment = Department::query()->orderBy('id')->first();

        if ($this->editing) {
            $update = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $update['password'] = Hash::make($validated['password']);
            }

            $this->editing->update($update);
        } else {
            $this->editing = Instructor::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Update subjects if provided.
        $subjectsCsv = trim((string) ($validated['subjects_csv'] ?? ''));
        if ($subjectsCsv !== '') {
            $names = array_values(array_filter(array_map(
                fn($v) => trim($v),
                explode(',', $subjectsCsv)
            )));

            $this->editing->subjects()->delete();

            foreach ($names as $name) {
                Subject::create([
                    'name' => $name,
                    'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4)),
                    'instructor_id' => $this->editing->id,
                    'department_id' => $defaultDepartment?->id,
                    'program_id' => $defaultProgram?->id,
                ]);
            }
        }

        $this->showModal = false;
        $this->editing = null;
        $this->dispatch('saved');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        if (! $this->confirmingDeleteId) {
            return;
        }

        $adminEmailProtected = 'instructor@example.com';
        $instructor = Instructor::query()->findOrFail($this->confirmingDeleteId);

        if (($instructor->email ?? null) === $adminEmailProtected) {
            $this->confirmingDeleteId = null;
            return;
        }

        $instructor->delete();
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        return view('livewire.instructor-credentials-table', [
            'instructors' => Instructor::query()->orderByDesc('id')->get(),
        ]);
    }
}

