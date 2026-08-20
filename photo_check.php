<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$user = User::where('email', 'employee@hrms.test')->first();
auth()->login($user);
$req = request(); $req->setUserResolver(fn () => $user);
view()->share('errors', new ViewErrorBag());

$html = (new ProfileController())->edit($req)->render();

echo 'Profile rendered - ' . strlen($html) . " bytes\n";
echo (str_contains($html, 'Profile Photo') ? "  Profile Photo section: yes\n" : "  NO\n");
echo (str_contains($html, 'type="file"') ? "  file input: yes\n" : "  NO\n");
echo (str_contains($html, 'profile/photo') ? "  upload route wired: yes\n" : "  NO\n");
echo "  current photo url: " . var_export($user->profilePhotoUrl(), true) . "\n";
echo "  initial: " . $user->initial() . "\n";
auth()->logout();
