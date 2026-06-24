import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { FineType } from '@/types/models';

type Props = { fineTypes: Paginated<FineType> };

export default function Page({ fineTypes }: Props) {
    return (
        <>
            <Head title="Fine Types" />
            <PageHeader title="Fine Types" actions={<Link href="/portal/fine-types/create" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />Add</Link>} />
            <DataTable columns={[
                    { key: 'name', label: 'Name' },
                    { key: 'amount', label: 'Amount' },
                    { key: 'actions', label: '', render: (row) => <Link href={`/portal/fine-types/${row.sqid}`} className="btn btn-sm btn-outline-primary">View</Link> }
            ]} data={fineTypes} />
        </>
    );
}
