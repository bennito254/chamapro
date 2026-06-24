import { Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import type { Fine } from '@/types/models';

type Props = { fine: Fine };

export default function Page({ fine }: Props) {
    return (
        <>
            <Head title="Fine" />
            <PageHeader title="Fine" />
            <DetailCard title="Details" editHref={`/portal/fines/${fine.sqid}/edit`} deleteHref={`/portal/fines/${fine.sqid}`} backHref="/portal/fines" fields={[
        { label: 'Amount', value: fine.amount },
        { label: 'Paid', value: fine.is_paid },
        { label: 'Member', value: fine.member?.full_name }
            ]} />
        </>
    );
}
