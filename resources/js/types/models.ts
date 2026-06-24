export type Group = {
    id: number;
    sqid: string;
    name: string;
    registration_number?: string | null;
    phone?: string | null;
    email?: string | null;
    address?: string | null;
    county?: string | null;
    constituency?: string | null;
    logo?: string | null;
    meeting_day?: string | null;
    meeting_frequency?: string | null;
    currency?: string;
    status: string;
    mpesa_settings?: Record<string, unknown> | null;
    members_count?: number;
    users_count?: number;
    members?: Member[];
    users?: User[];
    active_subscription?: Subscription | null;
    activeSubscription?: Subscription | null;
    created_at?: string;
    updated_at?: string;
};

export type SubscriptionPlan = {
    id: number;
    sqid: string;
    name: string;
    billing_cycle?: string;
    amount?: number | string;
    price?: number | string;
    discount_percentage?: number | string;
    max_members?: number;
    max_users?: number;
    trial_days?: number;
    status?: string;
    subscriptions_count?: number;
};

export type SubscriptionPayment = {
    id: number;
    sqid: string;
    group_id: number;
    subscription_plan_id: number;
    status: string;
    amount: number | string;
    phone_number: string;
    mpesa_receipt_number?: string | null;
    checkout_request_id?: string | null;
    paid_at?: string | null;
    plan?: SubscriptionPlan;
    group?: Group;
    created_at?: string;
    updated_at?: string;
};

export type Subscription = {
    id: number;
    sqid: string;
    group_id: number;
    subscription_plan_id: number;
    status: string;
    start_date: string;
    end_date: string;
    trial_ends_at?: string | null;
    plan?: SubscriptionPlan;
    group?: Group;
    created_at?: string;
    updated_at?: string;
};

export type Member = {
    id: number;
    sqid: string;
    group_id?: number;
    membership_number: string;
    full_name: string;
    id_number?: string | null;
    phone_number?: string | null;
    email?: string | null;
    gender?: string | null;
    date_joined: string;
    address?: string | null;
    occupation?: string | null;
    next_of_kin?: string | null;
    next_of_kin_phone?: string | null;
    photo?: string | null;
    status: string;
    contributions?: Contribution[];
    loans?: Loan[];
    fines?: Fine[];
    created_at?: string;
    updated_at?: string;
};

export type ContributionType = {
    id: number;
    sqid: string;
    name: string;
    description?: string | null;
    default_amount?: number | string | null;
    amount?: number | string | null;
    amount_type?: string | null;
    frequency?: string | null;
    save_to_bank: boolean;
    status?: string;
};

export type ContributionChannel = {
    id: number;
    sqid: string;
    name: string;
    description?: string | null;
    status?: string;
};

export type Contribution = {
    id: number;
    sqid: string;
    member_id: number;
    contribution_type_id: number;
    contribution_channel_id?: number | null;
    amount: number | string;
    date: string;
    reference?: string | null;
    notes?: string | null;
    member?: Member;
    contribution_type?: ContributionType;
    contributionType?: ContributionType;
    contribution_channel?: ContributionChannel;
    contributionChannel?: ContributionChannel;
    recorded_by?: User;
    recordedBy?: User;
};

export type BankAccount = {
    id: number;
    sqid: string;
    account_name: string;
    bank_name: string;
    account_number: string;
    branch?: string | null;
    opening_balance?: number | string;
    current_balance?: number | string;
    status?: string;
    transactions?: BankTransaction[];
};

export type BankTransaction = {
    id: number;
    sqid: string;
    bank_account_id: number;
    type: string;
    amount: number | string;
    date: string;
    reference?: string | null;
    description?: string | null;
};

export type CashAccount = {
    id: number;
    sqid: string;
    name: string;
    balance?: number | string;
    chart_of_account_id?: number | null;
    transactions?: CashTransaction[];
};

export type CashTransaction = {
    id: number;
    sqid: string;
    cash_account_id: number;
    type: string;
    amount: number | string;
    date: string;
    reference?: string | null;
    description?: string | null;
};

export type LoanProduct = {
    id: number;
    sqid: string;
    name: string;
    description?: string | null;
    max_amount: number | string;
    max_multiplier?: number | string | null;
    interest_type: string;
    interest_value: number | string;
    repayment_period: number;
    grace_period?: number | null;
    status: string;
};

export type LoanApplication = {
    id: number;
    sqid: string;
    member_id: number;
    loan_product_id: number;
    requested_amount: number | string;
    purpose?: string | null;
    status: string;
    review_notes?: string | null;
    member?: Member;
    loan_product?: LoanProduct;
    loanProduct?: LoanProduct;
    guarantors?: LoanGuarantor[];
    loan?: Loan;
};

export type LoanGuarantor = {
    id: number;
    sqid: string;
    member_id: number;
    member?: Member;
};

export type Loan = {
    id: number;
    sqid: string;
    member_id: number;
    loan_application_id?: number | null;
    loan_product_id?: number | null;
    product_name?: string | null;
    principal_amount: number | string;
    interest_amount?: number | string;
    total_amount?: number | string;
    outstanding_balance: number | string;
    interest_type?: string;
    interest_value?: number | string;
    repayment_period?: number;
    disbursement_date?: string | null;
    due_date?: string | null;
    status: string;
    member?: Member;
    loan_product?: LoanProduct;
    loanProduct?: LoanProduct;
    repayments?: LoanRepayment[];
};

export type LoanRepayment = {
    id: number;
    sqid: string;
    loan_id: number;
    amount: number | string;
    principal_paid?: number | string;
    interest_paid?: number | string;
    balance_after?: number | string;
    date: string;
    method?: string | null;
    reference_number?: string | null;
    notes?: string | null;
    loan?: Loan;
};

export type FineType = {
    id: number;
    sqid: string;
    name: string;
    description?: string | null;
    amount: number | string;
    status: string;
};

export type Fine = {
    id: number;
    sqid: string;
    member_id: number;
    fine_type_id: number;
    amount: number | string;
    date: string;
    reason?: string | null;
    is_paid: boolean;
    member?: Member;
    fine_type?: FineType;
    fineType?: FineType;
};

export type Meeting = {
    id: number;
    sqid: string;
    title: string;
    date: string;
    time?: string | null;
    venue?: string | null;
    agenda?: string | null;
    minutes?: string | null;
    status?: string;
    attendees?: MeetingAttendee[];
};

export type MeetingAttendee = {
    id: number;
    sqid: string;
    member_id: number;
    status: string;
    notes?: string | null;
    member?: Member;
};

export type ExpenseCategory = {
    id: number;
    sqid: string;
    name: string;
    description?: string | null;
};

export type Expense = {
    id: number;
    sqid: string;
    expense_category_id: number;
    amount: number | string;
    date: string;
    description?: string | null;
    reference?: string | null;
    category?: ExpenseCategory;
    expense_category?: ExpenseCategory;
};

export type SupportTicket = {
    id: number;
    sqid: string;
    subject: string;
    message?: string;
    status: string;
    priority?: string;
    group_id?: number | null;
    user_id?: number | null;
    group?: Group;
    user?: User;
    notes?: SupportTicketNote[];
    created_at?: string;
    updated_at?: string;
};

export type SupportTicketNote = {
    id: number;
    sqid: string;
    note: string;
    created_at?: string;
    author?: User;
};

export type SmsProvider = {
    id: number;
    sqid: string;
    name: string;
    driver: string;
    config?: Record<string, unknown>;
    is_default?: boolean;
    status: string;
};

export type SmsTemplate = {
    id: number;
    sqid: string;
    name: string;
    body: string;
    status: string;
    created_at?: string;
    updated_at?: string;
};

export type SmsMessage = {
    id: number;
    sqid: string;
    recipient: string;
    body: string;
    provider?: string | null;
    status: string;
    delivered_at?: string | null;
    error_message?: string | null;
    member_id?: number | null;
    sms_template_id?: number | null;
    member?: Member | null;
    template?: SmsTemplate | null;
    sender?: User | null;
    created_at?: string;
    updated_at?: string;
};

export type SystemSetting = {
    id: number;
    sqid: string;
    key: string;
    value: string;
    description?: string | null;
};

export type Notification = {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at?: string | null;
    created_at: string;
};

export type SharePurchase = {
    id: number;
    sqid: string;
    member_id: number;
    shares: number;
    amount: number | string;
    date: string;
    member?: Member;
};

export type ShareSetting = {
    id: number;
    sqid: string;
    share_value: number | string;
    min_shares?: number | null;
    max_shares?: number | null;
};

export type WelfareContribution = {
    id: number;
    sqid: string;
    member_id: number;
    amount: number | string;
    date: string;
    member?: Member;
};

export type WelfareDisbursement = {
    id: number;
    sqid: string;
    member_id: number;
    amount: number | string;
    date: string;
    reason?: string | null;
    member?: Member;
};

export type DividendRun = {
    id: number;
    sqid: string;
    year: number;
    total_amount: number | string;
    status: string;
    allocations?: DividendAllocation[];
};

export type DividendAllocation = {
    id: number;
    sqid: string;
    member_id: number;
    amount: number | string;
    member?: Member;
};

export type MemberOption = Pick<Member, 'id' | 'full_name' | 'membership_number'>;
