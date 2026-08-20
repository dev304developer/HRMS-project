<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Generic welcome (only when there's no admin/employee rich dashboard) --}}
            @if (empty($adminDash) && empty($empDash))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="text-lg">
                            {{ __("Welcome back") }}, <span class="font-semibold">{{ Auth::user()->name }}</span>!
                        </p>
                        <p class="mt-2 text-sm text-gray-600">
                            Your role:
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold uppercase
                                {{ Auth::user()->isAdmin() ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ Auth::user()->role }}
                            </span>
                        </p>
                    </div>
                </div>
            @endif

            @if (!empty($adminDash))
                @include('partials.org-dashboard', ['dash' => $adminDash])
            @else

            {{-- ===================== EMPLOYEE DASHBOARD ===================== --}}
            @if (!empty($empDash))
                @php($emp = $empDash['employee'])

                {{-- 1. Welcome card with employee details --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center gap-4">
                    @if ($emp->user->profilePhotoUrl())
                        <img src="{{ $emp->user->profilePhotoUrl() }}" alt="{{ $emp->user->name }}" class="h-14 w-14 rounded-full object-cover border border-gray-200">
                    @else
                        <span class="h-14 w-14 rounded-full flex items-center justify-center text-white text-xl font-bold" style="background-color:#2f80ed;">{{ $emp->user->initial() }}</span>
                    @endif
                    <div>
                        <p class="text-lg text-gray-900">Welcome back, <span class="font-semibold">{{ $emp->user->name }}</span>!</p>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $emp->designation }} &middot; {{ $emp->department }} &middot;
                            <span class="font-medium" style="color:#2f80ed;">{{ $emp->employee_code }}</span>
                        </p>
                    </div>
                </div>

                {{-- 2. Statistics cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white shadow-sm sm:rounded-lg p-5 border-l-4 {{ $empDash['isPresent'] ? 'border-green-400' : 'border-red-400' }}">
                        <div class="text-xs text-gray-500 uppercase">Present Status</div>
                        <div class="mt-1 text-2xl font-bold {{ $empDash['isPresent'] ? 'text-green-700' : 'text-red-700' }}">{{ $empDash['isPresent'] ? 'Present' : 'Absent' }}</div>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-5 border-l-4 border-indigo-400">
                        <div class="text-xs text-gray-500 uppercase">Today's Productive Hours</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ intdiv($empDash['todayProductive'], 60) }}h {{ str_pad((string) ($empDash['todayProductive'] % 60), 2, '0', STR_PAD_LEFT) }}m</div>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-5 border-l-4 border-blue-400">
                        <div class="text-xs text-gray-500 uppercase">Leave Balance</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $empDash['leaveRemaining'] }} <span class="text-sm font-normal text-gray-500">days</span></div>
                    </div>
                    <a href="{{ route('leaves.index') }}" class="block bg-white shadow-sm sm:rounded-lg p-5 border-l-4 border-yellow-400 hover:shadow-md transition">
                        <div class="text-xs text-gray-500 uppercase">Pending Leave Requests</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $empDash['pendingLeaves'] }}</div>
                    </a>
                </div>

                {{-- 3. Attendance Summary --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Attendance Summary</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="rounded-lg p-4" style="background-color:#f5fbff;">
                            <div class="text-xs text-gray-500 uppercase">This Week</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">{{ intdiv($empDash['weekProductive'], 60) }}h {{ str_pad((string) ($empDash['weekProductive'] % 60), 2, '0', STR_PAD_LEFT) }}m</div>
                            <div class="text-xs text-gray-400">productive hours</div>
                        </div>
                        <div class="rounded-lg p-4" style="background-color:#f5fbff;">
                            <div class="text-xs text-gray-500 uppercase">This Month</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">{{ intdiv($empDash['monthProductive'], 60) }}h {{ str_pad((string) ($empDash['monthProductive'] % 60), 2, '0', STR_PAD_LEFT) }}m</div>
                            <div class="text-xs text-gray-400">productive hours</div>
                        </div>
                        <div class="rounded-lg p-4" style="background-color:#f5fbff;">
                            <div class="text-xs text-gray-500 uppercase">Present Days</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">{{ $empDash['presentDaysMonth'] }}</div>
                            <div class="text-xs text-gray-400">this month</div>
                        </div>
                    </div>
                </div>

                {{-- 4. Recent Leave Requests --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <div class="px-6 py-4 border-b flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Recent Leave Requests</h3>
                        <a href="{{ route('leaves.index') }}" class="text-sm hover:underline" style="color:#2f80ed;">View all</a>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Session</th>
                                <th class="px-6 py-3">Dates</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                            @forelse ($empDash['recentLeaves'] as $leave)
                                <tr>
                                    <td class="px-6 py-4">{{ $leave->typeLabel() }}</td>
                                    <td class="px-6 py-4">{{ $leave->sessionLabel() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4"><x-leave-status-badge :status="$leave->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No leave requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 5 & 6. Upcoming Holidays | Company Announcements --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Upcoming Holidays</h3>
                        <div class="space-y-3">
                            @forelse ($empDash['upcomingHolidays'] as $h)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">{{ $h->title }}</span>
                                    <span class="text-gray-500">{{ $h->date->format('d M Y') }} ({{ $h->date->format('D') }})</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No upcoming holidays.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Company Announcements</h3>
                        <div class="space-y-4">
                            @forelse ($empDash['announcements'] as $a)
                                <div class="pl-3 border-l-2" style="border-color:#8acbf8;">
                                    <div class="font-medium text-gray-900 text-sm">{{ $a->title }}</div>
                                    @if ($a->body)<div class="text-xs text-gray-600">{{ $a->body }}</div>@endif
                                    <div class="text-xs text-gray-400 mt-1">{{ $a->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No announcements.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- 7 & 8. Schedules | Birthdays --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Schedules --}}
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b">
                            <h3 class="font-semibold text-gray-800">Schedules</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse ($empDash['schedules'] as $meeting)
                                <div class="rounded-xl border border-gray-100 p-4" style="background-color:#f8fbff;">
                                    @if ($meeting->role_tag)
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-gray-800 text-white">{{ $meeting->role_tag }}</span>
                                    @endif
                                    <div class="mt-2 font-semibold text-gray-900">{{ $meeting->title }}</div>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-8 gap-y-1 text-sm text-gray-500">
                                        <span class="inline-flex items-center gap-2">
                                            <svg class="h-4 w-4" style="color:#2f80ed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $meeting->meeting_date->format('D, d M Y') }}
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <svg class="h-4 w-4" style="color:#2f80ed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $meeting->timeRange() }}
                                        </span>
                                    </div>
                                    @if ($meeting->meeting_link)
                                        <div class="mt-3">
                                            <a href="{{ $meeting->meeting_link }}" target="_blank" rel="noopener"
                                               class="inline-flex items-center px-4 py-1.5 rounded-lg text-sm font-semibold text-white hover:opacity-90"
                                               style="background-color:#2f80ed;">Join Meeting</a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No upcoming meetings scheduled.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Birthdays --}}
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Birthdays</h3>
                            <span class="text-xl">🎂</span>
                        </div>
                        <div class="p-6 space-y-3">
                            @forelse ($empDash['birthdays'] as $b)
                                @php($bemp = $b['employee'])
                                <div class="flex items-center gap-3 rounded-xl p-3 {{ $b['daysUntil'] === 0 ? 'text-white' : 'bg-gray-50' }}"
                                     @if ($b['daysUntil'] === 0) style="background-color:#134e5e;" @endif>
                                    @if ($bemp->user?->profilePhotoUrl())
                                        <img src="{{ $bemp->user->profilePhotoUrl() }}" alt="{{ $bemp->user->name }}" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <span class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background-color:#2f80ed;">{{ $bemp->user?->initial() ?? '?' }}</span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold {{ $b['daysUntil'] === 0 ? 'text-white' : 'text-gray-900' }} truncate">{{ $bemp->user?->name ?? '—' }}</div>
                                        <div class="text-xs {{ $b['daysUntil'] === 0 ? 'text-white/80' : 'text-gray-500' }} truncate">{{ $bemp->designation }}</div>
                                    </div>
                                    <span class="text-xs font-medium {{ $b['daysUntil'] === 0 ? 'text-white' : 'text-gray-500' }} whitespace-nowrap">
                                        @if ($b['daysUntil'] === 0)
                                            Today
                                        @elseif ($b['daysUntil'] === 1)
                                            Tomorrow
                                        @else
                                            {{ $b['date']->format('d M') }}
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No upcoming birthdays.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            @else
                {{-- No employee profile: quick links + hint --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="{{ route('profile.edit') }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-900">My Profile</h3>
                        <p class="mt-1 text-sm text-gray-600">Update your name, email and password.</p>
                    </a>
                    @if (Auth::user()->hasAnyRole(['admin', 'hr']))
                        <a href="{{ route('hr.dashboard') }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition border-l-4 border-indigo-400">
                            <h3 class="font-semibold text-gray-900">HR Area</h3>
                            <p class="mt-1 text-sm text-gray-600">Human-resources tools.</p>
                        </a>
                    @endif
                </div>
                <div class="bg-blue-50 text-blue-800 text-sm sm:rounded-lg p-4">
                    You don't have an employee profile yet. Your dashboard widgets will appear once HR sets up your record.
                </div>
            @endif

            @endif {{-- end admin/non-admin branch --}}

        </div>
    </div>
</x-app-layout>
