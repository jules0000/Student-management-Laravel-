<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\Program;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentsTable extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $sectionFilter = 'All';
    public ?Student $editing = null;
    public bool $showModal = false;
    public ?int $confirmingDeleteId = null;

    public string $importStatus = '';

    // Students importer reads CSV via fgetcsv(), so validate CSV uploads here.
    #[Validate('nullable|file|mimes:csv,txt|max:20480')]
    public $importFile;

    public array $form = [
        'first_name' => '',
        'middle_name' => '',
        'last_name' => '',
        'birthdate' => '',
        'section' => '',
        'address' => '',
        'photo' => null,
    ];

    public function mount(): void
    {
        $this->sectionFilter = 'All';
    }

    public function updated($field): void
    {
        if (str_starts_with($field, 'form.')) {
            $this->validateOnly($field, $this->rules());
        }
    }

    protected function rules(): array
    {
        return [
            'form.first_name' => ['required', 'string', 'max:255'],
            'form.middle_name' => ['nullable', 'string', 'max:255'],
            'form.last_name' => ['required', 'string', 'max:255'],
            'form.birthdate' => ['required', 'date'],
            'form.section' => ['required', 'string', 'max:255'],
            'form.address' => ['required', 'string'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editing = null;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $student = Student::findOrFail($id);
        $this->editing = $student;
        $this->form = [
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name ?? '',
            'last_name' => $student->last_name,
            'birthdate' => optional($student->birthdate)->format('Y-m-d'),
            'section' => $student->section,
            'address' => $student->address,
            'photo' => null,
        ];
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules())['form'];

        $defaultProgramId = Program::query()->orderBy('id')->value('id');

        $photoDataUrl = $this->editing?->photo_url;

        if ($this->form['photo']) {
            $file = $this->form['photo'];
            $mime = $file->getMimeType();
            $contents = file_get_contents($file->getRealPath());
            $photoDataUrl = 'data:' . $mime . ';base64,' . base64_encode($contents);
        }

        if ($this->editing) {
            $this->editing->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?: null,
                'last_name' => $validated['last_name'],
                'birthdate' => $validated['birthdate'],
                'section' => $validated['section'],
                'address' => $validated['address'],
                'photo_url' => $photoDataUrl,
            ]);
        } else {
            Student::create([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?: null,
                'last_name' => $validated['last_name'],
                'birthdate' => $validated['birthdate'],
                'section' => $validated['section'],
                'address' => $validated['address'],
                'photo_url' => $photoDataUrl,
                'program_id' => $defaultProgramId,
            ]);
        }

        $this->dispatch('saved');
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        if ($this->confirmingDeleteId) {
            Student::whereKey($this->confirmingDeleteId)->delete();
            $this->confirmingDeleteId = null;
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->sectionFilter = 'All';
    }

    public function export()
    {
        $fileName = 'students_export_' . now()->toDateString() . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['first_name', 'middle_name', 'last_name', 'birthdate', 'section', 'address']);

            Student::chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $student) {
                    fputcsv($handle, [
                        $student->first_name,
                        $student->middle_name,
                        $student->last_name,
                        optional($student->birthdate)->format('Y-m-d'),
                        $student->section,
                        $student->address,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(): void
    {
        // Basic CSV import.
        // Accepts slightly different header variants (ex: `First Name` vs `first_name`)
        // and trims values to avoid silently skipping every row.
        $this->validateOnly('importFile');

        if (! $this->importFile) {
            return;
        }

        $this->importStatus = '';

        $path = $this->importFile->getRealPath();
        if (! $path) {
            return;
        }

        if (($handle = fopen($path, 'r')) === false) {
            return;
        }

        $defaultProgramId = Program::query()->orderBy('id')->value('id');

        $requiredKeys = ['first_name', 'last_name', 'birthdate', 'section', 'address'];
        $canonicalHeaderMap = [
            'firstname' => 'first_name',
            'first_name' => 'first_name',
            'first' => 'first_name',
            'middlename' => 'middle_name',
            'middle_name' => 'middle_name',
            'middle' => 'middle_name',
            'lastname' => 'last_name',
            'last_name' => 'last_name',
            'last' => 'last_name',
            'birthdate' => 'birthdate',
            'birth_date' => 'birthdate',
            'birth' => 'birthdate',
            'section' => 'section',
            'address' => 'address',
        ];

        $header = null;
        $headerRowIndex = 0;
        // Some CSVs might contain an extra title row before the real header.
        for ($i = 0; $i < 5; $i++) {
            $rawHeader = fgetcsv($handle);
            if ($rawHeader === false || $rawHeader === null) {
                break;
            }

            $headerRowIndex = $i + 1;
            $normalized = array_map(function ($value) {
                $value = (string) $value;
                // Strip UTF-8 BOM if present.
                $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
                $value = strtolower(trim($value));
                $value = str_replace(['-', ' '], '_', $value);
                $value = preg_replace('/[^a-z0-9_]/', '', $value);
                $value = preg_replace('/_+/', '_', $value);
                return $value;
            }, $rawHeader);

            $canonical = array_map(function ($value) use ($canonicalHeaderMap) {
                if ($value === '') {
                    return '';
                }

                if (isset($canonicalHeaderMap[$value])) {
                    return $canonicalHeaderMap[$value];
                }

                // Tolerate headers like `Student First Name` or `Birth Date (YYYY-MM-DD)`.
                if (str_contains($value, 'first') && str_contains($value, 'name')) {
                    return 'first_name';
                }
                if ((str_contains($value, 'middle') && str_contains($value, 'name')) || $value === 'middle') {
                    return 'middle_name';
                }
                if (str_contains($value, 'last') && str_contains($value, 'name')) {
                    return 'last_name';
                }
                if (str_contains($value, 'birth') && str_contains($value, 'date')) {
                    return 'birthdate';
                }
                if (str_contains($value, 'section')) {
                    return 'section';
                }
                if (str_contains($value, 'address')) {
                    return 'address';
                }

                return $value;
            }, $normalized);

            $foundRequired = count(array_intersect($requiredKeys, $canonical)) === count($requiredKeys);
            if ($foundRequired) {
                $header = $canonical;
                break;
            }
        }

        if (! $header) {
            fclose($handle);
            $this->importStatus = 'Import failed: CSV header not recognized. Use columns: first_name, middle_name, last_name, birthdate, section, address.';
            $this->importFile = null;
            return;
        }

        $inserted = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === null) {
                continue;
            }

            // Skip fully-empty rows.
            $allEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            // Normalize row length to match header.
            $row = array_pad($row, count($header), null);
            $row = array_slice($row, 0, count($header));

            $data = array_combine($header, $row);

            if (! is_array($data)) {
                $skipped++;
                continue;
            }

            $data = array_map(function ($value) {
                if (! is_string($value)) {
                    return $value;
                }

                return trim($value);
            }, $data);

            // Normalize birthdate to Y-m-d when possible.
            if (! empty($data['birthdate'])) {
                try {
                    $data['birthdate'] = \Carbon\CarbonImmutable::parse($data['birthdate'])->format('Y-m-d');
                } catch (\Throwable) {
                    // Keep original value so validator can fail and row will be skipped.
                }
            }

            $validator = Validator::make($data, [
                'first_name' => 'required',
                'middle_name' => 'nullable',
                'last_name' => 'required',
                'birthdate' => 'required|date',
                'section' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            Student::create([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'birthdate' => $data['birthdate'],
                'section' => $data['section'],
                'address' => $data['address'],
                'program_id' => $defaultProgramId,
            ]);

            $inserted++;
            if ($inserted % 50 === 0) {
                // Help Livewire keep the UI responsive for large uploads.
                usleep(100000);
            }
        }

        fclose($handle);
        $this->importFile = null;

        $this->importStatus = sprintf('%d student(s) imported. %d row(s) skipped (invalid or empty data).', $inserted, $skipped);
    }

    protected function resetForm(): void
    {
        $this->form = [
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'birthdate' => '',
            'section' => '',
            'address' => '',
            'photo' => null,
        ];
    }

    public function getSectionsProperty()
    {
        // Trim stored section values so dropdown matching is consistent.
        return Student::query()
            ->select('section')
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->map(fn ($section) => trim((string) $section))
            ->filter(fn ($section) => $section !== '')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function getSectionCountsProperty()
    {
        // Keep counts aligned with the trimmed dropdown values.
        return Student::query()
            ->select('section')
            ->get()
            ->map(fn ($row) => trim((string) ($row->section ?? '')))
            ->filter(fn ($section) => $section !== '')
            ->groupBy(fn ($section) => $section)
            ->map
            ->count()
            ->sortKeys()
            ->toArray();
    }

    public function render()
    {
        $query = Student::query();

        $searchTerm = trim($this->search);

        if ($searchTerm !== '') {
            $q = '%' . $searchTerm . '%';
            $query->where(function ($builder) use ($q) {
                $builder
                    ->where('first_name', 'like', $q)
                    ->orWhere('middle_name', 'like', $q)
                    ->orWhere('last_name', 'like', $q)
                    ->orWhere('section', 'like', $q)
                    ->orWhere('address', 'like', $q);
            });
        }

        $sectionFilter = trim($this->sectionFilter);
        if ($sectionFilter !== 'All' && $sectionFilter !== '') {
            $query->where('section', $sectionFilter);
        }

        return view('livewire.students-table', [
            'students' => $query->latest()->get(),
        ]);
    }
}

