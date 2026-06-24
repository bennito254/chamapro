import { Head, Link, router } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import DataTable from '@/components/shared/DataTable';
import PageHeader from '@/components/shared/PageHeader';
import { loanStatusFilterOptions } from '@/lib/form-options';
import { formatCurrency, formatDate, titleCase } from '@/lib/format';
import { index as loansIndex } from '@/routes/portal/loans';
import type { Loan } from '@/types/models';
import type { Paginated } from '@/types/pagination';

type Filters = {
    search: string;
    status: string;
};

type Props = {
    loans: Paginated<Loan>;
    filters: Filters;
};

function statusBadge(status: string) {
    const map: Record<string, string> = {
        active: 'bg-success',
        closed: 'bg-secondary',
        defaulted: 'bg-danger',
    };

    return (
        <span className={`badge ${map[status] ?? 'bg-secondary'}`}>
            {titleCase(status)}
        </span>
    );
}

export default function Page({ loans, filters }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();

        router.get(
            loansIndex.url({
                query: {
                    search: search || undefined,
                    status: status !== 'all' ? status : undefined,
                },
            }),
            {},
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('all');
        router.get(
            loansIndex.url(),
            {},
            { preserveState: true, replace: true },
        );
    };

    const hasFilters = filters.search !== '' || filters.status !== 'all';

    return (
        <>
            <Head title="Loans" />
            <PageHeader
                title="Loans"
                description="Search and filter active and closed member loans."
            />

            <div className="card mb-4 border-0 shadow-sm">
                <div className="card-body">
                    <form
                        onSubmit={applyFilters}
                        className="row g-3 align-items-end"
                    >
                        <div className="col-md-5">
                            <label htmlFor="loan-search" className="form-label">
                                Search
                            </label>
                            <div className="input-group">
                                <span className="input-group-text bg-white">
                                    <i className="bi bi-search" />
                                </span>
                                <input
                                    id="loan-search"
                                    type="search"
                                    className="form-control"
                                    placeholder="Member name, membership #, or product..."
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                />
                            </div>
                        </div>
                        <div className="col-md-3">
                            <label htmlFor="loan-status" className="form-label">
                                Status
                            </label>
                            <select
                                id="loan-status"
                                className="form-select"
                                value={status}
                                onChange={(event) =>
                                    setStatus(event.target.value)
                                }
                            >
                                {loanStatusFilterOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="col-md-4 d-flex gap-2">
                            <button type="submit" className="btn btn-primary">
                                Apply filters
                            </button>
                            {hasFilters && (
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    onClick={clearFilters}
                                >
                                    Clear
                                </button>
                            )}
                        </div>
                    </form>
                </div>
            </div>

            <DataTable
                columns={[
                    {
                        key: 'member',
                        label: 'Member',
                        render: (row) => (
                            <div>
                                <div className="fw-medium">
                                    {row.member?.full_name ?? '—'}
                                </div>
                                <small className="text-muted">
                                    {row.member?.membership_number}
                                </small>
                            </div>
                        ),
                    },
                    {
                        key: 'product',
                        label: 'Product',
                        render: (row) =>
                            row.product_name ?? row.loanProduct?.name ?? '—',
                    },
                    {
                        key: 'principal_amount',
                        label: 'Principal',
                        className: 'text-end',
                        render: (row) => formatCurrency(row.principal_amount),
                    },
                    {
                        key: 'outstanding_balance',
                        label: 'Outstanding',
                        className: 'text-end',
                        render: (row) =>
                            formatCurrency(row.outstanding_balance),
                    },
                    {
                        key: 'disbursement_date',
                        label: 'Disbursed',
                        render: (row) => formatDate(row.disbursement_date),
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        render: (row) => statusBadge(row.status),
                    },
                    {
                        key: 'actions',
                        label: '',
                        render: (row) => (
                            <Link
                                href={`/portal/loans/${row.sqid}`}
                                className="btn btn-sm btn-outline-primary"
                            >
                                View
                            </Link>
                        ),
                    },
                ]}
                data={loans}
                searchable={false}
                emptyMessage={
                    hasFilters
                        ? 'No loans match your filters.'
                        : 'No loans recorded yet.'
                }
            />
        </>
    );
}
