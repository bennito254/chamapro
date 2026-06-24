import type { ReactNode } from 'react';

type Props = {
    title: string;
    description?: string;
    actions?: ReactNode;
    breadcrumbs?: Array<{ label: string; href?: string }>;
};

export default function PageHeader({
    title,
    description,
    actions,
    breadcrumbs,
}: Props) {
    return (
        <div className="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
            <div>
                {breadcrumbs && breadcrumbs.length > 0 && (
                    <nav aria-label="breadcrumb">
                        <ol className="breadcrumb small mb-2">
                            {breadcrumbs.map((crumb, i) => (
                                <li
                                    key={i}
                                    className={`breadcrumb-item ${!crumb.href ? 'active' : ''}`}
                                >
                                    {crumb.href ? (
                                        <a
                                            href={crumb.href}
                                            className="text-decoration-none"
                                        >
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
                <h1 className="h3 fw-bold mb-1">{title}</h1>
                {description && (
                    <p className="mb-0 text-muted">{description}</p>
                )}
            </div>
            {actions && <div className="d-flex flex-wrap gap-2">{actions}</div>}
        </div>
    );
}
