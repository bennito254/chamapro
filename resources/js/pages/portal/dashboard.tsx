import { Head } from '@inertiajs/react';
import Chart from 'react-apexcharts';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import QuickLinks from '@/components/shared/QuickLinks';
import StatCard from '@/components/shared/StatCard';
import { formatCurrency, formatDate, formatDateTime } from '@/lib/format';
import { portalQuickLinks } from '@/lib/navigation';
import type { Contribution, Group } from '@/types/models';

type Stats = {
    members_total: number;
    members_active: number;
    contributions_month: number;
    loan_fund_available: number;
    loans_active: number;
    fines_unpaid: number;
};

type Props = {
    group: Group;
    stats: Stats;
    recentContributions: Contribution[];
};

export default function PortalDashboard({ group, stats, recentContributions }: Props) {
    const today = formatDateTime(new Date());

    return (
        <>
            <Head title="Dashboard" />
            <PageHeader title={`Welcome back`} description={`${group.name} · ${today}`} />

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Active Members"
                        value={stats.members_active}
                        subtitle={`${stats.members_total} total registered`}
                        icon="people-fill"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="This Month"
                        value={formatCurrency(stats.contributions_month)}
                        subtitle="Contributions collected"
                        icon="cash-stack"
                        color="success"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Loan Fund"
                        value={formatCurrency(stats.loan_fund_available)}
                        subtitle={`${stats.loans_active} active loan(s)`}
                        icon="bank"
                        color="primary"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Unpaid Fines"
                        value={stats.fines_unpaid}
                        subtitle="Pending collection"
                        icon="exclamation-triangle-fill"
                        color="warning"
                    />
                </div>
            </div>

            <div className="mb-4">
                <QuickLinks links={portalQuickLinks} />
            </div>

            <div className="row g-4">
                <div className="col-lg-4">
                    <div className="card cp-panel border-0 h-100">
                        <div className="card-header bg-transparent border-0 pt-4 px-4">
                            <h6 className="fw-semibold mb-0">Activity Snapshot</h6>
                        </div>
                        <div className="card-body">
                            <Chart
                                type="bar"
                                height={280}
                                series={[{
                                    name: 'Count',
                                    data: [stats.members_active, stats.loans_active, stats.fines_unpaid],
                                }]}
                                options={{
                                    chart: { toolbar: { show: false }, fontFamily: 'inherit' },
                                    plotOptions: { bar: { borderRadius: 8, columnWidth: '45%' } },
                                    xaxis: { categories: ['Members', 'Loans', 'Fines'] },
                                    colors: ['#047857'],
                                    dataLabels: { enabled: false },
                                }}
                            />
                        </div>
                    </div>
                </div>
                <div className="col-lg-8">
                    <div className="card cp-panel border-0 h-100">
                        <div className="card-header bg-transparent border-0 pt-4 px-4">
                            <h6 className="fw-semibold mb-0">Recent Contributions</h6>
                        </div>
                        <div className="card-body pt-0">
                            <DataTable
                                searchable={false}
                                data={recentContributions}
                                columns={[
                                    { key: 'date', label: 'Date', render: (r) => formatDate(r.date) },
                                    { key: 'member', label: 'Member', render: (r) => r.member?.full_name ?? '—' },
                                    { key: 'amount', label: 'Amount', render: (r) => formatCurrency(Number(r.amount)) },
                                ]}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
