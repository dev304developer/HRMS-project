<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'break_minutes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'break_minutes' => 'integer',
        ];
    }

    /**
     * The employee this attendance record belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * True while the employee is clocked in but hasn't clocked out yet.
     */
    public function isOpen(): bool
    {
        return $this->clock_out === null;
    }

    /**
     * Total worked minutes for a completed session (null if still open).
     */
    public function getWorkedMinutesAttribute(): ?int
    {
        if (! $this->clock_in || ! $this->clock_out) {
            return null;
        }

        return $this->clock_in->diffInMinutes($this->clock_out);
    }

    /**
     * Human-friendly worked duration, e.g. "8h 30m" (or "In progress").
     */
    public function workedLabel(): string
    {
        if ($this->isOpen()) {
            return 'In progress';
        }

        $minutes = $this->worked_minutes;

        return intdiv($minutes, 60) . 'h ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
    }

    /**
     * Productive minutes for a completed session (null while still open).
     *
     * Formula:  productive = (clock_out - clock_in) - break_minutes
     * Floored at 0 so an over-long break can never produce a negative value.
     */
    public function getProductiveMinutesAttribute(): ?int
    {
        if ($this->isOpen()) {
            return null;
        }

        return max(0, $this->worked_minutes - $this->break_minutes);
    }

    /**
     * Human-friendly productive duration, e.g. "7h 30m" (or "In progress").
     */
    public function productiveLabel(): string
    {
        if ($this->isOpen()) {
            return 'In progress';
        }

        $minutes = $this->productive_minutes;

        return intdiv($minutes, 60) . 'h ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
    }
}
