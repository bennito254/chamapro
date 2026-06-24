import { Link } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import SidebarNav from '@/components/shared/SidebarNav';
import { useFlashToasts } from '@/hooks/use-flash-toasts';
import type { NavGroup } from '@/lib/navigation';
import { getTheme, toggleTheme } from '@/lib/theme';

type Props = {
    children: ReactNode;
    variant: 'admin' | 'portal';
    navGroups: NavGroup[];
    brandIcon: string;
    brandTitle: string;
    brandSubtitle: string;
    userName?: string;
    logoutHref: string;
    headerExtra?: ReactNode;
    topStrip?: ReactNode;
    mainTop?: ReactNode;
};

export default function AppShell({
    children,
    variant,
    navGroups,
    brandIcon,
    brandTitle,
    brandSubtitle,
    userName,
    logoutHref,
    headerExtra,
    topStrip,
    mainTop,
}: Props) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [theme, setThemeState] = useState(getTheme);
    useFlashToasts();

    const sidebarClass = variant === 'admin' ? 'cp-sidebar cp-sidebar--admin' : 'cp-sidebar cp-sidebar--portal';

    return (
        <div className="cp-app">
            {sidebarOpen && (
                <button
                    type="button"
                    className="cp-sidebar-backdrop d-lg-none"
                    aria-label="Close menu"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            <aside className={`${sidebarClass} ${sidebarOpen ? 'show' : ''}`}>
                <div className="cp-sidebar-brand">
                    <div className="cp-sidebar-brand__icon">
                        <i className={`bi bi-${brandIcon}`} />
                    </div>
                    <div className="cp-sidebar-brand__text">
                        <span className="cp-sidebar-brand__title">{brandTitle}</span>
                        <span className="cp-sidebar-brand__subtitle">{brandSubtitle}</span>
                    </div>
                </div>

                <div className="cp-sidebar-scroll">
                    <SidebarNav groups={navGroups} storageKey={`cp-nav-${variant}`} />
                </div>

                <div className="cp-sidebar-footer">
                    <div className="cp-sidebar-user">
                        <div className="cp-sidebar-user__avatar">
                            {(userName ?? 'U').charAt(0).toUpperCase()}
                        </div>
                        <div className="cp-sidebar-user__info">
                            <span className="cp-sidebar-user__name">{userName ?? 'User'}</span>
                            <span className="cp-sidebar-user__role">{variant === 'admin' ? 'Super Admin' : 'Portal User'}</span>
                        </div>
                    </div>
                </div>
            </aside>

            <div className="cp-main">
                <header className="cp-topbar">
                    <div className="d-flex align-items-center gap-3">
                        <button
                            type="button"
                            className="btn cp-menu-btn d-lg-none"
                            onClick={() => setSidebarOpen(true)}
                        >
                            <i className="bi bi-list fs-5" />
                        </button>
                        {headerExtra}
                    </div>

                    <div className="d-flex align-items-center gap-2">
                        <button
                            type="button"
                            className="btn btn-sm cp-icon-btn"
                            onClick={() => setThemeState(toggleTheme())}
                            title="Toggle theme"
                        >
                            <i className={`bi bi-${theme === 'dark' ? 'sun' : 'moon'}`} />
                        </button>
                        <Link
                            href={logoutHref}
                            method="post"
                            as="button"
                            className="btn btn-outline-danger btn-sm"
                        >
                            <i className="bi bi-box-arrow-right me-1" />
                            <span className="d-none d-md-inline">Logout</span>
                        </Link>
                    </div>
                </header>

                {topStrip}

                <main className="cp-content">
                    {mainTop}
                    {children}
                </main>
            </div>
        </div>
    );
}
