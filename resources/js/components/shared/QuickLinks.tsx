import { Link } from '@inertiajs/react';
import type { QuickLink } from '@/lib/navigation';

type Props = {
    title?: string;
    links: QuickLink[];
};

const colorClasses: Record<string, string> = {
    primary: 'cp-quick-link--primary',
    success: 'cp-quick-link--success',
    info: 'cp-quick-link--info',
    warning: 'cp-quick-link--warning',
    danger: 'cp-quick-link--danger',
    secondary: 'cp-quick-link--secondary',
    dark: 'cp-quick-link--dark',
};

export default function QuickLinks({ title = 'Quick Actions', links }: Props) {
    return (
        <div className="card cp-quick-links-card border-0">
            <div className="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                <h6 className="fw-semibold mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-lightning-charge-fill text-warning" />
                    {title}
                </h6>
            </div>
            <div className="card-body p-4">
                <div className="row g-3">
                    {links.map((link) => (
                        <div key={link.href} className="col-6 col-md-4 col-xl-3">
                            <Link
                                href={link.href}
                                className={`cp-quick-link ${colorClasses[link.color ?? 'primary']}`}
                            >
                                <span className="cp-quick-link__icon">
                                    <i className={`bi bi-${link.icon}`} />
                                </span>
                                <span className="cp-quick-link__content">
                                    <span className="cp-quick-link__label">{link.label}</span>
                                    {link.description && (
                                        <span className="cp-quick-link__desc">{link.description}</span>
                                    )}
                                </span>
                                <i className="bi bi-arrow-right cp-quick-link__arrow" />
                            </Link>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
