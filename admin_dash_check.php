<?php

use App\Http\Controllers\HomeController;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

// Seed a couple of announcements if none exist.
if (Announcement::count() === 0) {
    Announcement::create(['title' => 'Welcome to the new HRMS', 'body' => 'Please complete your profile details.']);
    Announcement::create(['title' => 'Payroll date moved', 'body' => 'Salaries will be credited on the 1st this month.']);
}

$admin = User::where('role', 'admin')->first();
auth()->login($admin);
$request = request();
$request->setUserResolver(fn () => $admin);
view()->share('errors', new ViewErrorBag());

$html = (new HomeController())->index($request)->render();

echo 'Rendered OK - ' . strlen($html) . " bytes\n";
foreach (['Total Employees', 'Attendance Trend', 'Department Statistics', 'Pending Leave Requests', 'Recent Announcements', 'Upcoming Holidays', 'attendanceTrend', 'chart.js'] as $needle) {
    echo (stripos($html, $needle) !== false ? "  has '{$needle}': yes\n" : "  has '{$needle}': NO\n");
}
auth()->logout();
