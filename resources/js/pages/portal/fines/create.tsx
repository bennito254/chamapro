import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { store } from '@/routes/portal/fines';
import PageHeader from '@/components/shared/PageHeader';
import type { Fine } from '@/types/models';

type Props = {
    members: Array<{ id: number; full_name: string }>;
    fineTypes: Array<{ id: number; name: string }>;
};

export default function Page({ members, fineTypes }: Props) {
    const route = store.form();

    return (
        <>
            <Head title="Issue Fine" />
            <PageHeader title="Issue Fine" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
                        <FormField label="Member" name="member_id" required options={members.map((m) => ({ value: String(m.id), label: m.full_name }))} error={errors.member_id} />
                        <FormField label="Fine Type" name="fine_type_id" required options={fineTypes.map((t) => ({ value: String(t.id), label: t.name }))} error={errors.fine_type_id} />
                        <FormField label="Amount" name="amount" type="number" required error={errors.amount} />
                        <FormField label="Date" name="date" type="date" required error={errors.date} />
                        <FormField label="Reason" name="reason" type="textarea" error={errors.reason} />
                            <div className="d-flex gap-2 mt-3">
                                <button type="submit" className="btn btn-primary" disabled={processing}>{processing ? 'Saving...' : 'Save'}</button>
                                <Link href="/portal/fines" className="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </>
                    )}
                </Form>
            </div></div>
        </>
    );
}
