import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { store } from '@/routes/portal/members';

export default function Page() {
    const route = store.form();

    return (
        <>
            <Head title="Add Member" />
            <PageHeader title="Add Member" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Membership #"
                                    name="membership_number"
                                    required
                                    error={errors.membership_number}
                                />
                                <FormField
                                    label="Full Name"
                                    name="full_name"
                                    required
                                    error={errors.full_name}
                                />
                                <FormField
                                    label="Phone"
                                    name="phone_number"
                                    error={errors.phone_number}
                                />
                                <FormField
                                    label="Email"
                                    name="email"
                                    type="email"
                                    error={errors.email}
                                />
                                <FormField
                                    label="Date Joined"
                                    name="date_joined"
                                    type="date"
                                    required
                                    error={errors.date_joined}
                                />
                                <FormField
                                    label="Gender"
                                    name="gender"
                                    options={[
                                        { value: 'male', label: 'Male' },
                                        { value: 'female', label: 'Female' },
                                    ]}
                                    error={errors.gender}
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
                                        href="/portal/members"
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
