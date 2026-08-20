<?php

$u = new App\Models\User([
    'name' => 'Role Test',
    'email' => 'roletest@example.com',
    'password' => 'secret123',
]);
$u->save();

$fresh = App\Models\User::find($u->id);
echo 'default role: ' . $fresh->role . PHP_EOL;
echo 'isAdmin(): ' . var_export($fresh->isAdmin(), true) . PHP_EOL;
echo 'hasRole(employee): ' . var_export($fresh->hasRole('employee'), true) . PHP_EOL;
echo 'hasAnyRole([admin,hr]): ' . var_export($fresh->hasAnyRole(['admin', 'hr']), true) . PHP_EOL;

$fresh->delete();
echo 'cleanup: deleted test user' . PHP_EOL;
