<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;

class LeavePolicy
{
    /**
     * Roles allowed to approve/reject leave requests.
     *
     * @var list<string>
     */
    private const APPROVER_ROLES = [
        User::ROLE_ADMIN,
        User::ROLE_HR,
        User::ROLE_MANAGER,
    ];

    /**
     * Only users who have an employee profile can apply for leave.
     */
    public function create(User $user): bool
    {
        return $user->employee !== null;
    }

    /**
     * A user may view their own leave; approvers may view anyone's.
     */
    public function view(User $user, Leave $leave): bool
    {
        return $this->owns($user, $leave) || $user->hasAnyRole(self::APPROVER_ROLES);
    }

    /**
     * Approvers can decide on a leave that is still pending and not their own.
     * (You can't approve your own request — separation of duties.)
     */
    public function approve(User $user, Leave $leave): bool
    {
        return $user->hasAnyRole(self::APPROVER_ROLES)
            && $leave->isPending()
            && ! $this->owns($user, $leave);
    }

    /**
     * Rejecting follows the same rules as approving.
     */
    public function reject(User $user, Leave $leave): bool
    {
        return $this->approve($user, $leave);
    }

    /**
     * Does this leave belong to the given user (via their employee profile)?
     */
    private function owns(User $user, Leave $leave): bool
    {
        return $user->employee !== null
            && $leave->employee_id === $user->employee->id;
    }
}
