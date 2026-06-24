import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { update } from '@/routes/portal/contributions';
import PageHeader from '@/components/shared/PageHeader';
import type { Contribution } from '@/types/models';

type Props = {
    contribution: Contribution;
    members: Array<{ id: number; full_name: string }>;
    types: Array<{ id: number; name: string }>;
    channels: Array<{ id: number; name: string }>;
};

export default function Page({ contribution, members, types, channels }: Props) {
    const route = update.form(contribution);

    return (
        <>
            <Head title="Edit Contribution" />
            <PageHeader title="Edit Contribution" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
                        <FormField label="Amount" name="amount" type="number" required defaultValue={String(contribution.amount ?? '')} error={errors.amount} />
                        <FormField label="Date" name="date" type="date" defaultValue={String(contribution.date ?? '')} error={errors.date} />
                        <FormField label="Notes" name="notes" type="textarea" defaultValue={String(contribution.notes ?? '')} error={errors.notes} />
                            <div className="d-flex gap-2 mt-3">
                                <button type="submit" className="btn btn-primary" disabled={processing}>{processing ? 'Saving...' : 'Save'}</button>
                                <Link href="/portal/contributions" className="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </>
                    )}
                </Form>
            </div></div>
        </>
    );
}
