import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import {
    amountTypeOptions,
    contributionFrequencyOptions,
    statusOptions,
} from '@/lib/form-options';
import { update } from '@/routes/portal/contribution-types';
import type { ContributionType } from '@/types/models';

type Props = {
    type: ContributionType;
};

export default function Page({ type }: Props) {
    const route = update.form(type);

    return (
        <>
            <Head title="Edit Contribution Type" />
            <PageHeader title="Edit Contribution Type" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Name"
                                    name="name"
                                    required
                                    defaultValue={String(type.name ?? '')}
                                    error={errors.name}
                                />
                                <FormField
                                    label="Description"
                                    name="description"
                                    type="textarea"
                                    defaultValue={String(
                                        type.description ?? '',
                                    )}
                                    error={errors.description}
                                />
                                <FormField
                                    label="Default Amount"
                                    name="default_amount"
                                    type="number"
                                    defaultValue={
                                        type.default_amount != null
                                            ? String(type.default_amount)
                                            : ''
                                    }
                                    error={errors.default_amount}
                                />
                                <FormField
                                    label="Amount Type"
                                    name="amount_type"
                                    required
                                    defaultValue={String(
                                        type.amount_type ?? 'fixed',
                                    )}
                                    options={amountTypeOptions}
                                    error={errors.amount_type}
                                />
                                <FormField
                                    label="Frequency"
                                    name="frequency"
                                    required
                                    defaultValue={String(
                                        type.frequency ?? 'monthly',
                                    )}
                                    options={contributionFrequencyOptions}
                                    error={errors.frequency}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue={String(
                                        type.status ?? 'active',
                                    )}
                                    options={statusOptions}
                                    error={errors.status}
                                />
                                <input
                                    type="hidden"
                                    name="save_to_bank"
                                    value="0"
                                />
                                <div className="form-check mb-3">
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        name="save_to_bank"
                                        value="1"
                                        id="save_to_bank"
                                        defaultChecked={type.save_to_bank}
                                    />
                                    <label
                                        className="form-check-label"
                                        htmlFor="save_to_bank"
                                    >
                                        Save to bank (available for loans)
                                    </label>
                                    <div className="form-text">
                                        When enabled, contributions of this type
                                        are deposited to the group bank account
                                        and count toward the loan fund.
                                    </div>
                                </div>
                                <div className="d-flex mt-3 gap-2">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                    <Link
                                        href="/portal/contribution-types"
                                        className="btn btn-outline-secondary"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}
