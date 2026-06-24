import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency, titleCase } from '@/lib/format';
import type { Paginated } from '@/types/pagination';
import type { LoanApplication } from '@/types/models';

type Props = { applications: Paginated<LoanApplication> };

export default function Page({ applications }: Props) {
    return (
        <>
            <Head title="Loan Applications" />
            <PageHeader
                title="Loan Applications"
                actions={
                    <Link href="/portal/loan-applications/create" className="btn btn-primary">
                        <i className="bi bi-plus-lg me-1" />
                        New application
                    </Link>
                }
            />
            <DataTable
                columns={[
                    {
                        key: 'member',
                        label: 'Member',
                        render: (row) => row.member?.full_name ?? '—',
                    },
                    {
                        key: 'product',
                        label: 'Product',
                        render: (row) => row.loan_product?.name ?? row.loanProduct?.name ?? '—',
                    },
                    {
                        key: 'requested_amount',
                        label: 'Amount',
                        render: (row) => formatCurrency(row.requested_amount),
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
                                href={`/portal/loan-applications/${row.sqid}`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                View
                            </Link>
                        ),
                    },
                ]}
                data={applications}
            />
        </>
    );
}
