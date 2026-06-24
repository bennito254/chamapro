import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { interestTypeOptions, statusOptions } from '@/lib/form-options';
import { store } from '@/routes/portal/loan-products';

export default function Page() {
    const route = store.form();

    return (
        <>
            <Head title="Create Loan Product" />
            <PageHeader title="Create Loan Product" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Name"
                                    name="name"
                                    required
                                    error={errors.name}
                                />
                                <FormField
                                    label="Description"
                                    name="description"
                                    type="textarea"
                                    error={errors.description}
                                />
                                <FormField
                                    label="Max Amount"
                                    name="max_amount"
                                    type="number"
                                    required
                                    error={errors.max_amount}
                                    help="Maximum principal a member can borrow under this product"
                                />
                                <FormField
                                    label="Max Multiplier"
                                    name="max_multiplier"
                                    type="number"
                                    defaultValue="3"
                                    error={errors.max_multiplier}
                                    help="Member capacity = bank contributions × this multiplier"
                                />
                                <FormField
                                    label="Interest Type"
                                    name="interest_type"
                                    required
                                    defaultValue="percentage"
                                    options={interestTypeOptions}
                                    error={errors.interest_type}
                                />
                                <FormField
                                    label="Interest Value"
                                    name="interest_value"
                                    type="number"
                                    required
                                    error={errors.interest_value}
                                    help="Percentage rate or fixed interest amount, depending on type"
                                />
                                <FormField
                                    label="Repayment Period (months)"
                                    name="repayment_period"
                                    type="number"
                                    required
                                    defaultValue="12"
                                    error={errors.repayment_period}
                                />
                                <FormField
                                    label="Grace Period (months)"
                                    name="grace_period"
                                    type="number"
                                    defaultValue="0"
                                    error={errors.grace_period}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue="active"
                                    options={statusOptions}
                                    error={errors.status}
                                />
                                <div className="d-flex mt-3 gap-2">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                    <Link
                                        href="/portal/loan-products"
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
