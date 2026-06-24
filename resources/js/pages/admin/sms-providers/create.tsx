import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { index, store } from '@/routes/admin/sms-providers';

const driverOptions = [
    { value: 'log', label: 'Log (development)' },
    { value: 'africas_talking', label: "Africa's Talking" },
    { value: 'twilio', label: 'Twilio' },
    { value: 'bulksms', label: 'BulkSMS' },
    { value: 'http', label: 'HTTP webhook' },
];

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

export default function Page() {
    return (
        <>
            <Head title="Create SMS Provider" />
            <PageHeader
                title="Create SMS provider"
                breadcrumbs={[
                    { label: 'SMS Providers', href: index.url() },
                    { label: 'Create' },
                ]}
            />
            <div className="card border-0 shadow-sm">
                <div className="card-body p-4">
                    <Form {...store.form()}>
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Name" name="name" required error={errors.name} />
                                <FormField
                                    label="Driver"
                                    name="driver"
                                    required
                                    options={driverOptions}
                                    defaultValue="log"
                                    error={errors.driver}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    options={statusOptions}
                                    defaultValue="active"
                                    error={errors.status}
                                />
                                <div className="mb-3">
                                    <label htmlFor="credentials" className="form-label">
                                        Credentials (JSON)
                                    </label>
                                    <textarea
                                        id="credentials"
                                        name="credentials"
                                        className={`form-control font-monospace ${errors.credentials ? 'is-invalid' : ''}`}
                                        rows={5}
                                        defaultValue="{}"
                                    />
                                    <div className="form-text">
                                        Use an empty object for the log driver. Example: {`{"api_key":"..."}`}
                                    </div>
                                    {errors.credentials && (
                                        <div className="invalid-feedback d-block">{errors.credentials}</div>
                                    )}
                                </div>
                                <div className="form-check mb-4">
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        name="is_default"
                                        id="is_default"
                                        value="1"
                                    />
                                    <label className="form-check-label" htmlFor="is_default">
                                        Set as default provider
                                    </label>
                                </div>
                                <div className="d-flex gap-2">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Create provider'}
                                    </button>
                                    <Link href={index()} className="btn btn-outline-secondary">
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
