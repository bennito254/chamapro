import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { BankAccount } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { accounts: Paginated<BankAccount> };

export default function Page({ accounts }: Props) {
    return (
        <>
            <Head title="Bank Accounts" />
            <PageHeader
                title="Bank Accounts"
                actions={
                    <Link
                        href="/portal/bank-accounts/create"
                        className="btn btn-primary"
                    >
                        <i className="bi bi-plus-lg me-1" />
                        Add
                    </Link>
                }
            />
            <DataTable
                columns={[
                    { key: 'account_name', label: 'Account Name' },
                    { key: 'bank_name', label: 'Bank' },
                    { key: 'account_number', label: 'Account Number' },
                    {
                        key: 'current_balance',
                        label: 'Balance',
                        render: (row) =>
                            row.current_balance != null
                                ? String(row.current_balance)
                                : '0',
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/bank-accounts/${row.sqid}`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                View
                            </Link>
                        ),
                    },
                ]}
                data={accounts}
            />
        </>
    );
}
