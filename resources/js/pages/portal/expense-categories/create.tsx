import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { store } from '@/routes/portal/expense-categories';

export default function Page() {
    const route = store.form();

    return (
        <>
            <Head title="Create ExpenseCategory" />
            <PageHeader title="Create ExpenseCategory" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Name"
                                    name="name"
                                    required
                                    error={errors.name}
                                />
                                <FormField
                                    label="Description"
                                    name="description"
                                    error={errors.description}
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
                                        href="/portal/expense-categories"
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
