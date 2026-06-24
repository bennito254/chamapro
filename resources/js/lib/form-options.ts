export const loanRepaymentTypeOptions = [
    { value: 'combined', label: 'Partial or full (interest first)' },
    { value: 'interest', label: 'Interest only' },
    { value: 'principal', label: 'Principal only' },
];

export const loanStatusFilterOptions = [
    { value: 'all', label: 'All statuses' },
    { value: 'active', label: 'Active' },
    { value: 'closed', label: 'Closed' },
    { value: 'defaulted', label: 'Defaulted' },
];

export const interestTypeOptions = [
    { value: 'percentage', label: 'Percentage' },
    { value: 'fixed', label: 'Fixed amount' },
];

export const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

export const amountTypeOptions = [
    { value: 'fixed', label: 'Fixed' },
    { value: 'variable', label: 'Variable' },
];

export const contributionFrequencyOptions = [
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'quarterly', label: 'Quarterly' },
    { value: 'annual', label: 'Annual' },
    { value: 'one_time', label: 'One Time' },
];
