import { Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import type { BankAccount } from '@/types/models';

type Props = { account: BankAccount };

export default function Page({ account }: Props) {
    return (
        <>
            <Head title="Bank Account" />
            <PageHeader title="Bank Account" />
            <DetailCard
                title="Details"
                editHref={`/portal/bank-accounts/${account.sqid}/edit`}
                deleteHref={`/portal/bank-accounts/${account.sqid}`}
                backHref="/portal/bank-accounts"
                fields={[
                    { label: 'Account Name', value: account.account_name },
                    { label: 'Bank', value: account.bank_name },
                    { label: 'Account Number', value: account.account_number },
                    { label: 'Branch', value: account.branch ?? '—' },
                    {
                        label: 'Current Balance',
                        value: String(account.current_balance ?? 0),
                    },
                    { label: 'Status', value: account.status ?? 'active' },
                ]}
            />
        </>
    );
}
