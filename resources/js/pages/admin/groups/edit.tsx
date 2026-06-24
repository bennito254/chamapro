import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { index, show, update } from '@/routes/admin/groups';
import type { Group } from '@/types/models';

type Props = {
    group: Group;
};

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'suspended', label: 'Suspended' },
];

export default function Page({ group }: Props) {
    return (
        <>
            <Head title={`Edit ${group.name}`} />
            <PageHeader
                title={`Edit ${group.name}`}
                breadcrumbs={[
                    { label: 'Groups', href: index.url() },
                    { label: group.name, href: show.url(group) },
                    { label: 'Edit' },
                ]}
            />
            <div className="card border-0 shadow-sm">
                <div className="card-body p-4">
                    <Form {...update.form(group)}>
                        {({ errors, processing }) => (
                            <>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <FormField
                                            label="Name"
                                            name="name"
                                            required
                                            defaultValue={group.name}
                                            error={errors.name}
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <FormField
                                            label="Registration number"
                                            name="registration_number"
                                            defaultValue={group.registration_number ?? ''}
                                            error={errors.registration_number}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Phone"
                                            name="phone"
                                            defaultValue={group.phone ?? ''}
                                            error={errors.phone}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Email"
                                            name="email"
                                            type="email"
                                            defaultValue={group.email ?? ''}
                                            error={errors.email}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="County"
                                            name="county"
                                            defaultValue={group.county ?? ''}
                                            error={errors.county}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Status"
                                            name="status"
                                            options={statusOptions}
                                            defaultValue={group.status}
                                            error={errors.status}
                                        />
                                    </div>
                                </div>
                                <div className="d-flex gap-2 mt-4">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save changes'}
                                    </button>
                                    <Link href={show.url(group)} className="btn btn-outline-secondary">
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
