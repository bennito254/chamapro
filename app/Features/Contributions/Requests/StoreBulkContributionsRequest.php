<?php

namespace App\Features\Contributions\Requests;

use App\Features\Contributions\Models\ContributionType;
use App\Features\Contributions\Services\ContributionEligibilityService;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

/**
 * Form request validation for Store Bulk Contributions.
 */
class StoreBulkContributionsRequest extends FormRequest
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
            'contribution_type_id' => ['required', 'exists:contribution_types,id'],
            'contribution_channel_id' => ['required', 'exists:contribution_channels,id'],
            'date' => ['required', 'date'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.member_id' => ['required', 'exists:members,id'],
            'entries.*.amount' => ['required', 'numeric', 'min:0.01'],
            'entries.*.transaction_reference' => ['nullable', 'string', 'max:255'],
            'entries.*.notes' => ['nullable', 'string', 'max:500'],
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

            $eligibility = app(ContributionEligibilityService::class);
            $date = $this->date('date')->toDateString();

            foreach ($this->input('entries', []) as $index => $entry) {
                try {
                    $eligibility->assertCanRecord(
                        (int) $entry['member_id'],
                        $type,
                        $date,
                        (float) $entry['amount'],
                    );
                } catch (InvalidArgumentException $exception) {
                    $validator->errors()->add("entries.{$index}.member_id", $exception->getMessage());
                }
            }
        });
    }
}
