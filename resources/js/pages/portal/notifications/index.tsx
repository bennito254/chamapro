import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import { formatDateTime } from '@/lib/format';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { Notification } from '@/types/models';

type Props = { notifications: Paginated<Notification> };

export default function Page({ notifications }: Props) {
    return (
        <>
            <Head title="Notifications" />
            <PageHeader title="Notifications" actions={undefined} />
            <DataTable columns={[
                    { key: 'type', label: 'Type' },
                    { key: 'created_at', label: 'Date', render: (row) => formatDateTime(row.created_at) }
            ]} data={notifications} />
        </>
    );
}
