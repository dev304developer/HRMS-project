<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveApprovalController;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$emp = User::where('email', 'employee@hrms.test')->first()->employee;

// --- Half-day math ---
$full = new Leave(['session' => 'full_day', 'start_date' => '2026-07-01', 'end_date' => '2026-07-03']);
$half = new Leave(['session' => 'first_half', 'start_date' => '2026-07-05', 'end_date' => '2026-07-05']);
echo 'Full-day (3-day span) dayCount: ' . $full->dayCount() . " (expect 3)\n";
echo 'Half-day dayCount: ' . $half->dayCount() . " (expect 0.5)\n";
echo 'Half sessionLabel: ' . $half->sessionLabel() . "\n";

// --- Render employee leave list (Session column) ---
$user = $emp->user;
auth()->login($user);
$req = request(); $req->setUserResolver(fn () => $user);
view()->share('errors', new ViewErrorBag());
$list = (new LeaveController())->index($req)->render();
echo "Leave list renders: " . (str_contains($list, '>Session<') ? "Session column yes\n" : "NO\n");

// --- Render approval screen (admin) ---
$admin = User::where('role', 'admin')->first();
auth()->login($admin);
$req2 = request(); $req2->setUserResolver(fn () => $admin);
$manage = (new LeaveApprovalController())->index()->render();
echo "Approval screen renders: " . (str_contains($manage, '>Session<') ? "Session column yes\n" : "NO\n");

// --- Employee dashboard leave balance reflects halves (render only) ---
auth()->login($user); $req3 = request(); $req3->setUserResolver(fn () => $user);
$dash = (new HomeController())->index($req3)->render();
echo "Employee dashboard renders: " . (str_contains($dash, 'Leave Balance') ? "yes\n" : "NO\n");
auth()->logout();
