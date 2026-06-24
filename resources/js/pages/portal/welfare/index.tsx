import { Form, Head } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import FormField from '@/components/shared/FormField';
import { formatDate } from '@/lib/format';
import PageHeader from '@/components/shared/PageHeader';
import type { MemberOption, WelfareContribution, WelfareDisbursement } from '@/types/models';
import type { Paginated } from '@/types/pagination';
type Props = { contributions: Paginated<WelfareContribution>; disbursements: Paginated<WelfareDisbursement>; members: MemberOption[] };
export default function WelfareIndex({ contributions, disbursements, members }: Props) {
    const memberOpts = members.map((m) => ({ value: String(m.id), label: m.full_name }));
    return (<><Head title="Welfare" /><PageHeader title="Welfare Fund" />
    <div className="row g-4">
        <div className="col-lg-6"><div className="card border-0 shadow-sm"><div className="card-body">
            <h6 className="fw-semibold">Contributions</h6>
            <Form action="/portal/welfare/contributions" method="post" className="row g-2 mb-3">{({ errors }) => (<>
                <div className="col-6"><FormField label="Member" name="member_id" required options={memberOpts} error={errors.member_id} /></div>
                <div className="col-4"><FormField label="Amount" name="amount" type="number" required error={errors.amount} /></div>
                <div className="col-2 d-flex align-items-end"><button type="submit" className="btn btn-sm btn-primary w-100">Add</button></div>
            </>)}</Form>
            <DataTable searchable={false} data={contributions} columns={[{ key: 'amount', label: 'Amount' }, { key: 'date', label: 'Date', render: (row) => formatDate(row.date) }]} />
        </div></div></div>
        <div className="col-lg-6"><div className="card border-0 shadow-sm"><div className="card-body">
            <h6 className="fw-semibold">Disbursements</h6>
            <Form action="/portal/welfare/disbursements" method="post" className="row g-2 mb-3">{({ errors }) => (<>
                <div className="col-6"><FormField label="Member" name="member_id" required options={memberOpts} error={errors.member_id} /></div>
                <div className="col-4"><FormField label="Amount" name="amount" type="number" required error={errors.amount} /></div>
                <div className="col-2 d-flex align-items-end"><button type="submit" className="btn btn-sm btn-primary w-100">Add</button></div>
            </>)}</Form>
            <DataTable searchable={false} data={disbursements} columns={[{ key: 'amount', label: 'Amount' }, { key: 'date', label: 'Date', render: (row) => formatDate(row.date) }]} />
        </div></div></div>
    </div></>);
}
