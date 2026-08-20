<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Employees') }}</h2>
            <a href="{{ route('employees.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                + New Employee
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash success message (set by the controller via ->with('success', ...)) --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-y-hidden whitespace-nowrap overflow-x-auto w-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3">Code</th>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Designation</th>
                            <th class="px-6 py-3">Department</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                        @forelse ($employees as $employee)
                            <tr>
                                <td class="px-6 py-4 font-medium">{{ $employee->employee_code }}</td>
                                <td class="px-6 py-4">{{ $employee->user->name ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $employee->designation }}</td>
                                <td class="px-6 py-4">{{ $employee->department }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('employees.show', $employee) }}" class="text-indigo-600 hover:underline">View</a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="text-blue-600 hover:underline">Edit</a>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this employee?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No employees yet. Click “New Employee” to add one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination links --}}
            <div class="mt-4">
                {{ $employees->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
