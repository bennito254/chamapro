import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { update } from '@/routes/portal/fine-types';
import PageHeader from '@/components/shared/PageHeader';
import type { FineType } from '@/types/models';

type Props = {
    fineType: FineType;
    
};

export default function Page({ fineType }: Props) {
    const route = update.form(fineType);

    return (
        <>
            <Head title="Edit FineType" />
            <PageHeader title="Edit FineType" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
                        <FormField label="Name" name="name" required defaultValue={String(fineType.name ?? '')} error={errors.name} />
                        <FormField label="Amount" name="amount" type="number" required defaultValue={String(fineType.amount ?? '')} error={errors.amount} />
                            <div className="d-flex gap-2 mt-3">
                                <button type="submit" className="btn btn-primary" disabled={processing}>{processing ? 'Saving...' : 'Save'}</button>
                                <Link href="/portal/fine-types" className="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </>
                    )}
                </Form>
            </div></div>
        </>
    );
}
