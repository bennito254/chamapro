<?php

namespace App\Features\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Update Expense Category.
 */
class UpdateExpenseCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
