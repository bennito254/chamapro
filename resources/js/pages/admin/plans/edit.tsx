import { Form, Head, Link } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { confirmDelete } from '@/lib/alerts';
import { destroy, index, update } from '@/routes/admin/plans';
import type { SubscriptionPlan } from '@/types/models';

type Props = {
    plan: SubscriptionPlan & {
        amount?: number | string;
        discount_percentage?: number | string;
        max_members?: number;
        max_users?: number;
        trial_days?: number;
        billing_cycle?: string;
    };
};

const billingOptions = [
    { value: 'monthly', label: 'Monthly' },
    { value: 'annual', label: 'Annual' },
];

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

export default function Page({ plan }: Props) {
    const handleDelete = async () => {
        const confirmed = await confirmDelete();

        if (confirmed) {
            router.delete(destroy.url(plan));
        }
    };

    return (
        <>
            <Head title={`Edit ${plan.name}`} />
            <PageHeader
                title={`Edit ${plan.name}`}
                description="Update plan pricing and limits."
                breadcrumbs={[
                    { label: 'Plans', href: index.url() },
                    { label: plan.name },
                ]}
                actions={
                    <button
                        type="button"
                        className="btn btn-outline-danger btn-sm"
                        onClick={handleDelete}
                    >
                        <i className="bi bi-trash me-1" />
                        Delete
                    </button>
                }
            />
            <div className="card border-0 shadow-sm">
                <div className="card-body p-4">
                    <Form {...update.form(plan)}>
                        {({ errors, processing }) => (
                            <>
                                <div className="row g-3">
                                    <div className="col-md-8">
                                        <FormField
                                            label="Plan name"
                                            name="name"
                                            required
                                            defaultValue={plan.name}
                                            error={errors.name}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Status"
                                            name="status"
                                            required
                                            options={statusOptions}
                                            defaultValue={
                                                plan.status ?? 'active'
                                            }
                                            error={errors.status}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Billing cycle"
                                            name="billing_cycle"
                                            required
                                            options={billingOptions}
                                            defaultValue={
                                                plan.billing_cycle ?? 'monthly'
                                            }
                                            error={errors.billing_cycle}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Amount (KES)"
                                            name="amount"
                                            type="number"
                                            required
                                            defaultValue={String(
                                                plan.amount ?? 0,
                                            )}
                                            error={errors.amount}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Discount %"
                                            name="discount_percentage"
                                            type="number"
                                            defaultValue={String(
                                                plan.discount_percentage ?? 0,
                                            )}
                                            error={errors.discount_percentage}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Max members"
                                            name="max_members"
                                            type="number"
                                            required
                                            defaultValue={String(
                                                plan.max_members ?? 50,
                                            )}
                                            error={errors.max_members}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Max users"
                                            name="max_users"
                                            type="number"
                                            required
                                            defaultValue={String(
                                                plan.max_users ?? 5,
                                            )}
                                            error={errors.max_users}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Trial days"
                                            name="trial_days"
                                            type="number"
                                            required
                                            defaultValue={String(
                                                plan.trial_days ?? 14,
                                            )}
                                            error={errors.trial_days}
                                        />
                                    </div>
                                </div>
                                <div className="d-flex mt-4 gap-2">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Saving...'
                                            : 'Save changes'}
                                    </button>
                                    <Link
                                        href={index()}
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
