<?php

use App\Models\Leave;
use App\Models\User;

$admin = User::where('email', 'admin@hrms.test')->first();    // admin + has employee EMP-0002
$employee = User::where('email', 'employee@hrms.test')->first(); // employee + has EMP-0001

// A pending leave that belongs to the EMPLOYEE (admin may approve it).
$othersLeave = Leave::where('employee_id', $employee->employee->id)
    ->where('status', Leave::STATUS_PENDING)->first();

// A pending leave that belongs to the ADMIN (admin may NOT approve own).
$ownLeave = Leave::where('employee_id', $admin->employee->id)
    ->where('status', Leave::STATUS_PENDING)->first();

echo '== Policy checks ==' . PHP_EOL;
echo 'employee can create (has profile):      ' . var_export($employee->can('create', Leave::class), true) . PHP_EOL;
echo 'admin can approve employee\'s pending:   ' . var_export($admin->can('approve', $othersLeave), true) . PHP_EOL;
echo 'admin can approve OWN pending (should be false): ' . var_export($ownLeave ? $admin->can('approve', $ownLeave) : 'n/a', true) . PHP_EOL;
echo 'employee can approve (not approver):     ' . var_export($employee->can('approve', $othersLeave), true) . PHP_EOL;
echo 'employee can view own leave:             ' . var_export($employee->can('view', $othersLeave), true) . PHP_EOL;
echo 'employee can view admin\'s leave (false): ' . var_export($ownLeave ? $employee->can('view', $ownLeave) : 'n/a', true) . PHP_EOL;

echo PHP_EOL . '== Approve flow ==' . PHP_EOL;
echo 'before: ' . $othersLeave->status . PHP_EOL;
$othersLeave->update(['status' => Leave::STATUS_APPROVED]);
echo 'after:  ' . $othersLeave->fresh()->status . PHP_EOL;
// revert so demo data stays pending
$othersLeave->update(['status' => Leave::STATUS_PENDING]);
echo 'reverted to: ' . $othersLeave->fresh()->status . PHP_EOL;
