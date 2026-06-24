import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { NavGroup } from '@/lib/navigation';

type Props = {
    groups: NavGroup[];
    storageKey: string;
};

function isActive(url: string, href: string): boolean {
    if (href === '/admin/dashboard' || href === '/portal/dashboard') {
        return url === href || url === href + '/';
    }

    return url === href || url.startsWith(href + '/');
}

function groupHasActiveItem(url: string, group: NavGroup): boolean {
    return group.items.some((item) => isActive(url, item.href));
}

export default function SidebarNav({ groups, storageKey }: Props) {
    const { url } = usePage();
    const [openGroups, setOpenGroups] = useState<Record<string, boolean>>({});

    useEffect(() => {
        const stored = localStorage.getItem(storageKey);
        const parsed = stored ? (JSON.parse(stored) as Record<string, boolean>) : {};

        const initial: Record<string, boolean> = {};
        groups.forEach((group) => {
            const hasActive = groupHasActiveItem(url, group);
            initial[group.id] = parsed[group.id] ?? hasActive ?? group.id === 'overview';
        });

        setOpenGroups(initial);
    }, [groups, storageKey, url]);

    const toggleGroup = (id: string) => {
        setOpenGroups((prev) => {
            const next = { ...prev, [id]: !prev[id] };
            localStorage.setItem(storageKey, JSON.stringify(next));

            return next;
        });
    };

    return (
        <nav className="cp-sidebar-nav">
            {groups.map((group) => {
                const isOpen = openGroups[group.id] ?? false;
                const hasActive = groupHasActiveItem(url, group);
                const isSingleItem = group.items.length === 1 && group.id === 'overview';

                if (isSingleItem) {
                    const item = group.items[0];

                    return (
                        <Link
                            key={group.id}
                            href={item.href}
                            className={`cp-nav-item ${isActive(url, item.href) ? 'active' : ''}`}
                        >
                            <i className={`bi bi-${item.icon}`} />
                            <span>{item.label}</span>
                        </Link>
                    );
                }

                return (
                    <div key={group.id} className="cp-nav-group">
                        <button
                            type="button"
                            className={`cp-nav-group-toggle ${hasActive ? 'has-active' : ''} ${isOpen ? 'open' : ''}`}
                            onClick={() => toggleGroup(group.id)}
                            aria-expanded={isOpen}
                        >
                            <span className="cp-nav-group-label">
                                <i className={`bi bi-${group.icon}`} />
                                <span>{group.label}</span>
                            </span>
                            <i className="bi bi-chevron-down cp-nav-chevron" />
                        </button>
                        <div className={`cp-nav-submenu ${isOpen ? 'show' : ''}`}>
                            <div className="cp-nav-submenu-inner">
                                {group.items.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={`cp-nav-subitem ${isActive(url, item.href) ? 'active' : ''}`}
                                >
                                    <i className={`bi bi-${item.icon}`} />
                                    <span>{item.label}</span>
                                </Link>
                                ))}
                            </div>
                        </div>
                    </div>
                );
            })}
        </nav>
    );
}
