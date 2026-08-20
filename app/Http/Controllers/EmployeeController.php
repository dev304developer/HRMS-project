<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Display a paginated list of employees.
     * with('user') eager-loads the related user to avoid N+1 queries in the view.
     */
    public function index(): View
    {
        $employees = Employee::with('user')->latest()->paginate(10);

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     * Only users that don't yet have an employee profile can be selected.
     */
    public function create(): View
    {
        $users = User::doesntHave('employee')->orderBy('name')->get();

        return view('employees.create', compact('users'));
    }

    /**
     * Persist a new employee. $request->validated() returns only the
     * rules-approved data from StoreEmployeeRequest.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        Employee::create($request->validated());

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display a single employee (route-model bound).
     */
    public function show(Employee $employee): View
    {
        $employee->load('user');

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the edit form. Selectable users = those without a profile,
     * plus the employee's own current user.
     */
    public function edit(Employee $employee): View
    {
        $users = User::doesntHave('employee')
            ->orWhere('id', $employee->user_id)
            ->orderBy('name')
            ->get();

        return view('employees.edit', compact('employee', 'users'));
    }

    /**
     * Apply validated changes to an existing employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Delete an employee profile (the linked user account is left intact).
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
