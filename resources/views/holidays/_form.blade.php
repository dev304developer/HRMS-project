@php($holiday = $holiday ?? null)

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

<div class="space-y-6">
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                      :value="old('title', $holiday?->title)" placeholder="e.g. Independence Day" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="date" :value="__('Date')" />
        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                      :value="old('date', $holiday?->date?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $holiday?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $submitLabel ?? __('Save') }}</x-primary-button>
    <a href="{{ route('holidays.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
</div>
