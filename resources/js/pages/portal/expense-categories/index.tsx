import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { ExpenseCategory } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { categories: Paginated<ExpenseCategory> };

export default function Page({ categories }: Props) {
    return (
        <>
            <Head title="Expense Categories" />
            <PageHeader
                title="Expense Categories"
                actions={
                    <Link
                        href="/portal/expense-categories/create"
                        className="btn btn-primary"
                    >
                        <i className="bi bi-plus-lg me-1" />
                        Add
                    </Link>
                }
            />
            <DataTable
                columns={[
                    { key: 'name', label: 'Name' },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/expense-categories/${row.sqid}`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                View
                            </Link>
                        ),
                    },
                ]}
                data={categories}
            />
        </>
    );
}
