import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import MeetingDateSelect, {
    resolveDefaultMeetingDate,
    type MeetingOption,
} from '@/components/shared/MeetingDateSelect';
import PageHeader from '@/components/shared/PageHeader';
import { toastError } from '@/lib/toast';
import type { ContributionChannel, ContributionType } from '@/types/models';

type MemberRow = {
    id: number;
    full_name: string;
    membership_number: string;
};

type EntryRow = {
    member_id: number;
    full_name: string;
    membership_number: string;
    included: boolean;
    amount: string;
    transaction_reference: string;
    notes: string;
};

type MemberContributionSummary = {
    contributed: number;
    required: number | null;
    met: boolean;
    remaining: number | null;
};

type Props = {
    members: MemberRow[];
    types: ContributionType[];
    channels: ContributionChannel[];
    meetings: MeetingOption[];
    defaultMeetingId?: number | null;
    selectedDate?: string;
    memberContributionTotals: Record<string, Record<string, MemberContributionSummary>>;
};

export default function BulkContributions({
    members,
    types,
    channels,
    meetings,
    defaultMeetingId,
    selectedDate,
    memberContributionTotals,
}: Props) {
    const defaultMeeting = useMemo(
        () => resolveDefaultMeetingDate(meetings, defaultMeetingId),
        [meetings, defaultMeetingId],
    );
    const defaultType = types[0];
    const defaultChannel = channels[0];

    const [contributionTypeId, setContributionTypeId] = useState(String(defaultType?.id ?? ''));
    const [contributionChannelId, setContributionChannelId] = useState(String(defaultChannel?.id ?? ''));
    const [date, setDate] = useState(selectedDate ?? defaultMeeting.date);
    const [defaultAmount, setDefaultAmount] = useState(
        defaultType?.default_amount ? String(defaultType.default_amount) : '',
    );
    const [processing, setProcessing] = useState(false);
    const [search, setSearch] = useState('');
    const selectAllRef = useRef<HTMLInputElement>(null);

    const [rows, setRows] = useState<EntryRow[]>(() =>
        members.map((m) => ({
            member_id: m.id,
            full_name: m.full_name,
            membership_number: m.membership_number,
            included: false,
            amount: defaultType?.default_amount ? String(defaultType.default_amount) : '',
            transaction_reference: '',
            notes: '',
        })),
    );

    const selectedType = types.find((t) => String(t.id) === contributionTypeId);

    useEffect(() => {
        if (selectedDate) {
            setDate(selectedDate);
        }
    }, [selectedDate]);

    const getMemberSummary = (memberId: number): MemberContributionSummary | undefined =>
        memberContributionTotals[String(memberId)]?.[contributionTypeId];

    const isMemberComplete = (memberId: number): boolean => getMemberSummary(memberId)?.met ?? false;

    const suggestedAmountForMember = (memberId: number, amount: string = defaultAmount): string => {
        const summary = getMemberSummary(memberId);

        if (summary?.remaining != null && summary.remaining > 0) {
            return String(summary.remaining);
        }

        return amount;
    };

    const syncRowAmounts = (amount: string) => {
        setRows((prev) =>
            prev.map((r) => {
                if (isMemberComplete(r.member_id)) {
                    return r;
                }

                return { ...r, amount: suggestedAmountForMember(r.member_id, amount) };
            }),
        );
    };

    const handleDateChange = (newDate: string) => {
        setDate(newDate);
        router.get(
            '/portal/contributions-bulk',
            { date: newDate },
            { preserveState: true, preserveScroll: true, only: ['memberContributionTotals', 'selectedDate'] },
        );
    };

    const filteredRows = useMemo(() => {
        const q = search.trim().toLowerCase();
        if (!q) {
            return rows;
        }

        return rows.filter(
            (r) =>
                r.full_name.toLowerCase().includes(q) ||
                r.membership_number.toLowerCase().includes(q),
        );
    }, [rows, search]);

    const includedCount = rows.filter((r) => r.included && parseFloat(r.amount) > 0).length;
    const totalAmount = rows
        .filter((r) => r.included)
        .reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);

    const selectableMemberIds = useMemo(
        () => rows.filter((r) => !isMemberComplete(r.member_id)).map((r) => r.member_id),
        [rows, memberContributionTotals, contributionTypeId],
    );

    const selectedSelectableCount = rows.filter(
        (r) => r.included && !isMemberComplete(r.member_id),
    ).length;

    const allMembersSelected =
        selectableMemberIds.length > 0 && selectedSelectableCount === selectableMemberIds.length;

    const someMembersSelected =
        selectedSelectableCount > 0 && selectedSelectableCount < selectableMemberIds.length;

    useEffect(() => {
        if (selectAllRef.current) {
            selectAllRef.current.indeterminate = someMembersSelected;
        }
    }, [someMembersSelected]);

    const toggleAllMembers = (checked: boolean) => {
        if (checked) {
            setRows((prev) =>
                prev.map((r) =>
                    !isMemberComplete(r.member_id)
                        ? {
                            ...r,
                            included: true,
                            amount: r.amount || suggestedAmountForMember(r.member_id, defaultAmount),
                        }
                        : r,
                ),
            );

            return;
        }

        clearAll();
    };

    const applyDefaultAmount = () => {
        if (!defaultAmount) {
            return;
        }

        setRows((prev) =>
            prev.map((r) =>
                r.included && !isMemberComplete(r.member_id)
                    ? { ...r, amount: suggestedAmountForMember(r.member_id, defaultAmount) }
                    : r,
            ),
        );
    };

    const selectAllVisible = () => {
        const visibleIds = new Set(filteredRows.map((r) => r.member_id));
        setRows((prev) =>
            prev.map((r) =>
                visibleIds.has(r.member_id) && !isMemberComplete(r.member_id)
                    ? {
                        ...r,
                        included: true,
                        amount: r.amount || suggestedAmountForMember(r.member_id, defaultAmount),
                    }
                    : r,
            ),
        );
    };

    const clearAll = () => {
        setRows((prev) => prev.map((r) => ({ ...r, included: false })));
    };

    const updateRow = (memberId: number, patch: Partial<EntryRow>) => {
        if (isMemberComplete(memberId)) {
            return;
        }

        setRows((prev) =>
            prev.map((r) => (r.member_id === memberId ? { ...r, ...patch } : r)),
        );
    };

    const handleTypeChange = (typeId: string) => {
        setContributionTypeId(typeId);
        const type = types.find((t) => String(t.id) === typeId);
        const amt = type?.default_amount ? String(type.default_amount) : '';
        setDefaultAmount(amt);
        setRows((prev) =>
            prev.map((r) => {
                const summary = memberContributionTotals[String(r.member_id)]?.[typeId];

                if (summary?.met) {
                    return { ...r, included: false };
                }

                const suggested = summary?.remaining != null && summary.remaining > 0
                    ? String(summary.remaining)
                    : amt;

                return { ...r, amount: suggested };
            }),
        );
    };

    const handleDefaultAmountChange = (value: string) => {
        setDefaultAmount(value);
        syncRowAmounts(value);
    };

    const submit = () => {
        const entries = rows
            .filter((r) => r.included && parseFloat(r.amount) > 0 && !isMemberComplete(r.member_id))
            .map((r) => ({
                member_id: r.member_id,
                amount: parseFloat(r.amount),
                transaction_reference: r.transaction_reference || null,
                notes: r.notes || null,
            }));

        if (entries.length === 0) {
            toastError('Select at least one member with a valid amount.');
            return;
        }

        setProcessing(true);
        router.post('/portal/contributions-bulk', {
            contribution_type_id: parseInt(contributionTypeId, 10),
            contribution_channel_id: parseInt(contributionChannelId, 10),
            date,
            default_amount: defaultAmount ? parseFloat(defaultAmount) : null,
            entries,
        }, {
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <>
            <Head title="Bulk Contributions" />
            <PageHeader
                title="Bulk Contribution Entry"
                description="Record contributions for multiple members in one session — ideal for meeting collections."
                actions={
                    <button type="button" className="btn btn-outline-secondary btn-sm" onClick={() => router.visit('/portal/contributions')}>
                        <i className="bi bi-arrow-left me-1" />
                        Back to list
                    </button>
                }
            />

            <div className="card cp-panel border-0 mb-4">
                <div className="card-body">
                    <div className="row g-3">
                        <div className="col-md-3">
                            <label className="form-label small fw-semibold text-muted">Contribution type</label>
                            <select className="form-select form-select-sm" value={contributionTypeId} onChange={(e) => handleTypeChange(e.target.value)}>
                                {types.map((t) => (
                                    <option key={t.id} value={t.id}>{t.name}</option>
                                ))}
                            </select>
                        </div>
                        <div className="col-md-3">
                            <label className="form-label small fw-semibold text-muted">Payment channel</label>
                            <select className="form-select form-select-sm" value={contributionChannelId} onChange={(e) => setContributionChannelId(e.target.value)}>
                                {channels.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                        <div className="col-md-4">
                            <MeetingDateSelect
                                meetings={meetings}
                                defaultMeetingId={defaultMeetingId}
                                onDateChange={handleDateChange}
                                selectClassName="form-select form-select-sm"
                                label="Meeting"
                            />
                        </div>
                        <div className="col-md-2">
                            <label className="form-label small fw-semibold text-muted">Default amount</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                className="form-control form-control-sm"
                                value={defaultAmount}
                                onChange={(e) => handleDefaultAmountChange(e.target.value)}
                                placeholder={selectedType?.default_amount ? String(selectedType.default_amount) : '0.00'}
                            />
                        </div>
                        <div className="col-md-2 d-flex align-items-end">
                            <button type="button" className="btn btn-outline-primary btn-sm w-100" onClick={applyDefaultAmount}>
                                Apply to selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div className="card cp-panel border-0">
                <div className="card-header bg-transparent border-0 pt-3 px-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div className="d-flex flex-wrap gap-2 align-items-center">
                        <input
                            type="search"
                            className="form-control form-control-sm"
                            style={{ width: 220 }}
                            placeholder="Search members..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                        <button type="button" className="btn btn-outline-primary btn-sm" onClick={selectAllVisible}>
                            Select visible
                        </button>
                        <button type="button" className="btn btn-outline-secondary btn-sm" onClick={clearAll}>
                            Clear all
                        </button>
                    </div>
                    <div className="small text-muted">
                        <strong className="text-primary">{includedCount}</strong> selected · Total{' '}
                        <strong className="text-primary">{totalAmount.toLocaleString('en-KE', { minimumFractionDigits: 2 })}</strong>
                    </div>
                </div>
                <div className="table-responsive">
                    <table className="table table-sm table-hover cp-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style={{ width: 40 }}>
                                    <input
                                        ref={selectAllRef}
                                        type="checkbox"
                                        className="form-check-input"
                                        checked={allMembersSelected}
                                        disabled={selectableMemberIds.length === 0}
                                        onChange={(e) => toggleAllMembers(e.target.checked)}
                                        title="Select all members"
                                        aria-label="Select all members"
                                    />
                                </th>
                                <th>Member</th>
                                <th style={{ width: 130 }}>Amount</th>
                                <th style={{ width: 150 }}>Reference</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredRows.map((row) => {
                                const summary = getMemberSummary(row.member_id);
                                const complete = summary?.met ?? false;

                                return (
                                <tr key={row.member_id} className={complete ? 'table-light text-muted' : row.included ? 'table-active' : ''}>
                                    <td>
                                        <input
                                            type="checkbox"
                                            className="form-check-input"
                                            checked={row.included}
                                            disabled={complete}
                                            onChange={(e) => updateRow(row.member_id, {
                                                included: e.target.checked,
                                                amount: e.target.checked
                                                    ? (row.amount || suggestedAmountForMember(row.member_id, defaultAmount))
                                                    : row.amount,
                                            })}
                                        />
                                    </td>
                                    <td>
                                        <div className="d-flex align-items-center gap-2">
                                            <div>
                                                <div className="fw-medium">{row.full_name}</div>
                                                <div className="text-muted small">{row.membership_number}</div>
                                            </div>
                                            {complete && (
                                                <span className="badge bg-success-subtle text-success border border-success-subtle">
                                                    Paid
                                                </span>
                                            )}
                                            {!complete && summary && summary.contributed > 0 && summary.remaining != null && (
                                                <span className="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                    {summary.remaining} due
                                                </span>
                                            )}
                                        </div>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            className="form-control form-control-sm"
                                            value={row.amount}
                                            disabled={!row.included || complete}
                                            onChange={(e) => updateRow(row.member_id, { amount: e.target.value })}
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            className="form-control form-control-sm"
                                            value={row.transaction_reference}
                                            disabled={!row.included || complete}
                                            onChange={(e) => updateRow(row.member_id, { transaction_reference: e.target.value })}
                                            placeholder="Optional"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            className="form-control form-control-sm"
                                            value={row.notes}
                                            disabled={!row.included || complete}
                                            onChange={(e) => updateRow(row.member_id, { notes: e.target.value })}
                                            placeholder="Optional"
                                        />
                                    </td>
                                </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                <div className="card-footer bg-transparent border-0 d-flex justify-content-end gap-2 py-3">
                    <button type="button" className="btn btn-outline-secondary" onClick={() => router.visit('/portal/contributions')}>
                        Cancel
                    </button>
                    <button type="button" className="btn btn-primary" disabled={processing || includedCount === 0} onClick={submit}>
                        {processing ? 'Saving...' : `Record ${includedCount} contribution(s)`}
                    </button>
                </div>
            </div>
        </>
    );
}
