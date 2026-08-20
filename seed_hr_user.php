<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::updateOrCreate(
    ['email' => 'hr@hrms.test'],
    [
        'name' => 'Demo HR',
        'role' => User::ROLE_HR,
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]
);

echo 'HR user ready:' . PHP_EOL;
echo '  email: ' . $user->email . PHP_EOL;
echo '  role:  ' . $user->role . PHP_EOL;
echo '  password: password' . PHP_EOL;
