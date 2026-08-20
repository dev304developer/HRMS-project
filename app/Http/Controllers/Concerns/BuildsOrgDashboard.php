<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Shared organisation-wide dashboard data used by both the Admin Dashboard
 * and the HR Dashboard.
 */
trait BuildsOrgDashboard
{
    /**
     * Employees whose first clock-in today is after this time (24h HH:MM)
     * are counted as "Late Arrival"; on or before it, "On Time".
     */
    protected const LATE_AFTER = '10:00';

    /**
     * Build the organisation overview: headcount/attendance stats, today's
     * attendance breakdown, department/designation breakdowns, pending leaves,
     * announcements and holidays.
     *
     * @return array<string, mixed>
     */
    protected function orgDashboard(): array
    {
        $totalEmployees = Employee::count();

        // Present = employees with any attendance record today.
        $presentToday = Employee::whereHas('attendances', fn ($q) => $q->whereDate('date', today()))->count();

        // On approved leave covering today.
        $onLeaveToday = Leave::where('status', Leave::STATUS_APPROVED)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->distinct('employee_id')
            ->count('employee_id');

        // Today's attendance split into On Time / Late Arrival / Absent for the
        // "Employee Attendance" donut. On Time vs Late is based on each
        // employee's FIRST clock-in against the LATE_AFTER office-start time.
        $todayClockIns = Attendance::whereDate('date', today())
            ->whereNotNull('clock_in')
            ->orderBy('clock_in')
            ->get(['employee_id', 'clock_in'])
            ->groupBy('employee_id');

        $onTime = 0;
        $late = 0;
        foreach ($todayClockIns as $records) {
            if ($records->first()->clock_in->format('H:i') <= self::LATE_AFTER) {
                $onTime++;
            } else {
                $late++;
            }
        }
        $attnAbsent = max(0, $totalEmployees - $onTime - $late);

        $departments = Employee::selectRaw('department, COUNT(*) as total')
            ->groupBy('department')
            ->orderByDesc('total')
            ->get();

        $designations = Employee::selectRaw('designation, COUNT(*) as total')
            ->groupBy('designation')
            ->orderByDesc('total')
            ->get();

        return [
            'totalEmployees' => $totalEmployees,
            'presentToday' => $presentToday,
            'absentToday' => max(0, $totalEmployees - $presentToday - $onLeaveToday),
            'onLeaveToday' => $onLeaveToday,
            'pendingTotal' => Leave::where('status', Leave::STATUS_PENDING)->count(),
            // Today's attendance donut breakdown.
            'attnOnTime' => $onTime,
            'attnLate' => $late,
            'attnAbsent' => $attnAbsent,
            'departments' => $departments,
            'maxDept' => (int) ($departments->max('total') ?: 1),
            'designations' => $designations,
            'employeeList' => Employee::with('user')->latest('id')->take(8)->get(),
            'pendingLeaves' => Leave::with('employee.user')
                ->where('status', Leave::STATUS_PENDING)->oldest()->take(5)->get(),
            'announcements' => Announcement::latest()->take(5)->get(),
            'upcomingHolidays' => Holiday::whereDate('date', '>=', today())
                ->orderBy('date')->take(5)->get(),
            // Upcoming interview/meeting schedules (today onward).
            'schedules' => Schedule::whereDate('meeting_date', '>=', today())
                ->orderBy('meeting_date')->orderBy('start_time')->take(5)->get(),
            // Upcoming birthdays derived from each employee's date_of_birth.
            'birthdays' => $this->upcomingBirthdays(),
        ];
    }

    /**
     * Employees with an upcoming birthday, soonest first.
     * Each entry: ['employee' => Employee, 'date' => Carbon, 'daysUntil' => int].
     * Only birthdays within the next ~60 days are returned.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function upcomingBirthdays(): Collection
    {
        $today = today();

        return Employee::with('user')
            ->whereNotNull('date_of_birth')
            ->get()
            ->map(function (Employee $emp) use ($today) {
                $dob = $emp->date_of_birth;

                // Next occurrence of the birthday (this year, or next year if it already passed).
                $next = Carbon::create($today->year, (int) $dob->format('n'), (int) $dob->format('j'));
                if ($next->lt($today)) {
                    $next = $next->addYear();
                }

                return [
                    'employee' => $emp,
                    'date' => $next,
                    'daysUntil' => (int) $today->diffInDays($next),
                ];
            })
            ->filter(fn (array $b) => $b['daysUntil'] <= 60)
            ->sortBy('daysUntil')
            ->take(6)
            ->values();
    }
}
