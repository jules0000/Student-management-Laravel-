<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Models\Instructor;
use App\Models\ProgramChair;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileSettings extends Component
{
    use WithFileUploads;

    public $photo = null;

    public ?string $current_password = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;

    public string $statusMessage = '';

    private function getAuthenticatedAccount(): Admin|Student|Instructor|ProgramChair|null
    {
        if (auth('admin')->check()) {
            return auth('admin')->user();
        }

        if (auth('student')->check()) {
            return auth('student')->user();
        }

        if (auth('instructor')->check()) {
            return auth('instructor')->user();
        }

        if (auth('program_chair')->check()) {
            return auth('program_chair')->user();
        }

        return null;
    }

    private function roleLabel(Admin|Student|Instructor|ProgramChair $account): string
    {
        if ($account instanceof Admin) {
            return 'Admin';
        }

        if ($account instanceof Student) {
            return 'Student';
        }

        if ($account instanceof Instructor) {
            return 'Instructor';
        }

        return 'Program Chair';
    }

    private function displayName(Admin|Student|Instructor|ProgramChair $account): string
    {
        if ($account instanceof Student) {
            return trim(($account->first_name ?? '') . ' ' . ($account->last_name ?? '')) ?: 'Student';
        }

        return $account->name ?? 'User';
    }

    private function displayEmail(Admin|Student|Instructor|ProgramChair $account): string
    {
        return $account->email ?? '';
    }

    protected function rules(): array
    {
        $changingPassword = filled((string) $this->password);

        return [
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:20480'],
            'current_password' => [
                'nullable',
                'string',
                'min:8',
                Rule::requiredIf($changingPassword),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                Rule::requiredIf($changingPassword),
                'confirmed',
            ],
            'password_confirmation' => [
                'nullable',
                'string',
                'min:8',
                Rule::requiredIf($changingPassword),
            ],
        ];
    }

    public function save(): void
    {
        $account = $this->getAuthenticatedAccount();
        if (! $account) {
            abort(403);
        }

        $changingPassword = filled((string) $this->password);

        $validated = $this->validate($this->rules());

        $update = [];

        if ($changingPassword) {
            $currentOk = filled((string) $this->current_password)
                && filled((string) $account->password)
                && Hash::check((string) $this->current_password, $account->password);

            if (! $currentOk) {
                throw ValidationException::withMessages([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }

            $update['password'] = Hash::make((string) $this->password);
        }

        if ($this->photo) {
            $file = $this->photo;
            $mime = $file->getMimeType();
            $contents = file_get_contents($file->getRealPath());
            if (! $mime || $contents === false) {
                throw ValidationException::withMessages([
                    'photo' => 'Unable to read the selected image file.',
                ]);
            }

            $update['photo_url'] = 'data:' . $mime . ';base64,' . base64_encode($contents);
        }

        if (empty($update)) {
            $this->statusMessage = 'Nothing to update.';
            return;
        }

        $account->update($update);

        $this->reset([
            'photo',
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $this->statusMessage = 'Profile updated successfully.';
    }

    public function render()
    {
        $account = $this->getAuthenticatedAccount();
        if (! $account) {
            return view('livewire.profile-settings', [
                'roleLabel' => '',
                'displayName' => '',
                'displayEmail' => '',
                'photoUrl' => null,
            ]);
        }

        return view('livewire.profile-settings', [
            'roleLabel' => $this->roleLabel($account),
            'displayName' => $this->displayName($account),
            'displayEmail' => $this->displayEmail($account),
            'photoUrl' => $account->photo_url ?? null,
        ]);
    }
}

