<?php

use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$admin = User::where('role', 'admin')->first();
auth()->login($admin);
$req = request(); $req->setUserResolver(fn () => $admin);
view()->share('errors', new ViewErrorBag());

$html = (new AdminController())->index()->render();
echo 'Admin page rendered - ' . strlen($html) . " bytes\n";
echo (str_contains($html, 'Sync from TimeCamp') ? "  Sync button present: yes\n" : "  NO\n");
echo (str_contains($html, 'admin/timecamp/sync') ? "  wired to sync route: yes\n" : "  NO\n");
auth()->logout();
