import { Form, Head } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import { index } from '@/routes/admin/subscription-logs';
import type { Paginated } from '@/types/pagination';

type Owner = {
    name?: string | null;
    email?: string | null;
    phone?: string | null;
};

type SubscriptionEntry = {
    sqid: string;
    status: string;
    start_date?: string;
    end_date?: string;
    plan?: string | null;
};

type PaymentEntry = {
    sqid: string;
    status: string;
    amount: number | string;
    phone_number: string;
    mpesa_receipt_number?: string | null;
    paid_at?: string | null;
    plan?: string | null;
};

type GroupLog = {
    sqid: string;
    name: string;
    status: string;
    owner: Owner;
    subscriptions_count: number;
    payments_count: number;
    active_subscription?: {
        status: string;
        end_date?: string;
        plan?: string | null;
    } | null;
    recent_subscriptions: SubscriptionEntry[];
    recent_payments: PaymentEntry[];
};

type Props = {
    groups: Paginated<GroupLog>;
    filters: { search: string };
};

function contactLabel(owner: Owner): string {
    const parts = [owner.phone, owner.email].filter(Boolean);

    return parts.length > 0 ? parts.join(' · ') : '—';
}

export default function Page({ groups, filters }: Props) {
    return (
        <>
            <Head title="Subscription Logs" />
            <PageHeader
                title="Subscription Logs"
                description="Subscription history and M-Pesa payment activity for every group."
            />

            <div className="card mb-4 border-0 shadow-sm">
                <div className="card-body">
                    <Form method="get" action={index.url()}>
                        <div className="row g-2 align-items-end">
                            <div className="col-md-6">
                                <label htmlFor="search" className="form-label">
                                    Search group
                                </label>
                                <input
                                    id="search"
                                    name="search"
                                    type="search"
                                    className="form-control"
                                    defaultValue={filters.search}
                                    placeholder="Group name…"
                                />
                            </div>
                            <div className="col-md-auto">
                                <button
                                    type="submit"
                                    className="btn btn-primary"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                    </Form>
                </div>
            </div>

            <div className="vstack gap-4">
                {groups.data.map((group) => (
                    <div key={group.sqid} className="card border-0 shadow-sm">
                        <div className="card-header bg-white">
                            <div className="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <h5 className="fw-semibold mb-1">
                                        {group.name}
                                    </h5>
                                    <p className="small mb-0 text-muted">
                                        Owner: {group.owner.name ?? '—'} ·{' '}
                                        {contactLabel(group.owner)}
                                    </p>
                                </div>
                                <div className="text-end">
                                    <span
                                        className={`badge cp-badge-status cp-badge-status--${group.status} me-2`}
                                    >
                                        {titleCase(group.status)}
                                    </span>
                                    {group.active_subscription && (
                                        <span className="badge bg-info-subtle text-info">
                                            {group.active_subscription.plan} ·{' '}
                                            {titleCase(
                                                group.active_subscription
                                                    .status,
                                            )}{' '}
                                            · ends{' '}
                                            {formatDate(
                                                group.active_subscription
                                                    .end_date,
                                            )}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="card-body">
                            <div className="row g-4">
                                <div className="col-lg-6">
                                    <h6 className="fw-semibold mb-3">
                                        Subscription history (
                                        {group.subscriptions_count})
                                    </h6>
                                    <DataTable
                                        searchable={false}
                                        data={group.recent_subscriptions}
                                        columns={[
                                            { key: 'plan', label: 'Plan' },
                                            {
                                                key: 'status',
                                                label: 'Status',
                                                render: (row) =>
                                                    titleCase(row.status),
                                            },
                                            {
                                                key: 'start_date',
                                                label: 'Start',
                                                render: (row) =>
                                                    formatDate(row.start_date),
                                            },
                                            {
                                                key: 'end_date',
                                                label: 'End',
                                                render: (row) =>
                                                    formatDate(row.end_date),
                                            },
                                        ]}
                                    />
                                </div>
                                <div className="col-lg-6">
                                    <h6 className="fw-semibold mb-3">
                                        Payment logs ({group.payments_count})
                                    </h6>
                                    <DataTable
                                        searchable={false}
                                        data={group.recent_payments}
                                        columns={[
                                            { key: 'plan', label: 'Plan' },
                                            {
                                                key: 'amount',
                                                label: 'Amount',
                                                render: (row) =>
                                                    formatCurrency(row.amount),
                                            },
                                            {
                                                key: 'phone_number',
                                                label: 'Phone',
                                            },
                                            {
                                                key: 'status',
                                                label: 'Status',
                                                render: (row) =>
                                                    titleCase(row.status),
                                            },
                                            {
                                                key: 'mpesa_receipt_number',
                                                label: 'Receipt',
                                                render: (row) =>
                                                    row.mpesa_receipt_number ??
                                                    '—',
                                            },
                                        ]}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {groups.data.length === 0 && (
                <div className="card border-0 shadow-sm">
                    <div className="card-body py-5 text-center text-muted">
                        No groups match your search.
                    </div>
                </div>
            )}
        </>
    );
}
