import { Form } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import { show } from '@/routes/portal/reports';

type MemberOption = { id: number; label: string };

type FilterConfig = {
    dateRange: boolean;
    period: boolean;
    member: boolean;
};

type Filters = {
    from?: string;
    to?: string;
    year?: number;
    month?: number;
    member_id?: number;
};

type Props = {
    type: string;
    filters: Filters;
    filterConfig: FilterConfig;
    members: MemberOption[];
};

const months = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

export default function ReportFilters({ type, filters, filterConfig, members }: Props) {
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 8 }, (_, index) => currentYear - index);

    return (
        <Form method="get" action={show.url({ type })} className="card border-0 shadow-sm mb-4">
            <div className="card-body">
                <div className="row g-3 align-items-end">
                    {filterConfig.dateRange && (
                        <>
                            <div className="col-md-3">
                                <FormField label="From" name="from" type="date" defaultValue={filters.from} />
                            </div>
                            <div className="col-md-3">
                                <FormField label="To" name="to" type="date" defaultValue={filters.to} />
                            </div>
                        </>
                    )}

                    {filterConfig.period && (
                        <>
                            <div className="col-md-3">
                                <label className="form-label" htmlFor="year">
                                    Year
                                </label>
                                <select
                                    id="year"
                                    name="year"
                                    className="form-select"
                                    defaultValue={filters.year ?? currentYear}
                                >
                                    {years.map((year) => (
                                        <option key={year} value={year}>
                                            {year}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            {type === 'monthly' && (
                                <div className="col-md-3">
                                    <label className="form-label" htmlFor="month">
                                        Month
                                    </label>
                                    <select
                                        id="month"
                                        name="month"
                                        className="form-select"
                                        defaultValue={filters.month ?? new Date().getMonth() + 1}
                                    >
                                        {months.map((month) => (
                                            <option key={month.value} value={month.value}>
                                                {month.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}
                        </>
                    )}

                    {filterConfig.member && (
                        <div className="col-md-4">
                            <label className="form-label" htmlFor="member_id">
                                Member
                            </label>
                            <select
                                id="member_id"
                                name="member_id"
                                className="form-select"
                                defaultValue={filters.member_id ?? ''}
                            >
                                <option value="">All members</option>
                                {members.map((member) => (
                                    <option key={member.id} value={member.id}>
                                        {member.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}

                    <div className="col-md-auto">
                        <button type="submit" className="btn btn-primary">
                            <i className="bi bi-funnel me-1" />
                            Apply filters
                        </button>
                    </div>
                </div>
            </div>
        </Form>
    );
}
