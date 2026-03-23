@extends('layouts.app')

@section('title', 'Instructor Details')

@section('content')
    <livewire:chair-instructor-details :instructorId="$instructor->id" />
@endsection

