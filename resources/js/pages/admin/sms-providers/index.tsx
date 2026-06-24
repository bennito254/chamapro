import { Head, Link } from '@inertiajs/react';
import AdminRowActions from '@/components/admin/AdminRowActions';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { titleCase } from '@/lib/format';
import { create, destroy, edit, index } from '@/routes/admin/sms-providers';
import type { Paginated } from '@/types/pagination';
import type { SmsProvider } from '@/types/models';

type Props = { providers: Paginated<SmsProvider> };

export default function Page({ providers }: Props) {
    return (
        <>
            <Head title="SMS Providers" />
            <PageHeader
                title="SMS Providers"
                description="Configure gateways used for portal and owner broadcasts."
                actions={
                    <Link href={create()} className="btn btn-primary">
                        <i className="bi bi-plus-lg me-1" />
                        Add provider
                    </Link>
                }
            />
            <DataTable
                columns={[
                    { key: 'name', label: 'Name' },
                    { key: 'driver', label: 'Driver' },
                    {
                        key: 'is_default',
                        label: 'Default',
                        render: (row) => (row.is_default ? <span className="badge bg-primary">Default</span> : '—'),
                    },
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
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <AdminRowActions editHref={edit.url(row)} deleteHref={destroy.url(row)} />
                        ),
                    },
                ]}
                data={providers}
                emptyMessage="No SMS providers configured."
            />
            <div className="mt-3">
                <Link href={index()} className="text-muted small text-decoration-none">
                    Refresh list
                </Link>
            </div>
        </>
    );
}
