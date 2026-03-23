<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold tracking-tight">Instructors & Advisers</h2>
            <p class="text-sm text-zinc-600">
                View instructor subjects, advising assignments, students in advising class, and grade information.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-zinc-100/50 border-b border-zinc-200">
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Instructor</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Subjects</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Advising Class</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($instructors as $instructor)
                        @php
                            $subjectsCnt = (int) ($subjectsCountByInstructorId[$instructor->id] ?? 0);
                            $adviseesCnt = (int) ($adviseesCountByInstructorId[$instructor->id] ?? 0);
                        @endphp
                        <tr class="group hover:bg-zinc-50 transition-colors">
                            <td class="px-6 py-3">
                                <div class="font-semibold text-zinc-900">{{ $instructor->name ?? '—' }}</div>
                                <div class="text-xs text-zinc-500 font-bold uppercase tracking-widest">{{ $instructor->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-3">
                                @if($subjectsCnt > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700">
                                        {{ $subjectsCnt }} subject(s)
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($adviseesCnt > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                        {{ $adviseesCnt }} student(s)
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-400">Not an adviser</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a
                                    href="{{ route('chair.instructor.details', $instructor->id) }}"
                                    class="px-4 py-2 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] inline-flex items-center justify-center">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-zinc-400 text-sm">
                                No instructors found for your program(s).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

