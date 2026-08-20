<?php

use App\Http\Controllers\HrController;
use App\Models\Employee;
use App\Models\User;

$hr = User::where('role', 'hr')->first() ?? User::where('role', 'admin')->first();

// Make one employee "present" today for a meaningful test.
$emp = Employee::first();
$emp->attendances()->whereDate('date', today())->delete();
$emp->attendances()->create(['date' => today(), 'clock_in' => today()->setTime(9, 0)]);

auth()->login($hr);
$request = request();
$request->setUserResolver(fn () => $hr);

$html = (new HrController())->index()->render();

$total = Employee::count();
echo "Total employees in DB: {$total}\n";
echo (str_contains($html, 'Total Employees') ? "Total Employees card: yes\n" : "NO\n");
echo (str_contains($html, 'Present Today') ? "Present Today card: yes\n" : "NO\n");
echo (str_contains($html, 'Absent Today') ? "Absent Today card: yes\n" : "NO\n");
echo (str_contains($html, 'Pending Leave Requests') ? "Pending Leave Requests card: yes\n" : "NO\n");

// cleanup
$emp->attendances()->whereDate('date', today())->delete();
auth()->logout();
echo "Cleanup done.\n";
