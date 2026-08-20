<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeProfileRequest extends FormRequest
{
    /**
     * Any logged-in user may manage their OWN employee details.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Hybrid model: employees may edit these fields themselves.
     * employee_code and salary are intentionally NOT here — they are HR-only
     * and never accepted from this self-service form.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'designation' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'hire_date' => ['required', 'date'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ];
    }
}
