<?php

use App\Http\Controllers\AttendanceController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$admin = User::where('role', 'admin')->first();
auth()->login($admin);
$request = request();
$request->setUserResolver(fn () => $admin);
view()->share('errors', new ViewErrorBag());

$html = (new AttendanceController())->index($request)->render();

echo 'Admin view rendered - ' . strlen($html) . " bytes\n";
echo (str_contains($html, 'Employees Attendance') ? "admin title: yes\n" : "NO\n");
echo (str_contains($html, 'All Attendance Records') ? "table heading: yes\n" : "NO\n");
echo (str_contains($html, 'Clock In') && str_contains($html, 'Clock Out') ? "table columns: yes\n" : "NO\n");
echo (str_contains($html, 'Clock In</button>') || str_contains($html, 'Save Break') ? "self-service controls present (should be NO): yes\n" : "self-service controls present: no (good)\n");
auth()->logout();
