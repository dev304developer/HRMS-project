<?php

use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$admin = User::where('role', 'admin')->first();
auth()->login($admin);
$req = request(); $req->setUserResolver(fn () => $admin);
view()->share('errors', new ViewErrorBag());

$html = (new HomeController())->index($req)->render();

echo 'Rendered OK - ' . strlen($html) . " bytes\n";
foreach (['HELLO', 'Total Present', 'Total Absent', 'Total On Leave', 'Attendance Performance', 'Total Employee', 'Employee Status', 'Job role', 'Announcements', 'Upcoming Holidays', 'perfChart', 'empDonut', '#8acbf8', '#2f80ed'] as $n) {
    echo (str_contains($html, $n) ? "  has '{$n}': yes\n" : "  has '{$n}': NO\n");
}
echo (str_contains($html, 'bg-green-') || str_contains($html, 'text-green-7') ? "  WARNING: green classes still present\n" : "  no green accent classes: good\n");
auth()->logout();
