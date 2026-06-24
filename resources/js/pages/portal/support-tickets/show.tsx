import { Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import type { SupportTicket } from '@/types/models';

type Props = { ticket: SupportTicket };

export default function Page({ ticket }: Props) {
    return (
        <>
            <Head title="Ticket" />
            <PageHeader title="Ticket" />
            <DetailCard title="Details" editHref="undefined" deleteHref="undefined" backHref="/portal/support-tickets" fields={[
        { label: 'Subject', value: ticket.subject },
        { label: 'Status', value: ticket.status, format: 'badge' },
        { label: 'Message', value: ticket.message }
            ]} />
        </>
    );
}
