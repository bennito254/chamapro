import { Link } from '@inertiajs/react';
import type { Subscription } from '@/types/models';

type Props = {
    subscription?: Subscription | null;
};

export default function SubscriptionBanner({ subscription }: Props) {
    if (!subscription) {
        return null;
    }

    const isExpired = subscription.status === 'expired';
    const isSuspended = subscription.status === 'suspended';
    const isTrialEnding =
        subscription.status === 'trial' &&
        subscription.end_date &&
        new Date(subscription.end_date) < new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);

    if (!isExpired && !isSuspended && !isTrialEnding) {
        return null;
    }

    const variant = isExpired || isSuspended ? 'danger' : 'warning';
    const message = isExpired
        ? 'Your subscription has expired. Renew to continue using ChamaPro.'
        : isSuspended
          ? 'Your subscription is suspended. Please contact support or renew.'
          : 'Your trial is ending soon. Renew your subscription to avoid interruption.';

    return (
        <div className={`alert alert-${variant} d-flex align-items-center justify-content-between mb-4`} role="alert">
            <div>
                <i className="bi bi-exclamation-triangle-fill me-2" />
                {message}
            </div>
            <Link href="/portal/subscription/renew" className={`btn btn-sm btn-${variant}`}>
                Renew Now
            </Link>
        </div>
    );
}
