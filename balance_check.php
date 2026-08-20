<?php

use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;

$user = User::where('email', 'employee@hrms.test')->first();
$emp = $user->employee;

// Clean leaves for a deterministic test; give carry forward = 3.
$emp->leaves()->delete();
$emp->update(['carry_forward' => 3]);

// Approved PAID leave: 2 days (full day).
$emp->leaves()->create(['leave_type' => 'paid', 'session' => 'full_day', 'start_date' => now()->startOfYear()->addDays(10), 'end_date' => now()->startOfYear()->addDays(11), 'status' => 'approved', 'reason' => 't']);
// Approved SPECIAL half-day: 0.5
$emp->leaves()->create(['leave_type' => 'special', 'session' => 'first_half', 'start_date' => now()->startOfYear()->addDays(20), 'end_date' => now()->startOfYear()->addDays(20), 'status' => 'approved', 'reason' => 't']);

echo "== Leave balances ==\n";
foreach ($emp->fresh()->leaveBalances() as $b) {
    echo sprintf("  %-45s remaining %s (allow %s, used %s)\n", $b['label'], $b['remaining'], $b['allowance'], $b['used']);
}

// Render My Leaves + Profile to confirm no errors.
auth()->login($user);
$req = request(); $req->setUserResolver(fn () => $user);
view()->share('errors', new ViewErrorBag());

$leaves = (new LeaveController())->index($req)->render();
echo "\nMy Leaves renders: " . (str_contains($leaves, 'Leave Balance') ? "Leave Balance shown\n" : "NO\n");

$profile = (new ProfileController())->edit($req)->render();
echo "Profile renders: " . (str_contains($profile, 'Leave Balance') ? "Leave Balance shown\n" : "NO\n");

// cleanup
$emp->leaves()->delete();
$emp->update(['carry_forward' => 0]);
auth()->logout();
echo "Cleanup done.\n";
