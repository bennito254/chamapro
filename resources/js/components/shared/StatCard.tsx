import type { CSSProperties } from 'react';

type Props = {
    title: string;
    value: string | number;
    icon?: string;
    color?: 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'secondary';
    subtitle?: string;
};

const accentMap = {
    primary: '#2563eb',
    success: '#059669',
    warning: '#d97706',
    danger: '#dc2626',
    info: '#0284c7',
    secondary: '#64748b',
};

export default function StatCard({ title, value, icon, color = 'primary', subtitle }: Props) {
    return (
        <div className="card cp-stat-card h-100 border-0">
            <div className="card-body d-flex align-items-start gap-3">
                {icon && (
                    <div
                        className="cp-stat-card__icon"
                        style={{ '--accent': accentMap[color] } as CSSProperties}
                    >
                        <i className={`bi bi-${icon}`} />
                    </div>
                )}
                <div className="flex-grow-1 min-w-0">
                    <p className="cp-stat-card__label mb-1">{title}</p>
                    <h3 className="cp-stat-card__value mb-0">{value}</h3>
                    {subtitle && <p className="cp-stat-card__subtitle mb-0">{subtitle}</p>}
                </div>
            </div>
        </div>
    );
}
