<?php

use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;

// Ensure a second employee in a different department (uses the Demo Admin user).
$adminUser = User::where('email', 'admin@hrms.test')->first();
$emp2 = Employee::firstOrCreate(
    ['user_id' => $adminUser->id],
    [
        'employee_code' => 'EMP-0002',
        'designation' => 'HR Manager',
        'department' => 'Human Resources',
        'hire_date' => '2025-09-01',
        'status' => 'active',
    ]
);

$emp1 = Employee::where('employee_code', 'EMP-0001')->first();

// Seed leaves with mixed statuses (idempotent-ish: only seed if none exist yet).
if (Leave::count() === 0) {
    $rows = [
        ['employee_id' => $emp1->id, 'leave_type' => 'annual', 'start_date' => '2026-06-20', 'end_date' => '2026-06-24', 'status' => 'pending',  'reason' => 'Family trip'],
        ['employee_id' => $emp1->id, 'leave_type' => 'sick',   'start_date' => '2026-05-10', 'end_date' => '2026-05-11', 'status' => 'approved', 'reason' => 'Flu'],
        ['employee_id' => $emp2->id, 'leave_type' => 'casual', 'start_date' => '2026-06-18', 'end_date' => '2026-06-18', 'status' => 'pending',  'reason' => 'Personal work'],
        ['employee_id' => $emp2->id, 'leave_type' => 'annual', 'start_date' => '2026-04-01', 'end_date' => '2026-04-05', 'status' => 'approved', 'reason' => 'Vacation'],
        ['employee_id' => $emp1->id, 'leave_type' => 'unpaid', 'start_date' => '2026-07-01', 'end_date' => '2026-07-03', 'status' => 'pending',  'reason' => 'Travel'],
    ];
    foreach ($rows as $r) {
        Leave::create($r);
    }
    echo 'Seeded ' . count($rows) . ' leave records.' . PHP_EOL;
} else {
    echo 'Leaves already exist (' . Leave::count() . '), skipping seed.' . PHP_EOL;
}

// Print the same stats the dashboard will show.
echo 'Employees:       ' . Employee::count() . PHP_EOL;
echo 'Pending leaves:  ' . Leave::where('status', 'pending')->count() . PHP_EOL;
echo 'Approved leaves: ' . Leave::where('status', 'approved')->count() . PHP_EOL;
echo 'Departments:     ' . Employee::query()->distinct()->count('department') . PHP_EOL;
