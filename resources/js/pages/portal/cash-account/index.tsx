import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { CashAccount } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { accounts: Paginated<CashAccount> };

export default function Page({ accounts }: Props) {
    return (
        <>
            <Head title="Cash Accounts" />
            <PageHeader title="Cash Accounts" actions={undefined} />
            <DataTable
                columns={[
                    { key: 'name', label: 'Name' },
                    { key: 'balance', label: 'Balance' },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/cash-account/${row.sqid}`}
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
