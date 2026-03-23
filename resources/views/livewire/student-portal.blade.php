<div class="space-y-8">
    <div class="space-y-3">
        <h2 class="text-2xl font-bold tracking-tight">My Details & Grades</h2>
        <p class="text-sm text-zinc-600">
            View your student information and term-wise grades.
        </p>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-zinc-100 border border-zinc-200 overflow-hidden flex items-center justify-center">
                    @if($student->photo_url)
                        <img src="{{ $student->photo_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <span class="text-sm font-bold text-zinc-500">
                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-lg font-bold text-zinc-900">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </p>
                    <p class="text-sm text-zinc-600">
                        Section: <span class="font-semibold">{{ $student->section }}</span>
                    </p>
                    <p class="text-sm text-zinc-600">
                        Email: <span class="font-semibold">{{ $student->email ?? '—' }}</span>
                    </p>
                </div>
            </div>

            <div class="text-sm text-zinc-600">
                Adviser: <span class="font-semibold">{{ $adviser?->name ?? '—' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-zinc-50 border border-zinc-200 p-4 space-y-1">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Birthdate</p>
                <p class="text-sm font-semibold text-zinc-800">
                    {{ optional($student->birthdate)->format('M d, Y') }}
                </p>
            </div>
            <div class="rounded-2xl bg-zinc-50 border border-zinc-200 p-4 space-y-1 md:col-span-2">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Address</p>
                <p class="text-sm font-semibold text-zinc-800">
                    {{ $student->address }}
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($terms as $term)
            <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-sm font-bold text-zinc-900">{{ $term->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $term->school_year ?? '' }}</p>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 bg-zinc-100 px-3 py-1 rounded-full">
                        Term Grades
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse {{ $supportsGradeComponents ? 'min-w-[960px]' : 'min-w-[640px]' }}">
                        <thead>
                        <tr class="bg-zinc-100/50 border-b border-zinc-200">
                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Subject</th>
                            @if($supportsGradeComponents)
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Prelim</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Midterm</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Final exam</th>
                            @endif
                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">{{ $supportsGradeComponents ? 'Final grade' : 'Grade' }}</th>
                            <th class="px-6 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Remarks</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                        @php
                            $termRows = $rowsByTerm->get($term->id, collect());
                            $fmt = fn ($v) => $v !== null && $v !== '' ? number_format((float) $v, 2) : '—';
                        @endphp

                        @forelse($termRows as $row)
                            @php
                                $enrollmentStatusNormalized = strtolower(trim((string) ($row->enrollmentStatus ?? '')));
                                $g = $row->grade;
                                $pub = $g && $g->isPublishedToStudent();
                            @endphp
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-6 py-3 font-semibold text-zinc-900">
                                    <div>{{ $row->subject?->name ?? '—' }}</div>
                                    @if($enrollmentStatusNormalized !== 'approved' && ! $row->grade)
                                        <div class="text-xs text-zinc-500 mt-1">Enrollment: {{ $row->enrollmentStatus ?? '—' }}</div>
                                    @endif
                                </td>
                                @if($supportsGradeComponents)
                                    <td class="px-6 py-3 text-sm text-zinc-700 tabular-nums">{{ $pub ? $fmt($g->prelim) : '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-zinc-700 tabular-nums">{{ $pub ? $fmt($g->midterm) : '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-zinc-700 tabular-nums">{{ $pub ? $fmt($g->final_exam) : '—' }}</td>
                                @endif
                                <td class="px-6 py-3 text-sm text-zinc-700">
                                    @if($g)
                                        @if($pub && $g->grade !== null)
                                            <span class="tabular-nums font-medium">{{ number_format((float) $g->grade, 2) }}</span>
                                        @elseif($pub)
                                            Not yet graded
                                        @elseif($g->grade !== null)
                                            Awaiting program chair approval
                                        @else
                                            Not yet graded
                                        @endif
                                    @else
                                        @if($enrollmentStatusNormalized !== 'approved')
                                            Pending approval
                                        @else
                                            Not yet graded
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-zinc-600">
                                    @if($g && $pub)
                                        {{ $g->remarks ?? '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $supportsGradeComponents ? 6 : 3 }}" class="px-6 py-10 text-center text-zinc-400 text-sm">
                                    No enrolled subjects for this term.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

