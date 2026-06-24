import { Head, Link, usePage } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { SmsTemplate } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Props = { templates: Paginated<SmsTemplate> };

export default function Page({ templates }: Props) {
    const { permissions } = usePage<{ permissions: string[] }>().props;
    const canManage = permissions.includes('sms.manage');

    return (
        <>
            <Head title="SMS Templates" />
            <PageHeader
                title="SMS Templates"
                actions={
                    canManage ? (
                        <Link
                            href="/portal/sms-templates/create"
                            className="btn btn-primary"
                        >
                            <i className="bi bi-plus-lg me-1" />
                            Add Template
                        </Link>
                    ) : undefined
                }
            />
            <DataTable
                columns={[
                    { key: 'name', label: 'Name' },
                    {
                        key: 'body',
                        label: 'Message',
                        render: (row) => (
                            <span
                                className="small text-truncate d-inline-block text-muted"
                                style={{ maxWidth: 360 }}
                            >
                                {row.body}
                            </span>
                        ),
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        render: (row) => (
                            <span className="text-capitalize">
                                {row.status}
                            </span>
                        ),
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) =>
                            canManage ? (
                                <Link
                                    href={`/portal/sms-templates/${row.sqid}/edit`}
                                    className="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </Link>
                            ) : null,
                    },
                ]}
                data={templates}
            />
        </>
    );
}
