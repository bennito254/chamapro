import { Head, usePage } from '@inertiajs/react';
import ReportBody from '@/components/reports/ReportBody';
import ReportFilters from '@/components/reports/ReportFilters';
import ExportButtons from '@/components/shared/ExportButtons';
import PageHeader from '@/components/shared/PageHeader';
import { exportMethod as exportReport, index as reportsIndex, show } from '@/routes/portal/reports';

type MemberOption = { id: number; label: string };

type FilterConfig = {
    dateRange: boolean;
    period: boolean;
    member: boolean;
};

type Filters = {
    from?: string;
    to?: string;
    year?: number;
    month?: number;
    member_id?: number;
};

type Props = {
    type: string;
    title: string;
    data: Record<string, unknown>;
    filters: Filters;
    members: MemberOption[];
    filterConfig: FilterConfig;
};

function buildExportUrl(type: string, filters: Filters, format: 'csv' | 'pdf'): string {
    const query: Record<string, string | number> = { format };

    if (filters.from) {
        query.from = filters.from;
    }
    if (filters.to) {
        query.to = filters.to;
    }
    if (filters.year) {
        query.year = filters.year;
    }
    if (filters.month) {
        query.month = filters.month;
    }
    if (filters.member_id) {
        query.member_id = filters.member_id;
    }

    return exportReport.url({ type }, { query });
}

export default function ReportShow({ type, title, data, filters, members, filterConfig }: Props) {
    const { permissions } = usePage<{ permissions: string[] }>().props;
    const canExport = permissions.includes('reports.export');

    return (
        <>
            <Head title={title} />
            <PageHeader
                title={title}
                breadcrumbs={[
                    { label: 'Reports', href: reportsIndex.url() },
                    { label: title },
                ]}
                actions={
                    canExport ? (
                        <ExportButtons
                            csvHref={buildExportUrl(type, filters, 'csv')}
                            pdfHref={buildExportUrl(type, filters, 'pdf')}
                        />
                    ) : undefined
                }
            />

            <ReportFilters
                type={type}
                filters={filters}
                filterConfig={filterConfig}
                members={members}
            />

            <ReportBody type={type} data={data} />
        </>
    );
}
