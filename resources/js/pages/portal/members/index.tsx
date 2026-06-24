import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { Member } from '@/types/models';

type Props = { members: Paginated<Member> };

export default function Page({ members }: Props) {
    return (
        <>
            <Head title="Members" />
            <PageHeader title="Members" actions={<Link href="/portal/members/create" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />Add</Link>} />
            <DataTable columns={[
                    { key: 'membership_number', label: '#' },
                    { key: 'full_name', label: 'Name' },
                    { key: 'phone_number', label: 'Phone' },
                    { key: 'status', label: 'Status' },
                    { key: 'actions', label: '', render: (row) => <Link href={`/portal/members/${row.sqid}`} className="btn btn-sm btn-outline-primary">View</Link> }
            ]} data={members} />
        </>
    );
}
