<div class="space-y-6" wire:poll.5s>
    <div class="space-y-3">
        <h2 class="text-2xl font-bold tracking-tight">Class Sessions & Attendance</h2>
        <p class="text-sm text-zinc-600">
            View enrolled students, session/class details, and attendance status per session.
        </p>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject</label>
                <select
                    wire:model="selectedSubjectId"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                >
                    <option value="">Select subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}{{ $subject->code ? " ($subject->code)" : '' }}</option>
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
                        <option value="{{ $term->id }}">{{ $term->name }}{{ $term->school_year ? " ($term->school_year)" : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Session</label>
                <select
                    wire:model="selectedSessionId"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                >
                    @if($sessions->isEmpty())
                        <option value="">No sessions found</option>
                    @else
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">
                                {{ $session->start_at?->format('M d, Y · g:i A') ?? '—' }} · {{ $session->classroom?->room_label ?? '—' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        @if(! $selectedSession || $enrollments->isEmpty())
            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                <p class="text-sm text-zinc-700">
                    Select a subject and term. Then create/schedule class sessions for that subject/term.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="space-y-1">
                        <h3 class="text-sm font-semibold">Enrolled Students</h3>
                        <p class="text-sm text-zinc-600">
                            Approved roster for this subject/term.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl border border-zinc-200 p-4">
                        @if($enrollments->isEmpty())
                            <p class="text-sm text-zinc-700">No enrolled students yet.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[640px]">
                                    <thead>
                                        <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                            <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Section</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200">
                                        @foreach($enrollments as $enrollment)
                                            @php $student = $enrollment->student; @endphp
                                            @continue(! $student)
                                            <tr class="hover:bg-zinc-50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <div class="font-bold text-sm text-zinc-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-sm text-zinc-600">{{ $student->section }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="space-y-1">
                        <h3 class="text-sm font-semibold">Session Details & Attendance</h3>
                        <p class="text-sm text-zinc-600">
                            {{ $attendanceSummary['marked'] }} marked · {{ $attendanceSummary['present'] }} present · {{ $attendanceSummary['failed_face'] }} no face · {{ $attendanceSummary['failed_face_match'] }} face mismatch · {{ $attendanceSummary['rejected_location'] }} rejected (geo)
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl border border-zinc-200 p-4 space-y-4">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-zinc-900">
                                    {{ $selectedSession->start_at?->format('M d, Y · g:i A') ?? '—' }}
                                </p>
                                <p class="text-xs text-zinc-500 mt-1">
                                    Classroom: <span class="font-semibold text-zinc-700">{{ $selectedSession->classroom?->room_label ?? '—' }}</span> · Geofence radius:
                                    <span class="font-semibold text-zinc-700">{{ $selectedSession->classroom?->radius_m ?? 100 }}m</span>
                                </p>
                                <p class="text-xs text-zinc-500 mt-1">
                                    Window: {{ $selectedSession->start_at?->copy()->subMinutes(10)?->format('g:i A') ?? '—' }} - {{ $selectedSession->marking_end_at?->copy()->addMinutes(30)?->format('g:i A') ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[920px]">
                                <thead>
                                    <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Status</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Match Δ</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Face</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Capture</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Geo Match</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Distance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach($enrollments as $enrollment)
                                        @php
                                            $student = $enrollment->student;
                                            if (! $student) continue;
                                            $log = $attendanceByStudentId->get($student->id);
                                        @endphp
                                        <tr class="hover:bg-zinc-50 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-sm text-zinc-900">
                                                    {{ $student->first_name }} {{ $student->last_name }}
                                                </div>
                                                <div class="text-xs text-zinc-500 mt-0.5">{{ $student->section }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(! $log)
                                                    <span class="text-sm text-zinc-600">not marked</span>
                                                @else
                                                    @php
                                                        $status = $log->status;
                                                        $color = match ($status) {
                                                            'present' => 'text-emerald-700',
                                                            'rejected_location' => 'text-rose-700',
                                                            'failed_face_match' => 'text-violet-700',
                                                            default => 'text-amber-700',
                                                        };
                                                    @endphp
                                                    <span class="text-sm font-semibold {{ $color }}">{{ $status }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(! $log)
                                                    <span class="text-sm text-zinc-600">—</span>
                                                @else
                                                    <span class="text-sm text-zinc-700" title="DeepFace embedding distance (lower is better)">
                                                        {{ $log->face_match_distance !== null ? number_format((float) $log->face_match_distance, 4) : '—' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(! $log)
                                                    <span class="text-sm text-zinc-600">—</span>
                                                @else
                                                    <span class="text-sm font-semibold {{ $log->face_detected ? 'text-emerald-700' : 'text-amber-700' }}">
                                                        {{ $log->face_detected ? 'yes' : 'no' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 align-middle">
                                                @if(! $log || ! $log->face_image_url)
                                                    <span class="text-sm text-zinc-600">—</span>
                                                @else
                                                    <a
                                                        href="{{ $log->face_image_url }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex rounded-lg border border-zinc-200 bg-zinc-50 overflow-hidden hover:ring-2 hover:ring-zinc-900/10 transition-shadow"
                                                        title="Open capture in new tab"
                                                    >
                                                        <img
                                                            src="{{ $log->face_image_url }}"
                                                            alt="Attendance capture for {{ $student->first_name }} {{ $student->last_name }}"
                                                            class="h-14 w-14 object-cover"
                                                            loading="lazy"
                                                        />
                                                    </a>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(! $log)
                                                    <span class="text-sm text-zinc-600">—</span>
                                                @else
                                                    <span class="text-sm font-semibold {{ $log->location_match ? 'text-emerald-700' : 'text-rose-700' }}">
                                                        {{ $log->location_match ? 'match' : 'mismatch' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(! $log)
                                                    <span class="text-sm text-zinc-600">—</span>
                                                @else
                                                    <span class="text-sm text-zinc-700">
                                                        {{ $log->distance_m !== null ? number_format((float) $log->distance_m, 1) . 'm' : '—' }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

