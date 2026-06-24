import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { update } from '@/routes/portal/fines';
import PageHeader from '@/components/shared/PageHeader';
import type { Fine } from '@/types/models';

type Props = {
    fine: Fine;
    members: Array<{ id: number; full_name: string }>;
    fineTypes: Array<{ id: number; name: string }>;
};

export default function Page({ fine, members, fineTypes }: Props) {
    const route = update.form(fine);

    return (
        <>
            <Head title="Edit Fine" />
            <PageHeader title="Edit Fine" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
                        <FormField label="Amount" name="amount" type="number" defaultValue={String(fine.amount ?? '')} error={errors.amount} />
                        <FormField label="Reason" name="reason" type="textarea" defaultValue={String(fine.reason ?? '')} error={errors.reason} />
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
