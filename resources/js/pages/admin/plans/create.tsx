import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { index, store } from '@/routes/admin/plans';

const billingOptions = [
    { value: 'monthly', label: 'Monthly' },
    { value: 'annual', label: 'Annual' },
];

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

export default function Page() {
    return (
        <>
            <Head title="Create Plan" />
            <PageHeader
                title="Create subscription plan"
                description="Define pricing, member limits, and trial period."
                breadcrumbs={[
                    { label: 'Plans', href: index.url() },
                    { label: 'Create' },
                ]}
            />
            <div className="card border-0 shadow-sm">
                <div className="card-body p-4">
                    <Form {...store.form()}>
                        {({ errors, processing }) => (
                            <>
                                <div className="row g-3">
                                    <div className="col-md-8">
                                        <FormField label="Plan name" name="name" required error={errors.name} />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Status"
                                            name="status"
                                            required
                                            options={statusOptions}
                                            defaultValue="active"
                                            error={errors.status}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Billing cycle"
                                            name="billing_cycle"
                                            required
                                            options={billingOptions}
                                            defaultValue="monthly"
                                            error={errors.billing_cycle}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Amount (KES)"
                                            name="amount"
                                            type="number"
                                            required
                                            defaultValue="0"
                                            error={errors.amount}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Discount %"
                                            name="discount_percentage"
                                            type="number"
                                            defaultValue="0"
                                            error={errors.discount_percentage}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Max members"
                                            name="max_members"
                                            type="number"
                                            required
                                            defaultValue="50"
                                            error={errors.max_members}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Max users"
                                            name="max_users"
                                            type="number"
                                            required
                                            defaultValue="5"
                                            error={errors.max_users}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Trial days"
                                            name="trial_days"
                                            type="number"
                                            required
                                            defaultValue="14"
                                            error={errors.trial_days}
                                        />
                                    </div>
                                </div>
                                <div className="d-flex gap-2 mt-4">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Create plan'}
                                    </button>
                                    <Link href={index()} className="btn btn-outline-secondary">
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
