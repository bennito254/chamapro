import { Link } from '@inertiajs/react';
import type { Paginated } from '@/types/pagination';

export type Column<T> = {
    key: string;
    label: string;
    render?: (row: T) => React.ReactNode;
    className?: string;
};

type Props<T extends Record<string, unknown>> = {
    columns: Column<T>[];
    data: Paginated<T> | T[];
    searchable?: boolean;
    searchPlaceholder?: string;
    emptyMessage?: string;
    rowKey?: keyof T | ((row: T) => string | number);
    footer?: React.ReactNode;
};

function getRows<T>(data: Paginated<T> | T[]): T[] {
    return Array.isArray(data) ? data : data.data;
}

function getPagination<T>(data: Paginated<T> | T[]): Paginated<T> | null {
    return Array.isArray(data) ? null : data;
}

export default function DataTable<T extends Record<string, unknown>>({
    columns,
    data,
    searchable = true,
    searchPlaceholder = 'Search...',
    emptyMessage = 'No records found.',
    rowKey = 'id' as keyof T,
    footer,
}: Props<T>) {
    const rows = getRows(data);
    const pagination = getPagination(data);

    const resolveKey = (row: T, index: number): string | number => {
        if (typeof rowKey === 'function') {
            return rowKey(row);
        }
        const value = row[rowKey];
        return typeof value === 'string' || typeof value === 'number' ? value : index;
    };

    return (
        <div className="card border-0 shadow-sm">
            {searchable && (
                <div className="card-header bg-white border-bottom py-3">
                    <div className="input-group" style={{ maxWidth: 320 }}>
                        <span className="input-group-text bg-white">
                            <i className="bi bi-search" />
                        </span>
                        <input
                            type="search"
                            className="form-control"
                            placeholder={searchPlaceholder}
                            onChange={(e) => {
                                const term = e.target.value.toLowerCase();
                                const table = e.currentTarget.closest('.card')?.querySelector('tbody');
                                table?.querySelectorAll('tr').forEach((tr) => {
                                    tr.classList.toggle('d-none', !tr.textContent?.toLowerCase().includes(term));
                                });
                            }}
                        />
                    </div>
                </div>
            )}
            <div className="table-responsive">
                <table className="table table-hover align-middle mb-0">
                    <thead className="table-light">
                        <tr>
                            {columns.map((col) => (
                                <th key={col.key} className={col.className}>
                                    {col.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 ? (
                            <tr>
                                <td colSpan={columns.length} className="text-center text-muted py-5">
                                    {emptyMessage}
                                </td>
                            </tr>
                        ) : (
                            rows.map((row, index) => (
                                <tr key={resolveKey(row, index)}>
                                    {columns.map((col) => (
                                        <td key={col.key} className={col.className}>
                                            {col.render
                                                ? col.render(row)
                                                : String(row[col.key] ?? '—')}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
            {footer && <div className="card-footer bg-light border-top">{footer}</div>}
            {pagination && pagination.last_page > 1 && (
                <div className="card-footer bg-white d-flex justify-content-between align-items-center">
                    <small className="text-muted">
                        Showing {pagination.from ?? 0}–{pagination.to ?? 0} of {pagination.total}
                    </small>
                    <nav>
                        <ul className="pagination pagination-sm mb-0">
                            {pagination.links.map((link, i) => (
                                <li
                                    key={i}
                                    className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}
                                >
                                    {link.url ? (
                                        <Link
                                            href={link.url}
                                            className="page-link"
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ) : (
                                        <span
                                            className="page-link"
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    )}
                                </li>
                            ))}
                        </ul>
                    </nav>
                </div>
            )}
        </div>
    );
}
