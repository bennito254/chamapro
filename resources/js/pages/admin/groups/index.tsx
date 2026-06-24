import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { Group } from '@/types/models';

type Props = { groups: Paginated<Group> };

export default function Page({ groups }: Props) {
    return (
        <>
            <Head title="Groups" />
            <PageHeader title="Groups" actions={<Link href="/admin/groups/create" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />Add</Link>} />
            <DataTable columns={[
                    { key: 'name', label: 'Name' },
                    { key: 'status', label: 'Status', render: (r) => <span className="badge bg-secondary">{r.status}</span> },
                    { key: 'county', label: 'County' },
                    { key: 'members_count', label: 'Members' },
                    { key: 'actions', label: '', render: (row) => <Link href={`/admin/groups/${row.sqid}`} className="btn btn-sm btn-outline-primary">View</Link> }
            ]} data={groups} />
        </>
    );
}
