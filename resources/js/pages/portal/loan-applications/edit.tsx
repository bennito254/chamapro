import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { update } from '@/routes/portal/loan-applications';
import type { LoanApplication, LoanProduct } from '@/types/models';

type MemberOption = {
    id: number;
    full_name: string;
    membership_number?: string;
};

type Props = {
    application: LoanApplication;
    members: MemberOption[];
    products: LoanProduct[];
};

export default function Page({ application, members, products }: Props) {
    const route = update.form(application);

    return (
        <>
            <Head title="Edit Loan Application" />
            <PageHeader title="Edit Loan Application" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Member"
                                    name="member_id"
                                    required
                                    defaultValue={String(application.member_id)}
                                    options={members.map((m) => ({
                                        value: String(m.id),
                                        label: m.membership_number
                                            ? `${m.full_name} (${m.membership_number})`
                                            : m.full_name,
                                    }))}
                                    error={errors.member_id}
                                />
                                <FormField
                                    label="Loan product"
                                    name="loan_product_id"
                                    required
                                    defaultValue={String(
                                        application.loan_product_id,
                                    )}
                                    options={products.map((p) => ({
                                        value: String(p.id),
                                        label: p.name,
                                    }))}
                                    error={errors.loan_product_id}
                                />
                                <FormField
                                    label="Requested amount"
                                    name="requested_amount"
                                    type="number"
                                    required
                                    defaultValue={String(
                                        application.requested_amount ?? '',
                                    )}
                                    error={errors.requested_amount}
                                />
                                <FormField
                                    label="Purpose"
                                    name="purpose"
                                    type="textarea"
                                    required
                                    defaultValue={String(
                                        application.purpose ?? '',
                                    )}
                                    error={errors.purpose}
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
                                        href={`/portal/loan-applications/${application.sqid}`}
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
