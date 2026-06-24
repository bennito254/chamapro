import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { store } from '@/routes/portal/expense-categories';
import PageHeader from '@/components/shared/PageHeader';
import type { ExpenseCategory } from '@/types/models';

type Props = {
    
    
};

export default function Page({  }: Props) {
    const route = store.form();

    return (
        <>
            <Head title="Create ExpenseCategory" />
            <PageHeader title="Create ExpenseCategory" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
                        <FormField label="Name" name="name" required error={errors.name} />
                        <FormField label="Description" name="description" error={errors.description} />
                            <div className="d-flex gap-2 mt-3">
                                <button type="submit" className="btn btn-primary" disabled={processing}>{processing ? 'Saving...' : 'Save'}</button>
                                <Link href="/portal/expense-categories" className="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </>
                    )}
                </Form>
            </div></div>
        </>
    );
}
