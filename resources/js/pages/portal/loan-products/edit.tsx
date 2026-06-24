import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { interestTypeOptions, statusOptions } from '@/lib/form-options';
import { update } from '@/routes/portal/loan-products';
import PageHeader from '@/components/shared/PageHeader';
import type { LoanProduct } from '@/types/models';

type Props = {
    product: LoanProduct;
};

export default function Page({ product }: Props) {
    const route = update.form(product);

    return (
        <>
            <Head title="Edit Loan Product" />
            <PageHeader title="Edit Loan Product" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Name"
                                    name="name"
                                    required
                                    defaultValue={String(product.name ?? '')}
                                    error={errors.name}
                                />
                                <FormField
                                    label="Description"
                                    name="description"
                                    type="textarea"
                                    defaultValue={String(product.description ?? '')}
                                    error={errors.description}
                                />
                                <FormField
                                    label="Max Amount"
                                    name="max_amount"
                                    type="number"
                                    required
                                    defaultValue={String(product.max_amount ?? '')}
                                    error={errors.max_amount}
                                />
                                <FormField
                                    label="Max Multiplier"
                                    name="max_multiplier"
                                    type="number"
                                    defaultValue={product.max_multiplier != null ? String(product.max_multiplier) : '3'}
                                    error={errors.max_multiplier}
                                />
                                <FormField
                                    label="Interest Type"
                                    name="interest_type"
                                    required
                                    defaultValue={String(product.interest_type ?? 'percentage')}
                                    options={interestTypeOptions}
                                    error={errors.interest_type}
                                />
                                <FormField
                                    label="Interest Value"
                                    name="interest_value"
                                    type="number"
                                    required
                                    defaultValue={String(product.interest_value ?? '')}
                                    error={errors.interest_value}
                                />
                                <FormField
                                    label="Repayment Period (months)"
                                    name="repayment_period"
                                    type="number"
                                    required
                                    defaultValue={String(product.repayment_period ?? 12)}
                                    error={errors.repayment_period}
                                />
                                <FormField
                                    label="Grace Period (months)"
                                    name="grace_period"
                                    type="number"
                                    defaultValue={String(product.grace_period ?? 0)}
                                    error={errors.grace_period}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue={String(product.status ?? 'active')}
                                    options={statusOptions}
                                    error={errors.status}
                                />
                                <div className="d-flex gap-2 mt-3">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                    <Link href="/portal/loan-products" className="btn btn-outline-secondary">
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
