import { Head, router } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import StatCard from '@/components/shared/StatCard';
import { formatCurrency, formatDateTime, titleCase } from '@/lib/format';
import { index } from '@/routes/admin/subscription-payments';
import type { Paginated } from '@/types/pagination';

type PaymentRow = {
    sqid: string;
    status: string;
    amount: number | string;
    phone_number: string;
    mpesa_receipt_number?: string | null;
    checkout_request_id?: string | null;
    paid_at?: string | null;
    created_at?: string | null;
    group?: { sqid: string; name: string } | null;
    plan?: { sqid: string; name: string } | null;
};

type StatusOption = { value: string; label: string };

type Props = {
    payments: Paginated<PaymentRow>;
    filters: { status: string };
    statusOptions: StatusOption[];
    stats: {
        total: number;
        completed: number;
        pending: number;
        failed: number;
    };
};

export default function Page({
    payments,
    filters,
    statusOptions,
    stats,
}: Props) {
    const applyStatus = (status: string) => {
        router.get(
            index.url({ query: status === 'all' ? {} : { status } }),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Subscription Payments" />
            <PageHeader
                title="Subscription Payments"
                description="M-Pesa Express payments for platform subscriptions."
            />

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Total"
                        value={stats.total}
                        icon="list-ul"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Completed"
                        value={stats.completed}
                        icon="check-circle"
                        color="success"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Pending"
                        value={stats.pending}
                        icon="hourglass-split"
                        color="warning"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Failed"
                        value={stats.failed}
                        icon="x-circle"
                        color="danger"
                    />
                </div>
            </div>

            <div className="card mb-4 border-0 shadow-sm">
                <div className="card-body py-3">
                    <div className="d-flex align-items-center flex-wrap gap-2">
                        <span className="small me-1 text-muted">
                            Filter by status:
                        </span>
                        {statusOptions.map((option) => (
                            <button
                                key={option.value}
                                type="button"
                                className={`btn btn-sm ${filters.status === option.value ? 'btn-primary' : 'btn-outline-secondary'}`}
                                onClick={() => applyStatus(option.value)}
                            >
                                {option.label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <DataTable
                        searchable={false}
                        data={payments.data}
                        columns={[
                            {
                                key: 'group',
                                label: 'Group',
                                render: (row) => row.group?.name ?? '—',
                            },
                            {
                                key: 'plan',
                                label: 'Plan',
                                render: (row) => row.plan?.name ?? '—',
                            },
                            {
                                key: 'amount',
                                label: 'Amount',
                                render: (row) => formatCurrency(row.amount),
                            },
                            { key: 'phone_number', label: 'Phone' },
                            {
                                key: 'status',
                                label: 'Status',
                                render: (row) => (
                                    <span
                                        className={`badge cp-badge-status cp-badge-status--${row.status}`}
                                    >
                                        {titleCase(row.status)}
                                    </span>
                                ),
                            },
                            {
                                key: 'mpesa_receipt_number',
                                label: 'Receipt',
                                render: (row) =>
                                    row.mpesa_receipt_number ?? '—',
                            },
                            {
                                key: 'paid_at',
                                label: 'Paid',
                                render: (row) =>
                                    row.paid_at
                                        ? formatDateTime(row.paid_at)
                                        : '—',
                            },
                        ]}
                        pagination={payments}
                    />
                </div>
            </div>
        </>
    );
}
