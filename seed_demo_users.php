<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$demo = [
    ['name' => 'Demo Admin',    'email' => 'admin@hrms.test',    'role' => User::ROLE_ADMIN],
    ['name' => 'Demo Employee', 'email' => 'employee@hrms.test', 'role' => User::ROLE_EMPLOYEE],
];

foreach ($demo as $d) {
    $user = User::updateOrCreate(
        ['email' => $d['email']],                       // match on email (no duplicates)
        [
            'name' => $d['name'],
            'role' => $d['role'],
            'password' => Hash::make('password'),        // demo password
            'email_verified_at' => now(),                // skip email verification for demo
        ]
    );
    echo str_pad($user->email, 24) . ' -> role: ' . $user->role . PHP_EOL;
}

echo 'Done. Password for both accounts: password' . PHP_EOL;
