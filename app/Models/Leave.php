<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    /** Selectable leave types (value => label). */
    public const TYPES = [
        'paid' => 'Paid Leave',
        'unpaid' => 'Unpaid Leave',
        'special' => 'Birthday / Anniversary / Saturday Leave',
    ];

    /** Annual allowance (days) per leave type. */
    public const ALLOWANCES = [
        'paid' => 12,
        'unpaid' => 12,
        'special' => 1,
    ];

    /** Selectable leave sessions (value => label). */
    public const SESSIONS = [
        'full_day' => 'Full Day',
        'first_half' => 'Half Day - First Half',
        'second_half' => 'Half Day - Second Half',
    ];

    /**
     * Whether this leave is still awaiting a decision.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Is this a half-day leave (first or second half)?
     */
    public function isHalfDay(): bool
    {
        return in_array($this->session, ['first_half', 'second_half'], true);
    }

    /**
     * Human-readable session label, e.g. "Full Day".
     */
    public function sessionLabel(): string
    {
        return self::SESSIONS[$this->session] ?? ucfirst((string) $this->session);
    }

    /**
     * Human-readable leave-type label, e.g. "Paid Leave".
     */
    public function typeLabel(): string
    {
        return self::TYPES[$this->leave_type] ?? ucfirst((string) $this->leave_type);
    }

    /**
     * Number of leave days this request consumes.
     * Half-day sessions count as 0.5; otherwise it's the inclusive date span.
     */
    public function dayCount(): float
    {
        if ($this->isHalfDay()) {
            return 0.5;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /** @var list<string> */
    protected $fillable = [
        'employee_id',
        'leave_type',
        'session',
        'start_date',
        'end_date',
        'reason',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * The employee who requested this leave.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
