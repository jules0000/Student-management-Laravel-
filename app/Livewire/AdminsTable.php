<?php

namespace App\Livewire;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AdminsTable extends Component
{
    public bool $canManageAdmins = false;

    public bool $showModal = false;
    public ?Admin $editing = null;
    public ?int $confirmingDeleteId = null;

    public array $form = [
        'email' => '',
        'name' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function mount(): void
    {
        $this->canManageAdmins = $this->isSuperAdmin();
    }

    private function isSuperAdmin(): bool
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            return false;
        }

        // RBAC (lightweight): only the seeded super admin can manage other admins.
        if (($admin->email ?? null) === 'admin@example.com') {
            return true;
        }

        return str_contains((string) ($admin->name ?? ''), 'Super');
    }

    protected function rules(): array
    {
        $emailRule = Rule::unique('admins', 'email')
            ->ignore($this->editing?->id);

        $passwordRules = $this->editing
            ? ['nullable', 'string', 'min:8']
            : ['required', 'string', 'min:8', 'confirmed'];

        $passwordConfirmationRules = $this->editing
            ? ['nullable', 'string', 'min:8']
            : ['required', 'string', 'min:8'];

        return [
            'form.email' => ['required', 'email', 'max:255', $emailRule],
            'form.name' => ['required', 'string', 'max:255'],
            'form.password' => $passwordRules,
            'form.password_confirmation' => $passwordConfirmationRules,
        ];
    }

    public function create(): void
    {
        abort_if(! $this->canManageAdmins, 403);

        $this->editing = null;
        $this->showModal = true;

        $this->form = [
            'email' => '',
            'name' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function edit(int $id): void
    {
        abort_if(! $this->canManageAdmins, 403);

        $this->editing = Admin::findOrFail($id);
        $this->showModal = true;
        $this->confirmingDeleteId = null;

        $this->form = [
            'email' => $this->editing->email,
            'name' => $this->editing->name,
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function save(): void
    {
        abort_if(! $this->canManageAdmins, 403);

        $validated = $this->validate($this->rules())['form'];

        if ($this->editing) {
            $update = [
                'email' => $validated['email'],
                'name' => $validated['name'],
            ];

            if (! empty($validated['password'])) {
                $update['password'] = Hash::make($validated['password']);
            }

            $this->editing->update($update);
        } else {
            Admin::create([
                'email' => $validated['email'],
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
            ]);
        }

        $this->showModal = false;
        $this->editing = null;
        $this->form = [
            'email' => '',
            'name' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function confirmDelete(int $id): void
    {
        abort_if(! $this->canManageAdmins, 403);
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        abort_if(! $this->canManageAdmins, 403);
        if (! $this->confirmingDeleteId) {
            return;
        }

        $admin = Admin::findOrFail($this->confirmingDeleteId);
        $current = auth('admin')->user();

        // Prevent deleting the currently logged-in admin and the seeded super admin.
        $isProtectedSuperAdmin = ($admin->email ?? null) === 'admin@example.com'
            || stripos((string) ($admin->name ?? ''), 'super') !== false;

        if (($current?->id) === $admin->id || $isProtectedSuperAdmin) {
            $this->confirmingDeleteId = null;
            return;
        }

        $admin->delete();
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        return view('livewire.admins-table', [
            'admins' => Admin::query()->orderByDesc('id')->get(),
        ]);
    }
}

