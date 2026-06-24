import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import { index, update } from '@/routes/admin/subscriptions';
import type { Subscription, SubscriptionPlan } from '@/types/models';

type Owner = {
    name?: string | null;
    email?: string | null;
    phone?: string | null;
};

type Props = {
    subscription: Subscription;
    owner: Owner;
    plans: Array<Pick<SubscriptionPlan, 'id' | 'name' | 'amount' | 'status'>>;
};

const statusOptions = [
    { value: 'trial', label: 'Trial' },
    { value: 'active', label: 'Active' },
    { value: 'expired', label: 'Expired' },
    { value: 'suspended', label: 'Suspended' },
];

export default function Page({ subscription, owner, plans }: Props) {
    const groupName = subscription.group?.name ?? 'Group';

    return (
        <>
            <Head title={`Edit subscription · ${groupName}`} />
            <PageHeader
                title={`Edit subscription`}
                description={groupName}
                breadcrumbs={[
                    { label: 'Subscriptions', href: index.url() },
                    { label: groupName },
                ]}
            />

            <div className="row g-4">
                <div className="col-lg-4">
                    <div className="card h-100 border-0 shadow-sm">
                        <div className="card-body">
                            <h6 className="fw-semibold mb-3">Group owner</h6>
                            <dl className="mb-0">
                                <dt className="small text-muted">Name</dt>
                                <dd>{owner.name ?? '—'}</dd>
                                <dt className="small text-muted">Email</dt>
                                <dd>{owner.email ?? '—'}</dd>
                                <dt className="small text-muted">Phone</dt>
                                <dd>{owner.phone ?? '—'}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body p-4">
                            <Form {...update.form(subscription)}>
                                {({ errors, processing }) => (
                                    <>
                                        <div className="row g-3">
                                            <div className="col-md-6">
                                                <FormField
                                                    label="Plan"
                                                    name="subscription_plan_id"
                                                    required
                                                    options={plans.map(
                                                        (plan) => ({
                                                            value: String(
                                                                plan.id,
                                                            ),
                                                            label: `${plan.name} (${formatCurrency(Number(plan.amount ?? 0))})`,
                                                        }),
                                                    )}
                                                    defaultValue={String(
                                                        subscription.subscription_plan_id,
                                                    )}
                                                    error={
                                                        errors.subscription_plan_id
                                                    }
                                                />
                                            </div>
                                            <div className="col-md-6">
                                                <FormField
                                                    label="Status"
                                                    name="status"
                                                    required
                                                    options={statusOptions}
                                                    defaultValue={
                                                        subscription.status
                                                    }
                                                    error={errors.status}
                                                />
                                            </div>
                                            <div className="col-md-6">
                                                <FormField
                                                    label="Start date"
                                                    name="start_date"
                                                    type="date"
                                                    required
                                                    defaultValue={subscription.start_date?.slice(
                                                        0,
                                                        10,
                                                    )}
                                                    error={errors.start_date}
                                                />
                                            </div>
                                            <div className="col-md-6">
                                                <FormField
                                                    label="End date"
                                                    name="end_date"
                                                    type="date"
                                                    required
                                                    defaultValue={subscription.end_date?.slice(
                                                        0,
                                                        10,
                                                    )}
                                                    error={errors.end_date}
                                                />
                                            </div>
                                        </div>
                                        <p className="small mt-3 mb-0 text-muted">
                                            Current period:{' '}
                                            {formatDate(
                                                subscription.start_date,
                                            )}{' '}
                                            –{' '}
                                            {formatDate(subscription.end_date)}{' '}
                                            ({titleCase(subscription.status)})
                                        </p>
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
                </div>
            </div>
        </>
    );
}
