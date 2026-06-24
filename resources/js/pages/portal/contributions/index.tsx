import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { formatDate } from '@/lib/format';
import type { Paginated } from '@/types/pagination';

type DateGroup = {
    date: string;
    contributions_count: number;
    total_amount: number | string;
    meeting_title?: string | null;
};

type Props = { dateGroups: Paginated<DateGroup> };

export default function Page({ dateGroups }: Props) {
    return (
        <>
            <Head title="Contributions by Meeting Date" />
            <PageHeader
                title="Contributions by Meeting Date"
                description="Contributions are grouped by the meeting date they were collected."
                actions={
                    <div className="d-flex gap-2">
                        <Link
                            href="/portal/contributions-bulk"
                            className="btn btn-primary btn-sm"
                        >
                            <i className="bi bi-people-fill me-1" />
                            Bulk entry
                        </Link>
                        <Link
                            href="/portal/contributions/create"
                            className="btn btn-outline-primary btn-sm"
                        >
                            <i className="bi bi-plus-lg me-1" />
                            Single
                        </Link>
                    </div>
                }
            />
            <DataTable
                columns={[
                    {
                        key: 'date',
                        label: 'Meeting Date',
                        render: (row) => formatDate(row.date),
                    },
                    {
                        key: 'meeting_title',
                        label: 'Meeting',
                        render: (row) => row.meeting_title ?? '—',
                    },
                    {
                        key: 'contributions_count',
                        label: 'Contributions',
                        render: (row) => String(row.contributions_count),
                    },
                    {
                        key: 'total_amount',
                        label: 'Total Amount',
                        render: (row) => formatCurrency(row.total_amount),
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/contributions/by-date/${row.date}`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                View all
                            </Link>
                        ),
                    },
                ]}
                data={dateGroups}
                searchPlaceholder="Search meeting dates..."
                emptyMessage="No contributions recorded yet."
                rowKey={(row) => row.date}
            />
        </>
    );
}
