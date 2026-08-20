<?php

use App\Http\Controllers\HolidayController;
use App\Models\User;

auth()->loginUsingId(User::where('role', 'admin')->first()->id);

$html = (new HolidayController())->index(request())->render();

echo 'Rendered OK - ' . strlen($html) . " bytes\n";
echo (str_contains($html, 'Company Holidays') ? "heading: yes\n" : "heading: NO\n");
echo (str_contains($html, 'rounded-full bg-indigo-600') ? "today-circle markup: yes\n" : "today-circle: NO\n");
echo (str_contains($html, '<div>Sun</div>') ? "weekday headers: yes\n" : "weekday headers: NO\n");
