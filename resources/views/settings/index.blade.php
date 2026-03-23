@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    @php
        $isAdmin = auth('admin')->check();
        $isStudent = auth('student')->check();
        $isInstructor = auth('instructor')->check();
        $isChair = auth('program_chair')->check();

        $profileName = $isAdmin
            ? (auth('admin')->user()->name ?? 'Admin User')
            : ($isStudent
                ? trim((auth('student')->user()->first_name ?? '') . ' ' . (auth('student')->user()->last_name ?? '')) ?: 'Student'
                : ($isInstructor
                    ? (auth('instructor')->user()->name ?? 'Instructor')
                    : ($isChair ? (auth('program_chair')->user()->name ?? 'Program Chair') : 'User')));

        $profileEmail = $isAdmin
            ? (auth('admin')->user()->email ?? 'admin@example.com')
            : ($isStudent
                ? (auth('student')->user()->email ?? '')
                : ($isInstructor
                    ? (auth('instructor')->user()->email ?? '')
                    : ($isChair ? (auth('program_chair')->user()->email ?? 'chair@example.com') : '')));

        $profileInitial = strtoupper(substr($profileName, 0, 1));

        $roleLabel = $isAdmin
            ? 'Admin'
            : ($isStudent ? 'Student' : ($isInstructor ? 'Instructor' : ($isChair ? 'Program Chair' : 'User')));

        $profileHeading = $isAdmin
            ? 'Admin Profile'
            : ($isStudent ? 'Student Profile' : ($isInstructor ? 'Instructor Profile' : 'Program Chair Profile'));

        $systemPreferencesText = $isChair
            ? 'Update your profile and manage adviser assignments.'
            : ($isAdmin
                ? 'Update your profile and manage credentials/adviser assignments.'
                : 'Update your profile (password + profile image).');
    @endphp

    <div class="max-w-6xl mx-auto space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">Settings</h2>
                <p class="text-sm text-zinc-600">
                    Manage credentials and adviser assignments for your campus workflow.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white">
                    {{ $roleLabel }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
            <section>
                <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-3">
                    {{ $profileHeading }}
                </h3>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-zinc-100 flex items-center justify-center text-zinc-500 font-semibold">
                        {{ $profileInitial }}
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ $profileName }}</p>
                        <p class="text-xs text-zinc-500">{{ $profileEmail }}</p>
                    </div>
                </div>
            </section>

            <section class="pt-4 border-t border-zinc-200">
                <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-3">System Preferences</h3>
                <p class="text-sm text-zinc-600">
                    {{ $systemPreferencesText }}
                </p>
            </section>
        </div>

        <div class="space-y-8">
            <div class="space-y-3">
                <h3 class="text-lg font-semibold tracking-tight">Profile Settings</h3>
                <p class="text-sm text-zinc-600">
                    Change your password and profile image. Email and name are locked.
                </p>
                <livewire:profile-settings />
            </div>

            @if($isAdmin || $isChair)
            <div class="space-y-3">
                <h3 class="text-lg font-semibold tracking-tight">Student Credentials</h3>
                <p class="text-sm text-zinc-600">
                    Create/update students' login email and password, and assign their adviser.
                </p>
                <livewire:student-credentials-table />
            </div>
            @endif

            @if($isAdmin)
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold tracking-tight">Admin Management</h3>
                    <p class="text-sm text-zinc-600">
                        Manage admin users (create and delete).
                    </p>
                    <livewire:admins-table />
                </div>

                <div class="space-y-3">
                    <h3 class="text-lg font-semibold tracking-tight">Instructor Credentials</h3>
                    <p class="text-sm text-zinc-600">
                        Manage instructor login credentials and the subjects they teach.
                    </p>
                    <livewire:instructor-credentials-table />
                </div>
            @elseif($isChair)
                <div class="bg-zinc-50 border border-zinc-200 rounded-3xl p-5">
                    <div class="text-sm font-semibold text-zinc-900">Chair focus</div>
                    <p class="text-sm text-zinc-600 mt-1">
                        Assign adviser students for a chosen instructor from the Instructor Details page.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

