<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsOrgDashboard;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    use BuildsOrgDashboard;

    /** Annual paid-leave allowance, in days, used for the leave balance. */
    private const ANNUAL_LEAVE_ALLOWANCE = 20;

    /**
     * The main dashboard. Admins get an organisation overview; everyone else
     * (with an employee profile) gets the self-service summary.
     */
    public function index(Request $request): View
    {
        // Admins and HR land on the organisation overview dashboard.
        if ($request->user()->hasAnyRole(['admin', 'hr'])) {
            return view('dashboard', ['adminDash' => $this->orgDashboard()]);
        }

        $employee = $request->user()->employee;
        $empDash = $employee ? $this->employeeDashboard($employee) : null;

        return view('dashboard', compact('empDash'));
    }

    /**
     * Build the employee self-service dashboard: today's status, productive
     * hours, leave balance, attendance summary, recent leaves, holidays and
     * announcements.
     *
     * @return array<string, mixed>
     */
    private function employeeDashboard(Employee $employee): array
    {
        $isPresent = $employee->attendances()->whereDate('date', today())->exists();

        // Productive minutes today / this week / this month.
        $todayProductive = $this->productiveSum($employee, today(), today());
        $weekProductive = $this->productiveSum($employee, now()->startOfWeek(), now()->endOfWeek());
        $monthProductive = $this->productiveSum($employee, now()->startOfMonth(), now()->endOfMonth());

        // Distinct days present this month.
        $presentDaysMonth = $employee->attendances()
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->distinct('date')->count('date');

        // Total remaining leave across all categories (paid/unpaid/special/carry).
        $balances = $employee->leaveBalances();
        $leaveRemaining = round(collect($balances)->sum('remaining'), 1);

        return [
            'employee' => $employee,
            'isPresent' => $isPresent,
            'todayProductive' => $todayProductive,
            'weekProductive' => $weekProductive,
            'monthProductive' => $monthProductive,
            'presentDaysMonth' => $presentDaysMonth,
            'leaveRemaining' => $leaveRemaining,
            'pendingLeaves' => $employee->leaves()->where('status', Leave::STATUS_PENDING)->count(),
            'recentLeaves' => $employee->leaves()->latest()->take(5)->get(),
            'upcomingHolidays' => Holiday::whereDate('date', '>=', today())->orderBy('date')->take(5)->get(),
            'announcements' => Announcement::latest()->take(5)->get(),
            // Upcoming meetings + birthdays (shared widgets, also on the org dashboard).
            'schedules' => Schedule::whereDate('meeting_date', '>=', today())
                ->orderBy('meeting_date')->orderBy('start_time')->take(5)->get(),
            'birthdays' => $this->upcomingBirthdays(),
        ];
    }

    /**
     * Sum productive minutes (worked - break, floored at 0) between two dates.
     */
    private function productiveSum(Employee $employee, \Carbon\Carbon $from, \Carbon\Carbon $to): int
    {
        return (int) $employee->attendances()
            ->whereNotNull('clock_out')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('COALESCE(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, clock_in, clock_out) - break_minutes, 0)), 0) as minutes')
            ->value('minutes');
    }

}
