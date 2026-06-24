import { Form, Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/components/shared/PageHeader';
import { create, store } from '@/routes/admin/owner-sms';
import { formatDateTime } from '@/lib/format';

type StatusOption = { value: string; label: string };

type RecentMessage = {
    id: number;
    recipient: string;
    body: string;
    status: string;
    created_at?: string;
};

type Props = {
    subscriptionStatus: string;
    recipientCount: number;
    statusOptions: StatusOption[];
    recentMessages: RecentMessage[];
};

export default function Page({ subscriptionStatus, recipientCount, statusOptions, recentMessages }: Props) {
    const [status, setStatus] = useState(subscriptionStatus);

    const refreshCount = (nextStatus: string) => {
        setStatus(nextStatus);
        router.get(create.url({ query: { subscription_status: nextStatus } }), {}, { preserveState: true, replace: true });
    };

    return (
        <>
            <Head title="Messaging" />
            <PageHeader
                title="Messaging"
                description="Send platform announcements to chama owners by subscription status."
            />

            <div className="row g-4">
                <div className="col-lg-7">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body p-4">
                            <Form {...store.form()}>
                                {({ errors, processing }) => (
                                    <>
                                        <input type="hidden" name="subscription_status" value={status} />

                                        <label className="form-label fw-medium">Recipients</label>
                                        <div className="d-flex flex-wrap gap-2 mb-3">
                                            {statusOptions.map((option) => (
                                                <button
                                                    key={option.value}
                                                    type="button"
                                                    className={`btn btn-sm ${status === option.value ? 'btn-primary' : 'btn-outline-secondary'}`}
                                                    onClick={() => refreshCount(option.value)}
                                                >
                                                    {option.label}
                                                </button>
                                            ))}
                                        </div>

                                        <div className="alert alert-info py-2 small mb-4">
                                            <i className="bi bi-telephone me-2" />
                                            <strong>{recipientCount}</strong> group owner(s) with a phone number will
                                            receive this message.
                                        </div>

                                        <div className="mb-3">
                                            <label htmlFor="body" className="form-label fw-medium">
                                                Message
                                            </label>
                                            <textarea
                                                id="body"
                                                name="body"
                                                className={`form-control ${errors.body ? 'is-invalid' : ''}`}
                                                rows={6}
                                                maxLength={1000}
                                                placeholder="Write your message to group owners..."
                                                required
                                            />
                                            {errors.body && (
                                                <div className="invalid-feedback d-block">{errors.body}</div>
                                            )}
                                            {errors.subscription_status && (
                                                <div className="invalid-feedback d-block">
                                                    {errors.subscription_status}
                                                </div>
                                            )}
                                        </div>

                                        <button type="submit" className="btn btn-primary" disabled={processing || recipientCount === 0}>
                                            {processing ? 'Sending...' : 'Send SMS'}
                                        </button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                </div>

                <div className="col-lg-5">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-header bg-transparent border-0 pt-4 px-4">
                            <h6 className="fw-semibold mb-0">Recent broadcasts</h6>
                        </div>
                        <div className="card-body pt-0">
                            {recentMessages.length === 0 ? (
                                <p className="text-muted small mb-0">No owner broadcasts sent yet.</p>
                            ) : (
                                <ul className="list-unstyled mb-0">
                                    {recentMessages.map((message) => (
                                        <li key={message.id} className="border-bottom py-3">
                                            <div className="d-flex justify-content-between gap-2 mb-1">
                                                <span className="small fw-medium">{message.recipient}</span>
                                                <span className={`badge ${message.status === 'sent' ? 'bg-success' : 'bg-danger'}`}>
                                                    {message.status}
                                                </span>
                                            </div>
                                            <p className="small text-muted mb-1">{message.body}</p>
                                            <span className="text-muted" style={{ fontSize: '0.75rem' }}>
                                                {formatDateTime(message.created_at)}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
