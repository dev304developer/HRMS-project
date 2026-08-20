<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Meeting Schedules') }}</h2>
            <a href="{{ route('schedules.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-md text-white hover:opacity-90"
               style="background-color:#2f80ed;">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Meeting
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3">Meeting</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Time</th>
                            <th class="px-6 py-3">Link</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td class="px-6 py-4">
                                    @if ($schedule->role_tag)
                                        <span class="inline-block mb-1 px-2 py-0.5 rounded text-xs font-semibold bg-gray-800 text-white">{{ $schedule->role_tag }}</span>
                                    @endif
                                    <div class="font-medium text-gray-900">{{ $schedule->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $schedule->meeting_date->format('D, d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $schedule->timeRange() }}</td>
                                <td class="px-6 py-4">
                                    @if ($schedule->meeting_link)
                                        <a href="{{ $schedule->meeting_link }}" target="_blank" rel="noopener"
                                           class="hover:underline" style="color:#2f80ed;">Join</a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('schedules.edit', $schedule) }}" class="hover:underline" style="color:#2f80ed;">Edit</a>
                                    <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" class="inline"
                                          onsubmit="return confirm('Delete this meeting?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ml-3 text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No meetings scheduled yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $schedules->links() }}</div>
        </div>
    </div>
</x-app-layout>
