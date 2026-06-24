#!/usr/bin/env node
/**
 * Generates all ChamaPro Inertia React pages.
 */
import fs from 'fs';
import path from 'path';

const pagesDir = path.join(path.resolve(import.meta.dirname, '..'), 'resources/js/pages');
let count = 0;

function w(rel, content) {
    const full = path.join(pagesDir, rel);
    fs.mkdirSync(path.dirname(full), { recursive: true });
    fs.writeFileSync(full, content.trimStart() + '\n');
    count++;
}

function idx(title, prop, type, cols, createHref, viewBase) {
    const colStr = cols.map((c) => `                    { key: '${c.k}', label: '${c.l}'${c.r ? `, render: ${c.r}` : ''} }`).join(',\n');
    const viewCol = viewBase
        ? `,\n                    { key: 'actions', label: '', render: (row) => <Link href={\`${viewBase}/\${row.id}\`} className="btn btn-sm btn-outline-primary">View</Link> }`
        : '';
    return `import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { ${type} } from '@/types/models';

type Props = { ${prop}: Paginated<${type}> };

export default function Page({ ${prop} }: Props) {
    return (
        <>
            <Head title="${title}" />
            <PageHeader title="${title}" actions={${createHref ? `<Link href="${createHref}" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />Add</Link>` : 'undefined'}} />
            <DataTable columns={[
${colStr}${viewCol}
            ]} data={${prop}} />
        </>
    );
}`;
}

function crudForm(title, storeAction, updateAction, entity, fields, cancelHref, extraProps = '') {
    const fieldLines = fields.map((f) => {
        const parts = [`label="${f.l}"`, `name="${f.n}"`];
        if (f.t) parts.push(`type="${f.t}"`);
        if (f.req) parts.push('required');
        if (f.entity) parts.push(`defaultValue={String(${entity}?.${f.n} ?? '')}`);
        if (f.opts) parts.push(`options={${f.opts}}`);
        return `                        <FormField ${parts.join(' ')} error={errors.${f.n}} />`;
    }).join('\n');

    return `import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { ${updateAction ? 'update' : 'store'} } from '${storeAction}';
import type { ${fields[0]?.typeImport ?? 'Record<string, unknown>'} } from '@/types/models';

type Props = {
    ${entity ? `${entity.replace('?', '')}?: ${fields[0]?.typeImport ?? 'Record<string, unknown>'};` : ''}
    ${extraProps}
};

export default function Page({ ${[entity?.replace('?', ''), ...extraProps.split(';').map((s) => s.split(':')[0]?.trim()).filter(Boolean)].filter(Boolean).join(', ')} }: Props) {
    const isEdit = Boolean(${entity ? entity.replace('?', '') : 'undefined'});
    const route = isEdit ? update.form(${entity ? entity.replace('?', '') : 'undefined'}) : store.form();

    return (
        <>
            <Head title="${title}" />
            <PageHeader title="${title}" />
            <div className="card border-0 shadow-sm"><div className="card-body">
                <Form {...route}>
                    {({ errors, processing }) => (
                        <>
${fieldLines}
                            <div className="d-flex gap-2 mt-3">
                                <button type="submit" className="btn btn-primary" disabled={processing}>{processing ? 'Saving...' : 'Save'}</button>
                                <Link href="${cancelHref}" className="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </>
                    )}
                </Form>
            </div></div>
        </>
    );
}`;
}

function show(title, entity, entityType, fields, editHref, deleteHref, backHref) {
    const fieldArr = fields.map((f) => `{ label: '${f.l}', value: ${f.v}${f.fmt ? `, format: '${f.fmt}'` : ''} }`).join(',\n        ');
    return `import { Head } from '@inertiajs/react';
import DetailCard from '@/components/shared/DetailCard';
import PageHeader from '@/components/shared/PageHeader';
import type { ${entityType} } from '@/types/models';

type Props = { ${entity}: ${entityType} };

export default function Page({ ${entity} }: Props) {
    return (
        <>
            <Head title="${title}" />
            <PageHeader title="${title}" />
            <DetailCard title="Details" editHref="${editHref}" deleteHref="${deleteHref}" backHref="${backHref}" fields={[
        ${fieldArr}
            ]} />
        </>
    );
}`;
}

// === ADMIN DASHBOARD ===
w('admin/dashboard.tsx', `import { Head } from '@inertiajs/react';
import Chart from 'react-apexcharts';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import StatCard from '@/components/shared/StatCard';
import type { Group } from '@/types/models';

type Stats = { groups_total: number; groups_active: number; groups_suspended: number; subscriptions_active: number; subscriptions_trial: number; subscriptions_expired: number; open_tickets: number };
type Props = { stats: Stats; recentGroups: Group[] };

export default function AdminDashboard({ stats, recentGroups }: Props) {
    return (
        <>
            <Head title="Admin Dashboard" />
            <PageHeader title="Dashboard" description="Platform overview" />
            <div className="row g-3 mb-4">
                <div className="col-md-3"><StatCard title="Groups" value={stats.groups_total} icon="people" /></div>
                <div className="col-md-3"><StatCard title="Active" value={stats.groups_active} icon="check-circle" color="success" /></div>
                <div className="col-md-3"><StatCard title="Subscriptions" value={stats.subscriptions_active} icon="credit-card" color="info" /></div>
                <div className="col-md-3"><StatCard title="Open Tickets" value={stats.open_tickets} icon="headset" color="warning" /></div>
            </div>
            <div className="row g-4">
                <div className="col-lg-4"><div className="card border-0 shadow-sm"><div className="card-body">
                    <h6 className="fw-semibold mb-3">Subscriptions</h6>
                    <Chart type="donut" height={280} series={[stats.subscriptions_active, stats.subscriptions_trial, stats.subscriptions_expired]}
                        options={{ labels: ['Active', 'Trial', 'Expired'], colors: ['#059669', '#f59e0b', '#dc3545'], legend: { position: 'bottom' } }} />
                </div></div></div>
                <div className="col-lg-8">
                    <DataTable searchable={false} data={recentGroups} columns={[
                        { key: 'name', label: 'Name' },
                        { key: 'status', label: 'Status', render: (r) => <span className="badge bg-secondary">{r.status}</span> },
                        { key: 'county', label: 'County' },
                    ]} />
                </div>
            </div>
        </>
    );
}`);

// === PORTAL DASHBOARD ===
w('portal/dashboard.tsx', `import { Head } from '@inertiajs/react';
import Chart from 'react-apexcharts';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import StatCard from '@/components/shared/StatCard';
import { formatCurrency } from '@/lib/format';
import type { Contribution, Group } from '@/types/models';

type Stats = { members_total: number; members_active: number; contributions_month: number; loans_active: number; fines_unpaid: number };
type Props = { group: Group; stats: Stats; recentContributions: Contribution[] };

export default function PortalDashboard({ group, stats, recentContributions }: Props) {
    return (
        <>
            <Head title="Dashboard" />
            <PageHeader title={group.name} description="Chama overview" />
            <div className="row g-3 mb-4">
                <div className="col-md-3"><StatCard title="Members" value={stats.members_active} subtitle={\`\${stats.members_total} total\`} icon="people" /></div>
                <div className="col-md-3"><StatCard title="Contributions" value={formatCurrency(stats.contributions_month)} icon="cash-stack" color="success" /></div>
                <div className="col-md-3"><StatCard title="Active Loans" value={stats.loans_active} icon="currency-exchange" color="info" /></div>
                <div className="col-md-3"><StatCard title="Unpaid Fines" value={stats.fines_unpaid} icon="exclamation-circle" color="warning" /></div>
            </div>
            <div className="row g-4">
                <div className="col-lg-4"><div className="card border-0 shadow-sm"><div className="card-body">
                    <Chart type="bar" height={260} series={[{ name: 'Count', data: [stats.members_active, stats.loans_active, stats.fines_unpaid] }]}
                        options={{ xaxis: { categories: ['Members', 'Loans', 'Fines'] }, colors: ['#1e40af'] }} />
                </div></div></div>
                <div className="col-lg-8"><DataTable searchable={false} data={recentContributions} columns={[
                    { key: 'date', label: 'Date' },
                    { key: 'member', label: 'Member', render: (r) => r.member?.full_name ?? '—' },
                    { key: 'amount', label: 'Amount', render: (r) => formatCurrency(Number(r.amount)) },
                ]} /></div>
            </div>
        </>
    );
}`);

// === AUTH ===
const authTpl = (file, title, action, fields, footer = '', props = '', destructure = '', layoutDesc = '') => {
    w(file, `import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
${props}
export default function Page(${destructure || ''}) {
    return (
        <>
            <Head title="${title}" />
            ${destructure.includes('status') ? '{status && <div className="alert alert-success mb-3">{status}</div>}' : ''}
            <Form action="${action}" method="post" resetOnSuccess={['password']}>
                {({ errors, processing }) => (<>
                    ${fields}
                    <button type="submit" className="btn btn-primary w-100 mt-2" disabled={processing}>{processing ? 'Please wait...' : '${title}'}</button>
                    ${footer}
                </>)}
            </Form>
        </>
    );
}
Page.layout = { title: '${title}', description: '${layoutDesc || 'Secure access to ChamaPro'}' };`);
};

authTpl('auth/admin/login.tsx', 'Sign In', '/admin/login',
    `<FormField label="Email" name="email" type="email" required error={errors.email} />
     <FormField label="Password" name="password" type="password" required error={errors.password} />
     <div className="form-check mb-3"><input className="form-check-input" type="checkbox" name="remember" id="remember" /><label className="form-check-label" htmlFor="remember">Remember me</label></div>`);

authTpl('auth/portal/login.tsx', 'Log In', '/portal/login',
    `<FormField label="Email" name="email" type="email" required error={errors.email} />
     <FormField label="Password" name="password" type="password" required error={errors.password} />
     <div className="form-check mb-3"><input className="form-check-input" type="checkbox" name="remember" id="remember" /><label className="form-check-label" htmlFor="remember">Remember me</label></div>`,
    `{canResetPassword && <div className="text-center mt-3"><Link href="/portal/forgot-password" className="small">Forgot password?</Link></div>}
     <div className="text-center mt-2 small">Don't have an account? <Link href="/portal/register">Register</Link></div>`,
    'type Props = { status?: string; canResetPassword?: boolean };', '{ status, canResetPassword }: Props');

authTpl('auth/portal/register.tsx', 'Register', '/portal/register',
    `<FormField label="Name" name="name" required error={errors.name} />
     <FormField label="Email" name="email" type="email" required error={errors.email} />
     <FormField label="Password" name="password" type="password" required error={errors.password} />
     <FormField label="Confirm Password" name="password_confirmation" type="password" required error={errors.password_confirmation} />`);

authTpl('auth/portal/forgot-password.tsx', 'Forgot Password', '/portal/forgot-password',
    `<FormField label="Email" name="email" type="email" required error={errors.email} />`, '', 'type Props = { status?: string };', '{ status }: Props');

authTpl('auth/portal/reset-password.tsx', 'Reset Password', '/portal/reset-password',
    `<input type="hidden" name="token" value={token} />
     <FormField label="Email" name="email" type="email" defaultValue={email} required error={errors.email} />
     <FormField label="Password" name="password" type="password" required error={errors.password} />
     <FormField label="Confirm Password" name="password_confirmation" type="password" required error={errors.password_confirmation} />`,
    '', 'type Props = { email: string; token: string; status?: string };', '{ email, token, status }: Props');

authTpl('auth/portal/two-factor-challenge.tsx', 'Two-Factor Challenge', '/portal/two-factor-challenge',
    `<FormField label="Code" name="code" error={errors.code} />
     <FormField label="Recovery Code" name="recovery_code" error={errors.recovery_code} />`);

authTpl('auth/portal/confirm-password.tsx', 'Confirm Password', '/portal/user/confirm-password',
    `<FormField label="Password" name="password" type="password" required error={errors.password} />`);

w('auth/portal/verify-email.tsx', `import { Form, Head } from '@inertiajs/react';
type Props = { status?: string };
export default function VerifyEmail({ status }: Props) {
    return (<><Head title="Verify Email" /><p className="text-muted small mb-4">Please verify your email address.</p>
    {status && <div className="alert alert-success">{status}</div>}
    <Form action="/portal/email/verification-notification" method="post">{({ processing }) => (
        <button type="submit" className="btn btn-primary w-100" disabled={processing}>Resend Email</button>
    )}</Form></>);
}
VerifyEmail.layout = { title: 'Verify Email' };`);

// === ADMIN INDEX PAGES ===
w('admin/groups/index.tsx', idx('Groups', 'groups', 'Group', [
    { k: 'name', l: 'Name' }, { k: 'status', l: 'Status', r: '(r) => <span className="badge bg-secondary">{r.status}</span>' },
    { k: 'county', l: 'County' }, { k: 'members_count', l: 'Members' },
], '/admin/groups/create', '/admin/groups'));

w('admin/plans/index.tsx', idx('Subscription Plans', 'plans', 'SubscriptionPlan', [
    { k: 'name', l: 'Name' }, { k: 'price', l: 'Price' }, { k: 'billing_cycle', l: 'Billing' }, { k: 'status', l: 'Status' },
], '/admin/plans/create', '/admin/plans'));

w('admin/subscriptions/index.tsx', idx('Subscriptions', 'subscriptions', 'Subscription', [
    { k: 'id', l: 'ID' }, { k: 'status', l: 'Status' }, { k: 'start_date', l: 'Start', r: '(r) => r.start_date' }, { k: 'end_date', l: 'End' },
]));

w('admin/sms-providers/index.tsx', idx('SMS Providers', 'providers', 'SmsProvider', [
    { k: 'name', l: 'Name' }, { k: 'driver', l: 'Driver' }, { k: 'status', l: 'Status' },
], '/admin/sms-providers/create', '/admin/sms-providers'));

w('admin/support-tickets/index.tsx', idx('Support Tickets', 'tickets', 'SupportTicket', [
    { k: 'subject', l: 'Subject' }, { k: 'status', l: 'Status' }, { k: 'priority', l: 'Priority' },
], null, '/admin/support-tickets'));

// Admin forms & show
w('admin/groups/create.tsx', crudForm('Create Group', '@/routes/admin/groups', null, null, [
    { l: 'Name', n: 'name', req: true, typeImport: 'Group' },
    { l: 'Registration Number', n: 'registration_number' },
    { l: 'Phone', n: 'phone' },
    { l: 'Email', n: 'email', t: 'email' },
    { l: 'County', n: 'county' },
    { l: 'Admin Name', n: 'admin_name', req: true },
    { l: 'Admin Email', n: 'admin_email', t: 'email', req: true },
    { l: 'Admin Password', n: 'admin_password', t: 'password', req: true },
    { l: 'Plan', n: 'subscription_plan_id', req: true, opts: 'plans.map((p) => ({ value: String(p.id), label: p.name }))' },
], '/admin/groups', 'plans: Array<{ id: number; name: string }>'));

w('admin/groups/edit.tsx', crudForm('Edit Group', '@/routes/admin/groups', 'update', 'group', [
    { l: 'Name', n: 'name', req: true, entity: true, typeImport: 'Group' },
    { l: 'Phone', n: 'phone', entity: true },
    { l: 'Email', n: 'email', t: 'email', entity: true },
    { l: 'County', n: 'county', entity: true },
    { l: 'Status', n: 'status', entity: true },
], '/admin/groups'));

w('admin/groups/show.tsx', show('Group Details', 'group', 'Group', [
    { l: 'Name', v: 'group.name' }, { l: 'Status', v: 'group.status', fmt: 'badge' },
    { l: 'County', v: 'group.county' }, { l: 'Phone', v: 'group.phone' }, { l: 'Email', v: 'group.email' },
], '/admin/groups/${group.id}/edit', '/admin/groups/${group.id}', '/admin/groups'));

w('admin/plans/create.tsx', crudForm('Create Plan', '@/routes/admin/plans', null, null, [
    { l: 'Name', n: 'name', req: true, typeImport: 'SubscriptionPlan' },
    { l: 'Price', n: 'price', t: 'number', req: true },
    { l: 'Billing Cycle', n: 'billing_cycle', req: true },
    { l: 'Member Limit', n: 'member_limit', t: 'number' },
    { l: 'Description', n: 'description', t: 'textarea' },
], '/admin/plans'));

w('admin/plans/edit.tsx', crudForm('Edit Plan', '@/routes/admin/plans', 'update', 'plan', [
    { l: 'Name', n: 'name', req: true, entity: true, typeImport: 'SubscriptionPlan' },
    { l: 'Price', n: 'price', t: 'number', entity: true },
    { l: 'Billing Cycle', n: 'billing_cycle', entity: true },
    { l: 'Status', n: 'status', entity: true },
], '/admin/plans'));

w('admin/sms-providers/create.tsx', crudForm('Create SMS Provider', '@/routes/admin/sms-providers', null, null, [
    { l: 'Name', n: 'name', req: true, typeImport: 'SmsProvider' },
    { l: 'Driver', n: 'driver', req: true },
    { l: 'Status', n: 'status' },
], '/admin/sms-providers'));

w('admin/sms-providers/edit.tsx', crudForm('Edit SMS Provider', '@/routes/admin/sms-providers', 'update', 'provider', [
    { l: 'Name', n: 'name', req: true, entity: true, typeImport: 'SmsProvider' },
    { l: 'Driver', n: 'driver', entity: true },
    { l: 'Status', n: 'status', entity: true },
], '/admin/sms-providers'));

w('admin/system-settings/index.tsx', `import { Form, Head } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { update } from '@/routes/admin/system-settings';

type Props = { settings: Record<string, { key: string; value: string }> };

export default function SystemSettings({ settings }: Props) {
    const entries = Object.values(settings);
    return (<><Head title="System Settings" /><PageHeader title="System Settings" />
    <div className="card border-0 shadow-sm"><div className="card-body">
        <Form {...update.form()}>{({ errors, processing }) => (<>
            {entries.map((s) => <FormField key={s.key} label={s.key} name={\`settings[\${s.key}]\`} defaultValue={s.value} error={errors[\`settings.\${s.key}\`]} />)}
            <button type="submit" className="btn btn-primary" disabled={processing}>Save Settings</button>
        </>)}</Form>
    </div></div></>);
}`);

w('admin/support-tickets/show.tsx', show('Support Ticket', 'ticket', 'SupportTicket', [
    { l: 'Subject', v: 'ticket.subject' }, { l: 'Status', v: 'ticket.status', fmt: 'badge' },
    { l: 'Priority', v: 'ticket.priority' }, { l: 'Message', v: 'ticket.message' },
], undefined, undefined, '/admin/support-tickets'));

// === PORTAL INDEX PAGES ===
const portalIndexes = [
    ['portal/members/index.tsx', 'Members', 'members', 'Member', [{ k: 'membership_number', l: '#' }, { k: 'full_name', l: 'Name' }, { k: 'phone_number', l: 'Phone' }, { k: 'status', l: 'Status' }], '/portal/members/create', '/portal/members'],
    ['portal/contribution-types/index.tsx', 'Contribution Types', 'types', 'ContributionType', [{ k: 'name', l: 'Name' }, { k: 'amount', l: 'Amount' }], '/portal/contribution-types/create', '/portal/contribution-types'],
    ['portal/contribution-channels/index.tsx', 'Channels', 'channels', 'ContributionChannel', [{ k: 'name', l: 'Name' }, { k: 'status', l: 'Status' }], '/portal/contribution-channels/create', '/portal/contribution-channels'],
    ['portal/contributions/index.tsx', 'Contributions', 'contributions', 'Contribution', [{ k: 'date', l: 'Date' }, { k: 'amount', l: 'Amount' }], '/portal/contributions/create', '/portal/contributions'],
    ['portal/bank-accounts/index.tsx', 'Bank Accounts', 'accounts', 'BankAccount', [{ k: 'name', l: 'Name' }, { k: 'bank_name', l: 'Bank' }], '/portal/bank-accounts/create', '/portal/bank-accounts'],
    ['portal/cash-account/index.tsx', 'Cash Accounts', 'accounts', 'CashAccount', [{ k: 'name', l: 'Name' }, { k: 'balance', l: 'Balance' }], null, '/portal/cash-account'],
    ['portal/loan-products/index.tsx', 'Loan Products', 'products', 'LoanProduct', [{ k: 'name', l: 'Name' }, { k: 'interest_rate', l: 'Rate' }], '/portal/loan-products/create', '/portal/loan-products'],
    ['portal/loan-applications/index.tsx', 'Loan Applications', 'applications', 'LoanApplication', [{ k: 'amount', l: 'Amount' }, { k: 'status', l: 'Status' }], '/portal/loan-applications/create', '/portal/loan-applications'],
    ['portal/loans/index.tsx', 'Loans', 'loans', 'Loan', [{ k: 'principal', l: 'Principal' }, { k: 'status', l: 'Status' }], null, '/portal/loans'],
    ['portal/fine-types/index.tsx', 'Fine Types', 'fineTypes', 'FineType', [{ k: 'name', l: 'Name' }, { k: 'amount', l: 'Amount' }], '/portal/fine-types/create', '/portal/fine-types'],
    ['portal/fines/index.tsx', 'Fines', 'fines', 'Fine', [{ k: 'amount', l: 'Amount' }, { k: 'is_paid', l: 'Paid', r: "(r) => r.is_paid ? 'Yes' : 'No'" }], '/portal/fines/create', '/portal/fines'],
    ['portal/meetings/index.tsx', 'Meetings', 'meetings', 'Meeting', [{ k: 'title', l: 'Title' }, { k: 'date', l: 'Date' }], '/portal/meetings/create', '/portal/meetings'],
    ['portal/expense-categories/index.tsx', 'Expense Categories', 'categories', 'ExpenseCategory', [{ k: 'name', l: 'Name' }], '/portal/expense-categories/create', '/portal/expense-categories'],
    ['portal/expenses/index.tsx', 'Expenses', 'expenses', 'Expense', [{ k: 'date', l: 'Date' }, { k: 'amount', l: 'Amount' }], '/portal/expenses/create', '/portal/expenses'],
    ['portal/notifications/index.tsx', 'Notifications', 'notifications', 'Notification', [{ k: 'type', l: 'Type' }, { k: 'created_at', l: 'Date' }]],
    ['portal/support-tickets/index.tsx', 'Support', 'tickets', 'SupportTicket', [{ k: 'subject', l: 'Subject' }, { k: 'status', l: 'Status' }], '/portal/support-tickets/create', '/portal/support-tickets'],
    ['portal/dividends/index.tsx', 'Dividends', 'runs', 'DividendRun', [{ k: 'year', l: 'Year' }, { k: 'total_amount', l: 'Total' }, { k: 'status', l: 'Status' }]],
    ['portal/shares/index.tsx', 'Shares', 'purchases', 'SharePurchase', [{ k: 'shares', l: 'Shares' }, { k: 'amount', l: 'Amount' }]],
    ['portal/welfare/index.tsx', 'Welfare', 'contributions', 'WelfareContribution', [{ k: 'amount', l: 'Amount' }, { k: 'date', l: 'Date' }]],
];
for (const args of portalIndexes) w(...args);

w('portal/reports/index.tsx', `import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/components/shared/PageHeader';
type Props = { reportTypes: Record<string, string> };
export default function ReportsIndex({ reportTypes }: Props) {
    return (<><Head title="Reports" /><PageHeader title="Reports" />
    <div className="row g-3">{Object.entries(reportTypes).map(([type, label]) => (
        <div key={type} className="col-md-4"><Link href={\`/portal/reports/\${type}\`} className="card border-0 shadow-sm text-decoration-none p-4">
            <i className="bi bi-file-bar-graph text-primary fs-3" /><h6 className="mt-2 text-dark">{label}</h6>
        </Link></div>
    ))}</div></>);
}`);

w('portal/reports/show.tsx', `import { Form, Head } from '@inertiajs/react';
import ExportButtons from '@/components/shared/ExportButtons';
import PageHeader from '@/components/shared/PageHeader';
type Props = { type: string; data: Record<string, unknown>; filters: Record<string, string> };
export default function ReportShow({ type, data, filters }: Props) {
    return (<><Head title={\`Report: \${type}\`} /><PageHeader title={\`\${type} Report\`} actions={<ExportButtons pdfHref={\`/portal/reports/\${type}/export?format=pdf\`} csvHref={\`/portal/reports/\${type}/export?format=csv\`} />} />
    <div className="card border-0 shadow-sm"><div className="card-body"><pre className="mb-0 small">{JSON.stringify(data, null, 2)}</pre></div></div></>);
}`);

// Portal member CRUD
w('portal/members/create.tsx', crudForm('Add Member', '@/routes/portal/members', null, null, [
    { l: 'Membership #', n: 'membership_number', req: true, typeImport: 'Member' },
    { l: 'Full Name', n: 'full_name', req: true },
    { l: 'Phone', n: 'phone_number' }, { l: 'Email', n: 'email', t: 'email' },
    { l: 'Date Joined', n: 'date_joined', t: 'date', req: true },
    { l: 'Gender', n: 'gender', opts: "[{ value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }]" },
], '/portal/members'));

w('portal/members/edit.tsx', crudForm('Edit Member', '@/routes/portal/members', 'update', 'member', [
    { l: 'Membership #', n: 'membership_number', req: true, entity: true, typeImport: 'Member' },
    { l: 'Full Name', n: 'full_name', req: true, entity: true },
    { l: 'Phone', n: 'phone_number', entity: true }, { l: 'Status', n: 'status', entity: true },
], '/portal/members'));

w('portal/members/show.tsx', show('Member', 'member', 'Member', [
    { l: 'Name', v: 'member.full_name' }, { l: 'Membership #', v: 'member.membership_number' },
    { l: 'Phone', v: 'member.phone_number' }, { l: 'Status', v: 'member.status', fmt: 'badge' },
], '/portal/members/${member.id}/edit', '/portal/members/${member.id}', '/portal/members'));

w('portal/members/statement.tsx', `import { Form, Head } from '@inertiajs/react';
import PageHeader from '@/components/shared/PageHeader';
import FormField from '@/components/shared/FormField';
import type { Member } from '@/types/models';
type Props = { member: Member; statement: Record<string, unknown>; filters: { from?: string; to?: string } };
export default function MemberStatement({ member, statement, filters }: Props) {
    return (<><Head title="Member Statement" /><PageHeader title={\`Statement: \${member.full_name}\`} />
    <Form method="get" action={\`/portal/members/\${member.id}/statement\`} className="row g-3 mb-4">
        <div className="col-md-4"><FormField label="From" name="from" type="date" defaultValue={filters.from} /></div>
        <div className="col-md-4"><FormField label="To" name="to" type="date" defaultValue={filters.to} /></div>
        <div className="col-md-4 d-flex align-items-end"><button type="submit" className="btn btn-primary">Filter</button></div>
    </Form>
    <div className="card border-0 shadow-sm"><div className="card-body"><pre className="small mb-0">{JSON.stringify(statement, null, 2)}</pre></div></div></>);
}`);

// Generic portal create/edit for simple resources
const simpleCrud = [
    ['contribution-types', 'ContributionType', 'type', [{ l: 'Name', n: 'name', req: true }, { l: 'Amount', n: 'amount', t: 'number' }, { l: 'Frequency', n: 'frequency' }]],
    ['contribution-channels', 'ContributionChannel', 'channel', [{ l: 'Name', n: 'name', req: true }, { l: 'Description', n: 'description' }]],
    ['loan-products', 'LoanProduct', 'product', [{ l: 'Name', n: 'name', req: true }, { l: 'Interest Rate', n: 'interest_rate', t: 'number', req: true }, { l: 'Max Amount', n: 'max_amount', t: 'number' }]],
    ['fine-types', 'FineType', 'fineType', [{ l: 'Name', n: 'name', req: true }, { l: 'Amount', n: 'amount', t: 'number', req: true }]],
    ['expense-categories', 'ExpenseCategory', 'category', [{ l: 'Name', n: 'name', req: true }, { l: 'Description', n: 'description' }]],
    ['meetings', 'Meeting', 'meeting', [{ l: 'Title', n: 'title', req: true }, { l: 'Date', n: 'date', t: 'date', req: true }, { l: 'Location', n: 'location' }, { l: 'Agenda', n: 'agenda', t: 'textarea' }]],
    ['bank-accounts', 'BankAccount', 'account', [{ l: 'Name', n: 'name', req: true }, { l: 'Bank', n: 'bank_name', req: true }, { l: 'Account Number', n: 'account_number', req: true }, { l: 'Branch', n: 'branch' }]],
];
for (const [slug, type, entity, fields] of simpleCrud) {
    w(`portal/${slug}/create.tsx`, crudForm(`Create ${type}`, `@/routes/portal/${slug}`, null, null, fields.map((f) => ({ ...f, typeImport: type })), `/portal/${slug}`));
    w(`portal/${slug}/edit.tsx`, crudForm(`Edit ${type}`, `@/routes/portal/${slug}`, 'update', entity, fields.map((f) => ({ ...f, entity: true, typeImport: type })), `/portal/${slug}`));
}

// Contributions
w('portal/contributions/create.tsx', crudForm('Record Contribution', '@/routes/portal/contributions', null, null, [
    { l: 'Member', n: 'member_id', req: true, opts: 'members.map((m) => ({ value: String(m.id), label: `${m.full_name} (${m.membership_number})` }))', typeImport: 'Contribution' },
    { l: 'Type', n: 'contribution_type_id', req: true, opts: 'types.map((t) => ({ value: String(t.id), label: t.name }))' },
    { l: 'Channel', n: 'contribution_channel_id', opts: 'channels.map((c) => ({ value: String(c.id), label: c.name }))' },
    { l: 'Amount', n: 'amount', t: 'number', req: true }, { l: 'Date', n: 'date', t: 'date', req: true },
], '/portal/contributions', 'members: Array<{ id: number; full_name: string; membership_number: string }>; types: Array<{ id: number; name: string }>; channels: Array<{ id: number; name: string }>'));

w('portal/contributions/edit.tsx', crudForm('Edit Contribution', '@/routes/portal/contributions', 'update', 'contribution', [
    { l: 'Amount', n: 'amount', t: 'number', req: true, entity: true, typeImport: 'Contribution' },
    { l: 'Date', n: 'date', t: 'date', entity: true }, { l: 'Notes', n: 'notes', t: 'textarea', entity: true },
], '/portal/contributions', 'members: Array<{ id: number; full_name: string }>; types: Array<{ id: number; name: string }>; channels: Array<{ id: number; name: string }>'));

w('portal/contributions/show.tsx', show('Contribution', 'contribution', 'Contribution', [
    { l: 'Amount', v: 'contribution.amount' }, { l: 'Date', v: 'contribution.date', fmt: 'date' },
    { l: 'Member', v: 'contribution.member?.full_name' },
], '/portal/contributions/${contribution.id}/edit', '/portal/contributions/${contribution.id}', '/portal/contributions'));

// Loans, fines, expenses, etc.
w('portal/loan-applications/create.tsx', crudForm('New Loan Application', '@/routes/portal/loan-applications', null, null, [
    { l: 'Member', n: 'member_id', req: true, opts: 'members.map((m) => ({ value: String(m.id), label: m.full_name }))', typeImport: 'LoanApplication' },
    { l: 'Product', n: 'loan_product_id', req: true, opts: 'products.map((p) => ({ value: String(p.id), label: p.name }))' },
    { l: 'Amount', n: 'amount', t: 'number', req: true }, { l: 'Term (months)', n: 'term_months', t: 'number', req: true },
    { l: 'Purpose', n: 'purpose', t: 'textarea' },
], '/portal/loan-applications', 'members: Array<{ id: number; full_name: string }>; products: Array<{ id: number; name: string }>'));

w('portal/loan-applications/edit.tsx', crudForm('Edit Application', '@/routes/portal/loan-applications', 'update', 'application', [
    { l: 'Amount', n: 'amount', t: 'number', entity: true, typeImport: 'LoanApplication' },
    { l: 'Term', n: 'term_months', t: 'number', entity: true }, { l: 'Purpose', n: 'purpose', t: 'textarea', entity: true },
], '/portal/loan-applications', 'members: Array<{ id: number; full_name: string }>; products: Array<{ id: number; name: string }>'));

w('portal/loan-applications/show.tsx', show('Loan Application', 'application', 'LoanApplication', [
    { l: 'Amount', v: 'application.amount' }, { l: 'Status', v: 'application.status', fmt: 'badge' },
    { l: 'Member', v: 'application.member?.full_name' },
], '/portal/loan-applications/${application.id}/edit', undefined, '/portal/loan-applications'));

w('portal/loans/show.tsx', show('Loan', 'loan', 'Loan', [
    { l: 'Principal', v: 'loan.principal' }, { l: 'Status', v: 'loan.status', fmt: 'badge' },
    { l: 'Member', v: 'loan.member?.full_name' },
], undefined, undefined, '/portal/loans'));

w('portal/fines/create.tsx', crudForm('Issue Fine', '@/routes/portal/fines', null, null, [
    { l: 'Member', n: 'member_id', req: true, opts: 'members.map((m) => ({ value: String(m.id), label: m.full_name }))', typeImport: 'Fine' },
    { l: 'Fine Type', n: 'fine_type_id', req: true, opts: 'fineTypes.map((t) => ({ value: String(t.id), label: t.name }))' },
    { l: 'Amount', n: 'amount', t: 'number', req: true }, { l: 'Date', n: 'date', t: 'date', req: true },
    { l: 'Reason', n: 'reason', t: 'textarea' },
], '/portal/fines', 'members: Array<{ id: number; full_name: string }>; fineTypes: Array<{ id: number; name: string }>'));

w('portal/fines/edit.tsx', crudForm('Edit Fine', '@/routes/portal/fines', 'update', 'fine', [
    { l: 'Amount', n: 'amount', t: 'number', entity: true, typeImport: 'Fine' },
    { l: 'Reason', n: 'reason', t: 'textarea', entity: true },
], '/portal/fines', 'members: Array<{ id: number; full_name: string }>; fineTypes: Array<{ id: number; name: string }>'));

w('portal/fines/show.tsx', show('Fine', 'fine', 'Fine', [
    { l: 'Amount', v: 'fine.amount' }, { l: 'Paid', v: 'fine.is_paid' }, { l: 'Member', v: 'fine.member?.full_name' },
], '/portal/fines/${fine.id}/edit', '/portal/fines/${fine.id}', '/portal/fines'));

w('portal/expenses/create.tsx', crudForm('Record Expense', '@/routes/portal/expenses', null, null, [
    { l: 'Category', n: 'expense_category_id', req: true, opts: 'categories.map((c) => ({ value: String(c.id), label: c.name }))', typeImport: 'Expense' },
    { l: 'Amount', n: 'amount', t: 'number', req: true }, { l: 'Date', n: 'date', t: 'date', req: true },
    { l: 'Description', n: 'description', t: 'textarea' },
], '/portal/expenses', 'categories: Array<{ id: number; name: string }>'));

w('portal/expenses/edit.tsx', crudForm('Edit Expense', '@/routes/portal/expenses', 'update', 'expense', [
    { l: 'Amount', n: 'amount', t: 'number', entity: true, typeImport: 'Expense' },
    { l: 'Date', n: 'date', t: 'date', entity: true }, { l: 'Description', n: 'description', t: 'textarea', entity: true },
], '/portal/expenses', 'categories: Array<{ id: number; name: string }>'));

w('portal/expenses/show.tsx', show('Expense', 'expense', 'Expense', [
    { l: 'Amount', v: 'expense.amount' }, { l: 'Date', v: 'expense.date', fmt: 'date' }, { l: 'Description', v: 'expense.description' },
], '/portal/expenses/${expense.id}/edit', '/portal/expenses/${expense.id}', '/portal/expenses'));

w('portal/bank-accounts/show.tsx', show('Bank Account', 'account', 'BankAccount', [
    { l: 'Name', v: 'account.name' }, { l: 'Bank', v: 'account.bank_name' }, { l: 'Account', v: 'account.account_number' },
], '/portal/bank-accounts/${account.id}/edit', '/portal/bank-accounts/${account.id}', '/portal/bank-accounts'));

w('portal/cash-account/show.tsx', show('Cash Account', 'account', 'CashAccount', [
    { l: 'Name', v: 'account.name' }, { l: 'Balance', v: 'account.balance' },
], undefined, undefined, '/portal/cash-account'));

w('portal/meetings/show.tsx', show('Meeting', 'meeting', 'Meeting', [
    { l: 'Title', v: 'meeting.title' }, { l: 'Date', v: 'meeting.date', fmt: 'date' }, { l: 'Location', v: 'meeting.location' },
], '/portal/meetings/${meeting.id}/edit', '/portal/meetings/${meeting.id}', '/portal/meetings'));

w('portal/support-tickets/create.tsx', crudForm('New Ticket', '@/routes/portal/support-tickets', null, null, [
    { l: 'Subject', n: 'subject', req: true, typeImport: 'SupportTicket' },
    { l: 'Message', n: 'message', t: 'textarea', req: true },
    { l: 'Priority', n: 'priority', opts: "[{ value: 'low', label: 'Low' }, { value: 'medium', label: 'Medium' }, { value: 'high', label: 'High' }]" },
], '/portal/support-tickets'));

w('portal/support-tickets/show.tsx', show('Ticket', 'ticket', 'SupportTicket', [
    { l: 'Subject', v: 'ticket.subject' }, { l: 'Status', v: 'ticket.status', fmt: 'badge' }, { l: 'Message', v: 'ticket.message' },
], undefined, undefined, '/portal/support-tickets'));

w('portal/subscription/renew.tsx', `import { Form, Head } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { store } from '@/routes/portal/subscription/renew';
import type { Group, SubscriptionPlan } from '@/types/models';
type Props = { group?: Group | null; plans: SubscriptionPlan[] };
export default function RenewSubscription({ group, plans }: Props) {
    return (<><Head title="Renew Subscription" /><PageHeader title="Renew Subscription" description={group?.name} />
    <div className="card border-0 shadow-sm"><div className="card-body">
        <Form {...store.form()}>{({ errors, processing }) => (<>
            <FormField label="Plan" name="subscription_plan_id" required options={plans.map((p) => ({ value: String(p.id), label: \`\${p.name} - \${p.price}\` }))} error={errors.subscription_plan_id} />
            <button type="submit" className="btn btn-primary" disabled={processing}>Renew</button>
        </>)}</Form>
    </div></div></>);
}`);

// Complex index pages with forms
w('portal/shares/index.tsx', `import { Form, Head } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { store as storePurchase } from '@/actions/App/Features/Shares/Controllers/ShareController';
import type { MemberOption, SharePurchase, ShareSetting } from '@/types/models';
import type { Paginated } from '@/types/pagination';
type Props = { purchases: Paginated<SharePurchase>; settings?: ShareSetting | null; members: MemberOption[] };
export default function SharesIndex({ purchases, settings, members }: Props) {
    return (<><Head title="Shares" /><PageHeader title="Shares" description={settings ? \`Share value: \${settings.share_value}\` : undefined} />
    <div className="card border-0 shadow-sm mb-4"><div className="card-body">
        <h6 className="fw-semibold mb-3">Record Purchase</h6>
        <Form {...storePurchase.form()} className="row g-3">{({ errors, processing }) => (<>
            <div className="col-md-4"><FormField label="Member" name="member_id" required options={members.map((m) => ({ value: String(m.id), label: m.full_name }))} error={errors.member_id} /></div>
            <div className="col-md-3"><FormField label="Shares" name="shares" type="number" required error={errors.shares} /></div>
            <div className="col-md-3"><FormField label="Date" name="date" type="date" required error={errors.date} /></div>
            <div className="col-md-2 d-flex align-items-end"><button type="submit" className="btn btn-primary w-100" disabled={processing}>Add</button></div>
        </>)}</Form>
    </div></div>
    <DataTable data={purchases} columns={[{ key: 'shares', label: 'Shares' }, { key: 'amount', label: 'Amount' }, { key: 'date', label: 'Date' }]} /></>);
}`);

w('portal/welfare/index.tsx', `import { Form, Head } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import type { MemberOption, WelfareContribution, WelfareDisbursement } from '@/types/models';
import type { Paginated } from '@/types/pagination';
type Props = { contributions: Paginated<WelfareContribution>; disbursements: Paginated<WelfareDisbursement>; members: MemberOption[] };
export default function WelfareIndex({ contributions, disbursements, members }: Props) {
    const memberOpts = members.map((m) => ({ value: String(m.id), label: m.full_name }));
    return (<><Head title="Welfare" /><PageHeader title="Welfare Fund" />
    <div className="row g-4">
        <div className="col-lg-6"><div className="card border-0 shadow-sm"><div className="card-body">
            <h6 className="fw-semibold">Contributions</h6>
            <Form action="/portal/welfare/contributions" method="post" className="row g-2 mb-3">{({ errors }) => (<>
                <div className="col-6"><FormField label="Member" name="member_id" required options={memberOpts} error={errors.member_id} /></div>
                <div className="col-4"><FormField label="Amount" name="amount" type="number" required error={errors.amount} /></div>
                <div className="col-2 d-flex align-items-end"><button type="submit" className="btn btn-sm btn-primary w-100">Add</button></div>
            </>)}</Form>
            <DataTable searchable={false} data={contributions} columns={[{ key: 'amount', label: 'Amount' }, { key: 'date', label: 'Date' }]} />
        </div></div></div>
        <div className="col-lg-6"><div className="card border-0 shadow-sm"><div className="card-body">
            <h6 className="fw-semibold">Disbursements</h6>
            <Form action="/portal/welfare/disbursements" method="post" className="row g-2 mb-3">{({ errors }) => (<>
                <div className="col-6"><FormField label="Member" name="member_id" required options={memberOpts} error={errors.member_id} /></div>
                <div className="col-4"><FormField label="Amount" name="amount" type="number" required error={errors.amount} /></div>
                <div className="col-2 d-flex align-items-end"><button type="submit" className="btn btn-sm btn-primary w-100">Add</button></div>
            </>)}</Form>
            <DataTable searchable={false} data={disbursements} columns={[{ key: 'amount', label: 'Amount' }, { key: 'date', label: 'Date' }]} />
        </div></div></div>
    </div></>);
}`);

w('portal/dividends/index.tsx', `import { Form, Head } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import type { DividendRun } from '@/types/models';
import type { Paginated } from '@/types/pagination';
type Props = { runs: Paginated<DividendRun>; formula: string };
export default function DividendsIndex({ runs, formula }: Props) {
    return (<><Head title="Dividends" /><PageHeader title="Dividends" description={\`Formula: \${formula}\`} />
    <div className="card border-0 shadow-sm mb-4"><div className="card-body">
        <Form action="/portal/dividends" method="post" className="row g-3">{({ errors, processing }) => (<>
            <div className="col-md-3"><FormField label="Year" name="year" type="number" required error={errors.year} /></div>
            <div className="col-md-3"><FormField label="Total Amount" name="total_amount" type="number" required error={errors.total_amount} /></div>
            <div className="col-md-3 d-flex align-items-end"><button type="submit" className="btn btn-primary" disabled={processing}>Run Dividends</button></div>
        </>)}</Form>
    </div></div>
    <DataTable data={runs} columns={[{ key: 'year', label: 'Year' }, { key: 'total_amount', label: 'Total' }, { key: 'status', label: 'Status' }]} /></>);
}`);

console.log(`Created ${count} pages`);
