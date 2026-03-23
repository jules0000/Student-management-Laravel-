<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\InstructorAuthController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Models\Instructor;
use App\Livewire\Dashboard;
use App\Livewire\StudentsTable;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Student auth
Route::get('/student/login', fn () => redirect()->route('login'))->name('student.login');
Route::post('/student/login', [AdminAuthController::class, 'login'])->name('student.login.attempt');
Route::post('/student/logout', [AdminAuthController::class, 'logout'])->name('student.logout');

// Instructor auth (login uses the main /login page; logout uses /logout)

Route::get('/', Dashboard::class)
    ->middleware('dashboard_any_role')
    ->name('dashboard');

Route::middleware('admin')->group(function () {
    Route::get('/students', function () {
        return view('students.index');
    })->name('students.index');

    Route::get('/notifications', function () {
        return view('notifications.index');
    })->name('notifications.index');
});

Route::middleware('dashboard_any_role')->group(function () {
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');
});

Route::middleware('student')->group(function () {
    Route::get('/student', function () {
        return view('student.portal');
    })->name('student.portal');

    Route::post('/student/attendance/mark', [AttendanceController::class, 'mark'])
        ->name('attendance.mark');
});

Route::middleware('instructor')->group(function () {
    Route::get('/instructor', function () {
        return view('instructor.portal');
    })->name('instructor.portal');

    Route::get('/instructor/classes', function () {
        return view('instructor.classes');
    })->name('instructor.classes');
});

// Students details view (admins + instructors)
Route::middleware('admin_or_instructor')->group(function () {
    Route::get('/students/details', function () {
        return view('students.details');
    })->name('students.details');
});

Route::middleware('program_chair')->group(function () {
    Route::get('/chair', function () {
        return view('chair.portal');
    })->name('chair.portal');

    Route::get('/chair/instructors', function () {
        return view('chair.instructors');
    })->name('chair.instructors');

    Route::get('/chair/instructors/{instructor}', function (Instructor $instructor) {
        return view('chair.instructor-details', ['instructor' => $instructor]);
    })->name('chair.instructor.details');
});

