<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Holiday;
use App\Models\Schedule;
use Illuminate\Support\Collection;

/**
 * Shared calendar panels — the month calendar and the merged
 * "Schedule & Holidays" agenda, used by the employee and manager dashboards.
 */
trait BuildsCalendarPanels
{
    /**
     * Upcoming meetings and public holidays merged into a single date-ordered
     * agenda for the dashboard "Schedule & Holidays" card.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function upcomingAgenda(int $limit = 5): Collection
    {
        $holidays = Holiday::whereDate('date', '>=', today())
            ->orderBy('date')->take($limit)->get()
            ->map(fn (Holiday $h) => [
                'type' => 'holiday',
                'title' => $h->title,
                'subtitle' => 'Public Holiday',
                'date' => $h->date,
                'link' => null,
            ]);

        $meetings = Schedule::whereDate('meeting_date', '>=', today())
            ->orderBy('meeting_date')->orderBy('start_time')->take($limit)->get()
            ->map(fn (Schedule $s) => [
                'type' => 'meeting',
                'title' => $s->title,
                'subtitle' => $s->timeRange(),
                'date' => $s->meeting_date,
                'link' => $s->meeting_link,
            ]);

        return $holidays->concat($meetings)->sortBy('date')->take($limit)->values();
    }

    /**
     * Map of "Y-m-d" => events, used to mark days in the dashboard calendar.
     * Spans a window around today so month navigation stays populated.
     *
     * @return array<string, list<array{type: string, title: string}>>
     */
    protected function calendarEvents(): array
    {
        $from = now()->startOfMonth()->subMonths(6);
        $to = now()->startOfMonth()->addMonths(12)->endOfMonth();

        $events = [];

        foreach (Holiday::whereBetween('date', [$from, $to])->orderBy('date')->get() as $h) {
            $events[$h->date->toDateString()][] = ['type' => 'holiday', 'title' => $h->title];
        }

        foreach (Schedule::whereBetween('meeting_date', [$from, $to])->orderBy('meeting_date')->get() as $s) {
            $events[$s->meeting_date->toDateString()][] = ['type' => 'meeting', 'title' => $s->title];
        }

        return $events;
    }
}
