<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Productive-time totals used by the employee dashboard and attendance page.
 *
 * These previously used MySQL-only GREATEST()/TIMESTAMPDIFF() in raw SQL, so
 * none of it could run on the sqlite test database. The whole file is a
 * regression guard against that coming back.
 */
class ProductiveMinutesTest extends TestCase
{
    use RefreshDatabase;

    private function employee(string $code = 'EMP-P1'): Employee
    {
        return Employee::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_EMPLOYEE])->id,
            'employee_code' => $code,
            'designation' => 'Tester',
            'department' => 'QA',
            'hire_date' => now()->subYear()->toDateString(),
            'status' => Employee::STATUS_ACTIVE,
        ]);
    }

    private function logSession(Employee $employee, string $date, string $in, string $out = null, int $break = 0): Attendance
    {
        return Attendance::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'clock_in' => $date . ' ' . $in,
            'clock_out' => $out === null ? null : $date . ' ' . $out,
            'break_minutes' => $break,
        ]);
    }

    public function test_productive_time_is_worked_time_minus_break(): void
    {
        $employee = $this->employee();
        // 08:00 -> 17:00 is 540 minutes, less a 60 minute break.
        $this->logSession($employee, '2026-03-02', '08:00:00', '17:00:00', 60);

        $this->assertSame(480, $employee->productiveMinutesBetween(
            now()->parse('2026-03-01'), now()->parse('2026-03-31')
        ));
    }

    public function test_an_overlong_break_never_produces_negative_time(): void
    {
        $employee = $this->employee();
        // 2 hours worked but a 5 hour break recorded.
        $this->logSession($employee, '2026-03-03', '09:00:00', '11:00:00', 300);

        $this->assertSame(0, $employee->productiveMinutesBetween(
            now()->parse('2026-03-01'), now()->parse('2026-03-31')
        ));
    }

    public function test_open_sessions_are_ignored(): void
    {
        $employee = $this->employee();
        $this->logSession($employee, '2026-03-04', '09:00:00', '12:00:00');  // 180
        $this->logSession($employee, '2026-03-05', '09:00:00');              // still clocked in

        $this->assertSame(180, $employee->productiveMinutesBetween(
            now()->parse('2026-03-01'), now()->parse('2026-03-31')
        ));
    }

    public function test_only_sessions_inside_the_range_are_counted(): void
    {
        $employee = $this->employee();
        $this->logSession($employee, '2026-03-10', '09:00:00', '10:00:00');  // 60, inside
        $this->logSession($employee, '2026-04-10', '09:00:00', '15:00:00');  // outside

        $this->assertSame(60, $employee->productiveMinutesBetween(
            now()->parse('2026-03-01'), now()->parse('2026-03-31')
        ));
    }

    public function test_multiple_sessions_add_up(): void
    {
        $employee = $this->employee();
        $this->logSession($employee, '2026-03-11', '09:00:00', '13:00:00', 30);  // 210
        $this->logSession($employee, '2026-03-12', '09:00:00', '17:30:00', 45);  // 465

        $this->assertSame(675, $employee->productiveMinutesBetween(
            now()->parse('2026-03-01'), now()->parse('2026-03-31')
        ));
    }

    public function test_another_employees_hours_are_not_included(): void
    {
        $mine = $this->employee('EMP-P1');
        $theirs = $this->employee('EMP-P2');

        $this->logSession($mine, '2026-03-13', '09:00:00', '10:00:00');
        $this->logSession($theirs, '2026-03-13', '09:00:00', '18:00:00');

        $this->assertSame(60, $mine->productiveMinutesBetween(
            now()->parse('2026-03-01'), now()->parse('2026-03-31')
        ));
    }

    /**
     * The point of the whole exercise: this request used to fail outright on
     * sqlite with "no such function: GREATEST".
     */
    public function test_the_employee_dashboard_renders_on_a_non_mysql_database(): void
    {
        $employee = $this->employee();
        $this->logSession($employee, now()->toDateString(), '09:00:00', '17:00:00', 30);

        $this->actingAs($employee->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Attendance Summary');
    }

    public function test_the_attendance_page_renders_on_a_non_mysql_database(): void
    {
        $employee = $this->employee();
        $this->logSession($employee, now()->toDateString(), '09:00:00', '17:00:00', 30);

        $this->actingAs($employee->user)->get(route('attendance.index'))->assertOk();
    }
}
