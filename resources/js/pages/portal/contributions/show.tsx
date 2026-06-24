import { Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import type { Contribution } from '@/types/models';

type Props = { contribution: Contribution };

export default function Page({ contribution }: Props) {
    return (
        <>
            <Head title="Contribution" />
            <PageHeader title="Contribution" />
            <DetailCard
                title="Details"
                editHref={`/portal/contributions/${contribution.sqid}/edit`}
                deleteHref={`/portal/contributions/${contribution.sqid}`}
                backHref={`/portal/contributions/by-date/${contribution.date}`}
                fields={[
                    { label: 'Amount', value: contribution.amount },
                    { label: 'Date', value: contribution.date, format: 'date' },
                    { label: 'Member', value: contribution.member?.full_name },
                ]}
            />
        </>
    );
}
