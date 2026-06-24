<?php

namespace App\Features\Dividends\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Dividend.
 */
class StoreDividendRequest extends FormRequest
{
    /**
     * Authorize.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'total_profit' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
