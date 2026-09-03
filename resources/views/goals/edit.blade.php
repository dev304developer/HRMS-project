<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Goal') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('goals.update', $goal) }}" class="bg-white shadow-sm rounded-2xl p-6">
                @csrf
                @method('PATCH')
                @include('goals._form')

                <div class="mt-6 flex items-center gap-3">
                    <x-primary-button>Save Changes</x-primary-button>
                    <a href="{{ route('goals.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
