import { Form, Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { formatDateTime } from '@/lib/format';
import { index, update } from '@/routes/admin/support-tickets';
import type { SupportTicket } from '@/types/models';

type Note = {
    id: number;
    body: string;
    is_internal: boolean;
    created_at?: string;
};

type Props = {
    ticket: SupportTicket & {
        notes?: Note[];
        group?: { name: string };
        user?: { name: string; email: string };
    };
};

const statusOptions = [
    { value: 'open', label: 'Open' },
    { value: 'pending', label: 'Pending' },
    { value: 'resolved', label: 'Resolved' },
    { value: 'closed', label: 'Closed' },
];

const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' },
];

export default function Page({ ticket }: Props) {
    return (
        <>
            <Head title={ticket.subject} />
            <PageHeader
                title={ticket.subject}
                breadcrumbs={[
                    { label: 'Support Tickets', href: index.url() },
                    { label: ticket.subject },
                ]}
            />

            <div className="row g-4">
                <div className="col-lg-7">
                    <DetailCard
                        title="Ticket details"
                        backHref={index.url()}
                        fields={[
                            { label: 'Group', value: ticket.group?.name },
                            { label: 'Submitted by', value: ticket.user?.name },
                            { label: 'Email', value: ticket.user?.email },
                            {
                                label: 'Priority',
                                value: ticket.priority,
                                format: 'badge',
                            },
                            {
                                label: 'Status',
                                value: ticket.status,
                                format: 'badge',
                            },
                            { label: 'Message', value: ticket.message },
                            {
                                label: 'Created',
                                value: ticket.created_at,
                                format: 'datetime',
                            },
                        ]}
                    />

                    {ticket.notes && ticket.notes.length > 0 && (
                        <div className="card mt-4 border-0 shadow-sm">
                            <div className="card-header bg-white">
                                <h6 className="fw-semibold mb-0">Notes</h6>
                            </div>
                            <ul className="list-group list-group-flush">
                                {ticket.notes.map((note) => (
                                    <li
                                        key={note.id}
                                        className="list-group-item"
                                    >
                                        <p className="mb-1">{note.body}</p>
                                        <span className="small text-muted">
                                            {formatDateTime(note.created_at)}
                                            {note.is_internal && ' · Internal'}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>

                <div className="col-lg-5">
                    <div className="card mb-4 border-0 shadow-sm">
                        <div className="card-header bg-white">
                            <h6 className="fw-semibold mb-0">Update ticket</h6>
                        </div>
                        <div className="card-body">
                            <Form {...update.form(ticket)}>
                                {({ errors, processing }) => (
                                    <>
                                        <FormField
                                            label="Status"
                                            name="status"
                                            required
                                            options={statusOptions}
                                            defaultValue={ticket.status}
                                            error={errors.status}
                                        />
                                        <FormField
                                            label="Priority"
                                            name="priority"
                                            required
                                            options={priorityOptions}
                                            defaultValue={ticket.priority}
                                            error={errors.priority}
                                        />
                                        <button
                                            type="submit"
                                            className="btn btn-primary"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Saving...'
                                                : 'Update ticket'}
                                        </button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>

                    <div className="card border-0 shadow-sm">
                        <div className="card-header bg-white">
                            <h6 className="fw-semibold mb-0">Add note</h6>
                        </div>
                        <div className="card-body">
                            <Form
                                action={`/admin/support-tickets/${ticket.sqid}/notes`}
                                method="post"
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <FormField
                                            label="Note"
                                            name="body"
                                            type="textarea"
                                            rows={4}
                                            required
                                            error={errors.body}
                                        />
                                        <div className="form-check mb-3">
                                            <input
                                                className="form-check-input"
                                                type="checkbox"
                                                name="is_internal"
                                                id="is_internal"
                                                value="1"
                                            />
                                            <label
                                                className="form-check-label"
                                                htmlFor="is_internal"
                                            >
                                                Internal note (not visible to
                                                group)
                                            </label>
                                        </div>
                                        <button
                                            type="submit"
                                            className="btn btn-outline-primary"
                                            disabled={processing}
                                        >
                                            Add note
                                        </button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
