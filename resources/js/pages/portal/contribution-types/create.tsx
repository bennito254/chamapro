import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { amountTypeOptions, contributionFrequencyOptions, statusOptions } from '@/lib/form-options';
import { store } from '@/routes/portal/contribution-types';
import PageHeader from '@/components/shared/PageHeader';

export default function Page() {
    const route = store.form();

    return (
        <>
            <Head title="Create Contribution Type" />
            <PageHeader title="Create Contribution Type" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Name" name="name" required error={errors.name} />
                                <FormField label="Description" name="description" type="textarea" error={errors.description} />
                                <FormField
                                    label="Default Amount"
                                    name="default_amount"
                                    type="number"
                                    error={errors.default_amount}
                                    help="Leave blank for variable amounts"
                                />
                                <FormField
                                    label="Amount Type"
                                    name="amount_type"
                                    required
                                    defaultValue="fixed"
                                    options={amountTypeOptions}
                                    error={errors.amount_type}
                                />
                                <FormField
                                    label="Frequency"
                                    name="frequency"
                                    required
                                    defaultValue="monthly"
                                    options={contributionFrequencyOptions}
                                    error={errors.frequency}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue="active"
                                    options={statusOptions}
                                    error={errors.status}
                                />
                                <input type="hidden" name="save_to_bank" value="0" />
                                <div className="form-check mb-3">
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        name="save_to_bank"
                                        value="1"
                                        id="save_to_bank"
                                        defaultChecked
                                    />
                                    <label className="form-check-label" htmlFor="save_to_bank">
                                        Save to bank (available for loans)
                                    </label>
                                    <div className="form-text">
                                        When enabled, contributions of this type are deposited to the group bank account and count toward the loan fund.
                                    </div>
                                </div>
                                <div className="d-flex gap-2 mt-3">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                    <Link href="/portal/contribution-types" className="btn btn-outline-secondary">
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
