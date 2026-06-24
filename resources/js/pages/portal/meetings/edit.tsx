import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { update } from '@/routes/portal/meetings';
import PageHeader from '@/components/shared/PageHeader';
import type { Meeting } from '@/types/models';

const meetingStatusOptions = [
    { value: 'scheduled', label: 'Scheduled' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

type Props = {
    meeting: Meeting;
};

export default function Page({ meeting }: Props) {
    const route = update.form(meeting);

    return (
        <>
            <Head title="Edit Meeting" />
            <PageHeader title="Edit Meeting" />
            <div className="card border-0 shadow-sm">
                <div className="card-body">
                    <Form {...route}>
                        {({ errors, processing }) => (
                            <>
                                <FormField
                                    label="Title"
                                    name="title"
                                    required
                                    defaultValue={String(meeting.title ?? '')}
                                    error={errors.title}
                                />
                                <FormField
                                    label="Date"
                                    name="date"
                                    type="date"
                                    required
                                    defaultValue={String(meeting.date ?? '').slice(0, 10)}
                                    error={errors.date}
                                />
                                <FormField
                                    label="Location"
                                    name="venue"
                                    defaultValue={String(meeting.venue ?? '')}
                                    error={errors.venue}
                                />
                                <FormField
                                    label="Agenda"
                                    name="agenda"
                                    type="textarea"
                                    defaultValue={String(meeting.agenda ?? '')}
                                    error={errors.agenda}
                                />
                                <FormField
                                    label="Status"
                                    name="status"
                                    required
                                    defaultValue={String(meeting.status ?? 'scheduled')}
                                    options={meetingStatusOptions}
                                    error={errors.status}
                                />
                                <div className="d-flex gap-2 mt-3">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save'}
                                    </button>
                                    <Link href={`/portal/meetings/${meeting.sqid}`} className="btn btn-outline-secondary">
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
