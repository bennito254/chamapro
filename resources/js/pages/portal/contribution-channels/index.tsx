import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { ContributionChannel } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { channels: Paginated<ContributionChannel> };

export default function Page({ channels }: Props) {
    return (
        <>
            <Head title="Payment Channels" />
            <PageHeader
                title="Payment Channels"
                actions={
                    <Link
                        href="/portal/contribution-channels/create"
                        className="btn btn-primary"
                    >
                        <i className="bi bi-plus-lg me-1" />
                        Add
                    </Link>
                }
            />
            <DataTable
                columns={[
                    { key: 'name', label: 'Name' },
                    { key: 'status', label: 'Status' },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/contribution-channels/${row.sqid}/edit`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                Edit
                            </Link>
                        ),
                    },
                ]}
                data={channels}
            />
        </>
    );
}
