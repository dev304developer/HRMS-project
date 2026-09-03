{{-- Shared create/edit form. Expects $employees and optionally $goal. --}}
@php($g = $goal ?? null)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

    <div class="sm:col-span-2">
        <x-input-label for="employee_id" value="Employee" />
        <select id="employee_id" name="employee_id" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">— Select employee —</option>
            @foreach ($employees as $id => $label)
                <option value="{{ $id }}" @selected(old('employee_id', $g?->employee_id) == $id)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="title" value="Goal" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                      :value="old('title', $g?->title)" required maxlength="150" />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description (optional)" />
        <textarea id="description" name="description" rows="3" maxlength="1000"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $g?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div x-data="{ progress: {{ (int) old('progress', $g?->progress ?? 0) }} }">
        <x-input-label for="progress" value="Progress" />
        <div class="mt-2 flex items-center gap-3">
            <input id="progress" name="progress" type="range" min="0" max="100" step="5"
                   x-model="progress" class="flex-1 accent-blue-600">
            <span class="w-12 text-right text-sm font-semibold text-gray-900" x-text="progress + '%'"></span>
        </div>
        <x-input-error :messages="$errors->get('progress')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_date" value="Due date (optional)" />
        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                      :value="old('due_date', $g?->due_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @foreach (\App\Models\Goal::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $g?->status ?? 'active') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
</div>
