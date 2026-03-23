@extends('layouts.app')

@section('title', 'Students Details')

@section('content')
    @php
        $selectedStudentId = request()->query('student_id');
        $selectedTermId = request()->query('term_id');
        $selectedSubjectId = request()->query('subject_id');

        // Convert empty strings to null so Livewire can keep the default selection.
        $selectedStudentId = is_numeric($selectedStudentId) ? (int) $selectedStudentId : null;
        $selectedTermId = is_numeric($selectedTermId) ? (int) $selectedTermId : null;
        $selectedSubjectId = is_numeric($selectedSubjectId) ? (int) $selectedSubjectId : null;
    @endphp
    <livewire:students-details
        wire:poll.5s
        :selected-student-id="$selectedStudentId"
        :selected-term-id="$selectedTermId"
        :selected-subject-id="$selectedSubjectId"
    />
@endsection

