import { Form, Head, Link } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import PageHeader from '@/components/shared/PageHeader';
import { store } from '@/routes/portal/sms-messages';
import type { Member, SmsTemplate } from '@/types/models';

type Placeholder = {
    key: string;
    label: string;
    description: string;
};

type Props = {
    templates: SmsTemplate[];
    members: Array<Member & { phone_number?: string | null }>;
    placeholders: Placeholder[];
};

export default function Page({ templates, members, placeholders }: Props) {
    const route = store.form();
    const [templateId, setTemplateId] = useState(String(templates[0]?.id ?? ''));
    const [selectedIds, setSelectedIds] = useState<number[]>([]);
    const [previewMemberId, setPreviewMemberId] = useState(String(members[0]?.id ?? ''));
    const [previewBody, setPreviewBody] = useState('');
    const [search, setSearch] = useState('');

    const filteredMembers = useMemo(() => {
        const q = search.trim().toLowerCase();

        if (!q) {
            return members;
        }

        return members.filter(
            (member) =>
                member.full_name.toLowerCase().includes(q)
                || member.membership_number.toLowerCase().includes(q)
                || (member.phone_number ?? '').includes(q),
        );
    }, [members, search]);

    const selectableMembers = filteredMembers.filter((member) => member.phone_number);
    const allSelected = selectableMembers.length > 0 && selectableMembers.every((member) => selectedIds.includes(member.id));

    useEffect(() => {
        const loadPreview = async () => {
            if (!templateId || !previewMemberId) {
                setPreviewBody('');

                return;
            }

            const response = await fetch('/portal/sms-messages/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    sms_template_id: Number(templateId),
                    member_id: Number(previewMemberId),
                }),
            });

            if (response.ok) {
                const data = await response.json() as { body: string };
                setPreviewBody(data.body);
            }
        };

        loadPreview();
    }, [templateId, previewMemberId]);

    const toggleMember = (memberId: number, checked: boolean) => {
        setSelectedIds((prev) => (checked ? [...prev, memberId] : prev.filter((id) => id !== memberId)));
    };

    const toggleAll = (checked: boolean) => {
        if (checked) {
            setSelectedIds(selectableMembers.map((member) => member.id));

            return;
        }

        setSelectedIds([]);
    };

    if (templates.length === 0) {
        return (
            <>
                <Head title="Send SMS" />
                <PageHeader title="Send SMS" />
                <div className="alert alert-warning">
                    No active SMS templates found.{' '}
                    <Link href="/portal/sms-templates/create">Create a template</Link> before sending messages.
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Send SMS" />
            <PageHeader title="Send SMS" />
            <div className="row g-3">
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body">
                            <Form {...route}>
                                {({ errors, processing }) => (
                                    <>
                                        <div className="mb-3">
                                            <label className="form-label" htmlFor="template-picker">
                                                Template
                                                <span className="text-danger ms-1">*</span>
                                            </label>
                                            <select
                                                id="template-picker"
                                                className={`form-select ${errors.sms_template_id ? 'is-invalid' : ''}`}
                                                value={templateId}
                                                onChange={(e) => setTemplateId(e.target.value)}
                                            >
                                                {templates.map((template) => (
                                                    <option key={template.id} value={template.id}>
                                                        {template.name}
                                                    </option>
                                                ))}
                                            </select>
                                            <input type="hidden" name="sms_template_id" value={templateId} />
                                            {errors.sms_template_id && <div className="invalid-feedback d-block">{errors.sms_template_id}</div>}
                                        </div>

                                        <div className="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-2">
                                            <label className="form-label mb-0">
                                                Recipients
                                                <span className="text-danger ms-1">*</span>
                                            </label>
                                            <div className="d-flex gap-2 align-items-center">
                                                <input
                                                    type="search"
                                                    className="form-control form-control-sm"
                                                    style={{ width: 220 }}
                                                    placeholder="Search members..."
                                                    value={search}
                                                    onChange={(e) => setSearch(e.target.value)}
                                                />
                                                <span className="small text-muted">{selectedIds.length} selected</span>
                                            </div>
                                        </div>

                                        {selectedIds.map((memberId) => (
                                            <input key={memberId} type="hidden" name="member_ids[]" value={memberId} />
                                        ))}

                                        <div className="table-responsive border rounded mb-3">
                                            <table className="table table-sm mb-0 align-middle">
                                                <thead>
                                                    <tr>
                                                        <th style={{ width: 40 }}>
                                                            <input
                                                                type="checkbox"
                                                                className="form-check-input"
                                                                checked={allSelected}
                                                                disabled={selectableMembers.length === 0}
                                                                onChange={(e) => toggleAll(e.target.checked)}
                                                                aria-label="Select all members"
                                                            />
                                                        </th>
                                                        <th>Member</th>
                                                        <th>Phone</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {filteredMembers.map((member) => {
                                                        const disabled = !member.phone_number;
                                                        const checked = selectedIds.includes(member.id);

                                                        return (
                                                            <tr key={member.id} className={disabled ? 'table-light text-muted' : ''}>
                                                                <td>
                                                                    <input
                                                                        type="checkbox"
                                                                        className="form-check-input"
                                                                        value={member.id}
                                                                        checked={checked}
                                                                        disabled={disabled}
                                                                        onChange={(e) => toggleMember(member.id, e.target.checked)}
                                                                    />
                                                                </td>
                                                                <td>
                                                                    <div className="fw-medium">{member.full_name}</div>
                                                                    <div className="small text-muted">{member.membership_number}</div>
                                                                </td>
                                                                <td>{member.phone_number ?? 'No phone'}</td>
                                                            </tr>
                                                        );
                                                    })}
                                                </tbody>
                                            </table>
                                        </div>
                                        {errors.member_ids && <div className="invalid-feedback d-block mb-3">{errors.member_ids}</div>}

                                        <div className="d-flex gap-2 mt-3">
                                            <button type="submit" className="btn btn-primary" disabled={processing || selectedIds.length === 0}>
                                                {processing ? 'Sending...' : `Send to ${selectedIds.length} member(s)`}
                                            </button>
                                            <Link href="/portal/sms-messages" className="btn btn-outline-secondary">
                                                Cancel
                                            </Link>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                </div>

                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm mb-3">
                        <div className="card-header bg-transparent border-0 pt-3">
                            <h6 className="mb-0">Preview</h6>
                        </div>
                        <div className="card-body pt-0">
                            <select
                                className="form-select form-select-sm mb-3"
                                value={previewMemberId}
                                onChange={(e) => setPreviewMemberId(e.target.value)}
                            >
                                {members.map((member) => (
                                    <option key={member.id} value={member.id}>
                                        {member.full_name}
                                    </option>
                                ))}
                            </select>
                            <div className="p-3 rounded bg-light border small" style={{ minHeight: 120, whiteSpace: 'pre-wrap' }}>
                                {previewBody || 'Select a template and member to preview the message.'}
                            </div>
                        </div>
                    </div>

                    <div className="card border-0 shadow-sm">
                        <div className="card-header bg-transparent border-0 pt-3">
                            <h6 className="mb-0">Placeholders</h6>
                        </div>
                        <div className="card-body pt-0">
                            <ul className="list-unstyled mb-0 small">
                                {placeholders.map((placeholder) => (
                                    <li key={placeholder.key} className="mb-2">
                                        <code>{`{${placeholder.key}}`}</code>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
