import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { statusOptions } from '@/lib/form-options';
import { update } from '@/routes/portal/sms-templates';
import type { SmsTemplate } from '@/types/models';

type Placeholder = {
    key: string;
    label: string;
    description: string;
};

type Props = {
    template: SmsTemplate;
    placeholders: Placeholder[];
};

export default function Page({ template, placeholders }: Props) {
    const route = update.form(template.sqid);

    return (
        <>
            <Head title={`Edit ${template.name}`} />
            <PageHeader title={`Edit ${template.name}`} />
            <div className="row g-3">
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body">
                            <Form {...route}>
                                {({ errors, processing }) => (
                                    <>
                                        <FormField
                                            label="Name"
                                            name="name"
                                            required
                                            defaultValue={template.name}
                                            error={errors.name}
                                        />
                                        <FormField
                                            label="Message body"
                                            name="body"
                                            type="textarea"
                                            rows={6}
                                            required
                                            defaultValue={template.body}
                                            error={errors.body}
                                            help="Use placeholders like {name} and {loan_balance}."
                                        />
                                        <FormField
                                            label="Status"
                                            name="status"
                                            required
                                            defaultValue={template.status}
                                            options={statusOptions}
                                            error={errors.status}
                                        />
                                        <div className="d-flex mt-3 gap-2">
                                            <button
                                                type="submit"
                                                className="btn btn-primary"
                                                disabled={processing}
                                            >
                                                {processing
                                                    ? 'Saving...'
                                                    : 'Save'}
                                            </button>
                                            <Link
                                                href="/portal/sms-templates"
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
                </div>
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm">
                        <div className="card-header border-0 bg-transparent pt-3">
                            <h6 className="mb-0">Available placeholders</h6>
                        </div>
                        <div className="card-body pt-0">
                            <ul className="list-unstyled small mb-0">
                                {placeholders.map((placeholder) => (
                                    <li key={placeholder.key} className="mb-2">
                                        <code>{`{${placeholder.key}}`}</code>
                                        <div className="text-muted">
                                            {placeholder.description}
                                        </div>
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
