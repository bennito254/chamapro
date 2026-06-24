import { Link } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { confirmDelete } from '@/lib/alerts';
import { formatDate, formatDateTime, titleCase } from '@/lib/format';

type Field = {
    label: string;
    value: unknown;
    format?: 'date' | 'datetime' | 'currency' | 'badge';
};

type Props = {
    title: string;
    fields: Field[];
    editHref?: string;
    deleteHref?: string;
    backHref?: string;
    actions?: React.ReactNode;
};

function formatValue(field: Field): React.ReactNode {
    const { value, format } = field;
    if (value === null || value === undefined || value === '') {
        return '—';
    }
    if (format === 'date') {
        return formatDate(String(value));
    }
    if (format === 'datetime') {
        return formatDateTime(String(value));
    }
    if (format === 'badge') {
        return <span className="badge bg-secondary">{titleCase(String(value))}</span>;
    }
    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }
    return String(value);
}

export default function DetailCard({ title, fields, editHref, deleteHref, backHref, actions }: Props) {
    const handleDelete = async () => {
        if (!deleteHref) {
            return;
        }
        const confirmed = await confirmDelete();
        if (confirmed) {
            router.delete(deleteHref);
        }
    };

    return (
        <div className="card border-0 shadow-sm">
            <div className="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 className="mb-0 fw-semibold">{title}</h5>
                <div className="d-flex gap-2">
                    {backHref && (
                        <Link href={backHref} className="btn btn-sm btn-outline-secondary">
                            <i className="bi bi-arrow-left" /> Back
                        </Link>
                    )}
                    {editHref && (
                        <Link href={editHref} className="btn btn-sm btn-primary">
                            <i className="bi bi-pencil" /> Edit
                        </Link>
                    )}
                    {deleteHref && (
                        <button type="button" className="btn btn-sm btn-outline-danger" onClick={handleDelete}>
                            <i className="bi bi-trash" /> Delete
                        </button>
                    )}
                    {actions}
                </div>
            </div>
            <div className="card-body">
                <dl className="row mb-0">
                    {fields.map((field) => (
                        <div key={field.label} className="col-md-6 mb-3">
                            <dt className="text-muted small">{field.label}</dt>
                            <dd className="mb-0 fw-medium">{formatValue(field)}</dd>
                        </div>
                    ))}
                </dl>
            </div>
        </div>
    );
}
