<?php

use App\Models\Employee;
use App\Models\User;

$profiles = [
    'dev357@yourdeveloperonline.com' => [
        'employee_code' => 'EMP-0003',
        'designation' => 'Software Developer',
        'department' => 'Engineering',
        'phone' => '0301-2345678',
        'address' => "Office No. 12, Tech Park\nLahore, Pakistan",
        'salary' => 90000,
        'hire_date' => '2025-11-01',
        'status' => 'active',
    ],
    'test@gmail.com' => [
        'employee_code' => 'EMP-0004',
        'designation' => 'UI/UX Designer',
        'department' => 'Design',
        'phone' => '0302-3456789',
        'address' => "45-B, Gulberg III\nLahore, Pakistan",
        'salary' => 75000,
        'hire_date' => '2026-02-15',
        'status' => 'active',
    ],
];

foreach ($profiles as $email => $data) {
    $user = User::where('email', $email)->first();
    if (! $user) {
        echo "skip (no user): {$email}" . PHP_EOL;
        continue;
    }
    $emp = Employee::updateOrCreate(['user_id' => $user->id], $data);
    echo "{$user->name} <{$email}> -> {$emp->employee_code} / {$emp->designation}" . PHP_EOL;
}
