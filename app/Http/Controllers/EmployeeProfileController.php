<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmployeeProfileRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;

class EmployeeProfileController extends Controller
{
    /**
     * Create or update the logged-in user's OWN employee details.
     * Self-service — no HR involvement required.
     */
    public function update(UpdateEmployeeProfileRequest $request): RedirectResponse
    {
        // Only the employee-editable fields (salary & code are NOT in here).
        $data = $request->validated();

        // Existing record, or a fresh one already scoped to this user.
        $employee = $request->user()->employee()->firstOrNew([]);

        // HR-only: assign a code automatically when creating; never editable here.
        // salary is left untouched (null on create, unchanged on update).
        if (! $employee->exists && empty($employee->employee_code)) {
            $employee->employee_code = 'EMP-' . str_pad((string) ((Employee::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        }

        $employee->fill($data);

        // Default status for a brand-new self-created profile.
        if (! $employee->status) {
            $employee->status = Employee::STATUS_ACTIVE;
        }

        $employee->save();

        return redirect()->route('profile.edit')->with('status', 'employee-profile-updated');
    }
}
