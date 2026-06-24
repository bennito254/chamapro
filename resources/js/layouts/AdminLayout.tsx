import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppShell from '@/components/shared/AppShell';
import SubscriptionBanner from '@/components/shared/SubscriptionBanner';
import { adminNavGroups } from '@/lib/navigation';
import type { Auth } from '@/types/auth';

type Props = {
    children: ReactNode;
};

export default function AdminLayout({ children }: Props) {
    const { auth, name } = usePage<{
        auth: Auth & { superAdmin?: { name: string; email: string } };
        name: string;
    }>().props;

    return (
        <AppShell
            variant="admin"
            navGroups={adminNavGroups}
            brandIcon="shield-lock-fill"
            brandTitle={name}
            brandSubtitle="Platform Admin"
            userName={auth.superAdmin?.name}
            logoutHref="/admin/logout"
        >
            {children}
        </AppShell>
    );
}
