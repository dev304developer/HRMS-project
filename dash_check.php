<?php

use App\Http\Controllers\HomeController;
use App\Models\User;

$user = User::where('email', 'employee@hrms.test')->first();
$emp = $user->employee;

$emp->attendances()->whereDate('date', today())->delete();
$emp->attendances()->create([
    'date' => today(),
    'clock_in' => today()->setTime(9, 0),
    'clock_out' => today()->setTime(18, 0),
    'break_minutes' => 60,
]);

auth()->login($user);                          // makes Auth::user() work in the view
$request = request();
$request->setUserResolver(fn () => $user);     // makes $request->user() work in the controller

$html = (new HomeController())->index($request)->render();

echo 'Rendered OK - ' . strlen($html) . " bytes\n";
echo (str_contains($html, 'Present Status') ? "Present Status widget: yes\n" : "NO\n");
echo (str_contains($html, '>Present') ? "shows Present: yes\n" : "shows Present: no\n");
echo (str_contains($html, "Today's Productive Hours") ? "Productive widget: yes\n" : "NO\n");
echo (str_contains($html, '8h 00m') ? "productive value 8h 00m: yes\n" : "productive value differs\n");
echo (str_contains($html, 'Leave Balance') ? "Leave Balance widget: yes\n" : "NO\n");
echo (str_contains($html, 'Pending Leave Requests') ? "Pending widget: yes\n" : "NO\n");

$emp->attendances()->whereDate('date', today())->delete();
auth()->logout();
echo "Cleanup done.\n";
