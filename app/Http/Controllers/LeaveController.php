<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\LeaveRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class LeaveController extends Controller
{
    /**
     * Show the logged-in employee's own leave history.
     */
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;

        // If the user has no employee profile, there are simply no leaves to show.
        $leaves = $employee
            ? $employee->leaves()->latest()->paginate(10)
            : Leave::query()->whereRaw('1 = 0')->paginate(10);

        return view('leaves.index', compact('leaves', 'employee'));
    }

    /**
     * Show the "apply for leave" form.
     */
    public function create(): View
    {
        $this->authorize('create', Leave::class);

        return view('leaves.create', ['today' => today()->toDateString()]);
    }

    /**
     * Persist a new leave request for the current employee.
     * Validation + authorization happen inside StoreLeaveRequest.
     */
    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        // employee_id comes from the server (the logged-in user), never the form.
        // status is forced to "pending" — a new request always starts unapproved.
        $leave = $request->user()->employee->leaves()->create([
            ...$request->validated(),
            'status' => Leave::STATUS_PENDING,
        ]);

        // Notify everyone who can act on this request (except the applicant).
        $approvers = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_HR, User::ROLE_MANAGER])
            ->whereKeyNot($request->user()->id)
            ->get();

        Notification::send($approvers, new LeaveRequested($leave));

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request submitted and is pending approval.');
    }

    /**
     * Show a single leave request (owner or an approver only — enforced by policy).
     */
    public function show(Leave $leave): View
    {
        $this->authorize('view', $leave);

        $leave->load('employee.user');

        return view('leaves.show', compact('leave'));
    }
}
