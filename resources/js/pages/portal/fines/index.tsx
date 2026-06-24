import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Fine } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { fines: Paginated<Fine> };

export default function Page({ fines }: Props) {
    return (
        <>
            <Head title="Fines" />
            <PageHeader
                title="Fines"
                actions={
                    <Link
                        href="/portal/fines/create"
                        className="btn btn-primary"
                    >
                        <i className="bi bi-plus-lg me-1" />
                        Add
                    </Link>
                }
            />
            <DataTable
                columns={[
                    { key: 'amount', label: 'Amount' },
                    {
                        key: 'is_paid',
                        label: 'Paid',
                        render: (r) => (r.is_paid ? 'Yes' : 'No'),
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/fines/${row.sqid}`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                View
                            </Link>
                        ),
                    },
                ]}
                data={fines}
            />
        </>
    );
}
