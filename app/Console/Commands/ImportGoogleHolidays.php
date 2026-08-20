<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportGoogleHolidays extends Command
{
    /**
     * Usage:
     *   php artisan holidays:import-google              (India, current + next year)
     *   php artisan holidays:import-google --country=us --years=1
     */
    protected $signature = 'holidays:import-google
                            {--country=in : Country code (in, us, uk, ca, au, sg, ae, ph, za)}
                            {--years=2 : Number of years to import, starting this year}';

    protected $description = 'Import public holidays / festivals from Google\'s public holiday calendar';

    /**
     * Google's public holiday calendars (one per country/locale).
     * These are read-only calendars Google publishes for everyone.
     *
     * @var array<string, string>
     */
    private const CALENDARS = [
        'in' => 'en.indian#holiday@group.v.calendar.google.com',
        'us' => 'en.usa#holiday@group.v.calendar.google.com',
        'uk' => 'en.uk#holiday@group.v.calendar.google.com',
        'ca' => 'en.canadian#holiday@group.v.calendar.google.com',
        'au' => 'en.australian#holiday@group.v.calendar.google.com',
        'sg' => 'en.singapore#holiday@group.v.calendar.google.com',
        'ae' => 'en.ae#holiday@group.v.calendar.google.com',
        'ph' => 'en.philippines#holiday@group.v.calendar.google.com',
        'za' => 'en.sa#holiday@group.v.calendar.google.com',
    ];

    public function handle(): int
    {
        $country = strtolower((string) $this->option('country'));
        $years = max(1, (int) $this->option('years'));

        if (! isset(self::CALENDARS[$country])) {
            $this->error("Unknown country '{$country}'. Supported: " . implode(', ', array_keys(self::CALENDARS)) . '.');

            return self::FAILURE;
        }

        $url = 'https://calendar.google.com/calendar/ical/'
            . rawurlencode(self::CALENDARS[$country]) . '/public/basic.ics';

        $this->info("Fetching Google holidays for '{$country}' …");

        try {
            $response = Http::timeout(30)->get($url);
        } catch (\Throwable $e) {
            $this->error('Could not reach Google Calendar: ' . $e->getMessage());

            return self::FAILURE;
        }

        if (! $response->ok()) {
            $this->error('Google Calendar returned HTTP ' . $response->status() . '. Try again later.');

            return self::FAILURE;
        }

        $events = $this->parseIcs($response->body());

        $minYear = now()->year;
        $maxYear = $minYear + $years - 1;

        $created = 0;
        $existing = 0;

        foreach ($events as $event) {
            $date = Carbon::createFromFormat('Ymd', $event['date']);

            if ($date->year < $minYear || $date->year > $maxYear) {
                continue;
            }

            $holiday = Holiday::firstOrCreate(
                ['date' => $date->toDateString(), 'title' => $event['summary']],
                ['description' => 'Imported from Google Calendar'],
            );

            $holiday->wasRecentlyCreated ? $created++ : $existing++;
        }

        $this->info("Google holidays import — Created: {$created}, Already present: {$existing} (years {$minYear}-{$maxYear}).");

        return self::SUCCESS;
    }

    /**
     * Parse an ICS (iCalendar) document into a list of ['summary' => , 'date' => Ymd].
     *
     * @return array<int, array{summary: string, date: string}>
     */
    private function parseIcs(string $ics): array
    {
        // Unfold folded lines (RFC 5545: a CRLF followed by a space/tab continues the line).
        $ics = preg_replace("/\r\n[ \t]/", '', $ics);
        $lines = preg_split('/\r\n|\n|\r/', (string) $ics);

        $events = [];
        $current = null;

        foreach ($lines as $line) {
            if (str_starts_with($line, 'BEGIN:VEVENT')) {
                $current = ['summary' => '', 'date' => ''];
            } elseif (str_starts_with($line, 'END:VEVENT')) {
                if ($current && $current['summary'] !== '' && $current['date'] !== '') {
                    $events[] = $current;
                }
                $current = null;
            } elseif ($current !== null) {
                if (str_starts_with($line, 'SUMMARY')) {
                    $value = substr($line, strpos($line, ':') + 1);
                    // Unescape ICS text escapes.
                    $current['summary'] = trim(str_replace(['\\,', '\\;', '\\n', '\\N'], [',', ';', ' ', ' '], $value));
                } elseif (str_starts_with($line, 'DTSTART')) {
                    $value = substr($line, strpos($line, ':') + 1);
                    $current['date'] = substr(trim($value), 0, 8); // YYYYMMDD
                }
            }
        }

        return $events;
    }
}
