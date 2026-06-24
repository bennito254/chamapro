import { Link, usePage } from '@inertiajs/react';
import { formatDate } from '@/lib/format';
import type { Subscription } from '@/types/models';

export default function SubscriptionStrip() {
    const { subscription } = usePage<{ subscription?: Subscription | null }>()
        .props;

    if (!subscription) {
        return null;
    }

    const planName = subscription.plan?.name ?? 'Subscription';
    const nextPayment = subscription.end_date;
    const statusLabel = subscription.status.replace(/_/g, ' ');
    const isWarning =
        subscription.status === 'expired' ||
        subscription.status === 'suspended';

    return (
        <div
            className={`cp-subscription-strip ${isWarning ? 'cp-subscription-strip--warning' : ''}`}
        >
            <div className="cp-subscription-strip__inner">
                <div className="cp-subscription-strip__plan">
                    <i className="bi bi-patch-check-fill" />
                    <span className="cp-subscription-strip__label">Plan</span>
                    <strong>{planName}</strong>
                    <span
                        className={`badge cp-subscription-strip__badge cp-subscription-strip__badge--${subscription.status}`}
                    >
                        {statusLabel}
                    </span>
                </div>
                <div className="cp-subscription-strip__divider d-none d-md-block" />
                <div className="cp-subscription-strip__payment">
                    <i className="bi bi-calendar-event" />
                    <span className="cp-subscription-strip__label">
                        Next payment
                    </span>
                    <strong>{formatDate(nextPayment)}</strong>
                </div>
                {(subscription.status === 'expired' ||
                    subscription.status === 'trial') && (
                    <Link
                        href="/portal/subscription/renew"
                        className="btn btn-sm btn-light ms-md-auto"
                    >
                        Manage subscription
                    </Link>
                )}
            </div>
        </div>
    );
}
