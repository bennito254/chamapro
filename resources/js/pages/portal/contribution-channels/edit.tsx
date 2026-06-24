import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { statusOptions } from '@/lib/form-options';
import { update } from '@/routes/portal/contribution-channels';
import type { ContributionChannel } from '@/types/models';

type Props = {
    channel: ContributionChannel;
};

export default function Page({ channel }: Props) {
    const route = update.form(channel);

    return (
        <>
            <Head title="Edit Payment Channel" />
            <PageHeader title="Edit Payment Channel" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Name"
                                    name="name"
                                    required
                                    defaultValue={String(channel.name ?? '')}
                                    error={errors.name}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue={String(
                                        channel.status ?? 'active',
                                    )}
                                    options={statusOptions}
                                    error={errors.status}
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
                                        href="/portal/contribution-channels"
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
