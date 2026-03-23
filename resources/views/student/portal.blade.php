@extends('layouts.app')

@section('title', 'Student Portal')

@section('content')
    <livewire:student-attendance />

    <div class="mt-8">
    <livewire:student-portal wire:poll.5s />
    </div>
@endsection

