#!/usr/bin/env node
import fs from 'fs';
import path from 'path';

const root = path.resolve(import.meta.dirname, '..');
const pagesDir = path.join(root, 'resources/js/pages');

function writePage(relPath, content) {
    const full = path.join(pagesDir, relPath);
    fs.mkdirSync(path.dirname(full), { recursive: true });
    fs.writeFileSync(full, content.trimStart() + '\n');
}

function indexPage({ title, resource, prop, columns, createHref }) {
    const cols = columns.map((c) => `{ key: '${c.key}', label: '${c.label}'${c.render ? `, render: (row) => ${c.render}` : ''} }`).join(',\n        ');

    return `import { Head, Link } from '@inertiajs/react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import type { Paginated } from '@/types/pagination';
import type { ${resource} } from '@/types/models';

type Props = {
    ${prop}: Paginated<${resource}>;
};

export default function Index({ ${prop} }: Props) {
    return (
        <>
            <Head title="${title}" />
            <PageHeader
                title="${title}"
                actions={
                    ${createHref ? `<Link href="${createHref}" className="btn btn-primary">
                        <i className="bi bi-plus-lg me-1" /> Add New
                    </Link>` : 'undefined'}
                }
            />
            <DataTable
                columns={[
        ${cols}
                ]}
                data={${prop}}
            />
        </>
    );
}
`;
}

// Simpler approach: write each page with tailored minimal content
const pages = [];

function add(rel, content) {
    pages.push([rel, content]);
}

// ADMIN DASHBOARD - special
add('admin/dashboard.tsx', `import { Head } from '@inertiajs/react';
import Chart from 'react-apexcharts';
import StatCard from '@/components/shared/StatCard';
import PageHeader from '@/components/shared/PageHeader';
import DataTable from '@/components/shared/DataTable';
import type { Group } from '@/types/models';

type Stats = {
    groups_total: number;
    groups_active: number;
    groups_suspended: number;
    subscriptions_active: number;
    subscriptions_trial: number;
    subscriptions_expired: number;
    open_tickets: number;
};

type Props = { stats: Stats; recentGroups: Group[] };

export default function AdminDashboard({ stats, recentGroups }: Props) {
    const chartOptions = {
        chart: { type: 'donut' as const },
        labels: ['Active', 'Trial', 'Expired'],
        colors: ['#059669', '#f59e0b', '#dc3545'],
        legend: { position: 'bottom' as const },
    };
    const chartSeries = [stats.subscriptions_active, stats.subscriptions_trial, stats.subscriptions_expired];

    return (
        <>
            <Head title="Admin Dashboard" />
            <PageHeader title="Dashboard" description="Platform overview and recent activity" />
            <div className="row g-3 mb-4">
                <div className="col-md-3"><StatCard title="Total Groups" value={stats.groups_total} icon="people" /></div>
                <div className="col-md-3"><StatCard title="Active Groups" value={stats.groups_active} icon="check-circle" color="success" /></div>
                <div className="col-md-3"><StatCard title="Active Subscriptions" value={stats.subscriptions_active} icon="credit-card" color="info" /></div>
                <div className="col-md-3"><StatCard title="Open Tickets" value={stats.open_tickets} icon="headset" color="warning" /></div>
            </div>
            <div className="row g-4">
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <h6 className="fw-semibold mb-3">Subscription Status</h6>
                            <Chart options={chartOptions} series={chartSeries} type="donut" height={280} />
                        </div>
                    </div>
                </div>
                <div className="col-lg-8">
                    <h6 className="fw-semibold mb-3">Recent Groups</h6>
                    <DataTable
                        data={recentGroups}
                        searchable={false}
                        columns={[
                            { key: 'name', label: 'Name' },
                            { key: 'status', label: 'Status', render: (r) => <span className="badge bg-secondary">{r.status}</span> },
                            { key: 'county', label: 'County' },
                        ]}
                    />
                </div>
            </div>
        </>
    );
}
`);

// PORTAL DASHBOARD
add('portal/dashboard.tsx', `import { Head } from '@inertiajs/react';
import Chart from 'react-apexcharts';
import StatCard from '@/components/shared/StatCard';
import PageHeader from '@/components/shared/PageHeader';
import DataTable from '@/components/shared/DataTable';
import { formatCurrency } from '@/lib/format';
import type { Contribution, Group } from '@/types/models';

type Stats = {
    members_total: number;
    members_active: number;
    contributions_month: number;
    loans_active: number;
    fines_unpaid: number;
};

type Props = { group: Group; stats: Stats; recentContributions: Contribution[] };

export default function PortalDashboard({ group, stats, recentContributions }: Props) {
    const chartOptions = {
        chart: { type: 'bar' as const, toolbar: { show: false } },
        xaxis: { categories: ['Members', 'Loans', 'Fines'] },
        colors: ['#1e40af'],
    };

    return (
        <>
            <Head title="Dashboard" />
            <PageHeader title={group.name} description="Chama overview and recent activity" />
            <div className="row g-3 mb-4">
                <div className="col-md-3"><StatCard title="Members" value={stats.members_active} subtitle={\`\${stats.members_total} total\`} icon="people" /></div>
                <div className="col-md-3"><StatCard title="Contributions (Month)" value={formatCurrency(stats.contributions_month)} icon="cash-stack" color="success" /></div>
                <div className="col-md-3"><StatCard title="Active Loans" value={stats.loans_active} icon="currency-exchange" color="info" /></div>
                <div className="col-md-3"><StatCard title="Unpaid Fines" value={stats.fines_unpaid} icon="exclamation-circle" color="warning" /></div>
            </div>
            <div className="row g-4">
                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body">
                            <h6 className="fw-semibold mb-3">Activity Overview</h6>
                            <Chart options={chartOptions} series={[{ name: 'Count', data: [stats.members_active, stats.loans_active, stats.fines_unpaid] }]} type="bar" height={260} />
                        </div>
                    </div>
                </div>
                <div className="col-lg-8">
                    <h6 className="fw-semibold mb-3">Recent Contributions</h6>
                    <DataTable
                        data={recentContributions}
                        searchable={false}
                        columns={[
                            { key: 'date', label: 'Date' },
                            { key: 'member', label: 'Member', render: (r) => r.member?.full_name ?? '—' },
                            { key: 'amount', label: 'Amount', render: (r) => formatCurrency(Number(r.amount)) },
                        ]}
                    />
                </div>
            </div>
        </>
    );
}
`);

// AUTH PAGES
const authPages = [
    ['auth/admin/login.tsx', 'Admin Login', '/admin/login', 'post', false],
    ['auth/portal/login.tsx', 'Log in', '/portal/login', 'post', true],
    ['auth/portal/register.tsx', 'Register', '/portal/register', 'post', false],
    ['auth/portal/forgot-password.tsx', 'Forgot Password', '/portal/forgot-password', 'post', false],
    ['auth/portal/reset-password.tsx', 'Reset Password', '/portal/reset-password', 'post', false],
    ['auth/portal/verify-email.tsx', 'Verify Email', null, null, false],
    ['auth/portal/two-factor-challenge.tsx', 'Two-Factor Challenge', '/portal/two-factor-challenge', 'post', false],
    ['auth/portal/confirm-password.tsx', 'Confirm Password', '/portal/user/confirm-password', 'post', false],
];

for (const [file, title, action, method, canReset] of authPages) {
    if (file.includes('verify-email')) {
        add(file, `import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';

type Props = { status?: string };

export default function VerifyEmail({ status }: Props) {
    return (
        <>
            <Head title="Verify Email" />
            <p className="text-muted small mb-4">Thanks for signing up! Please verify your email address by clicking the link we emailed you.</p>
            {status && <div className="alert alert-success">{status}</div>}
            <Form action="/portal/email/verification-notification" method="post">
                {({ processing }) => (
                    <button type="submit" className="btn btn-primary w-100" disabled={processing}>
                        Resend Verification Email
                    </button>
                )}
            </Form>
        </>
    );
}
VerifyEmail.layout = { title: 'Verify Email', description: 'Email verification required' };
`);
        continue;
    }

    const extraFields = file.includes('reset-password')
        ? `<input type="hidden" name="token" value={token} />
            <FormField label="Email" name="email" type="email" defaultValue={email} required error={errors.email} />
            <FormField label="Password" name="password" type="password" required error={errors.password} />
            <FormField label="Confirm Password" name="password_confirmation" type="password" required error={errors.password_confirmation} />`
        : file.includes('two-factor')
          ? `<FormField label="Authentication Code" name="code" required error={errors.code} />
            <p className="text-muted small">Or use a recovery code</p>
            <FormField label="Recovery Code" name="recovery_code" error={errors.recovery_code} />`
          : file.includes('register')
            ? `<FormField label="Name" name="name" required error={errors.name} />
            <FormField label="Email" name="email" type="email" required error={errors.email} />
            <FormField label="Password" name="password" type="password" required error={errors.password} />
            <FormField label="Confirm Password" name="password_confirmation" type="password" required error={errors.password_confirmation} />`
            : `<FormField label="Email" name="email" type="email" required error={errors.email} />
            <FormField label="Password" name="password" type="password" required error={errors.password} />
            ${file.includes('login') ? `<div className="form-check mb-3">
                <input className="form-check-input" type="checkbox" name="remember" id="remember" />
                <label className="form-check-label" htmlFor="remember">Remember me</label>
            </div>` : ''}`;

    const propsType = file.includes('reset-password')
        ? 'type Props = { email: string; token: string; status?: string; passwordRules?: string };'
        : file.includes('login') && file.includes('portal')
          ? 'type Props = { status?: string; canResetPassword?: boolean };'
          : file.includes('forgot')
            ? 'type Props = { status?: string };'
            : 'type Props = { status?: string };';

    const propsDestructure = file.includes('reset-password') ? '{ email, token, status }' : file.includes('portal/login') ? '{ status, canResetPassword }' : '{ status }';

    add(file, `import { Form, Head, Link } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';

${propsType}

export default function AuthPage(${propsDestructure}: Props) {
    return (
        <>
            <Head title="${title}" />
            {status && <div className="alert alert-success mb-3">{status}</div>}
            <Form action="${action}" method="${method}" resetOnSuccess={['password']}>
                {({ errors, processing }) => (
                    <>
                        ${extraFields}
                        <button type="submit" className="btn btn-primary w-100 mt-2" disabled={processing}>
                            {processing ? 'Please wait...' : '${title}'}
                        </button>
                        ${canReset ? `{canResetPassword && (
                            <div className="text-center mt-3">
                                <Link href="/portal/forgot-password" className="small">Forgot password?</Link>
                            </div>
                        )}
                        <div className="text-center mt-2 small">
                            Don't have an account? <Link href="/portal/register">Register</Link>
                        </div>` : ''}
                    </>
                )}
            </Form>
        </>
    );
}
AuthPage.layout = { title: '${title}', description: 'Secure access to ChamaPro' };
`);
}

// CRUD page definitions
const crud = [
    // Admin
    ['admin/groups/index.tsx', 'Groups', 'groups', 'Group', [{ key: 'name', label: 'Name' }, { key: 'status', label: 'Status', render: `(r) => <span className="badge bg-secondary">{r.status}</span>` }, { key: 'county', label: 'County' }], '/admin/groups/create', '/admin/groups'],
    ['admin/plans/index.tsx', 'Subscription Plans', 'plans', 'SubscriptionPlan', [{ key: 'name', label: 'Name' }, { key: 'price', label: 'Price' }, { key: 'billing_cycle', label: 'Billing' }, { key: 'status', label: 'Status' }], '/admin/plans/create'],
    ['admin/subscriptions/index.tsx', 'Subscriptions', 'subscriptions', 'Subscription', [{ key: 'id', label: 'ID' }, { key: 'status', label: 'Status' }, { key: 'start_date', label: 'Start' }, { key: 'end_date', label: 'End' }]],
    ['admin/sms-providers/index.tsx', 'SMS Providers', 'providers', 'SmsProvider', [{ key: 'name', label: 'Name' }, { key: 'driver', label: 'Driver' }, { key: 'status', label: 'Status' }], '/admin/sms-providers/create'],
    ['admin/support-tickets/index.tsx', 'Support Tickets', 'tickets', 'SupportTicket', [{ key: 'subject', label: 'Subject' }, { key: 'status', label: 'Status' }, { key: 'priority', label: 'Priority' }]],
    // Portal indexes
    ['portal/members/index.tsx', 'Members', 'members', 'Member', [{ key: 'membership_number', label: '#' }, { key: 'full_name', label: 'Name' }, { key: 'phone_number', label: 'Phone' }, { key: 'status', label: 'Status' }], '/portal/members/create', '/portal/members'],
    ['portal/contribution-types/index.tsx', 'Contribution Types', 'types', 'ContributionType', [{ key: 'name', label: 'Name' }, { key: 'amount', label: 'Amount' }, { key: 'frequency', label: 'Frequency' }], '/portal/contribution-types/create'],
    ['portal/contribution-channels/index.tsx', 'Contribution Channels', 'channels', 'ContributionChannel', [{ key: 'name', label: 'Name' }, { key: 'status', label: 'Status' }], '/portal/contribution-channels/create'],
    ['portal/contributions/index.tsx', 'Contributions', 'contributions', 'Contribution', [{ key: 'date', label: 'Date' }, { key: 'member', label: 'Member', render: `(r) => r.member?.full_name ?? '—'` }, { key: 'amount', label: 'Amount' }], '/portal/contributions/create', '/portal/contributions'],
    ['portal/bank-accounts/index.tsx', 'Bank Accounts', 'accounts', 'BankAccount', [{ key: 'name', label: 'Name' }, { key: 'bank_name', label: 'Bank' }, { key: 'account_number', label: 'Account' }], '/portal/bank-accounts/create', '/portal/bank-accounts'],
    ['portal/cash-account/index.tsx', 'Cash Accounts', 'accounts', 'CashAccount', [{ key: 'name', label: 'Name' }, { key: 'balance', label: 'Balance' }]],
    ['portal/loan-products/index.tsx', 'Loan Products', 'products', 'LoanProduct', [{ key: 'name', label: 'Name' }, { key: 'interest_rate', label: 'Rate %' }, { key: 'status', label: 'Status' }], '/portal/loan-products/create'],
    ['portal/loan-applications/index.tsx', 'Loan Applications', 'applications', 'LoanApplication', [{ key: 'member', label: 'Member', render: `(r) => r.member?.full_name ?? '—'` }, { key: 'amount', label: 'Amount' }, { key: 'status', label: 'Status' }], '/portal/loan-applications/create', '/portal/loan-applications'],
    ['portal/loans/index.tsx', 'Loans', 'loans', 'Loan', [{ key: 'member', label: 'Member', render: `(r) => r.member?.full_name ?? '—'` }, { key: 'principal', label: 'Principal' }, { key: 'status', label: 'Status' }], null, '/portal/loans'],
    ['portal/fine-types/index.tsx', 'Fine Types', 'fineTypes', 'FineType', [{ key: 'name', label: 'Name' }, { key: 'amount', label: 'Amount' }, { key: 'status', label: 'Status' }], '/portal/fine-types/create'],
    ['portal/fines/index.tsx', 'Fines', 'fines', 'Fine', [{ key: 'member', label: 'Member', render: `(r) => r.member?.full_name ?? '—'` }, { key: 'amount', label: 'Amount' }, { key: 'is_paid', label: 'Paid', render: `(r) => r.is_paid ? 'Yes' : 'No'` }], '/portal/fines/create', '/portal/fines'],
    ['portal/meetings/index.tsx', 'Meetings', 'meetings', 'Meeting', [{ key: 'title', label: 'Title' }, { key: 'date', label: 'Date' }, { key: 'location', label: 'Location' }], '/portal/meetings/create', '/portal/meetings'],
    ['portal/expense-categories/index.tsx', 'Expense Categories', 'categories', 'ExpenseCategory', [{ key: 'name', label: 'Name' }, { key: 'description', label: 'Description' }], '/portal/expense-categories/create'],
    ['portal/expenses/index.tsx', 'Expenses', 'expenses', 'Expense', [{ key: 'date', label: 'Date' }, { key: 'amount', label: 'Amount' }, { key: 'description', label: 'Description' }], '/portal/expenses/create', '/portal/expenses'],
    ['portal/notifications/index.tsx', 'Notifications', 'notifications', 'Notification', [{ key: 'type', label: 'Type' }, { key: 'created_at', label: 'Date' }]],
    ['portal/support-tickets/index.tsx', 'Support Tickets', 'tickets', 'SupportTicket', [{ key: 'subject', label: 'Subject' }, { key: 'status', label: 'Status' }], '/portal/support-tickets/create', '/portal/support-tickets'],
    ['portal/dividends/index.tsx', 'Dividends', 'runs', 'DividendRun', [{ key: 'year', label: 'Year' }, { key: 'total_amount', label: 'Total' }, { key: 'status', label: 'Status' }]],
    ['portal/shares/index.tsx', 'Shares', 'purchases', 'SharePurchase', [{ key: 'member', label: 'Member', render: `(r) => r.member?.full_name ?? '—'` }, { key: 'shares', label: 'Shares' }, { key: 'amount', label: 'Amount' }]],
    ['portal/welfare/index.tsx', 'Welfare', 'contributions', 'WelfareContribution', [{ key: 'member', label: 'Member', render: `(r) => r.member?.full_name ?? '—'` }, { key: 'amount', label: 'Amount' }, { key: 'date', label: 'Date' }]],
    ['portal/reports/index.tsx', 'Reports', 'reportTypes', null, []],
];

for (const [file, title, prop, type, columns, createHref, showBase] of crud) {
    if (file === 'portal/reports/index.tsx') {
        add(file, `import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/components/shared/PageHeader';

type Props = { reportTypes: Record<string, string> };

export default function ReportsIndex({ reportTypes }: Props) {
    return (
        <>
            <Head title="Reports" />
            <PageHeader title="Reports" description="Generate and export chama reports" />
            <div className="row g-3">
                {Object.entries(reportTypes).map(([type, label]) => (
                    <div key={type} className="col-md-4">
                        <Link href={\`/portal/reports/\${type}\`} className="card border-0 shadow-sm text-decoration-none h-100">
                            <div className="card-body">
                                <i className="bi bi-file-bar-graph text-primary fs-3 mb-2 d-block" />
                                <h6 className="fw-semibold text-dark">{label}</h6>
                            </div>
                        </Link>
                    </div>
                ))}
            </div>
        </>
    );
}
`);
        continue;
    }

    const showCol = showBase ? `, { key: 'id', label: '', render: (r) => <Link href={\`${showBase}/\${r.id}\`} className="btn btn-sm btn-outline-primary">View</Link> }` : '';
    add(file, indexPage({ title, resource: type, prop, columns: [...columns, ...(showCol ? [{ key: 'actions', label: '', render: showCol.replace(', { key: \'id\', label: \'\', render: ', '').replace(' }', '') }] : [])], createHref }));
}

// Write all pages
let count = 0;

for (const [rel, content] of pages) {
    writePage(rel, content);
    count++;
}

console.log(`Generated ${count} pages (partial - run extended generator for forms/show)`);
