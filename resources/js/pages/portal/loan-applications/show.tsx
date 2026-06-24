import { Head, Link } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency } from '@/lib/format';
import type { LoanApplication } from '@/types/models';

type Props = { application: LoanApplication };

export default function Page({ application }: Props) {
    const product = application.loan_product ?? application.loanProduct;

    return (
        <>
            <Head title="Loan Application" />
            <PageHeader
                title="Loan Application"
                description={application.member?.full_name}
                actions={
                    <Link
                        href={`/portal/loan-applications/${application.sqid}/edit`}
                        className="btn btn-outline-primary btn-sm"
                    >
                        Edit
                    </Link>
                }
            />
            <DetailCard
                title="Application details"
                editHref={`/portal/loan-applications/${application.sqid}/edit`}
                backHref="/portal/loan-applications"
                fields={[
                    { label: 'Member', value: application.member?.full_name },
                    { label: 'Product', value: product?.name },
                    {
                        label: 'Requested amount',
                        value: formatCurrency(application.requested_amount),
                    },
                    { label: 'Purpose', value: application.purpose },
                    {
                        label: 'Status',
                        value: application.status,
                        format: 'badge',
                    },
                    {
                        label: 'Repayment period',
                        value: product?.repayment_period
                            ? `${product.repayment_period} months`
                            : '—',
                    },
                    { label: 'Review notes', value: application.review_notes },
                ]}
            />
            {application.loan && (
                <div className="mt-4">
                    <Link
                        href={`/portal/loans/${application.loan.sqid}`}
                        className="btn btn-primary btn-sm"
                    >
                        View active loan
                    </Link>
                </div>
            )}
        </>
    );
}
