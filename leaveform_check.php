<?php

use App\Http\Requests\StoreLeaveRequest;
use App\Models\Leave;
use Illuminate\Support\Facades\Validator;

// --- Single-day submission (multiple_days unchecked) ---
$single = new StoreLeaveRequest();
$single->merge([
    'leave_type' => 'sick',
    'session' => 'first_half',
    'start_date' => today()->addDays(2)->toDateString(),
    'reason' => 'Doctor appointment',
]);
$single->setMethod('POST');
// emulate prepareForValidation
if (! $single->boolean('multiple_days')) {
    $single->merge(['end_date' => $single->input('start_date')]);
}
$rules = (new StoreLeaveRequest())->rules();
$v1 = Validator::make($single->all(), $rules);
echo 'Single-day valid: ' . var_export(! $v1->fails(), true) . PHP_EOL;
echo '  end_date set to start_date: ' . var_export($single->input('end_date') === $single->input('start_date'), true) . PHP_EOL;

// --- Multi-day submission ---
$multi = [
    'multiple_days' => '1',
    'leave_type' => 'annual',
    'session' => 'full_day',
    'start_date' => today()->addDays(3)->toDateString(),
    'end_date' => today()->addDays(6)->toDateString(),
    'reason' => 'Vacation',
];
$v2 = Validator::make($multi, $rules);
echo 'Multi-day valid: ' . var_export(! $v2->fails(), true) . PHP_EOL;

// --- Missing reason should now FAIL (reason is required) ---
$v3 = Validator::make([
    'leave_type' => 'sick', 'session' => 'full_day',
    'start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDay()->toDateString(),
], $rules);
echo 'Missing reason rejected: ' . var_export($v3->fails(), true) . PHP_EOL;

// --- Bad session rejected ---
$v4 = Validator::make([
    'leave_type' => 'sick', 'session' => 'bogus',
    'start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDay()->toDateString(),
    'reason' => 'x',
], $rules);
echo 'Bad session rejected: ' . var_export($v4->fails(), true) . PHP_EOL;
echo 'Sessions available: ' . implode(', ', array_keys(Leave::SESSIONS)) . PHP_EOL;
