import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { statusOptions } from '@/lib/form-options';
import { store } from '@/routes/portal/sms-templates';

type Placeholder = {
    key: string;
    label: string;
    description: string;
};

type Props = {
    placeholders: Placeholder[];
};

export default function Page({ placeholders }: Props) {
    const route = store.form();

    return (
        <>
            <Head title="Create SMS Template" />
            <PageHeader title="Create SMS Template" />
            <div className="row g-3">
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body">
                            <Form {...route}>
                                {({ errors, processing }) => (
                                    <>
                                        <FormField label="Name" name="name" required error={errors.name} />
                                        <FormField
                                            label="Message body"
                                            name="body"
                                            type="textarea"
                                            rows={6}
                                            required
                                            error={errors.body}
                                            help="Use placeholders like {name} and {loan_balance}."
                                        />
                                        <FormField
                                            label="Status"
                                            name="status"
                                            required
                                            defaultValue="active"
                                            options={statusOptions}
                                            error={errors.status}
                                        />
                                        <div className="d-flex gap-2 mt-3">
                                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                                {processing ? 'Saving...' : 'Save'}
                                            </button>
                                            <Link href="/portal/sms-templates" className="btn btn-outline-secondary">
                                                Cancel
                                            </Link>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                </div>
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm">
                        <div className="card-header bg-transparent border-0 pt-3">
                            <h6 className="mb-0">Available placeholders</h6>
                        </div>
                        <div className="card-body pt-0">
                            <ul className="list-unstyled mb-0 small">
                                {placeholders.map((placeholder) => (
                                    <li key={placeholder.key} className="mb-2">
                                        <code>{`{${placeholder.key}}`}</code>
                                        <div className="text-muted">{placeholder.description}</div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
