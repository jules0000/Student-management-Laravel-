@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8">
        @if(($role ?? '') === 'admin')
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Dashboard Overview</h2>
                    <p class="text-zinc-600 mt-1">Summary of your student records.</p>
                </div>
                <div class="px-4 py-2 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 flex items-center gap-2 shadow-sm">
                    <span class="w-4 h-4 rounded-full bg-zinc-900"></span>
                    {{ now()->format('F j, Y') }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-zinc-900 text-white p-6 rounded-3xl border border-zinc-900 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-300 uppercase tracking-widest">Total Students</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $totalStudents }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Active Sections</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $activeSections }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">New This Week</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $newThisWeek }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <h3 class="text-sm font-semibold mb-4">Students by Section</h3>
                    <div class="space-y-2">
                        @forelse($sectionCounts as $section => $count)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium">{{ $section }}</span>
                                    <span class="text-zinc-500">{{ $count }} students</span>
                                </div>
                                <div class="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-zinc-900 rounded-full"
                                        style="width: {{ $sectionMaxCount > 0 ? round(($count / $sectionMaxCount) * 100, 1) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No data yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold">Recent Students</h3>
                        <a href="{{ route('students.index') }}" class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">View All</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentStudents as $student)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-zinc-100 overflow-hidden">
                                    @if($student->photo_url)
                                        <img src="{{ $student->photo_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-zinc-400 text-xs font-medium">
                                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $student->first_name }} {{ $student->last_name }}</p>
                                    <p class="text-xs text-zinc-500 truncate">{{ $student->section }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No students yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @elseif(($role ?? '') === 'instructor')
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Instructor Snapshot</h2>
                    <p class="text-zinc-600 mt-1">Your advisees and pending requests.</p>
                </div>
                <a href="{{ route('instructor.portal') }}" class="text-xs font-semibold text-zinc-500 uppercase tracking-widest hover:text-zinc-900">
                    Go to Grades & Advising
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-zinc-900 text-white p-6 rounded-3xl border border-zinc-900 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-300 uppercase tracking-widest">Your Advisees</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $totalAdvisees }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Pending Requests</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $pendingEnrollmentCount }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Pending Grades</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $pendingGradeCount }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <h3 class="text-sm font-semibold mb-4">Students by Section</h3>
                    <div class="space-y-2">
                        @forelse($sectionCounts as $section => $count)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium">{{ $section }}</span>
                                    <span class="text-zinc-500">{{ $count }} students</span>
                                </div>
                                <div class="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-zinc-900 rounded-full"
                                        style="width: {{ $sectionMaxCount > 0 ? round(($count / $sectionMaxCount) * 100, 1) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No advisees yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold">Recent Students</h3>
                        <a href="{{ route('students.details') }}" class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">View</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentStudents as $student)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-zinc-100 overflow-hidden">
                                    @if($student->photo_url)
                                        <img src="{{ $student->photo_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-zinc-400 text-xs font-medium">
                                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $student->first_name }} {{ $student->last_name }}</p>
                                    <p class="text-xs text-zinc-500 truncate">{{ $student->section }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No advisees yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @elseif(($role ?? '') === 'program_chair')
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Program Chair Dashboard</h2>
                    <p class="text-zinc-600 mt-1">Pending enrollments and grade approvals.</p>
                </div>
                <a href="{{ route('chair.portal') }}" class="text-xs font-semibold text-zinc-500 uppercase tracking-widest hover:text-zinc-900">
                    Go to Program Chair Portal
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-zinc-900 text-white p-6 rounded-3xl border border-zinc-900 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-300 uppercase tracking-widest">Students in Your Programs</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $totalStudents }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Pending Enrollments</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $pendingEnrollmentCount }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Pending Grades</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $pendingGradeCount }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <h3 class="text-sm font-semibold mb-4">Students by Section</h3>
                    <div class="space-y-2">
                        @forelse($sectionCounts as $section => $count)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium">{{ $section }}</span>
                                    <span class="text-zinc-500">{{ $count }} students</span>
                                </div>
                                <div class="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-zinc-900 rounded-full"
                                        style="width: {{ $sectionMaxCount > 0 ? round(($count / $sectionMaxCount) * 100, 1) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No students yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold">Recent Students</h3>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentStudents as $student)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-zinc-100 overflow-hidden">
                                    @if($student->photo_url)
                                        <img src="{{ $student->photo_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-zinc-400 text-xs font-medium">
                                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $student->first_name }} {{ $student->last_name }}</p>
                                    <p class="text-xs text-zinc-500 truncate">{{ $student->section }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No students yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @elseif(($role ?? '') === 'student')
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Your Grades Overview</h2>
                    <p class="text-zinc-600 mt-1">A quick snapshot of approved grades.</p>
                </div>
                <a href="{{ route('student.portal') }}" class="text-xs font-semibold text-zinc-500 uppercase tracking-widest hover:text-zinc-900">
                    Go to Student Portal
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-zinc-900 text-white p-6 rounded-3xl border border-zinc-900 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-300 uppercase tracking-widest">Approved Grades</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $approvedGradesCount }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Terms With Grades</p>
                    <p class="text-3xl font-bold tracking-tight">{{ $termsCount }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm space-y-3">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Your Section</p>
                    <p class="text-3xl font-bold tracking-tight">{{ auth('student')->user()?->section ?? '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <h3 class="text-sm font-semibold mb-4">Recent Enrolled Subjects</h3>
                    <div class="space-y-2">
                        @forelse($recentEnrollments as $row)
                            <div class="flex items-center justify-between text-sm p-3 rounded-xl hover:bg-zinc-50">
                                <div class="min-w-0">
                                    <p class="font-medium truncate">
                                        {{ $row->subject?->name ?? '—' }}
                                    </p>
                                    <p class="text-xs text-zinc-500 truncate">
                                        {{ $row->term?->name ?? '' }} &middot; {{ $row->subject?->code ?? '' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    @if($row->grade)
                                        @if($row->grade->isPublishedToStudent() && $row->grade->grade !== null)
                                            <p class="text-sm font-bold text-zinc-900 tabular-nums">
                                                {{ number_format((float) $row->grade->grade, 2) }}
                                            </p>
                                            @if(!empty($supportsGradeComponents))
                                                <p class="text-[11px] text-zinc-500 mt-0.5 tabular-nums">
                                                    P {{ $row->grade->prelim !== null ? number_format((float) $row->grade->prelim, 2) : '—' }}
                                                    &middot; M {{ $row->grade->midterm !== null ? number_format((float) $row->grade->midterm, 2) : '—' }}
                                                    &middot; F {{ $row->grade->final_exam !== null ? number_format((float) $row->grade->final_exam, 2) : '—' }}
                                                </p>
                                            @endif
                                            <p class="text-xs text-zinc-500 truncate">{{ $row->grade->remarks ?? '—' }}</p>
                                        @elseif($row->grade->isPublishedToStudent())
                                            <p class="text-sm font-bold text-zinc-900">Not yet graded</p>
                                            <p class="text-xs text-zinc-500 truncate">—</p>
                                        @elseif($row->grade->grade !== null)
                                            <p class="text-sm font-bold text-zinc-900">Awaiting program chair approval</p>
                                            <p class="text-xs text-zinc-500 truncate">—</p>
                                        @else
                                            <p class="text-sm font-bold text-zinc-900">Not yet graded</p>
                                            <p class="text-xs text-zinc-500 truncate">—</p>
                                        @endif
                                    @else
                                        @if(($row->enrollmentStatus ?? null) !== 'approved')
                                            <p class="text-sm font-bold text-zinc-900">Pending approval</p>
                                            <p class="text-xs text-zinc-500 truncate">—</p>
                                        @else
                                            <p class="text-sm font-bold text-zinc-900">Not yet graded</p>
                                            <p class="text-xs text-zinc-500 truncate">—</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">No enrolled subjects yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
                    <h3 class="text-sm font-semibold mb-4">Your Adviser</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-400 text-sm font-medium">
                            {{ strtoupper(substr(($adviser?->name ?? 'A'), 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ $adviser?->name ?? '—' }}</p>
                            <p class="text-xs text-zinc-500 truncate">{{ $adviser?->email ?? '' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Tip</p>
                        <p class="text-sm text-zinc-700 mt-2">
                            For detailed term-wise grades, open the Student Portal.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white border border-zinc-200 rounded-3xl p-6 text-sm text-zinc-600">
                Role not recognized for the current user.
            </div>
        @endif
    </div>
@endsection

