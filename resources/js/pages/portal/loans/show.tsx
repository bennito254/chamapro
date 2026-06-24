import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import DataTable from '@/components/shared/DataTable';
import DetailCard from '@/components/shared/DetailCard';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import StatCard from '@/components/shared/StatCard';
import { loanRepaymentTypeOptions } from '@/lib/form-options';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import { repayments } from '@/routes/portal/loans';
import type { Loan, LoanRepayment } from '@/types/models';

type Props = {
    loan: Loan;
    interest_outstanding: number;
    principal_outstanding: number;
};

export default function Page({
    loan,
    interest_outstanding,
    principal_outstanding,
}: Props) {
    const repaymentRoute = repayments.form(loan);
    const isActive = loan.status === 'active';
    const totalOutstanding = Number(loan.outstanding_balance);
    const [amount, setAmount] = useState('');

    const setPayInFull = () => {
        setAmount(totalOutstanding.toFixed(2));
    };

    return (
        <>
            <Head title={`Loan · ${loan.member?.full_name ?? 'Member'}`} />
            <PageHeader
                title={loan.product_name ?? loan.loanProduct?.name ?? 'Loan'}
                description={loan.member?.full_name}
                actions={
                    <Link
                        href="/portal/loans"
                        className="btn btn-outline-secondary btn-sm"
                    >
                        Back to loans
                    </Link>
                }
            />

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Outstanding"
                        value={formatCurrency(loan.outstanding_balance)}
                        subtitle={`Due ${loan.due_date ? formatDate(loan.due_date) : '—'}`}
                        icon="wallet2"
                        color="warning"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Interest Due"
                        value={formatCurrency(interest_outstanding)}
                        subtitle="Included in combined payments"
                        icon="percent"
                        color="info"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Principal Due"
                        value={formatCurrency(principal_outstanding)}
                        subtitle="After interest is cleared"
                        icon="cash-stack"
                        color="primary"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Status"
                        value={titleCase(loan.status)}
                        subtitle={`Disbursed ${loan.disbursement_date ? formatDate(loan.disbursement_date) : '—'}`}
                        icon="flag"
                        color="secondary"
                    />
                </div>
            </div>

            <DetailCard
                title="Loan details"
                backHref="/portal/loans"
                fields={[
                    { label: 'Member', value: loan.member?.full_name },
                    {
                        label: 'Principal',
                        value: formatCurrency(loan.principal_amount),
                    },
                    {
                        label: 'Interest',
                        value: formatCurrency(loan.interest_amount ?? 0),
                    },
                    {
                        label: 'Total',
                        value: formatCurrency(
                            loan.total_amount ?? loan.outstanding_balance,
                        ),
                    },
                    {
                        label: 'Repayment period',
                        value: loan.repayment_period
                            ? `${loan.repayment_period} months`
                            : '—',
                    },
                    { label: 'Status', value: loan.status, format: 'badge' },
                ]}
            />

            {isActive && totalOutstanding > 0 && (
                <div className="card mt-4 border-0 shadow-sm">
                    <div className="card-header border-bottom d-flex justify-content-between align-items-center bg-white py-3">
                        <h2 className="h6 mb-0">Record repayment</h2>
                        <button
                            type="button"
                            className="btn btn-outline-success btn-sm"
                            onClick={setPayInFull}
                        >
                            Pay in full ({formatCurrency(totalOutstanding)})
                        </button>
                    </div>
                    <div className="card-body">
                        <Form {...repaymentRoute}>
                            {({ errors, processing }) => (
                                <>
                                    <FormField
                                        label="Payment type"
                                        name="payment_type"
                                        required
                                        defaultValue="combined"
                                        options={loanRepaymentTypeOptions}
                                        error={errors.payment_type}
                                        help="Combined payments apply to outstanding interest first, then principal. Use interest or principal only for targeted payments."
                                    />
                                    <div className="mb-3">
                                        <label
                                            htmlFor="field-amount"
                                            className="form-label"
                                        >
                                            Amount
                                            <span className="text-danger ms-1">
                                                *
                                            </span>
                                        </label>
                                        <input
                                            id="field-amount"
                                            name="amount"
                                            type="number"
                                            className={`form-control ${errors.amount ? 'is-invalid' : ''}`}
                                            value={amount}
                                            onChange={(event) =>
                                                setAmount(event.target.value)
                                            }
                                            min="0.01"
                                            step="0.01"
                                            max={totalOutstanding}
                                            required
                                        />
                                        <div className="form-text">
                                            Outstanding balance:{' '}
                                            {formatCurrency(totalOutstanding)}
                                        </div>
                                        {errors.amount && (
                                            <div className="invalid-feedback d-block">
                                                {errors.amount}
                                            </div>
                                        )}
                                    </div>
                                    <FormField
                                        label="Date"
                                        name="date"
                                        type="date"
                                        required
                                        defaultValue={new Date()
                                            .toISOString()
                                            .slice(0, 10)}
                                        error={errors.date}
                                    />
                                    <FormField
                                        label="Method"
                                        name="method"
                                        error={errors.method}
                                        placeholder="Cash, M-Pesa, bank..."
                                    />
                                    <FormField
                                        label="Reference"
                                        name="reference_number"
                                        error={errors.reference_number}
                                    />
                                    <FormField
                                        label="Notes"
                                        name="notes"
                                        type="textarea"
                                        error={errors.notes}
                                    />
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing || !amount}
                                    >
                                        {processing
                                            ? 'Recording...'
                                            : 'Record repayment'}
                                    </button>
                                </>
                            )}
                        </Form>
                    </div>
                </div>
            )}

            <div className="card mt-4 border-0 shadow-sm">
                <div className="card-header border-bottom bg-white py-3">
                    <h2 className="h6 mb-0">Repayment history</h2>
                </div>
                <div className="card-body p-0">
                    <DataTable
                        columns={[
                            {
                                key: 'date',
                                label: 'Date',
                                render: (row: LoanRepayment) =>
                                    formatDate(row.date),
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
                        data={loan.repayments ?? []}
                        searchable={false}
                        emptyMessage="No repayments recorded yet."
                    />
                </div>
            </div>
        </>
    );
}
