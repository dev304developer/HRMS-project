<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TimeCampService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncTimeCampUsers extends Command
{
    protected $signature = 'timecamp:sync-users {--dry-run : Show what would change without saving}';

    protected $description = 'Import/refresh users from TimeCamp into the HRMS';

    public function handle(TimeCampService $timecamp): int
    {
        $this->info('Fetching users from TimeCamp...');

        try {
            $tcUsers = $timecamp->users();
        } catch (\Throwable $e) {
            $this->error('TimeCamp request failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Received ' . count($tcUsers) . ' user(s).');
        $created = 0;
        $updated = 0;

        foreach ($tcUsers as $tc) {
            $email = $tc['email'] ?? null;
            if (! $email) {
                continue;
            }

            $name = $tc['display_name'] ?? $tc['name'] ?? $email;

            $user = User::firstOrNew(['email' => $email]);
            $isNew = ! $user->exists;

            $user->name = $name;

            if ($isNew) {
                // New accounts get the default role + a random password
                // (they sign in via "forgot password" to set their own).
                $user->role = User::ROLE_EMPLOYEE;
                $user->password = Hash::make(Str::random(32));
                $user->email_verified_at = now();
            }

            if ($this->option('dry-run')) {
                $this->line(($isNew ? '  + create ' : '  ~ update ') . $email . ' (' . $name . ')');
                $isNew ? $created++ : $updated++;

                continue;
            }

            $user->save();
            $isNew ? $created++ : $updated++;
        }

        $this->newLine();
        $this->info(($this->option('dry-run') ? '[dry run] ' : '') . "Done. Created: {$created}, Updated: {$updated}.");

        return self::SUCCESS;
    }
}
