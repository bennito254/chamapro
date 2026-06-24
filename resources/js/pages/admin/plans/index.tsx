import { Head, Link } from '@inertiajs/react';
import AdminRowActions from '@/components/admin/AdminRowActions';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency, titleCase } from '@/lib/format';
import { create, destroy, edit, index } from '@/routes/admin/plans';
import type { Paginated } from '@/types/pagination';
import type { SubscriptionPlan } from '@/types/models';

type PlanRow = SubscriptionPlan & { subscriptions_count?: number };

type Props = { plans: Paginated<PlanRow> };

export default function Page({ plans }: Props) {
    return (
        <>
            <Head title="Subscription Plans" />
            <PageHeader
                title="Subscription Plans"
                description="Manage billing tiers, limits, and trial settings."
                actions={
                    <Link href={create()} className="btn btn-primary">
                        <i className="bi bi-plus-lg me-1" />
                        New plan
                    </Link>
                }
            />
            <DataTable
                columns={[
                    { key: 'name', label: 'Name' },
                    {
                        key: 'amount',
                        label: 'Amount',
                        render: (row) => formatCurrency(Number(row.amount ?? 0)),
                        className: 'text-end',
                    },
                    {
                        key: 'billing_cycle',
                        label: 'Billing',
                        render: (row) => titleCase(String(row.billing_cycle ?? '')),
                    },
                    { key: 'max_members', label: 'Max members', className: 'text-end' },
                    { key: 'max_users', label: 'Max users', className: 'text-end' },
                    {
                        key: 'status',
                        label: 'Status',
                        render: (row) => (
                            <span className={`badge ${row.status === 'active' ? 'bg-success' : 'bg-secondary'}`}>
                                {titleCase(String(row.status ?? ''))}
                            </span>
                        ),
                    },
                    {
                        key: 'subscriptions_count',
                        label: 'Subscriptions',
                        className: 'text-end',
                        render: (row) => row.subscriptions_count ?? 0,
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <AdminRowActions
                                editHref={edit.url(row)}
                                deleteHref={destroy.url(row)}
                            />
                        ),
                    },
                ]}
                data={plans}
                emptyMessage="No subscription plans yet."
            />
            <div className="mt-3">
                <Link href={index()} className="text-muted small text-decoration-none">
                    Refresh list
                </Link>
            </div>
        </>
    );
}
