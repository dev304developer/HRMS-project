{{--
    Shared create/edit form.
    Expects:  $users (selectable users), and optionally $employee (when editing).
    The parent view sets the <form> action/method and includes this partial.
--}}
@php($employee = $employee ?? null)

@csrf

{{-- Validation summary --}}
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

    {{-- Linked user account --}}
    <div>
        <x-input-label for="user_id" :value="__('User account')" />
        <select name="user_id" id="user_id"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— Select a user —</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}"
                    @selected(old('user_id', $employee?->user_id) == $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
    </div>

    {{-- Employee code --}}
    <div>
        <x-input-label for="employee_code" :value="__('Employee code')" />
        <x-text-input id="employee_code" name="employee_code" type="text" class="mt-1 block w-full"
                      :value="old('employee_code', $employee?->employee_code)" placeholder="EMP-0001" />
        <x-input-error :messages="$errors->get('employee_code')" class="mt-2" />
    </div>

    {{-- Designation --}}
    <div>
        <x-input-label for="designation" :value="__('Designation')" />
        <x-text-input id="designation" name="designation" type="text" class="mt-1 block w-full"
                      :value="old('designation', $employee?->designation)" />
        <x-input-error :messages="$errors->get('designation')" class="mt-2" />
    </div>

    {{-- Department --}}
    <div>
        <x-input-label for="department" :value="__('Department')" />
        <x-text-input id="department" name="department" type="text" class="mt-1 block w-full"
                      :value="old('department', $employee?->department)" />
        <x-input-error :messages="$errors->get('department')" class="mt-2" />
    </div>

    {{-- Phone --}}
    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                      :value="old('phone', $employee?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    {{-- Address (full width) --}}
    <div class="sm:col-span-2">
        <x-input-label for="address" :value="__('Address')" />
        <textarea id="address" name="address" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $employee?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    {{-- Salary --}}
    <div>
        <x-input-label for="salary" :value="__('Salary')" />
        <x-text-input id="salary" name="salary" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      :value="old('salary', $employee?->salary)" />
        <x-input-error :messages="$errors->get('salary')" class="mt-2" />
    </div>

    {{-- Hire date --}}
    <div>
        <x-input-label for="hire_date" :value="__('Hire date')" />
        <x-text-input id="hire_date" name="hire_date" type="date" class="mt-1 block w-full"
                      :value="old('hire_date', $employee?->hire_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('hire_date')" class="mt-2" />
    </div>

    {{-- Date of birth (for birthday wishes) --}}
    <div>
        <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
        <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full"
                      :value="old('date_of_birth', $employee?->date_of_birth?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
    </div>

    {{-- Status --}}
    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select name="status" id="status"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach (\App\Models\Employee::STATUSES as $status)
                <option value="{{ $status }}"
                    @selected(old('status', $employee?->status ?? 'active') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $submitLabel ?? __('Save') }}</x-primary-button>
    <a href="{{ route('employees.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
</div>
