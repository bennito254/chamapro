import FormField from '@/components/shared/FormField';
import { formatDate } from '@/lib/format';
import { useMemo, useState } from 'react';

export type MeetingOption = {
    id: number;
    title: string;
    date: string;
};

type Props = {
    meetings: MeetingOption[];
    defaultMeetingId?: number | null;
    name?: string;
    error?: string;
    required?: boolean;
    onDateChange?: (date: string) => void;
    className?: string;
    selectClassName?: string;
    label?: string;
};

function meetingLabel(meeting: MeetingOption): string {
    return `${meeting.title} (${formatDate(meeting.date)})`;
}

export function resolveDefaultMeetingDate(
    meetings: MeetingOption[],
    defaultMeetingId?: number | null,
): { meetingId: string; date: string } {
    const today = new Date().toISOString().slice(0, 10);

    if (meetings.length === 0) {
        return { meetingId: 'custom', date: today };
    }

    const defaultMeeting =
        meetings.find((meeting) => meeting.id === defaultMeetingId) ?? meetings[0];

    return {
        meetingId: String(defaultMeeting.id),
        date: defaultMeeting.date.slice(0, 10),
    };
}

export default function MeetingDateSelect({
    meetings,
    defaultMeetingId,
    name = 'date',
    error,
    required = true,
    onDateChange,
    className,
    selectClassName = 'form-select',
    label = 'Meeting',
}: Props) {
    const defaults = useMemo(
        () => resolveDefaultMeetingDate(meetings, defaultMeetingId),
        [meetings, defaultMeetingId],
    );

    const [meetingId, setMeetingId] = useState(defaults.meetingId);
    const [date, setDate] = useState(defaults.date);

    const handleMeetingChange = (value: string) => {
        setMeetingId(value);

        if (value === 'custom') {
            return;
        }

        const meeting = meetings.find((item) => String(item.id) === value);
        if (!meeting) {
            return;
        }

        const meetingDate = meeting.date.slice(0, 10);
        setDate(meetingDate);
        onDateChange?.(meetingDate);
    };

    const handleDateChange = (value: string) => {
        setDate(value);
        onDateChange?.(value);
    };

    if (meetings.length === 0) {
        return (
            <FormField
                label="Contribution date"
                name={name}
                type="date"
                required={required}
                defaultValue={date}
                error={error}
            />
        );
    }

    return (
        <div className={className}>
            <label className="form-label">
                {label}
                {required && <span className="text-danger ms-1">*</span>}
            </label>
            <select
                className={`${selectClassName} ${error ? 'is-invalid' : ''}`}
                value={meetingId}
                onChange={(e) => handleMeetingChange(e.target.value)}
            >
                {meetings.map((meeting) => (
                    <option key={meeting.id} value={meeting.id}>
                        {meetingLabel(meeting)}
                    </option>
                ))}
                <option value="custom">Other date...</option>
            </select>
            {meetingId === 'custom' ? (
                <div className="mt-2">
                    <label htmlFor="custom-meeting-date" className="form-label">
                        Contribution date
                        {required && <span className="text-danger ms-1">*</span>}
                    </label>
                    <input
                        id="custom-meeting-date"
                        name={name}
                        type="date"
                        className={`form-control ${error ? 'is-invalid' : ''}`}
                        value={date}
                        required={required}
                        onChange={(e) => handleDateChange(e.target.value)}
                    />
                    {error && <div className="invalid-feedback d-block">{error}</div>}
                </div>
            ) : (
                <input type="hidden" name={name} value={date} />
            )}
            {meetingId !== 'custom' && (
                <div className="form-text">Contributions will be recorded for {formatDate(date)}.</div>
            )}
            {error && meetingId !== 'custom' && (
                <div className="invalid-feedback d-block">{error}</div>
            )}
        </div>
    );
}
