import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { update } from '@/routes/portal/expenses';
import type { Expense } from '@/types/models';

type Props = {
    expense: Expense;
    categories: Array<{ id: number; name: string }>;
};

export default function Page({ expense }: Props) {
    const route = update.form(expense);

    return (
        <>
            <Head title="Edit Expense" />
            <PageHeader title="Edit Expense" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Amount"
                                    name="amount"
                                    type="number"
                                    defaultValue={String(expense.amount ?? '')}
                                    error={errors.amount}
                                />
                                <FormField
                                    label="Date"
                                    name="date"
                                    type="date"
                                    defaultValue={String(expense.date ?? '')}
                                    error={errors.date}
                                />
                                <FormField
                                    label="Description"
                                    name="description"
                                    type="textarea"
                                    defaultValue={String(
                                        expense.description ?? '',
                                    )}
                                    error={errors.description}
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
                                        href="/portal/expenses"
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
