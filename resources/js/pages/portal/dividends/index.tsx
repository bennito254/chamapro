import { Form, Head } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import type { DividendRun } from '@/types/models';
import type { Paginated } from '@/types/pagination';
type Props = { runs: Paginated<DividendRun>; formula: string };
export default function DividendsIndex({ runs, formula }: Props) {
    return (<><Head title="Dividends" /><PageHeader title="Dividends" description={`Formula: ${formula}`} />
    <div className="card border-0 shadow-sm mb-4"><div className="card-body">
        <Form action="/portal/dividends" method="post" className="row g-3">{({ errors, processing }) => (<>
            <div className="col-md-3"><FormField label="Year" name="year" type="number" required error={errors.year} /></div>
            <div className="col-md-3"><FormField label="Total Amount" name="total_amount" type="number" required error={errors.total_amount} /></div>
            <div className="col-md-3 d-flex align-items-end"><button type="submit" className="btn btn-primary" disabled={processing}>Run Dividends</button></div>
        </>)}</Form>
    </div></div>
    <DataTable data={runs} columns={[{ key: 'year', label: 'Year' }, { key: 'total_amount', label: 'Total' }, { key: 'status', label: 'Status' }]} /></>);
}
