<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * List all scheduled meetings (soonest first).
     */
    public function index(): View
    {
        $schedules = Schedule::orderBy('meeting_date')->orderBy('start_time')->paginate(15);

        return view('schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        return view('schedules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;

        Schedule::create($data);

        return redirect()->route('schedules.index')->with('success', 'Meeting scheduled successfully.');
    }

    public function edit(Schedule $schedule): View
    {
        return view('schedules.edit', compact('schedule'));
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($this->validateData($request));

        return redirect()->route('schedules.index')->with('success', 'Meeting updated successfully.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Meeting deleted successfully.');
    }

    /**
     * Shared validation rules for create/update.
     *
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'role_tag' => ['nullable', 'string', 'max:100'],
            'meeting_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'meeting_link' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
