import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { ContributionType } from '@/types/models';

type Props = { types: Paginated<ContributionType> };

export default function Page({ types }: Props) {
    return (
        <>
            <Head title="Contribution Types" />
            <PageHeader
                title="Contribution Types"
                actions={
                    <Link href="/portal/contribution-types/create" className="btn btn-primary">
                        <i className="bi bi-plus-lg me-1" />
                        Add
                    </Link>
                }
            />
            <DataTable
                columns={[
                    { key: 'name', label: 'Name' },
                    {
                        key: 'default_amount',
                        label: 'Default Amount',
                        render: (row) => (row.default_amount != null ? String(row.default_amount) : '—'),
                    },
                    { key: 'frequency', label: 'Frequency' },
                    {
                        key: 'save_to_bank',
                        label: 'Loan fund',
                        render: (row) => (
                            <span className={`badge ${row.save_to_bank ? 'bg-success' : 'bg-secondary'}`}>
                                {row.save_to_bank ? 'Bank' : 'Separate'}
                            </span>
                        ),
                    },
                    { key: 'status', label: 'Status' },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/contribution-types/${row.sqid}/edit`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                Edit
                            </Link>
                        ),
                    },
                ]}
                data={types}
            />
        </>
    );
}
