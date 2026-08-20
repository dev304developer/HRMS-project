<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Notifications\LeaveProcessed;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    /**
     * List pending leave requests awaiting a decision.
     * Route is already restricted to admin/hr/manager via middleware.
     */
    public function index(): View
    {
        $pendingLeaves = Leave::with('employee.user')
            ->where('status', Leave::STATUS_PENDING)
            ->oldest()        // oldest requests first (FIFO)
            ->paginate(10);

        return view('leaves.manage', compact('pendingLeaves'));
    }

    /**
     * Approve a pending leave request.
     */
    public function approve(Leave $leave): RedirectResponse
    {
        $this->authorize('approve', $leave);

        $leave->update(['status' => Leave::STATUS_APPROVED]);

        // Notify the employee of the decision.
        $leave->employee->user->notify(new LeaveProcessed($leave));

        return back()->with('success', 'Leave request approved.');
    }

    /**
     * Reject a pending leave request.
     */
    public function reject(Leave $leave): RedirectResponse
    {
        $this->authorize('reject', $leave);

        $leave->update(['status' => Leave::STATUS_REJECTED]);

        // Notify the employee of the decision.
        $leave->employee->user->notify(new LeaveProcessed($leave));

        return back()->with('success', 'Leave request rejected.');
    }
}
