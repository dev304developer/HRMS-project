<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\HrController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

function render($controller, $method, $user) {
    auth()->login($user);
    $req = request(); $req->setUserResolver(fn () => $user);
    view()->share('errors', new ViewErrorBag());
    $c = new $controller();
    return $method === 'index' && $controller === HrController::class
        ? $c->index()->render()
        : $c->index($req)->render();
}

$admin = User::where('role', 'admin')->first();
$hr = User::where('role', 'hr')->first();

// Admin dashboard
auth()->login($admin);
$reqA = request(); $reqA->setUserResolver(fn () => $admin);
view()->share('errors', new ViewErrorBag());
$a = (new HomeController())->index($reqA)->render();
echo "ADMIN dashboard: " . strlen($a) . " bytes\n";
echo (str_contains($a, 'Attendance Performance') && str_contains($a, 'Employee Status') ? "  org overview present: yes\n" : "  NO\n");

// HR dashboard
auth()->login($hr);
$reqH = request(); $reqH->setUserResolver(fn () => $hr);
view()->share('errors', new ViewErrorBag());
$h = (new HrController())->index()->render();
echo "HR dashboard: " . strlen($h) . " bytes\n";
echo (str_contains($h, 'Attendance Performance') ? "  Attendance Performance: yes\n" : "  NO\n");
echo (str_contains($h, 'Total Employee') ? "  Total Employee donut: yes\n" : "  NO\n");
echo (str_contains($h, 'Employee Status') ? "  Employee Status table: yes\n" : "  NO\n");
echo (str_contains($h, 'perfChart') && str_contains($h, 'empDonut') ? "  charts: yes\n" : "  NO\n");
echo (str_contains($h, 'HELLO') ? "  hero: yes\n" : "  NO\n");
auth()->logout();
