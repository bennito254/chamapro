import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { store } from '@/routes/portal/fine-types';

export default function Page() {
    const route = store.form();

    return (
        <>
            <Head title="Create FineType" />
            <PageHeader title="Create FineType" />
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
                                    label="Amount"
                                    name="amount"
                                    type="number"
                                    required
                                    error={errors.amount}
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
                                        href="/portal/fine-types"
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
