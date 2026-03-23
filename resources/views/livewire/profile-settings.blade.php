<div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-zinc-100 flex items-center justify-center text-zinc-500 font-semibold overflow-hidden">
                @php
                    $initial = strtoupper(substr($displayName ?: 'U', 0, 1));
                    $previewUrl = $photo?->temporaryUrl() ?: $photoUrl;
                @endphp
                @if($previewUrl)
                    <img src="{{ $previewUrl }}" alt="Profile photo" class="w-full h-full object-cover">
                @else
                    <span class="text-xl">{{ $initial }}</span>
                @endif
            </div>

            <div class="space-y-0.5">
                <div class="text-sm text-zinc-600">{{ $roleLabel }} Account</div>
                <div class="text-lg font-semibold text-zinc-900">{{ $displayName }}</div>
                <div class="text-xs text-zinc-500">{{ $displayEmail }}</div>
            </div>
        </div>

        @if($statusMessage !== '')
            <div class="text-sm font-medium {{ str_contains($statusMessage, 'success') ? 'text-green-700' : 'text-zinc-700' }}">
                {{ $statusMessage }}
            </div>
        @endif
    </div>

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Profile image</label>
                    <input type="file"
                           wire:model="photo"
                           accept="image/*"
                           class="w-full text-sm text-zinc-600">
                    @error('photo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-zinc-500">
                        Leave empty to keep the current image.
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Change password</label>
                    <p class="text-xs text-zinc-500">
                        Email and name are locked. Enter a new password to update it.
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Current password</label>
                    <input type="password"
                           wire:model.defer="current_password"
                           class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                    @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">New password</label>
                    <input type="password"
                           wire:model.defer="password"
                           class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                    @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Confirm new password</label>
                    <input type="password"
                           wire:model.defer="password_confirmation"
                           class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm">
                    @error('password_confirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="px-8 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-lg shadow-zinc-900/10">
                Save Changes
            </button>
        </div>
    </form>
</div>

