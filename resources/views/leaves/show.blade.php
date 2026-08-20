<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Leave Request') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Employee</dt>
                        <dd class="font-medium text-gray-900">{{ $leave->employee->user->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd><x-leave-status-badge :status="$leave->status" /></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Type</dt>
                        <dd class="font-medium text-gray-900">{{ $leave->typeLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Session</dt>
                        <dd class="font-medium text-gray-900">{{ $leave->sessionLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Dates</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Reason</dt>
                        <dd class="font-medium text-gray-900 whitespace-pre-line">{{ $leave->reason ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <a href="{{ route('leaves.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to my leaves</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
