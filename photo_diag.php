<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;

echo 'APP_URL: ' . config('app.url') . PHP_EOL;
echo 'public disk url config: ' . config('filesystems.disks.public.url') . PHP_EOL;

$users = User::whereNotNull('profile_photo_path')->get(['id', 'name', 'email', 'profile_photo_path']);
echo 'Users with a photo: ' . $users->count() . PHP_EOL;
foreach ($users as $u) {
    $path = $u->profile_photo_path;
    echo "  {$u->email}\n";
    echo "    path: {$path}\n";
    echo "    url:  " . Storage::disk('public')->url($path) . "\n";
    echo "    exists on disk: " . var_export(Storage::disk('public')->exists($path), true) . "\n";
}

echo 'public/storage symlink exists: ' . var_export(file_exists(public_path('storage')), true) . PHP_EOL;
echo 'public/storage is link: ' . var_export(is_link(public_path('storage')), true) . PHP_EOL;
