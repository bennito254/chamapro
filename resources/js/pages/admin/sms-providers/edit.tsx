import { Form, Head, Link } from '@inertiajs/react';
import { confirmDelete } from '@/lib/alerts';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { destroy, index, update } from '@/routes/admin/sms-providers';
import { router } from '@inertiajs/react';
import type { SmsProvider } from '@/types/models';

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

type Props = {
    provider: SmsProvider & { credentials?: Record<string, unknown> };
};

export default function Page({ provider }: Props) {
    const handleDelete = async () => {
        const confirmed = await confirmDelete();

        if (confirmed) {
            router.delete(destroy.url(provider));
        }
    };

    return (
        <>
            <Head title={`Edit ${provider.name}`} />
            <PageHeader
                title={`Edit ${provider.name}`}
                breadcrumbs={[
                    { label: 'SMS Providers', href: index.url() },
                    { label: provider.name },
                ]}
                actions={
                    <button type="button" className="btn btn-outline-danger btn-sm" onClick={handleDelete}>
                        <i className="bi bi-trash me-1" />
                        Delete
                    </button>
                }
            />
            <div className="card border-0 shadow-sm">
                <div className="card-body p-4">
                    <Form {...update.form(provider)}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Name"
                                    name="name"
                                    required
                                    defaultValue={provider.name}
                                    error={errors.name}
                                />
                                <FormField
                                    label="Driver"
                                    name="driver"
                                    required
                                    options={driverOptions}
                                    defaultValue={provider.driver}
                                    error={errors.driver}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    options={statusOptions}
                                    defaultValue={provider.status ?? 'active'}
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
                                        defaultValue={JSON.stringify(provider.credentials ?? {}, null, 2)}
                                    />
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
                                        defaultChecked={provider.is_default}
                                    />
                                    <label className="form-check-label" htmlFor="is_default">
                                        Set as default provider
                                    </label>
                                </div>
                                <div className="d-flex gap-2">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save changes'}
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
