import { Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import type { CashAccount } from '@/types/models';

type Props = { account: CashAccount };

export default function Page({ account }: Props) {
    return (
        <>
            <Head title="Cash Account" />
            <PageHeader title="Cash Account" />
            <DetailCard title="Details" editHref="undefined" deleteHref="undefined" backHref="/portal/cash-account" fields={[
        { label: 'Name', value: account.name },
        { label: 'Balance', value: account.balance }
            ]} />
        </>
    );
}
