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

$admin = User::where('role', 'admin')->first();
$hr = User::where('role', 'hr')->first();

$a = render($admin);
$h = render($hr);

echo "ADMIN sidebar has 'HR Dashboard' link: " . (str_contains($a, 'route') && substr_count($a, 'HR Dashboard') > 0 ? "yes (" . substr_count($a, 'HR Dashboard') . ")\n" : "NO\n");
echo "HR sidebar has 'HR Dashboard' link:    " . (substr_count($h, 'HR Dashboard') > 0 ? "yes (" . substr_count($h, 'HR Dashboard') . ")\n" : "no\n");
auth()->logout();
