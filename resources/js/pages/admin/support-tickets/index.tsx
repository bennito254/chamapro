import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { SupportTicket } from '@/types/models';

type Props = { tickets: Paginated<SupportTicket> };

export default function Page({ tickets }: Props) {
    return (
        <>
            <Head title="Support Tickets" />
            <PageHeader title="Support Tickets" actions={undefined} />
            <DataTable columns={[
                    { key: 'subject', label: 'Subject' },
                    { key: 'status', label: 'Status' },
                    { key: 'priority', label: 'Priority' },
                    { key: 'actions', label: '', render: (row) => <Link href={`/admin/support-tickets/${row.sqid}`} className="btn btn-sm btn-outline-primary">View</Link> }
            ]} data={tickets} />
        </>
    );
}
