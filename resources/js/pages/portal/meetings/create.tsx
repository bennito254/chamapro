import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { store } from '@/routes/portal/meetings';
import PageHeader from '@/components/shared/PageHeader';

type Props = {
    defaults: {
        title: string;
        date: string;
        status: string;
    };
};

export default function Page({ defaults }: Props) {
    const route = store.form();

    return (
        <>
            <Head title="Create Meeting" />
            <PageHeader title="Create Meeting" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Title"
                                    name="title"
                                    required
                                    defaultValue={defaults.title}
                                    error={errors.title}
                                />
                                <FormField
                                    label="Date"
                                    name="date"
                                    type="date"
                                    required
                                    defaultValue={defaults.date}
                                    error={errors.date}
                                />
                                <FormField label="Location" name="venue" error={errors.venue} />
                                <FormField label="Agenda" name="agenda" type="textarea" error={errors.agenda} />
                                <input type="hidden" name="status" value={defaults.status} />
                                <div className="d-flex gap-2 mt-3">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                    <Link href="/portal/meetings" className="btn btn-outline-secondary">
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
