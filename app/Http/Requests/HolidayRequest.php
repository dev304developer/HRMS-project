<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
{
    /**
     * Authorization is enforced by the route middleware (role:admin,hr).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Shared rules for creating and updating a holiday.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
