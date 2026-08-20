<?php

use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$admin = User::where('role', 'admin')->first();
auth()->login($admin);
$req = request(); $req->setUserResolver(fn () => $admin);
view()->share('errors', new ViewErrorBag());
$a = (new HomeController())->index($req)->render();

echo 'isAdmin: ' . var_export($admin->isAdmin(), true) . "\n";
foreach (['Management', 'Leave Requests', 'Employees', 'HR Dashboard', 'HRMS Stats', '>Admin<', 'Holidays'] as $n) {
    echo "  '{$n}': " . substr_count($a, $n) . "\n";
}
auth()->logout();
