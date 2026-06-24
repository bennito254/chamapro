import { Head, Link, usePage } from '@inertiajs/react';
import PageHeader from '@/components/shared/PageHeader';
import { show } from '@/routes/portal/reports';

type Props = { reportTypes: Record<string, string> };

const reportIcons: Record<string, string> = {
    contributions: 'cash-stack',
    loans: 'bank',
    closed_loans: 'archive',
    loan_aging: 'hourglass-split',
    loan_defaulters: 'exclamation-octagon',
    repayments: 'arrow-repeat',
    interest_earned: 'percent',
    fines: 'exclamation-triangle',
    bank: 'building-columns',
    cash: 'wallet2',
    monthly: 'calendar-month',
    annual: 'graph-up',
};

const reportDescriptions: Record<string, string> = {
    contributions: 'Member contributions by type, channel, and period.',
    loans: 'Currently active loans with balances and due dates.',
    closed_loans: 'Fully repaid or closed loan accounts.',
    loan_aging: 'Overdue loans ranked by days past due.',
    loan_defaulters: 'Members with active loans past their due date.',
    repayments: 'Principal and interest repayments received.',
    interest_earned: 'Interest income collected from loan repayments.',
    fines: 'Paid and unpaid fines with revenue totals.',
    bank: 'Bank transactions and account balances.',
    cash: 'Cash received, paid out, and current position.',
    monthly: 'Month-end snapshot of group finances.',
    annual: 'Yearly trends, growth, and monthly breakdown.',
};

export default function ReportsIndex({ reportTypes }: Props) {
    return (
        <>
            <Head title="Reports" />
            <PageHeader
                title="Reports"
                description="Financial summaries, loan portfolio views, and exportable statements."
            />

            <div className="row g-3">
                {Object.entries(reportTypes).map(([type, label]) => (
                    <div key={type} className="col-md-6 col-xl-4">
                        <Link
                            href={show.url({ type })}
                            className="card border-0 shadow-sm text-decoration-none h-100 cp-report-card"
                        >
                            <div className="card-body p-4 d-flex gap-3">
                                <div className="cp-report-card__icon">
                                    <i className={`bi bi-${reportIcons[type] ?? 'file-bar-graph'}`} />
                                </div>
                                <div>
                                    <h6 className="fw-semibold text-dark mb-1">{label}</h6>
                                    <p className="text-muted small mb-0">
                                        {reportDescriptions[type] ?? 'View and export report data.'}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                ))}
            </div>
        </>
    );
}
