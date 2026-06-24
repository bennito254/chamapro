export type NavItem = {
    label: string;
    href: string;
    icon: string;
    permission?: string;
};

export type NavGroup = {
    id: string;
    label: string;
    icon: string;
    items: NavItem[];
};

export type QuickLink = {
    label: string;
    href: string;
    icon: string;
    description?: string;
    color?: string;
    permission?: string;
};

export function filterNavByPermissions(groups: NavGroup[], permissions: string[]): NavGroup[] {
    return groups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => !item.permission || permissions.includes(item.permission)),
        }))
        .filter((group) => group.items.length > 0);
}

export function filterQuickLinksByPermissions(links: QuickLink[], permissions: string[]): QuickLink[] {
    return links.filter((link) => !link.permission || permissions.includes(link.permission));
}

export const adminNavGroups: NavGroup[] = [
    {
        id: 'overview',
        label: 'Overview',
        icon: 'speedometer2',
        items: [{ label: 'Dashboard', href: '/admin/dashboard', icon: 'grid-1x2' }],
    },
    {
        id: 'tenants',
        label: 'Tenants',
        icon: 'buildings',
        items: [{ label: 'Groups', href: '/admin/groups', icon: 'people' }],
    },
    {
        id: 'billing',
        label: 'Billing',
        icon: 'credit-card',
        items: [
            { label: 'Plans', href: '/admin/plans', icon: 'card-list' },
            { label: 'Subscriptions', href: '/admin/subscriptions', icon: 'receipt' },
            { label: 'Payments', href: '/admin/subscription-payments', icon: 'phone' },
            { label: 'Subscription Logs', href: '/admin/subscription-logs', icon: 'journal-text' },
        ],
    },
    {
        id: 'messaging',
        label: 'Messaging',
        icon: 'chat-dots',
        items: [
            { label: 'SMS Providers', href: '/admin/sms-providers', icon: 'hdd-network' },
            { label: 'Messaging', href: '/admin/owner-sms', icon: 'send' },
        ],
    },
    {
        id: 'platform',
        label: 'Platform',
        icon: 'gear-wide-connected',
        items: [
            { label: 'System Settings', href: '/admin/system-settings', icon: 'sliders' },
            { label: 'Support Tickets', href: '/admin/support-tickets', icon: 'headset' },
        ],
    },
];

export const portalNavGroups: NavGroup[] = [
    {
        id: 'overview',
        label: 'Overview',
        icon: 'speedometer2',
        items: [{ label: 'Dashboard', href: '/portal/dashboard', icon: 'grid-1x2' }],
    },
    {
        id: 'members',
        label: 'Members',
        icon: 'people',
        items: [{ label: 'All Members', href: '/portal/members', icon: 'person-lines-fill' }],
    },
    {
        id: 'contributions',
        label: 'Contributions',
        icon: 'cash-stack',
        items: [
            { label: 'Bulk Entry', href: '/portal/contributions-bulk', icon: 'people-fill' },
            { label: 'Record Single', href: '/portal/contributions/create', icon: 'plus-circle' },
            { label: 'By Meeting Date', href: '/portal/contributions', icon: 'calendar-event' },
            { label: 'Contribution Types', href: '/portal/contribution-types', icon: 'tags' },
            { label: 'Payment Channels', href: '/portal/contribution-channels', icon: 'broadcast' },
        ],
    },
    {
        id: 'banking',
        label: 'Banking & Cash',
        icon: 'bank',
        items: [
            { label: 'Bank Accounts', href: '/portal/bank-accounts', icon: 'bank2' },
            { label: 'Cash on Hand', href: '/portal/cash-account', icon: 'wallet2' },
        ],
    },
    {
        id: 'loans',
        label: 'Loans',
        icon: 'currency-exchange',
        items: [
            { label: 'New Application', href: '/portal/loan-applications/create', icon: 'file-earmark-plus' },
            { label: 'Applications', href: '/portal/loan-applications', icon: 'file-earmark-text' },
            { label: 'Active Loans', href: '/portal/loans', icon: 'cash-coin' },
            { label: 'Loan Products', href: '/portal/loan-products', icon: 'box' },
        ],
    },
    {
        id: 'fines',
        label: 'Fines',
        icon: 'shield-exclamation',
        items: [
            { label: 'Issue Fine', href: '/portal/fines/create', icon: 'plus-circle' },
            { label: 'All Fines', href: '/portal/fines', icon: 'list-check' },
            { label: 'Fine Types', href: '/portal/fine-types', icon: 'exclamation-circle' },
        ],
    },
    {
        id: 'funds',
        label: 'Welfare & Shares',
        icon: 'heart',
        items: [
            { label: 'Welfare Fund', href: '/portal/welfare', icon: 'heart-pulse' },
            { label: 'Share Capital', href: '/portal/shares', icon: 'pie-chart' },
            { label: 'Dividends', href: '/portal/dividends', icon: 'graph-up-arrow' },
        ],
    },
    {
        id: 'operations',
        label: 'Operations',
        icon: 'calendar3',
        items: [
            { label: 'Meetings', href: '/portal/meetings', icon: 'calendar-event' },
            { label: 'Expenses', href: '/portal/expenses', icon: 'receipt' },
            { label: 'Expense Categories', href: '/portal/expense-categories', icon: 'folder' },
        ],
    },
    {
        id: 'reports',
        label: 'Reports',
        icon: 'bar-chart-line',
        items: [{ label: 'All Reports', href: '/portal/reports', icon: 'file-bar-graph', permission: 'reports.view' }],
    },
    {
        id: 'account',
        label: 'Account',
        icon: 'person-circle',
        items: [
            { label: 'SMS Messages', href: '/portal/sms-messages', icon: 'chat-dots', permission: 'sms.view' },
            { label: 'SMS Templates', href: '/portal/sms-templates', icon: 'chat-square-text', permission: 'sms.view' },
            { label: 'Notifications', href: '/portal/notifications', icon: 'bell' },
            { label: 'Support', href: '/portal/support-tickets', icon: 'headset' },
        ],
    },
];

export const adminQuickLinks: QuickLink[] = [
    { label: 'New Group', href: '/admin/groups/create', icon: 'building-add', description: 'Onboard a chama', color: 'primary' },
    { label: 'Manage Plans', href: '/admin/plans', icon: 'card-list', description: 'Subscription tiers', color: 'info' },
    { label: 'Subscriptions', href: '/admin/subscriptions', icon: 'credit-card', description: 'Billing status', color: 'success' },
    { label: 'Payments', href: '/admin/subscription-payments', icon: 'phone', description: 'M-Pesa checkout', color: 'primary' },
    { label: 'Subscription Logs', href: '/admin/subscription-logs', icon: 'journal-text', description: 'History & receipts', color: 'info' },
    { label: 'Support Tickets', href: '/admin/support-tickets', icon: 'headset', description: 'Open requests', color: 'warning' },
    { label: 'Messaging', href: '/admin/owner-sms', icon: 'send', description: 'Message group owners', color: 'secondary' },
    { label: 'SMS Providers', href: '/admin/sms-providers', icon: 'chat-dots', description: 'Messaging config', color: 'secondary' },
    { label: 'System Settings', href: '/admin/system-settings', icon: 'gear', description: 'Platform config', color: 'dark' },
];

export const portalQuickLinks: QuickLink[] = [
    { label: 'Bulk Contributions', href: '/portal/contributions-bulk', icon: 'people-fill', description: 'Record many at once', color: 'primary' },
    { label: 'Record Contribution', href: '/portal/contributions/create', icon: 'cash-coin', description: 'Single payment', color: 'success' },
    { label: 'Add Member', href: '/portal/members/create', icon: 'person-plus', description: 'Register member', color: 'primary' },
    { label: 'Loan Application', href: '/portal/loan-applications/create', icon: 'file-earmark-plus', description: 'Apply for loan', color: 'info' },
    { label: 'Issue Fine', href: '/portal/fines/create', icon: 'shield-exclamation', description: 'Record penalty', color: 'warning' },
    { label: 'Schedule Meeting', href: '/portal/meetings/create', icon: 'calendar-plus', description: 'Plan gathering', color: 'secondary' },
    { label: 'Record Expense', href: '/portal/expenses/create', icon: 'receipt', description: 'Log spending', color: 'danger' },
    { label: 'Welfare', href: '/portal/welfare', icon: 'heart-pulse', description: 'Fund activity', color: 'success' },
    { label: 'Reports', href: '/portal/reports', icon: 'file-bar-graph', description: 'Export data', color: 'dark', permission: 'reports.view' },
];
