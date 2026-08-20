<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayRequest;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class HolidayController extends Controller
{
    /**
     * List company holidays — a monthly calendar plus a list.
     * Visible to every authenticated user; management actions are role-gated.
     */
    public function index(Request $request): View
    {
        // Which month is the calendar showing? (?month=YYYY-MM, default this month)
        try {
            $cursor = $request->filled('month')
                ? Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth()
                : now()->startOfMonth();
        } catch (\Exception $e) {
            $cursor = now()->startOfMonth();
        }

        // Holidays in the visible month, keyed by Y-m-d for quick lookup in the grid.
        $monthHolidays = Holiday::whereBetween('date', [
                $cursor->copy()->startOfMonth(),
                $cursor->copy()->endOfMonth(),
            ])
            ->orderBy('date')
            ->get()
            ->groupBy(fn (Holiday $h) => $h->date->format('Y-m-d'));

        // Build the calendar grid: full weeks (Sun–Sat) spanning the month.
        // Everything the view needs is precomputed here (keeps the Blade simple
        // and avoids @php blocks inside loops).
        $gridStart = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $calendar = [];
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $inMonth = $day->month === $cursor->month;
            $isToday = $day->isToday();

            $calendar[] = [
                'number' => $day->day,
                'inMonth' => $inMonth,
                'isToday' => $isToday,
                'numClass' => $isToday
                    ? 'rounded-full bg-indigo-600 text-white font-bold'
                    : ($inMonth ? 'text-gray-700' : 'text-gray-300'),
                'cellClass' => $inMonth ? 'bg-white' : 'bg-gray-50',
                'holidays' => $monthHolidays->get($day->format('Y-m-d')) ?? collect(),
            ];
        }

        // The list below the calendar shows the SAME month as the calendar,
        // so Prev/Next navigation updates both together.
        $holidays = Holiday::whereBetween('date', [
                $cursor->copy()->startOfMonth(),
                $cursor->copy()->endOfMonth(),
            ])
            ->orderBy('date')
            ->get();

        return view('holidays.index', compact('holidays', 'cursor', 'calendar'));
    }

    /**
     * Show the create form (HR/admin only — route is role-protected).
     */
    public function create(): View
    {
        return view('holidays.create');
    }

    /**
     * Store a new holiday.
     */
    public function store(HolidayRequest $request): RedirectResponse
    {
        Holiday::create($request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday added.');
    }

    /**
     * Show the edit form.
     */
    public function edit(Holiday $holiday): View
    {
        return view('holidays.edit', compact('holiday'));
    }

    /**
     * Update an existing holiday.
     */
    public function update(HolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $holiday->update($request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday updated.');
    }

    /**
     * Delete a holiday.
     */
    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted.');
    }

    /**
     * Import public holidays / festivals from Google's public holiday calendar
     * (runs the holidays:import-google command).
     */
    public function importGoogle(Request $request): RedirectResponse
    {
        $country = $request->input('country', 'in');

        $exit = Artisan::call('holidays:import-google', ['--country' => $country]);

        $lines = array_values(array_filter(array_map('trim', explode("\n", Artisan::output()))));
        $summary = end($lines) ?: 'No output.';

        return $exit === 0
            ? back()->with('success', $summary)
            : back()->with('error', 'Google import failed — ' . $summary);
    }
}
