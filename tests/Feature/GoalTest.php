<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with an employee profile attached.
     */
    private function employeeUser(string $role, string $code): Employee
    {
        $user = User::factory()->create(['role' => $role]);

        return Employee::create([
            'user_id' => $user->id,
            'employee_code' => $code,
            'designation' => 'Tester',
            'department' => 'QA',
            'hire_date' => now()->subYear()->toDateString(),
            'status' => Employee::STATUS_ACTIVE,
        ]);
    }

    public function test_hr_can_view_the_goals_list(): void
    {
        $hr = $this->employeeUser(User::ROLE_HR, 'EMP-HR1');

        $this->actingAs($hr->user)->get(route('goals.index'))->assertOk();
    }

    public function test_employee_cannot_reach_goal_management(): void
    {
        $staff = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-E1');

        $this->actingAs($staff->user)->get(route('goals.index'))->assertForbidden();
        $this->actingAs($staff->user)->get(route('goals.create'))->assertForbidden();
    }

    public function test_hr_can_assign_a_goal(): void
    {
        $hr = $this->employeeUser(User::ROLE_HR, 'EMP-HR2');
        $staff = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-E2');

        $this->actingAs($hr->user)
            ->post(route('goals.store'), [
                'employee_id' => $staff->id,
                'title' => 'Ship the payroll module',
                'progress' => 40,
                'status' => Goal::STATUS_ACTIVE,
                'due_date' => now()->addMonth()->toDateString(),
            ])
            ->assertRedirect(route('goals.index'));

        $this->assertDatabaseHas('goals', [
            'employee_id' => $staff->id,
            'title' => 'Ship the payroll module',
            'progress' => 40,
            'created_by' => $hr->user->id,
        ]);
    }

    public function test_progress_above_100_is_rejected(): void
    {
        $hr = $this->employeeUser(User::ROLE_HR, 'EMP-HR3');
        $staff = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-E3');

        $this->actingAs($hr->user)
            ->post(route('goals.store'), [
                'employee_id' => $staff->id,
                'title' => 'Impossible',
                'progress' => 150,
                'status' => Goal::STATUS_ACTIVE,
            ])
            ->assertSessionHasErrors('progress');

        $this->assertDatabaseCount('goals', 0);
    }

    /**
     * These two exercise the exact query the dashboard card runs, rather than
     * the rendered page: the employee dashboard also computes productive hours
     * with MySQL-only SQL (GREATEST/TIMESTAMPDIFF), which sqlite cannot run.
     */
    public function test_dashboard_goal_query_returns_only_the_employees_own_goals(): void
    {
        $mine = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-M1');
        $theirs = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-T1');

        Goal::create(['employee_id' => $mine->id, 'title' => 'My own goal', 'progress' => 60, 'status' => Goal::STATUS_ACTIVE]);
        Goal::create(['employee_id' => $theirs->id, 'title' => 'Somebody elses goal', 'progress' => 20, 'status' => Goal::STATUS_ACTIVE]);

        $titles = $mine->goals()->active()->pluck('title')->all();

        $this->assertSame(['My own goal'], $titles);
    }

    public function test_active_scope_hides_completed_and_cancelled_goals(): void
    {
        $staff = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-C1');

        Goal::create(['employee_id' => $staff->id, 'title' => 'Finished work', 'progress' => 100, 'status' => Goal::STATUS_COMPLETED]);
        Goal::create(['employee_id' => $staff->id, 'title' => 'Dropped work', 'progress' => 10, 'status' => Goal::STATUS_CANCELLED]);
        Goal::create(['employee_id' => $staff->id, 'title' => 'Ongoing work', 'progress' => 30, 'status' => Goal::STATUS_ACTIVE]);

        $titles = $staff->goals()->active()->pluck('title')->all();

        $this->assertSame(['Ongoing work'], $titles);
    }

    public function test_dashboard_card_orders_least_complete_first(): void
    {
        $staff = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-S1');

        Goal::create(['employee_id' => $staff->id, 'title' => 'Nearly done', 'progress' => 90, 'status' => Goal::STATUS_ACTIVE]);
        Goal::create(['employee_id' => $staff->id, 'title' => 'Just started', 'progress' => 10, 'status' => Goal::STATUS_ACTIVE]);

        $titles = $staff->goals()->active()->orderBy('progress')->pluck('title')->all();

        $this->assertSame(['Just started', 'Nearly done'], $titles);
    }

    public function test_overdue_is_only_true_for_unfinished_past_due_goals(): void
    {
        $staff = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-O1');

        $past = Goal::create(['employee_id' => $staff->id, 'title' => 'Late', 'progress' => 10, 'status' => Goal::STATUS_ACTIVE, 'due_date' => now()->subDay()]);
        $future = Goal::create(['employee_id' => $staff->id, 'title' => 'On track', 'progress' => 10, 'status' => Goal::STATUS_ACTIVE, 'due_date' => now()->addDay()]);
        $doneLate = Goal::create(['employee_id' => $staff->id, 'title' => 'Done late', 'progress' => 100, 'status' => Goal::STATUS_COMPLETED, 'due_date' => now()->subDay()]);
        $noDate = Goal::create(['employee_id' => $staff->id, 'title' => 'No deadline', 'progress' => 10, 'status' => Goal::STATUS_ACTIVE]);

        $this->assertTrue($past->isOverdue());
        $this->assertFalse($future->isOverdue());
        $this->assertFalse($doneLate->isOverdue(), 'a completed goal should never read as overdue');
        $this->assertFalse($noDate->isOverdue(), 'a goal with no due date should never read as overdue');
    }

    public function test_deleting_an_employee_removes_their_goals(): void
    {
        $staff = $this->employeeUser(User::ROLE_EMPLOYEE, 'EMP-D1');
        Goal::create(['employee_id' => $staff->id, 'title' => 'Doomed', 'progress' => 5, 'status' => Goal::STATUS_ACTIVE]);

        $staff->delete();

        $this->assertDatabaseCount('goals', 0);
    }
}
