import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency } from '@/lib/format';
import { renew as renewPage } from '@/routes/portal/subscription';

type Payment = {
    sqid: string;
    status: string;
    amount: number | string;
    phone_number: string;
    mpesa_receipt_number?: string | null;
    plan?: { name: string; amount?: number | string } | null;
};

type Props = {
    payment: Payment;
};

export default function SubscriptionPaymentStatus({ payment }: Props) {
    useEffect(() => {
        if (payment.status !== 'pending') {
            return;
        }

        const interval = window.setInterval(() => {
            router.reload({ only: ['payment'] });
        }, 5000);

        return () => window.clearInterval(interval);
    }, [payment.status]);

    const isPending = payment.status === 'pending';
    const isCompleted = payment.status === 'completed';
    const isFailed = payment.status === 'failed';

    return (
        <>
            <Head title="Payment Status" />
            <PageHeader
                title="M-Pesa Payment"
                description={payment.plan?.name}
            />

            <div className="card border-0 shadow-sm">
                <div className="card-body py-5 text-center">
                    {isPending && (
                        <>
                            <div
                                className="spinner-border mb-3 text-primary"
                                role="status"
                            />
                            <h5 className="fw-semibold">Waiting for payment</h5>
                            <p className="mb-0 text-muted">
                                Check your phone ({payment.phone_number}) and
                                enter your M-Pesa PIN to complete{' '}
                                {formatCurrency(payment.amount)}.
                            </p>
                        </>
                    )}

                    {isCompleted && (
                        <>
                            <div className="text-success display-6 mb-3">
                                <i className="bi bi-check-circle-fill" />
                            </div>
                            <h5 className="fw-semibold">Payment successful</h5>
                            <p className="mb-0 text-muted">
                                Receipt: {payment.mpesa_receipt_number ?? '—'}
                            </p>
                        </>
                    )}

                    {isFailed && (
                        <>
                            <div className="text-danger display-6 mb-3">
                                <i className="bi bi-x-circle-fill" />
                            </div>
                            <h5 className="fw-semibold">Payment failed</h5>
                            <p className="mb-3 text-muted">
                                The M-Pesa request was not completed.
                            </p>
                            <Link
                                href={renewPage.url()}
                                className="btn btn-primary"
                            >
                                Try again
                            </Link>
                        </>
                    )}
                </div>
            </div>
        </>
    );
}
