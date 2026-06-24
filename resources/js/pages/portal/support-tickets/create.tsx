import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { store } from '@/routes/portal/support-tickets';

export default function Page() {
    const route = store.form();

    return (
        <>
            <Head title="New Ticket" />
            <PageHeader title="New Ticket" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Subject"
                                    name="subject"
                                    required
                                    error={errors.subject}
                                />
                                <FormField
                                    label="Message"
                                    name="message"
                                    type="textarea"
                                    required
                                    error={errors.message}
                                />
                                <FormField
                                    label="Priority"
                                    name="priority"
                                    options={[
                                        { value: 'low', label: 'Low' },
                                        { value: 'medium', label: 'Medium' },
                                        { value: 'high', label: 'High' },
                                    ]}
                                    error={errors.priority}
                                />
                                <div className="d-flex mt-3 gap-2">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                    <Link
                                        href="/portal/support-tickets"
                                        className="btn btn-outline-secondary"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}
