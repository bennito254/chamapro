import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { update } from '@/routes/portal/members';
import PageHeader from '@/components/shared/PageHeader';
import type { Member } from '@/types/models';

type Props = {
    member: Member;
    
};

export default function Page({ member }: Props) {
    const route = update.form(member);

    return (
        <>
            <Head title="Edit Member" />
            <PageHeader title="Edit Member" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
                        <FormField label="Membership #" name="membership_number" required defaultValue={String(member.membership_number ?? '')} error={errors.membership_number} />
                        <FormField label="Full Name" name="full_name" required defaultValue={String(member.full_name ?? '')} error={errors.full_name} />
                        <FormField label="Phone" name="phone_number" defaultValue={String(member.phone_number ?? '')} error={errors.phone_number} />
                        <FormField label="Status" name="status" defaultValue={String(member.status ?? '')} error={errors.status} />
                            <div className="d-flex gap-2 mt-3">
                                <button type="submit" className="btn btn-primary" disabled={processing}>{processing ? 'Saving...' : 'Save'}</button>
                                <Link href="/portal/members" className="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </>
                    )}
                </Form>
            </div></div>
        </>
    );
}
