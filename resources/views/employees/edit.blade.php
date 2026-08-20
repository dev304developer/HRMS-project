<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Employee') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                {{-- PUT to employees.update via @method spoofing --}}
                <form method="POST" action="{{ route('employees.update', $employee) }}">
                    @method('PUT')
                    @include('employees._form', ['submitLabel' => 'Update Employee'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
