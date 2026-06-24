import Chart from 'react-apexcharts';
import DataTable from '@/components/shared/DataTable';
import StatCard from '@/components/shared/StatCard';
import { formatCurrency } from '@/lib/format';

type SummaryItem = {
    label: string;
    value: number;
    format: 'currency' | 'number' | string;
};

type ReportData = {
    summary?: SummaryItem[];
    rows?: Record<string, unknown>[];
    balances?: Array<{ bank: string; account_number: string; balance: number }>;
    period_label?: string;
    contribution_trends?: Array<{ month_label: string; total: number }>;
    member_growth?: Array<{ month_label: string; total: number }>;
    monthly_totals?: Array<{
        month_label: string;
        contributions: number;
        loans_disbursed: number;
        repayments: number;
        fines: number;
        expenses: number;
    }>;
};

type Props = {
    type: string;
    data: ReportData;
};

function formatSummaryValue(item: SummaryItem): string {
    if (item.format === 'currency') {
        return formatCurrency(item.value);
    }

    return String(item.value);
}

function summaryColor(
    index: number,
): 'primary' | 'success' | 'warning' | 'info' | 'secondary' {
    const colors: Array<
        'primary' | 'success' | 'warning' | 'info' | 'secondary'
    > = ['primary', 'success', 'warning', 'info', 'secondary'];

    return colors[index % colors.length];
}

function currencyColumn(key: string, label: string) {
    return {
        key,
        label,
        render: (row: Record<string, unknown>) =>
            formatCurrency(Number(row[key] ?? 0)),
        className: 'text-end',
    };
}

export default function ReportBody({ type, data }: Props) {
    if (type === 'annual') {
        const contributionCategories =
            data.contribution_trends?.map((item) => item.month_label) ?? [];
        const contributionSeries =
            data.contribution_trends?.map((item) => item.total) ?? [];
        const memberCategories =
            data.member_growth?.map((item) => item.month_label) ?? [];
        const memberSeries =
            data.member_growth?.map((item) => item.total) ?? [];

        return (
            <>
                {data.summary && (
                    <div className="row g-3 mb-4">
                        {data.summary.map((item, index) => (
                            <div key={item.label} className="col-sm-6 col-xl-3">
                                <StatCard
                                    title={item.label}
                                    value={formatSummaryValue(item)}
                                    color={summaryColor(index)}
                                />
                            </div>
                        ))}
                    </div>
                )}

                <div className="row g-4 mb-4">
                    <div className="col-lg-6">
                        <div className="card cp-panel h-100 border-0">
                            <div className="card-header border-0 bg-transparent px-4 pt-4">
                                <h6 className="fw-semibold mb-0">
                                    Contribution trends
                                </h6>
                            </div>
                            <div className="card-body px-2 pb-3">
                                <Chart
                                    type="bar"
                                    height={280}
                                    series={[
                                        {
                                            name: 'Contributions',
                                            data: contributionSeries,
                                        },
                                    ]}
                                    options={{
                                        chart: { toolbar: { show: false } },
                                        xaxis: {
                                            categories: contributionCategories,
                                        },
                                        colors: ['#2563eb'],
                                        dataLabels: { enabled: false },
                                    }}
                                />
                            </div>
                        </div>
                    </div>
                    <div className="col-lg-6">
                        <div className="card cp-panel h-100 border-0">
                            <div className="card-header border-0 bg-transparent px-4 pt-4">
                                <h6 className="fw-semibold mb-0">
                                    Member growth
                                </h6>
                            </div>
                            <div className="card-body px-2 pb-3">
                                <Chart
                                    type="line"
                                    height={280}
                                    series={[
                                        {
                                            name: 'New members',
                                            data: memberSeries,
                                        },
                                    ]}
                                    options={{
                                        chart: { toolbar: { show: false } },
                                        xaxis: { categories: memberCategories },
                                        stroke: { curve: 'smooth' },
                                        dataLabels: { enabled: false },
                                    }}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {data.monthly_totals && (
                    <DataTable
                        searchable={false}
                        columns={[
                            { key: 'month_label', label: 'Month' },
                            currencyColumn('contributions', 'Contributions'),
                            currencyColumn('loans_disbursed', 'Loans'),
                            currencyColumn('repayments', 'Repayments'),
                            currencyColumn('fines', 'Fines'),
                            currencyColumn('expenses', 'Expenses'),
                        ]}
                        data={data.monthly_totals}
                        emptyMessage="No annual data for this year."
                    />
                )}
            </>
        );
    }

    if (type === 'monthly') {
        return (
            <>
                {data.period_label && (
                    <p className="mb-3 text-muted">
                        Period: <strong>{data.period_label}</strong>
                    </p>
                )}
                {data.summary && (
                    <div className="row g-3">
                        {data.summary.map((item, index) => (
                            <div key={item.label} className="col-sm-6 col-xl-4">
                                <StatCard
                                    title={item.label}
                                    value={formatSummaryValue(item)}
                                    color={summaryColor(index)}
                                />
                            </div>
                        ))}
                    </div>
                )}
            </>
        );
    }

    const tableColumns = getTableColumns(type);

    return (
        <>
            {data.summary && (
                <div className="row g-3 mb-4">
                    {data.summary.map((item, index) => (
                        <div key={item.label} className="col-sm-6 col-xl-3">
                            <StatCard
                                title={item.label}
                                value={formatSummaryValue(item)}
                                color={summaryColor(index)}
                            />
                        </div>
                    ))}
                </div>
            )}

            {data.balances && data.balances.length > 0 && (
                <div className="mb-4">
                    <DataTable
                        searchable={false}
                        columns={[
                            { key: 'bank', label: 'Bank' },
                            { key: 'account_number', label: 'Account' },
                            currencyColumn('balance', 'Balance'),
                        ]}
                        data={data.balances}
                        emptyMessage="No bank accounts found."
                    />
                </div>
            )}

            {data.rows && tableColumns.length > 0 && (
                <DataTable
                    columns={tableColumns}
                    data={data.rows}
                    emptyMessage="No records match the selected filters."
                />
            )}
        </>
    );
}

function getTableColumns(type: string) {
    switch (type) {
        case 'contributions':
            return [
                { key: 'date', label: 'Date' },
                { key: 'member', label: 'Member' },
                { key: 'membership_number', label: 'Membership #' },
                { key: 'type', label: 'Type' },
                { key: 'channel', label: 'Channel' },
                currencyColumn('amount', 'Amount'),
            ];
        case 'loans':
        case 'closed_loans':
        case 'loan_defaulters':
            return [
                { key: 'member', label: 'Member' },
                { key: 'membership_number', label: 'Membership #' },
                currencyColumn('principal', 'Principal'),
                currencyColumn('outstanding', 'Outstanding'),
                { key: 'disbursed', label: 'Disbursed' },
                { key: 'due_date', label: 'Due date' },
                { key: 'status', label: 'Status' },
            ];
        case 'loan_aging':
            return [
                { key: 'member', label: 'Member' },
                { key: 'membership_number', label: 'Membership #' },
                currencyColumn('outstanding', 'Outstanding'),
                { key: 'due_date', label: 'Due date' },
                {
                    key: 'days_overdue',
                    label: 'Days overdue',
                    className: 'text-end',
                },
            ];
        case 'repayments':
            return [
                { key: 'date', label: 'Date' },
                { key: 'member', label: 'Member' },
                currencyColumn('amount', 'Amount'),
                currencyColumn('principal', 'Principal'),
                currencyColumn('interest', 'Interest'),
                currencyColumn('balance_after', 'Balance after'),
            ];
        case 'interest_earned':
            return [
                { key: 'date', label: 'Date' },
                { key: 'member', label: 'Member' },
                currencyColumn('interest', 'Interest'),
            ];
        case 'fines':
            return [
                { key: 'date', label: 'Date' },
                { key: 'member', label: 'Member' },
                { key: 'type', label: 'Type' },
                currencyColumn('amount', 'Amount'),
                { key: 'paid', label: 'Paid' },
            ];
        case 'bank':
            return [
                { key: 'date', label: 'Date' },
                { key: 'account', label: 'Account' },
                { key: 'type', label: 'Type' },
                currencyColumn('amount', 'Amount'),
                { key: 'reference', label: 'Reference' },
            ];
        case 'cash':
            return [
                { key: 'date', label: 'Date' },
                { key: 'type', label: 'Type' },
                currencyColumn('amount', 'Amount'),
                { key: 'description', label: 'Description' },
            ];
        default:
            return [];
    }
}
