import { Head, Link } from '@inertiajs/react';
import ActivitySection from '@/components/shared/ActivitySection';
import DataTable from '@/components/shared/DataTable';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import StatCard from '@/components/shared/StatCard';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import type { Loan, LoanRepayment, Member } from '@/types/models';

type ContributionDateGroup = {
    date: string;
    contributions_count: number;
    total_amount: number;
};

type ActivitySummary = {
    total_contributions: number;
    contributions_count: number;
    loans_count: number;
    active_loans: number;
    loan_outstanding: number;
    total_repaid: number;
};

type Activity = {
    contributions_by_date: ContributionDateGroup[];
    loans: Loan[];
    repayments: LoanRepayment[];
    summary: ActivitySummary;
};

type Props = {
    member: Member;
    activity: Activity;
};

function loanStatusBadge(status: string) {
    const map: Record<string, string> = {
        active: 'bg-success',
        closed: 'bg-secondary',
        defaulted: 'bg-danger',
    };

    return (
        <span className={`badge ${map[status] ?? 'bg-secondary'}`}>
            {titleCase(status)}
        </span>
    );
}

export default function Page({ member, activity }: Props) {
    const { contributions_by_date, loans, repayments, summary } = activity;

    return (
        <>
            <Head title={member.full_name} />
            <PageHeader
                title={member.full_name}
                description={`Member #${member.membership_number}`}
                actions={
                    <div className="d-flex gap-2">
                        <Link
                            href={`/portal/members/${member.sqid}/statement`}
                            className="btn btn-outline-secondary btn-sm"
                        >
                            Statement
                        </Link>
                        <Link
                            href={`/portal/members/${member.sqid}/edit`}
                            className="btn btn-outline-primary btn-sm"
                        >
                            Edit
                        </Link>
                        <Link
                            href="/portal/members"
                            className="btn btn-outline-secondary btn-sm"
                        >
                            Back
                        </Link>
                    </div>
                }
            />

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Contributions"
                        value={formatCurrency(summary.total_contributions)}
                        subtitle={`${summary.contributions_count} payment(s)`}
                        icon="piggy-bank"
                        color="success"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Active loans"
                        value={summary.active_loans}
                        subtitle={`${summary.loans_count} total loan(s)`}
                        icon="cash-coin"
                        color="warning"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Loan outstanding"
                        value={formatCurrency(summary.loan_outstanding)}
                        subtitle="Across active loans"
                        icon="wallet2"
                        color="danger"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Repaid"
                        value={formatCurrency(summary.total_repaid)}
                        subtitle={`${repayments.length} repayment(s)`}
                        icon="arrow-repeat"
                        color="info"
                    />
                </div>
            </div>

            <DetailCard
                title="Member details"
                editHref={`/portal/members/${member.sqid}/edit`}
                backHref="/portal/members"
                fields={[
                    { label: 'Name', value: member.full_name },
                    { label: 'Membership #', value: member.membership_number },
                    { label: 'Phone', value: member.phone_number },
                    { label: 'Email', value: member.email },
                    {
                        label: 'Date joined',
                        value: member.date_joined,
                        format: 'date',
                    },
                    { label: 'Status', value: member.status, format: 'badge' },
                ]}
            />

            {contributions_by_date.length > 0 && (
                <ActivitySection
                    title="Contributions by date"
                    count={contributions_by_date.length}
                >
                    <DataTable
                        columns={[
                            {
                                key: 'date',
                                label: 'Date',
                                render: (row: ContributionDateGroup) =>
                                    formatDate(row.date),
                            },
                            {
                                key: 'contributions_count',
                                label: 'Entries',
                                className: 'text-end',
                                render: (row: ContributionDateGroup) =>
                                    String(row.contributions_count),
                            },
                            {
                                key: 'total_amount',
                                label: 'Total',
                                className: 'text-end',
                                render: (row: ContributionDateGroup) =>
                                    formatCurrency(row.total_amount),
                            },
                        ]}
                        data={contributions_by_date}
                        searchable={false}
                        emptyMessage="No contributions recorded."
                        rowKey={(row) => row.date}
                    />
                </ActivitySection>
            )}

            {loans.length > 0 && (
                <ActivitySection title="Loans" count={loans.length}>
                    <DataTable
                        columns={[
                            {
                                key: 'product',
                                label: 'Product',
                                render: (row: Loan) =>
                                    row.product_name ??
                                    row.loanProduct?.name ??
                                    '—',
                            },
                            {
                                key: 'principal_amount',
                                label: 'Principal',
                                className: 'text-end',
                                render: (row: Loan) =>
                                    formatCurrency(row.principal_amount),
                            },
                            {
                                key: 'outstanding_balance',
                                label: 'Outstanding',
                                className: 'text-end',
                                render: (row: Loan) =>
                                    formatCurrency(row.outstanding_balance),
                            },
                            {
                                key: 'disbursement_date',
                                label: 'Disbursed',
                                render: (row: Loan) =>
                                    formatDate(row.disbursement_date),
                            },
                            {
                                key: 'status',
                                label: 'Status',
                                render: (row: Loan) =>
                                    loanStatusBadge(row.status),
                            },
                            {
                                key: 'actions',
                                label: '',
                                render: (row: Loan) => (
                                    <Link
                                        href={`/portal/loans/${row.sqid}`}
                                        className="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </Link>
                                ),
                            },
                        ]}
                        data={loans}
                        searchable={false}
                        emptyMessage="No loans recorded."
                    />
                </ActivitySection>
            )}

            {repayments.length > 0 && (
                <ActivitySection
                    title="Loan repayments"
                    count={repayments.length}
                >
                    <DataTable
                        columns={[
                            {
                                key: 'date',
                                label: 'Date',
                                render: (row: LoanRepayment) =>
                                    formatDate(row.date),
                            },
                            {
                                key: 'loan',
                                label: 'Loan',
                                render: (row: LoanRepayment) =>
                                    row.loan?.product_name ?? '—',
                            },
                            {
                                key: 'interest_paid',
                                label: 'Interest',
                                className: 'text-end',
                                render: (row: LoanRepayment) =>
                                    formatCurrency(row.interest_paid ?? 0),
                            },
                            {
                                key: 'principal_paid',
                                label: 'Principal',
                                className: 'text-end',
                                render: (row: LoanRepayment) =>
                                    formatCurrency(row.principal_paid ?? 0),
                            },
                            {
                                key: 'amount',
                                label: 'Total',
                                className: 'text-end',
                                render: (row: LoanRepayment) =>
                                    formatCurrency(row.amount),
                            },
                            {
                                key: 'balance_after',
                                label: 'Balance after',
                                className: 'text-end',
                                render: (row: LoanRepayment) =>
                                    formatCurrency(row.balance_after ?? 0),
                            },
                        ]}
                        data={repayments}
                        searchable={false}
                        emptyMessage="No repayments recorded."
                    />
                </ActivitySection>
            )}

            {contributions_by_date.length === 0 &&
                loans.length === 0 &&
                repayments.length === 0 && (
                    <div className="card border-0 shadow-sm">
                        <div className="card-body py-5 text-center text-muted">
                            <i className="bi bi-inbox display-6 d-block mb-3" />
                            <p className="mb-0">
                                No contributions, loans, or repayments recorded
                                for this member yet.
                            </p>
                        </div>
                    </div>
                )}
        </>
    );
}
