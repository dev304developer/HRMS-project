@props(['employee' => null])

@php($balances = $employee?->leaveBalances())

@if ($balances)
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Leave Balance ({{ now()->year }})</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($balances as $b)
                <div class="rounded-lg border p-4" style="border-color: #dbeefb; background-color: #f5fbff;">
                    <div class="text-xs text-gray-500 leading-tight" style="min-height: 2.2rem;">{{ $b['label'] }}</div>
                    <div class="mt-1 text-2xl font-bold" style="color: #1d6fb8;">{{ $b['remaining'] }}</div>
                    @if (empty($b['carry']))
                        <div class="text-xs text-gray-400">of {{ $b['allowance'] }} &middot; {{ $b['used'] }} used</div>
                    @else
                        <div class="text-xs text-gray-400">previous year remaining</div>
                    @endif
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-gray-400">Remaining updates automatically as approved leaves are deducted.</p>
    </div>
@endif
