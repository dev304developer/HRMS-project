<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin landing page: role stats + user/role management table.
     */
    public function index(): View
    {
        $users = User::with('employee')->orderBy('name')->paginate(15);

        $stats = [
            'users' => User::count(),
            'admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'hr' => User::where('role', User::ROLE_HR)->count(),
            'managers' => User::where('role', User::ROLE_MANAGER)->count(),
        ];

        return view('admin.index', compact('users', 'stats'));
    }

    /**
     * Change a user's role.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        // Safeguard: an admin can't change their own role (avoids self-lockout).
        if ($user->is($request->user())) {
            return back()->with('error', "You can't change your own role.");
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "{$user->name}'s role updated to {$user->role}.");
    }

    /**
     * Pull users from TimeCamp into the HRMS (runs the sync command).
     */
    public function syncTimeCamp(): RedirectResponse
    {
        $exit = Artisan::call('timecamp:sync-users');

        // Take the last non-empty line of the command output as the summary.
        $lines = array_values(array_filter(array_map('trim', explode("\n", Artisan::output()))));
        $summary = end($lines) ?: 'No output.';

        return $exit === 0
            ? back()->with('success', 'TimeCamp sync — ' . $summary)
            : back()->with('error', 'TimeCamp sync failed — ' . $summary);
    }
}
