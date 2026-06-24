import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { titleCase } from '@/lib/format';
import type { LoanProduct } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { products: Paginated<LoanProduct> };

export default function Page({ products }: Props) {
    return (
        <>
            <Head title="Loan Products" />
            <PageHeader
                title="Loan Products"
                actions={
                    <Link
                        href="/portal/loan-products/create"
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
                        key: 'interest_value',
                        label: 'Interest',
                        render: (row) =>
                            row.interest_type === 'percentage'
                                ? `${row.interest_value}%`
                                : String(row.interest_value),
                    },
                    { key: 'max_amount', label: 'Max Amount' },
                    {
                        key: 'repayment_period',
                        label: 'Term',
                        render: (row) => `${row.repayment_period} mo`,
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        render: (row) => titleCase(row.status),
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/loan-products/${row.sqid}/edit`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                Edit
                            </Link>
                        ),
                    },
                ]}
                data={products}
            />
        </>
    );
}
