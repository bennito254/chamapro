import { Head, Link, usePage } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { formatDateTime } from '@/lib/format';
import type { Paginated } from '@/types/pagination';
import type { Member, SmsMessage, SmsTemplate, User } from '@/types/models';

type MessageRow = SmsMessage & {
    member?: Member | null;
    template?: SmsTemplate | null;
    sender?: User | null;
};

type Props = { messages: Paginated<MessageRow> };

export default function Page({ messages }: Props) {
    const { permissions } = usePage<{ permissions: string[] }>().props;
    const canSend = permissions.includes('sms.send');

    return (
        <>
            <Head title="SMS Messages" />
            <PageHeader
                title="SMS Messages"
                actions={canSend ? (
                    <Link href="/portal/sms-messages/create" className="btn btn-primary">
                        <i className="bi bi-send me-1" />
                        Send SMS
                    </Link>
                ) : undefined}
            />
            <DataTable
                columns={[
                    {
                        key: 'created_at',
                        label: 'Sent',
                        render: (row) => formatDateTime(row.created_at),
                    },
                    {
                        key: 'member',
                        label: 'Member',
                        render: (row) => row.member?.full_name ?? '—',
                    },
                    { key: 'recipient', label: 'Phone' },
                    {
                        key: 'template',
                        label: 'Template',
                        render: (row) => row.template?.name ?? '—',
                    },
                    {
                        key: 'body',
                        label: 'Message',
                        render: (row) => (
                            <span className="text-muted small text-truncate d-inline-block" style={{ maxWidth: 280 }}>
                                {row.body}
                            </span>
                        ),
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        render: (row) => (
                            <span className={`badge ${row.status === 'sent' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}`}>
                                {row.status}
                            </span>
                        ),
                    },
                    { key: 'provider', label: 'Provider' },
                ]}
                data={messages}
            />
        </>
    );
}
