{{--
    Shared create/edit form for meeting schedules.
    The parent view sets the <form> action/method and includes this partial.
    Expects optionally $schedule (when editing).
--}}
@php($schedule = $schedule ?? null)

@csrf

@if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

    {{-- Title (full width) --}}
    <div class="sm:col-span-2">
        <x-input-label for="title" :value="__('Meeting title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                      :value="old('title', $schedule?->title)" placeholder="Interview Candidates - UI/UX Designer" />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    {{-- Role tag / badge --}}
    <div>
        <x-input-label for="role_tag" :value="__('Role / Tag (optional)')" />
        <x-text-input id="role_tag" name="role_tag" type="text" class="mt-1 block w-full"
                      :value="old('role_tag', $schedule?->role_tag)" placeholder="UI/UX Designer" />
        <x-input-error :messages="$errors->get('role_tag')" class="mt-2" />
    </div>

    {{-- Date --}}
    <div>
        <x-input-label for="meeting_date" :value="__('Date')" />
        <x-text-input id="meeting_date" name="meeting_date" type="date" class="mt-1 block w-full"
                      :value="old('meeting_date', $schedule?->meeting_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('meeting_date')" class="mt-2" />
    </div>

    {{-- Start time --}}
    <div>
        <x-input-label for="start_time" :value="__('Start time')" />
        <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full"
                      :value="old('start_time', $schedule ? \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) : '')" />
        <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
    </div>

    {{-- End time --}}
    <div>
        <x-input-label for="end_time" :value="__('End time (optional)')" />
        <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full"
                      :value="old('end_time', $schedule && $schedule->end_time ? \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) : '')" />
        <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
    </div>

    {{-- Meeting link --}}
    <div class="sm:col-span-2">
        <x-input-label for="meeting_link" :value="__('Meeting link (optional)')" />
        <x-text-input id="meeting_link" name="meeting_link" type="url" class="mt-1 block w-full"
                      :value="old('meeting_link', $schedule?->meeting_link)" placeholder="https://meet.google.com/..." />
        <x-input-error :messages="$errors->get('meeting_link')" class="mt-2" />
    </div>

    {{-- Description --}}
    <div class="sm:col-span-2">
        <x-input-label for="description" :value="__('Description (optional)')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $schedule?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $submitLabel ?? __('Save') }}</x-primary-button>
    <a href="{{ route('schedules.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
</div>
