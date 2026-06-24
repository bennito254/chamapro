import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { SupportTicket } from '@/types/models';

type Props = { tickets: Paginated<SupportTicket> };

export default function Page({ tickets }: Props) {
    return (
        <>
            <Head title="Support" />
            <PageHeader title="Support" actions={<Link href="/portal/support-tickets/create" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />Add</Link>} />
            <DataTable columns={[
                    { key: 'subject', label: 'Subject' },
                    { key: 'status', label: 'Status' },
                    { key: 'actions', label: '', render: (row) => <Link href={`/portal/support-tickets/${row.sqid}`} className="btn btn-sm btn-outline-primary">View</Link> }
            ]} data={tickets} />
        </>
    );
}
