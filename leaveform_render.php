<?php

use App\Http\Controllers\LeaveController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$user = User::where('email', 'employee@hrms.test')->first();
auth()->login($user);
$request = request();
$request->setUserResolver(fn () => $user);

// Web middleware normally shares $errors on every request; emulate that here.
view()->share('errors', new ViewErrorBag());

$html = (new LeaveController())->create()->render();

echo 'Rendered OK - ' . strlen($html) . " bytes\n";
echo (str_contains($html, 'Apply for multiple days') ? "multiple-days toggle: yes\n" : "NO\n");
echo (str_contains($html, 'Select leave session') ? "session field: yes\n" : "NO\n");
echo (str_contains($html, 'Full Day') ? "session options: yes\n" : "NO\n");
echo (str_contains($html, 'Select leave type') ? "leave type field: yes\n" : "NO\n");
echo (str_contains($html, 'Leave reason') ? "reason field: yes\n" : "NO\n");
echo (str_contains($html, 'Apply</button>') ? "Apply button: yes\n" : "Apply button: check\n");
auth()->logout();
