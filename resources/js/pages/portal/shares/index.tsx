import { Form, Head } from '@inertiajs/react';
import { storePurchase } from '@/actions/App/Features/Shares/Controllers/ShareController';
import DataTable from '@/components/shared/DataTable';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { formatDate } from '@/lib/format';
import type { MemberOption, SharePurchase, ShareSetting } from '@/types/models';
import type { Paginated } from '@/types/pagination';
type Props = {
    purchases: Paginated<SharePurchase>;
    settings?: ShareSetting | null;
    members: MemberOption[];
};
export default function SharesIndex({ purchases, settings, members }: Props) {
    return (
        <>
            <Head title="Shares" />
            <PageHeader
                title="Shares"
                description={
                    settings
                        ? `Share value: ${settings.share_value}`
                        : undefined
                }
            />
            <div className="card mb-4 border-0 shadow-sm">
                <div className="card-body">
                    <h6 className="fw-semibold mb-3">Record Purchase</h6>
                    <Form {...storePurchase.form()} className="row g-3">
                        {({ errors, processing }) => (
                            <>
                                <div className="col-md-4">
                                    <FormField
                                        label="Member"
                                        name="member_id"
                                        required
                                        options={members.map((m) => ({
                                            value: String(m.id),
                                            label: m.full_name,
                                        }))}
                                        error={errors.member_id}
                                    />
                                </div>
                                <div className="col-md-3">
                                    <FormField
                                        label="Shares"
                                        name="shares"
                                        type="number"
                                        required
                                        error={errors.shares}
                                    />
                                </div>
                                <div className="col-md-3">
                                    <FormField
                                        label="Date"
                                        name="date"
                                        type="date"
                                        required
                                        error={errors.date}
                                    />
                                </div>
                                <div className="col-md-2 d-flex align-items-end">
                                    <button
                                        type="submit"
                                        className="btn btn-primary w-100"
                                        disabled={processing}
                                    >
                                        Add
                                    </button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
            <DataTable
                data={purchases}
                columns={[
                    { key: 'shares', label: 'Shares' },
                    { key: 'amount', label: 'Amount' },
                    {
                        key: 'date',
                        label: 'Date',
                        render: (row) => formatDate(row.date),
                    },
                ]}
            />
        </>
    );
}
