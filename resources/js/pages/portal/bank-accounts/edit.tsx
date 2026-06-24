import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { statusOptions } from '@/lib/form-options';
import { update } from '@/routes/portal/bank-accounts';
import type { BankAccount } from '@/types/models';

type Props = {
    account: BankAccount;
};

export default function Page({ account }: Props) {
    const route = update.form(account);

    return (
        <>
            <Head title="Edit Bank Account" />
            <PageHeader title="Edit Bank Account" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Account Name"
                                    name="account_name"
                                    required
                                    defaultValue={String(
                                        account.account_name ?? '',
                                    )}
                                    error={errors.account_name}
                                />
                                <FormField
                                    label="Bank"
                                    name="bank_name"
                                    required
                                    defaultValue={String(
                                        account.bank_name ?? '',
                                    )}
                                    error={errors.bank_name}
                                />
                                <FormField
                                    label="Account Number"
                                    name="account_number"
                                    required
                                    defaultValue={String(
                                        account.account_number ?? '',
                                    )}
                                    error={errors.account_number}
                                />
                                <FormField
                                    label="Branch"
                                    name="branch"
                                    defaultValue={String(account.branch ?? '')}
                                    error={errors.branch}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue={String(
                                        account.status ?? 'active',
                                    )}
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
                                        href={`/portal/bank-accounts/${account.sqid}`}
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
