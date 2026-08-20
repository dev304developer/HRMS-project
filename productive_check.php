<?php

use App\Models\User;

$employee = User::where('email', 'employee@hrms.test')->first()->employee;
$employee->attendances()->delete();

// Session 1 (today): 9h gross, 60m break -> 8h productive.
$employee->attendances()->create([
    'date' => today()->toDateString(),
    'clock_in' => today()->setTime(9, 0),
    'clock_out' => today()->setTime(18, 0),
    'break_minutes' => 60,
]);

// Session 2 (earlier this week): 8h gross, 30m break -> 7h30 productive.
$earlier = now()->startOfWeek()->addDay();
$employee->attendances()->create([
    'date' => $earlier->toDateString(),
    'clock_in' => $earlier->copy()->setTime(9, 0),
    'clock_out' => $earlier->copy()->setTime(17, 0),
    'break_minutes' => 30,
]);

$first = $employee->attendances()->latest('id')->first();
$today = $employee->attendances()->whereDate('date', today())->first();

echo '== Per-session (today: 9h gross - 60m break) ==' . PHP_EOL;
echo 'worked:     ' . $today->workedLabel() . PHP_EOL;
echo 'break:      ' . $today->break_minutes . ' min' . PHP_EOL;
echo 'productive: ' . $today->productiveLabel() . '  (' . $today->productive_minutes . " min)\n";

$sum = fn ($from, $to) => (int) $employee->attendances()
    ->whereNotNull('clock_out')
    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
    ->selectRaw('COALESCE(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, clock_in, clock_out) - break_minutes, 0)),0) as m')
    ->value('m');

$fmt = fn ($m) => intdiv($m, 60) . 'h ' . ($m % 60) . 'm';

echo PHP_EOL . '== Dashboard totals ==' . PHP_EOL;
echo 'Today:   ' . $fmt($sum(today(), today())) . PHP_EOL;
echo 'Week:    ' . $fmt($sum(now()->startOfWeek(), now()->endOfWeek())) . PHP_EOL;
echo 'Month:   ' . $fmt($sum(now()->startOfMonth(), now()->endOfMonth())) . PHP_EOL;

$employee->attendances()->delete();
echo PHP_EOL . 'Cleanup done.' . PHP_EOL;
