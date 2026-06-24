import { Form, Head, Link, router } from '@inertiajs/react';
import AdminRowActions from '@/components/admin/AdminRowActions';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import { edit, index } from '@/routes/admin/subscriptions';
import type { Paginated } from '@/types/pagination';

type Owner = {
    name?: string | null;
    email?: string | null;
    phone?: string | null;
};

type SubscriptionRow = {
    sqid: string;
    status: string;
    start_date: string;
    end_date: string;
    group?: { sqid: string; name: string } | null;
    plan?: { sqid: string; name: string; amount?: number | string } | null;
    owner: Owner;
};

type StatusOption = { value: string; label: string };

type Props = {
    subscriptions: Paginated<SubscriptionRow>;
    filters: { status: string };
    statusOptions: StatusOption[];
};

function contactLabel(owner: Owner): string {
    const parts = [owner.phone, owner.email].filter(Boolean);

    return parts.length > 0 ? parts.join(' · ') : '—';
}

export default function Page({ subscriptions, filters, statusOptions }: Props) {
    const applyStatus = (status: string) => {
        router.get(
            index.url({ query: status === 'all' ? {} : { status } }),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Subscriptions" />
            <PageHeader
                title="Subscriptions"
                description="Billing status across all groups with owner contact details."
            />

            <div className="card border-0 shadow-sm mb-4">
                <div className="card-body py-3">
                    <div className="d-flex flex-wrap align-items-center gap-2">
                        <span className="text-muted small me-1">Filter by status:</span>
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

            <DataTable
                columns={[
                    {
                        key: 'group',
                        label: 'Group',
                        render: (row) => row.group?.name ?? '—',
                    },
                    {
                        key: 'owner_name',
                        label: 'Owner',
                        render: (row) => row.owner.name ?? '—',
                    },
                    {
                        key: 'owner_contact',
                        label: 'Contact',
                        render: (row) => contactLabel(row.owner),
                    },
                    {
                        key: 'plan',
                        label: 'Plan',
                        render: (row) => row.plan?.name ?? '—',
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        render: (row) => (
                            <span className="badge bg-secondary">{titleCase(row.status)}</span>
                        ),
                    },
                    {
                        key: 'start_date',
                        label: 'Start',
                        render: (row) => formatDate(row.start_date),
                    },
                    {
                        key: 'end_date',
                        label: 'End',
                        render: (row) => formatDate(row.end_date),
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => <AdminRowActions editHref={edit.url(row)} />,
                    },
                ]}
                data={subscriptions}
                emptyMessage="No subscriptions match this filter."
            />
        </>
    );
}
