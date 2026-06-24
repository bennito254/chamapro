<?php

namespace App\Features\Expenses\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Expense Category.
 */
class StoreExpenseCategoryRequest extends FormRequest
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
