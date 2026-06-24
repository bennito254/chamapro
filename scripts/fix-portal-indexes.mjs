#!/usr/bin/env node
import fs from 'fs';
import path from 'path';

const pagesDir = path.join(path.resolve(import.meta.dirname, '..'), 'resources/js/pages');

function w(rel, content) {
    const full = path.join(pagesDir, rel);
    fs.mkdirSync(path.dirname(full), { recursive: true });
    fs.writeFileSync(full, content.trimStart() + '\n');
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
];

for (const [file, title, prop, type, cols, createHref, viewBase] of portalIndexes) {
    w(file, idx(title, prop, type, cols, createHref, viewBase));
}

console.log('Regenerated', portalIndexes.length, 'portal index pages');
