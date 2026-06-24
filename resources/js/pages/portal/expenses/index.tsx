import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { formatDate } from '@/lib/format';
import type { Expense } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { expenses: Paginated<Expense> };

export default function Page({ expenses }: Props) {
    return (
        <>
            <Head title="Expenses" />
            <PageHeader
                title="Expenses"
                actions={
                    <Link
                        href="/portal/expenses/create"
                        className="btn btn-primary"
                    >
                        <i className="bi bi-plus-lg me-1" />
                        Add
                    </Link>
                }
            />
            <DataTable
                columns={[
                    {
                        key: 'date',
                        label: 'Date',
                        render: (row) => formatDate(row.date),
                    },
                    { key: 'amount', label: 'Amount' },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/expenses/${row.sqid}`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                View
                            </Link>
                        ),
                    },
                ]}
                data={expenses}
            />
        </>
    );
}
