import { Head, Link } from '@inertiajs/react';
import ContributionsByTypeList, {
    type ContributionTypeGroup,
} from '@/components/shared/ContributionsByTypeList';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import StatCard from '@/components/shared/StatCard';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import type {
    Contribution,
    Expense,
    Fine,
    Loan,
    LoanRepayment,
    Meeting,
    SharePurchase,
    WelfareContribution,
    WelfareDisbursement,
} from '@/types/models';

type AttendanceRecord = {
    member_id: number;
    full_name: string;
    membership_number: string;
    status: 'present' | 'absent' | 'not_recorded';
    notes?: string | null;
};

type MemberSummary = {
    member_id: number;
    full_name: string;
    membership_number: string;
    contributions: number;
    loans_taken: number;
    principal_repaid: number;
    interest_repaid: number;
    fines: number;
    shares: number;
    welfare_in: number;
    welfare_out: number;
    loan_outstanding: number;
    net_position: number;
};

type Summary = {
    attendance: {
        total_members: number;
        present: number;
        absent: number;
        not_recorded: number;
        turnout_rate: number;
        records: AttendanceRecord[];
    };
    contributions: Contribution[];
    contributions_by_type: ContributionTypeGroup[];
    loans_disbursed: Loan[];
    loan_repayments: LoanRepayment[];
    fines_paid: Fine[];
    share_purchases: SharePurchase[];
    welfare_contributions: WelfareContribution[];
    welfare_disbursements: WelfareDisbursement[];
    expenses: Expense[];
    member_summaries: MemberSummary[];
    totals: {
        contributions: number;
        loans_disbursed: number;
        principal_repaid: number;
        interest_repaid: number;
        repayments: number;
        fines: number;
        shares: number;
        welfare_in: number;
        welfare_out: number;
        expenses: number;
        net_cash_in: number;
    };
};

type Props = {
    meeting: Meeting;
    summary: Summary;
};

function attendanceBadge(status: AttendanceRecord['status']) {
    const map = {
        present: 'bg-success',
        absent: 'bg-danger',
        not_recorded: 'bg-secondary',
    };

    return <span className={`badge ${map[status]}`}>{titleCase(status)}</span>;
}

export default function Page({ meeting, summary }: Props) {
    const date = String(meeting.date).slice(0, 10);

    return (
        <>
            <Head title={meeting.title} />
            <PageHeader
                title={meeting.title}
                description={`${formatDate(meeting.date)}${meeting.venue ? ` · ${meeting.venue}` : ''}`}
                actions={
                    <div className="d-flex gap-2">
                        <Link
                            href={`/portal/contributions/by-date/${date}`}
                            className="btn btn-outline-primary btn-sm"
                        >
                            Contributions
                        </Link>
                        <Link href={`/portal/meetings/${meeting.sqid}/edit`} className="btn btn-outline-secondary btn-sm">
                            Edit
                        </Link>
                        <Link href="/portal/meetings" className="btn btn-outline-secondary btn-sm">
                            Back
                        </Link>
                    </div>
                }
            />

            {meeting.agenda && (
                <div className="card border-0 shadow-sm mb-4">
                    <div className="card-body">
                        <h2 className="h6 text-muted mb-2">Agenda</h2>
                        <p className="mb-0">{meeting.agenda}</p>
                    </div>
                </div>
            )}

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Turn-up"
                        value={`${summary.attendance.present}/${summary.attendance.total_members}`}
                        subtitle={`${summary.attendance.turnout_rate}% attendance`}
                        icon="people-fill"
                        color="primary"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Contributions"
                        value={formatCurrency(summary.totals.contributions)}
                        subtitle={`${summary.contributions.length} payment(s)`}
                        icon="cash-stack"
                        color="success"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Loans Taken"
                        value={formatCurrency(summary.totals.loans_disbursed)}
                        subtitle={`${summary.loans_disbursed.length} disbursement(s)`}
                        icon="bank"
                        color="warning"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Interest Repaid"
                        value={formatCurrency(summary.totals.interest_repaid)}
                        subtitle={`${formatCurrency(summary.totals.principal_repaid)} principal`}
                        icon="percent"
                        color="info"
                    />
                </div>
            </div>

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Loan Repayments"
                        value={formatCurrency(summary.totals.repayments)}
                        subtitle="Total collected"
                        icon="arrow-repeat"
                        color="success"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Fines Collected"
                        value={formatCurrency(summary.totals.fines)}
                        subtitle={`${summary.fines_paid.length} fine(s)`}
                        icon="exclamation-triangle"
                        color="danger"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Welfare"
                        value={formatCurrency(summary.totals.welfare_in)}
                        subtitle={`${formatCurrency(summary.totals.welfare_out)} disbursed`}
                        icon="heart-pulse"
                        color="secondary"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Net Cash In"
                        value={formatCurrency(summary.totals.net_cash_in)}
                        subtitle={`Includes interest repaid · ${formatCurrency(summary.totals.expenses)} expenses`}
                        icon="graph-up-arrow"
                        color="primary"
                    />
                </div>
            </div>

            <Section title="Turn-up" count={summary.attendance.present}>
                <DataTable
                    columns={[
                        { key: 'membership_number', label: '#' },
                        { key: 'full_name', label: 'Member' },
                        {
                            key: 'status',
                            label: 'Status',
                            render: (row) => attendanceBadge(row.status),
                        },
                        {
                            key: 'notes',
                            label: 'Notes',
                            render: (row) => row.notes ?? '—',
                        },
                    ]}
                    data={summary.attendance.records}
                    searchPlaceholder="Search members..."
                    emptyMessage="No members found."
                    rowKey={(row) => row.member_id}
                />
            </Section>

            <Section title="Contributions" count={summary.contributions.length}>
                <ContributionsByTypeList
                    groups={summary.contributions_by_type}
                    emptyMessage="No contributions on this meeting date."
                    showActions={false}
                />
            </Section>

            <Section title="Loans Taken" count={summary.loans_disbursed.length}>
                <DataTable
                    columns={[
                        {
                            key: 'member',
                            label: 'Member',
                            render: (row) => row.member?.full_name ?? '—',
                        },
                        {
                            key: 'product',
                            label: 'Product',
                            render: (row) => row.product_name ?? row.loanProduct?.name ?? '—',
                        },
                        {
                            key: 'principal_amount',
                            label: 'Principal',
                            render: (row) => formatCurrency(row.principal_amount ?? 0),
                        },
                        {
                            key: 'actions',
                            label: '',
                            render: (row) => (
                                <Link href={`/portal/loans/${row.sqid}`} className="btn btn-sm btn-outline-primary">
                                    View
                                </Link>
                            ),
                        },
                    ]}
                    data={summary.loans_disbursed}
                    emptyMessage="No loans disbursed on this meeting date."
                />
            </Section>

            <Section title="Loan Repayments" count={summary.loan_repayments.length}>
                <DataTable
                    columns={[
                        {
                            key: 'member',
                            label: 'Member',
                            render: (row) => row.loan?.member?.full_name ?? '—',
                        },
                        {
                            key: 'amount',
                            label: 'Total Paid',
                            render: (row) => formatCurrency(row.amount),
                        },
                        {
                            key: 'principal_paid',
                            label: 'Principal',
                            render: (row) => formatCurrency(row.principal_paid ?? 0),
                        },
                        {
                            key: 'interest_paid',
                            label: 'Interest',
                            render: (row) => formatCurrency(row.interest_paid ?? 0),
                        },
                        {
                            key: 'balance_after',
                            label: 'Balance After',
                            render: (row) => formatCurrency(row.balance_after ?? 0),
                        },
                    ]}
                    data={summary.loan_repayments}
                    emptyMessage="No loan repayments on this meeting date."
                />
            </Section>

            <Section title="Member Balances & Activity" count={summary.member_summaries.length}>
                <DataTable
                    columns={[
                        { key: 'membership_number', label: '#' },
                        { key: 'full_name', label: 'Member' },
                        {
                            key: 'contributions',
                            label: 'Contributions',
                            render: (row) => formatCurrency(row.contributions),
                        },
                        {
                            key: 'loans_taken',
                            label: 'Loans Taken',
                            render: (row) => formatCurrency(row.loans_taken),
                        },
                        {
                            key: 'principal_repaid',
                            label: 'Principal',
                            render: (row) => formatCurrency(row.principal_repaid),
                        },
                        {
                            key: 'interest_repaid',
                            label: 'Interest',
                            render: (row) => formatCurrency(row.interest_repaid),
                        },
                        {
                            key: 'loan_outstanding',
                            label: 'Loan Balance',
                            render: (row) => formatCurrency(row.loan_outstanding),
                        },
                        {
                            key: 'net_position',
                            label: 'Net (Meeting)',
                            render: (row) => formatCurrency(row.net_position),
                        },
                    ]}
                    data={summary.member_summaries}
                    searchPlaceholder="Search members..."
                    emptyMessage="No member activity recorded for this meeting."
                    rowKey={(row) => row.member_id}
                />
            </Section>

            {(summary.fines_paid.length > 0 ||
                summary.share_purchases.length > 0 ||
                summary.welfare_contributions.length > 0 ||
                summary.welfare_disbursements.length > 0 ||
                summary.expenses.length > 0) && (
                <Section title="Other Transactions">
                    <div className="row g-4">
                        {summary.fines_paid.length > 0 && (
                            <div className="col-lg-6">
                                <h3 className="h6 mb-3">Fines Paid</h3>
                                <DataTable
                                    columns={[
                                        {
                                            key: 'member',
                                            label: 'Member',
                                            render: (row) => row.member?.full_name ?? '—',
                                        },
                                        {
                                            key: 'amount',
                                            label: 'Amount',
                                            render: (row) => formatCurrency(row.amount),
                                        },
                                    ]}
                                    data={summary.fines_paid}
                                    searchable={false}
                                />
                            </div>
                        )}
                        {summary.share_purchases.length > 0 && (
                            <div className="col-lg-6">
                                <h3 className="h6 mb-3">Share Purchases</h3>
                                <DataTable
                                    columns={[
                                        {
                                            key: 'member',
                                            label: 'Member',
                                            render: (row) => row.member?.full_name ?? '—',
                                        },
                                        {
                                            key: 'amount',
                                            label: 'Amount',
                                            render: (row) => formatCurrency(row.amount),
                                        },
                                    ]}
                                    data={summary.share_purchases}
                                    searchable={false}
                                />
                            </div>
                        )}
                        {summary.welfare_contributions.length > 0 && (
                            <div className="col-lg-6">
                                <h3 className="h6 mb-3">Welfare Contributions</h3>
                                <DataTable
                                    columns={[
                                        {
                                            key: 'member',
                                            label: 'Member',
                                            render: (row) => row.member?.full_name ?? '—',
                                        },
                                        {
                                            key: 'amount',
                                            label: 'Amount',
                                            render: (row) => formatCurrency(row.amount),
                                        },
                                    ]}
                                    data={summary.welfare_contributions}
                                    searchable={false}
                                />
                            </div>
                        )}
                        {summary.welfare_disbursements.length > 0 && (
                            <div className="col-lg-6">
                                <h3 className="h6 mb-3">Welfare Disbursements</h3>
                                <DataTable
                                    columns={[
                                        {
                                            key: 'member',
                                            label: 'Member',
                                            render: (row) => row.member?.full_name ?? '—',
                                        },
                                        {
                                            key: 'amount',
                                            label: 'Amount',
                                            render: (row) => formatCurrency(row.amount),
                                        },
                                    ]}
                                    data={summary.welfare_disbursements}
                                    searchable={false}
                                />
                            </div>
                        )}
                        {summary.expenses.length > 0 && (
                            <div className="col-lg-6">
                                <h3 className="h6 mb-3">Expenses</h3>
                                <DataTable
                                    columns={[
                                        {
                                            key: 'category',
                                            label: 'Category',
                                            render: (row) => row.expense_category?.name ?? '—',
                                        },
                                        {
                                            key: 'payee',
                                            label: 'Payee',
                                            render: (row) => row.payee ?? '—',
                                        },
                                        {
                                            key: 'amount',
                                            label: 'Amount',
                                            render: (row) => formatCurrency(row.amount),
                                        },
                                    ]}
                                    data={summary.expenses}
                                    searchable={false}
                                />
                            </div>
                        )}
                    </div>
                </Section>
            )}
        </>
    );
}

function Section({
    title,
    count,
    children,
}: {
    title: string;
    count?: number;
    children: React.ReactNode;
}) {
    return (
        <div className="mb-4">
            <div className="d-flex align-items-center gap-2 mb-3">
                <h2 className="h5 mb-0">{title}</h2>
                {count !== undefined && <span className="badge bg-light text-dark border">{count}</span>}
            </div>
            {children}
        </div>
    );
}
