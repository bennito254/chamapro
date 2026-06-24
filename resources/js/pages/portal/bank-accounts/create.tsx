import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { statusOptions } from '@/lib/form-options';
import { store } from '@/routes/portal/bank-accounts';

export default function Page() {
    const route = store.form();

    return (
        <>
            <Head title="Create Bank Account" />
            <PageHeader title="Create Bank Account" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Account Name"
                                    name="account_name"
                                    required
                                    error={errors.account_name}
                                />
                                <FormField
                                    label="Bank"
                                    name="bank_name"
                                    required
                                    error={errors.bank_name}
                                />
                                <FormField
                                    label="Account Number"
                                    name="account_number"
                                    required
                                    error={errors.account_number}
                                />
                                <FormField
                                    label="Branch"
                                    name="branch"
                                    error={errors.branch}
                                />
                                <FormField
                                    label="Opening Balance"
                                    name="opening_balance"
                                    type="number"
                                    defaultValue="0"
                                    error={errors.opening_balance}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue="active"
                                    options={statusOptions}
                                    error={errors.status}
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
                                        href="/portal/bank-accounts"
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
