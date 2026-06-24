import { usePage } from '@inertiajs/react';
import { useMemo, type ReactNode } from 'react';
import AppShell from '@/components/shared/AppShell';
import SubscriptionBanner from '@/components/shared/SubscriptionBanner';
import SubscriptionStrip from '@/components/shared/SubscriptionStrip';
import { filterNavByPermissions, portalNavGroups } from '@/lib/navigation';
import type { Auth } from '@/types/auth';
import type { Group, Subscription } from '@/types/models';

type Props = {
    children: ReactNode;
};

export default function PortalLayout({ children }: Props) {
    const { auth, name, group, subscription, permissions } = usePage<{
        auth: Auth;
        name: string;
        group?: Group | null;
        subscription?: Subscription | null;
        permissions: string[];
    }>().props;

    const navGroups = useMemo(
        () => filterNavByPermissions(portalNavGroups, permissions ?? []),
        [permissions],
    );

    return (
        <AppShell
            variant="portal"
            navGroups={navGroups}
            brandIcon="building-fill"
            brandTitle={group?.name ?? name}
            brandSubtitle="Chama Portal"
            userName={auth.user?.name}
            logoutHref="/portal/logout"
            topStrip={<SubscriptionStrip />}
            mainTop={<SubscriptionBanner subscription={subscription} />}
        >
            {children}
        </AppShell>
    );
}
