import { Form, Head, Link, router } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import { edit, index, show } from '@/routes/admin/groups';
import type { Group, Subscription } from '@/types/models';

type Owner = {
    name?: string | null;
    email?: string | null;
    phone?: string | null;
};

type Props = {
    group: Group & { active_subscription?: Subscription | null; activeSubscription?: Subscription | null };
    owner: Owner;
};

export default function Page({ group, owner }: Props) {
    const subscription = group.active_subscription ?? group.activeSubscription ?? null;

    const suspend = () => router.post(`/admin/groups/${group.sqid}/suspend`);
    const activate = () => router.post(`/admin/groups/${group.sqid}/activate`);
    const impersonate = () => router.post(`/admin/impersonate/${group.sqid}`);

    return (
        <>
            <Head title={group.name} />
            <PageHeader
                title={group.name}
                description="Group profile, subscription, and platform actions."
                breadcrumbs={[
                    { label: 'Groups', href: index.url() },
                    { label: group.name },
                ]}
                actions={
                    <div className="d-flex flex-wrap gap-2">
                        <button type="button" className="btn btn-outline-primary btn-sm" onClick={impersonate}>
                            <i className="bi bi-box-arrow-in-right me-1" />
                            Impersonate
                        </button>
                        {group.status === 'active' ? (
                            <button type="button" className="btn btn-outline-warning btn-sm" onClick={suspend}>
                                Suspend
                            </button>
                        ) : (
                            <button type="button" className="btn btn-outline-success btn-sm" onClick={activate}>
                                Activate
                            </button>
                        )}
                        <Link href={edit.url(group)} className="btn btn-primary btn-sm">
                            Edit
                        </Link>
                    </div>
                }
            />

            <div className="row g-4">
                <div className="col-lg-6">
                    <DetailCard
                        title="Group details"
                        backHref={index.url()}
                        fields={[
                            { label: 'Name', value: group.name },
                            { label: 'Status', value: group.status, format: 'badge' },
                            { label: 'County', value: group.county },
                            { label: 'Phone', value: group.phone },
                            { label: 'Email', value: group.email },
                            { label: 'Members', value: group.members_count },
                            { label: 'Users', value: group.users_count },
                        ]}
                    />
                </div>
                <div className="col-lg-6">
                    <DetailCard
                        title="Owner contact"
                        fields={[
                            { label: 'Name', value: owner.name },
                            { label: 'Email', value: owner.email },
                            { label: 'Phone', value: owner.phone },
                        ]}
                    />
                </div>
                <div className="col-lg-6">
                    <DetailCard
                        title="Subscription"
                        fields={subscription
                            ? [
                                  { label: 'Plan', value: subscription.plan?.name },
                                  {
                                      label: 'Amount',
                                      value: subscription.plan?.amount
                                          ? formatCurrency(Number(subscription.plan.amount))
                                          : null,
                                  },
                                  { label: 'Status', value: subscription.status, format: 'badge' },
                                  { label: 'Start', value: subscription.start_date, format: 'date' },
                                  { label: 'End', value: subscription.end_date, format: 'date' },
                              ]
                            : [{ label: 'Status', value: 'No active subscription' }]}
                    />
                </div>
                <div className="col-lg-6">
                    <div className="card border-0 shadow-sm">
                        <div className="card-header bg-white">
                            <h5 className="mb-0 fw-semibold">Extend subscription</h5>
                        </div>
                        <div className="card-body">
                            <Form
                                action={`/admin/groups/${group.sqid}/extend-subscription`}
                                method="post"
                                className="d-flex gap-2 align-items-end"
                            >
                                <div className="flex-grow-1">
                                    <label htmlFor="days" className="form-label">
                                        Days to extend
                                    </label>
                                    <input
                                        id="days"
                                        name="days"
                                        type="number"
                                        min={1}
                                        max={365}
                                        defaultValue={30}
                                        className="form-control"
                                        required
                                    />
                                </div>
                                <button type="submit" className="btn btn-primary">
                                    Extend
                                </button>
                            </Form>
                            {subscription && (
                                <p className="text-muted small mt-3 mb-0">
                                    Current status: {titleCase(subscription.status)} until{' '}
                                    {formatDate(subscription.end_date)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
