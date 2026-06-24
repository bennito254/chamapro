import { Form, Head } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import type { Member } from '@/types/models';
type Props = {
    member: Member;
    statement: Record<string, unknown>;
    filters: { from?: string; to?: string };
};
export default function MemberStatement({ member, statement, filters }: Props) {
    return (
        <>
            <Head title="Member Statement" />
            <PageHeader title={`Statement: ${member.full_name}`} />
            <Form
                method="get"
                action={`/portal/members/${member.sqid}/statement`}
                className="row g-3 mb-4"
            >
                <div className="col-md-4">
                    <FormField
                        label="From"
                        name="from"
                        type="date"
                        defaultValue={filters.from}
                    />
                </div>
                <div className="col-md-4">
                    <FormField
                        label="To"
                        name="to"
                        type="date"
                        defaultValue={filters.to}
                    />
                </div>
                <div className="col-md-4 d-flex align-items-end">
                    <button type="submit" className="btn btn-primary">
                        Filter
                    </button>
                </div>
            </Form>
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <pre className="small mb-0">
                        {JSON.stringify(statement, null, 2)}
                    </pre>
                </div>
            </div>
        </>
    );
}
