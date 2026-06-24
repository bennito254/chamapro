import type { ReactNode } from 'react';

type Props = {
    title: string;
    description?: string;
    actions?: ReactNode;
    breadcrumbs?: Array<{ label: string; href?: string }>;
};

export default function PageHeader({ title, description, actions, breadcrumbs }: Props) {
    return (
        <div className="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                {breadcrumbs && breadcrumbs.length > 0 && (
                    <nav aria-label="breadcrumb">
                        <ol className="breadcrumb mb-2 small">
                            {breadcrumbs.map((crumb, i) => (
                                <li
                                    key={i}
                                    className={`breadcrumb-item ${!crumb.href ? 'active' : ''}`}
                                >
                                    {crumb.href ? (
                                        <a href={crumb.href} className="text-decoration-none">
                                            {crumb.label}
                                        </a>
                                    ) : (
                                        crumb.label
                                    )}
                                </li>
                            ))}
                        </ol>
                    </nav>
                )}
                <h1 className="h3 mb-1 fw-bold">{title}</h1>
                {description && <p className="text-muted mb-0">{description}</p>}
            </div>
            {actions && <div className="d-flex flex-wrap gap-2">{actions}</div>}
        </div>
    );
}
