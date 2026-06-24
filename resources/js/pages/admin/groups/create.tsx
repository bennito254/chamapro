import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { index, store } from '@/routes/admin/groups';
import type { SubscriptionPlan } from '@/types/models';

type Props = {
    plans: Array<Pick<SubscriptionPlan, 'id' | 'name'>>;
};

export default function Page({ plans }: Props) {
    return (
        <>
            <Head title="Create Group" />
            <PageHeader
                title="Onboard new group"
                description="Create a chama tenant and start a trial subscription."
                breadcrumbs={[
                    { label: 'Groups', href: index.url() },
                    { label: 'Create' },
                ]}
            />
            <div className="card border-0 shadow-sm">
                <div className="card-body p-4">
                    <Form {...store.form()}>
                        {({ errors, processing }) => (
                            <>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <FormField
                                            label="Group name"
                                            name="name"
                                            required
                                            error={errors.name}
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <FormField
                                            label="Registration number"
                                            name="registration_number"
                                            error={errors.registration_number}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Phone"
                                            name="phone"
                                            error={errors.phone}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="Email"
                                            name="email"
                                            type="email"
                                            error={errors.email}
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <FormField
                                            label="County"
                                            name="county"
                                            error={errors.county}
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <FormField
                                            label="Subscription plan"
                                            name="subscription_plan_id"
                                            required
                                            options={plans.map((plan) => ({
                                                value: String(plan.id),
                                                label: plan.name,
                                            }))}
                                            error={errors.subscription_plan_id}
                                        />
                                    </div>
                                </div>
                                <div className="d-flex mt-4 gap-2">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Creating...'
                                            : 'Create group'}
                                    </button>
                                    <Link
                                        href={index()}
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
