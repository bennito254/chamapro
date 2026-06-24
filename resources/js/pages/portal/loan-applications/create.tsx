import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { store } from '@/routes/portal/loan-applications';
import PageHeader from '@/components/shared/PageHeader';
import type { LoanProduct } from '@/types/models';

type MemberOption = {
    id: number;
    full_name: string;
    membership_number?: string;
};

type Props = {
    members: MemberOption[];
    products: LoanProduct[];
};

export default function Page({ members, products }: Props) {
    const route = store.form();

    return (
        <>
            <Head title="New Loan Application" />
            <PageHeader title="New Loan Application" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Member"
                                    name="member_id"
                                    required
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
                                    options={products.map((p) => ({
                                        value: String(p.id),
                                        label: p.max_amount
                                            ? `${p.name} · max ${p.max_amount}`
                                            : p.name,
                                    }))}
                                    error={errors.loan_product_id}
                                />
                                <FormField
                                    label="Requested amount"
                                    name="requested_amount"
                                    type="number"
                                    required
                                    error={errors.requested_amount}
                                    help="Repayment term is taken from the selected loan product."
                                />
                                <FormField
                                    label="Purpose"
                                    name="purpose"
                                    type="textarea"
                                    required
                                    error={errors.purpose}
                                />
                                <div className="d-flex gap-2 mt-3">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Submit application'}
                                    </button>
                                    <Link href="/portal/loan-applications" className="btn btn-outline-secondary">
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
