<?php

namespace App\Features\Reports\Requests;

use App\Policies\ReportPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates filters and format for exporting a portal report.
 */
class ExportReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return app(ReportPolicy::class)->export($this->user());
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
            'format' => ['nullable', 'string', Rule::in(['csv', 'pdf', 'json'])],
        ];
    }
}
