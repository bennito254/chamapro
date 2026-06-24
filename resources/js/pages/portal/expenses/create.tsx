import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { store } from '@/routes/portal/expenses';
import PageHeader from '@/components/shared/PageHeader';
import type { Expense } from '@/types/models';

type Props = {
    categories: Array<{ id: number; name: string }>;
};

export default function Page({ categories }: Props) {
    const route = store.form();

    return (
        <>
            <Head title="Record Expense" />
            <PageHeader title="Record Expense" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
                        <FormField label="Category" name="expense_category_id" required options={categories.map((c) => ({ value: String(c.id), label: c.name }))} error={errors.expense_category_id} />
                        <FormField label="Amount" name="amount" type="number" required error={errors.amount} />
                        <FormField label="Date" name="date" type="date" required error={errors.date} />
                        <FormField label="Description" name="description" type="textarea" error={errors.description} />
                            <div className="d-flex gap-2 mt-3">
                                <button type="submit" className="btn btn-primary" disabled={processing}>{processing ? 'Saving...' : 'Save'}</button>
                                <Link href="/portal/expenses" className="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </>
                    )}
                </Form>
            </div></div>
        </>
    );
}
