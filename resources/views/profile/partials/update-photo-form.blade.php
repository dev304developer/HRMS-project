@php($url = auth()->user()->profilePhotoUrl())

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Profile Photo') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Upload a photo (JPG, PNG or WEBP, max 2MB).') }}</p>
    </header>

    <div class="mt-6 flex items-center gap-6">
        {{-- Current photo / initials --}}
        @if ($url)
            <img src="{{ $url }}" alt="Profile photo" class="h-20 w-20 rounded-full object-cover border border-gray-200">
        @else
            <span class="h-20 w-20 rounded-full flex items-center justify-center text-white text-2xl font-bold" style="background-color: #2f80ed;">
                {{ auth()->user()->initial() }}
            </span>
        @endif

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="photo" accept="image/*" required
                       class="block text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 file:cursor-pointer hover:file:bg-gray-200" />
                <x-primary-button class="hover:opacity-90" style="background-color:#2f80ed;">{{ __('Upload') }}</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('photo')" />

            @if ($url)
                <form method="POST" action="{{ route('profile.photo.destroy') }}"
                      onsubmit="return confirm('Remove your profile photo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:underline">Remove photo</button>
                </form>
            @endif

            @if (session('status') === 'photo-updated')
                <p class="text-sm text-gray-600">Photo updated.</p>
            @elseif (session('status') === 'photo-removed')
                <p class="text-sm text-gray-600">Photo removed.</p>
            @endif
        </div>
    </div>
</section>
