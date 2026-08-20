<?php

use App\Models\Leave;
use App\Models\User;
use App\Notifications\LeaveProcessed;
use App\Notifications\LeaveRequested;
use Illuminate\Support\Facades\Notification;

$employee = User::where('email', 'employee@hrms.test')->first();
$admin = User::where('email', 'admin@hrms.test')->first();

// Clean slate for a deterministic test.
$employee->notifications()->delete();
$admin->notifications()->delete();

// 1) Employee applies -> notify approvers (admin/hr/manager) except applicant.
$leave = $employee->employee->leaves()->create([
    'leave_type' => 'sick',
    'start_date' => '2026-08-01',
    'end_date' => '2026-08-02',
    'reason' => 'Notification test',
    'status' => Leave::STATUS_PENDING,
]);

$approvers = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_HR, User::ROLE_MANAGER])
    ->whereKeyNot($employee->id)->get();
Notification::send($approvers, new LeaveRequested($leave));

echo 'Approvers notified: ' . $approvers->count() . PHP_EOL;
echo 'Admin unread after apply: ' . $admin->fresh()->unreadNotifications->count() . PHP_EOL;
echo 'Admin latest message: ' . ($admin->fresh()->notifications->first()->data['message'] ?? '—') . PHP_EOL;

// 2) Admin approves -> notify employee.
$leave->update(['status' => Leave::STATUS_APPROVED]);
$leave->employee->user->notify(new LeaveProcessed($leave));

echo PHP_EOL;
echo 'Employee unread after decision: ' . $employee->fresh()->unreadNotifications->count() . PHP_EOL;
echo 'Employee latest message: ' . ($employee->fresh()->notifications->first()->data['message'] ?? '—') . PHP_EOL;
echo 'Employee notification url: ' . ($employee->fresh()->notifications->first()->data['url'] ?? '—') . PHP_EOL;

// Cleanup test artifacts (keep dashboard data tidy).
$leave->delete();
$employee->notifications()->delete();
$admin->notifications()->delete();
echo PHP_EOL . 'Cleanup done.' . PHP_EOL;
