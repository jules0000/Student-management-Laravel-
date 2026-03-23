@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold tracking-tight">Notifications</h2>
        <div class="space-y-4">
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    •
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-sm">System Ready</h3>
                        <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest">Just now</span>
                    </div>
                    <p class="text-sm text-zinc-600 mt-1">
                        Your Laravel + Livewire student admin panel is running.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

