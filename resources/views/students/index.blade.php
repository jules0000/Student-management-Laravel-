@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Student Directory</h2>
                <p class="text-zinc-600 mt-1">Manage, edit, and monitor all student records.</p>
            </div>
        </div>

        <livewire:students-table />
    </div>
@endsection

