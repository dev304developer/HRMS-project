<?php

use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

function render($user) {
    auth()->login($user);
    $req = request(); $req->setUserResolver(fn () => $user);
    view()->share('errors', new ViewErrorBag());
    return (new HomeController())->index($req)->render();
}

$hr = User::where('role', 'hr')->first();
$admin = User::where('role', 'admin')->first();

$h = render($hr);
echo "HR main dashboard: " . strlen($h) . " bytes\n";
echo (str_contains($h, 'Attendance Performance') && str_contains($h, 'Employee Status') ? "  org overview on Dashboard: yes\n" : "  NO\n");
echo (str_contains($h, '>HR Dashboard<') ? "  HR Dashboard link present (should be NO): YES\n" : "  HR Dashboard link hidden for HR: good\n");

$a = render($admin);
echo "ADMIN main dashboard: " . strlen($a) . " bytes\n";
echo (str_contains($a, '>HR Dashboard<') ? "  HR Dashboard link kept for admin: yes\n" : "  HR Dashboard link missing for admin (unexpected)\n");
auth()->logout();
