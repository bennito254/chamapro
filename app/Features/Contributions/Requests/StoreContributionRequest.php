<?php

namespace App\Features\Contributions\Requests;

use App\Features\Contributions\Models\ContributionType;
use App\Features\Contributions\Services\ContributionEligibilityService;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

/**
 * Form request validation for Store Contribution.
 */
class StoreContributionRequest extends FormRequest
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
            'member_id' => ['required', 'exists:members,id'],
            'contribution_type_id' => ['required', 'exists:contribution_types,id'],
            'contribution_channel_id' => ['required', 'exists:contribution_channels,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * With validator.
     *
     * @param  mixed  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $type = ContributionType::query()->find($this->integer('contribution_type_id'));

            if ($type === null) {
                return;
            }

            try {
                app(ContributionEligibilityService::class)->assertCanRecord(
                    $this->integer('member_id'),
                    $type,
                    $this->date('date')->toDateString(),
                    (float) $this->input('amount'),
                );
            } catch (InvalidArgumentException $exception) {
                $validator->errors()->add('amount', $exception->getMessage());
            }
        });
    }
}
