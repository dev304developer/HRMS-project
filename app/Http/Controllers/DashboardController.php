<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the Bootstrap HRMS dashboard with summary statistics.
     */
    public function index(): View
    {
        $stats = [
            'employees' => Employee::count(),
            'pendingLeaves' => Leave::where('status', Leave::STATUS_PENDING)->upcoming()->count(),
            'approvedLeaves' => Leave::where('status', Leave::STATUS_APPROVED)->count(),
            // Number of distinct (non-empty) departments across all employees.
            'departments' => Employee::query()->distinct()->count('department'),
        ];

        // Per-department headcount for the breakdown table.
        $departmentBreakdown = Employee::selectRaw('department, COUNT(*) as total')
            ->groupBy('department')
            ->orderByDesc('total')
            ->get();

        // A few most-recent leave requests for the table at the bottom.
        $recentLeaves = Leave::with('employee.user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.hrms', compact('stats', 'departmentBreakdown', 'recentLeaves'));
    }
}
