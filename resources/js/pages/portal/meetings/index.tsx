import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import { formatDate } from '@/lib/format';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { Meeting } from '@/types/models';

type Props = { meetings: Paginated<Meeting> };

export default function Page({ meetings }: Props) {
    return (
        <>
            <Head title="Meetings" />
            <PageHeader title="Meetings" actions={<Link href="/portal/meetings/create" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />Add</Link>} />
            <DataTable columns={[
                    { key: 'title', label: 'Title' },
                    { key: 'date', label: 'Date', render: (row) => formatDate(row.date) },
                    { key: 'actions', label: '', render: (row) => <Link href={`/portal/meetings/${row.sqid}`} className="btn btn-sm btn-outline-primary">View</Link> }
            ]} data={meetings} />
        </>
    );
}
