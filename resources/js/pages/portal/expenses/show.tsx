import { Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import type { Expense } from '@/types/models';

type Props = { expense: Expense };

export default function Page({ expense }: Props) {
    return (
        <>
            <Head title="Expense" />
            <PageHeader title="Expense" />
            <DetailCard
                title="Details"
                editHref={`/portal/expenses/${expense.sqid}/edit`}
                deleteHref={`/portal/expenses/${expense.sqid}`}
                backHref="/portal/expenses"
                fields={[
                    { label: 'Amount', value: expense.amount },
                    { label: 'Date', value: expense.date, format: 'date' },
                    { label: 'Description', value: expense.description },
                ]}
            />
        </>
    );
}
