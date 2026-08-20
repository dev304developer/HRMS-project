<?php

use App\Models\Employee;
use App\Models\User;

// Link an employee profile to the existing Demo Employee user.
$user = User::where('email', 'employee@hrms.test')->first();

$emp = Employee::updateOrCreate(
    ['user_id' => $user->id],
    [
        'employee_code' => 'EMP-0001',
        'designation' => 'Software Engineer',
        'department' => 'Engineering',
        'phone' => '0300-1234567',
        'salary' => 85000.00,
        'hire_date' => '2026-01-15',
        'status' => Employee::STATUS_ACTIVE,
    ]
);

echo 'Created employee #' . $emp->id . ' code=' . $emp->employee_code . PHP_EOL;
echo 'Employee -> user->name: ' . $emp->user->name . PHP_EOL;       // belongsTo
echo 'User -> employee->designation: ' . $user->fresh()->employee->designation . PHP_EOL; // hasOne
echo 'hire_date cast: ' . $emp->hire_date->format('d M Y') . ' (' . get_class($emp->hire_date) . ')' . PHP_EOL;
echo 'salary cast: ' . $emp->salary . PHP_EOL;
