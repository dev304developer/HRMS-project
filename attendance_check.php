<?php

use App\Models\Attendance;
use App\Models\User;

$employee = User::where('email', 'employee@hrms.test')->first()->employee;

// Clean slate.
$employee->attendances()->delete();

// Simulate a clock-in 8.5 hours ago, then clock out now.
$in = now()->subMinutes(510);   // 8h 30m ago
$att = $employee->attendances()->create([
    'date' => $in->toDateString(),
    'clock_in' => $in,
]);

echo 'After clock-in:' . PHP_EOL;
echo '  isOpen():      ' . var_export($att->isOpen(), true) . PHP_EOL;
echo '  workedLabel(): ' . $att->workedLabel() . PHP_EOL;

// Guard: a second open session must be blocked at controller level.
echo '  hasOpenSession: ' . var_export($employee->attendances()->whereNull('clock_out')->exists(), true) . PHP_EOL;

// Clock out.
$att->update(['clock_out' => now()]);
$att->refresh();

echo PHP_EOL . 'After clock-out:' . PHP_EOL;
echo '  isOpen():        ' . var_export($att->isOpen(), true) . PHP_EOL;
echo '  worked_minutes:  ' . $att->worked_minutes . PHP_EOL;
echo '  workedLabel():   ' . $att->workedLabel() . PHP_EOL;

// Weekly total (the same query the controller uses).
$weekMinutes = (int) $employee->attendances()
    ->whereNotNull('clock_out')
    ->whereBetween('clock_in', [now()->startOfWeek(), now()->endOfWeek()])
    ->selectRaw('COALESCE(SUM(TIMESTAMPDIFF(MINUTE, clock_in, clock_out)), 0) as minutes')
    ->value('minutes');
echo '  week total:      ' . intdiv($weekMinutes, 60) . 'h ' . ($weekMinutes % 60) . 'm' . PHP_EOL;

// Cleanup.
$employee->attendances()->delete();
echo PHP_EOL . 'Cleanup done.' . PHP_EOL;
