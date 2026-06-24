<?php

namespace App\Features\Reports\Requests;

use App\Policies\ReportPolicy;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates filters for viewing a portal report.
 */
class ReportFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return app(ReportPolicy::class)->viewAny($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
        ];
    }
}
