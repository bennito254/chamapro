import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import MeetingDateSelect from '@/components/shared/MeetingDateSelect';
import type { MeetingOption } from '@/components/shared/MeetingDateSelect';
import PageHeader from '@/components/shared/PageHeader';
import { store } from '@/routes/portal/contributions';

type Props = {
    members: Array<{
        id: number;
        full_name: string;
        membership_number: string;
    }>;
    types: Array<{ id: number; name: string }>;
    channels: Array<{ id: number; name: string }>;
    meetings: MeetingOption[];
    defaultMeetingId?: number | null;
};

export default function Page({
    members,
    types,
    channels,
    meetings,
    defaultMeetingId,
}: Props) {
    const route = store.form();

    return (
        <>
            <Head title="Record Contribution" />
            <PageHeader title="Record Contribution" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Member"
                                    name="member_id"
                                    required
                                    options={members.map((m) => ({
                                        value: String(m.id),
                                        label: `${m.full_name} (${m.membership_number})`,
                                    }))}
                                    error={errors.member_id}
                                />
                                <FormField
                                    label="Type"
                                    name="contribution_type_id"
                                    required
                                    options={types.map((t) => ({
                                        value: String(t.id),
                                        label: t.name,
                                    }))}
                                    error={errors.contribution_type_id}
                                />
                                <FormField
                                    label="Channel"
                                    name="contribution_channel_id"
                                    options={channels.map((c) => ({
                                        value: String(c.id),
                                        label: c.name,
                                    }))}
                                    error={errors.contribution_channel_id}
                                />
                                <FormField
                                    label="Amount"
                                    name="amount"
                                    type="number"
                                    required
                                    error={errors.amount}
                                />
                                <MeetingDateSelect
                                    meetings={meetings}
                                    defaultMeetingId={defaultMeetingId}
                                    error={errors.date}
                                    className="mb-3"
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
                                        href="/portal/contributions"
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
