import { Link } from '@inertiajs/react';
import { useState } from 'react';
import DataTable from '@/components/shared/DataTable';
import { formatCurrency } from '@/lib/format';
import type { Contribution } from '@/types/models';

export type ContributionTypeGroup = {
    type: {
        id: number;
        name: string;
    };
    contributions_count: number;
    total_amount: number;
    contributions: Contribution[];
};

type Props = {
    groups: ContributionTypeGroup[];
    emptyMessage?: string;
    showActions?: boolean;
    showSummary?: boolean;
};

function contributionColumns(showActions: boolean) {
    return [
        {
            key: 'member',
            label: 'Member',
            render: (row: Contribution) => row.member?.full_name ?? '—',
        },
        {
            key: 'channel',
            label: 'Channel',
            render: (row: Contribution) =>
                row.contribution_channel?.name ??
                row.contributionChannel?.name ??
                '—',
        },
        {
            key: 'amount',
            label: 'Amount',
            className: 'text-end',
            render: (row: Contribution) => formatCurrency(row.amount),
        },
        ...(showActions
            ? [
                  {
                      key: 'actions',
                      label: '',
                      render: (row: Contribution) => (
                          <Link
                              href={`/portal/contributions/${row.sqid}`}
                              className="btn btn-sm btn-outline-primary"
                          >
                              View
                          </Link>
                      ),
                  },
              ]
            : []),
    ];
}

export default function ContributionsByTypeList({
    groups = [],
    emptyMessage = 'No contributions recorded.',
    showActions = true,
    showSummary = true,
}: Props) {
    const [activeTypeId, setActiveTypeId] = useState<number | null>(
        groups[0]?.type.id ?? null,
    );

    if (groups.length === 0) {
        return <p className="mb-0 text-muted">{emptyMessage}</p>;
    }

    const grandTotal = groups.reduce(
        (sum, group) => sum + group.total_amount,
        0,
    );
    const grandCount = groups.reduce(
        (sum, group) => sum + group.contributions_count,
        0,
    );
    const activeGroup =
        groups.find((group) => group.type.id === activeTypeId) ?? groups[0];
    const columns = contributionColumns(showActions);

    return (
        <div className="d-flex flex-column gap-4">
            {showSummary && (
                <div className="card border-0 shadow-sm">
                    <div className="card-header border-bottom bg-white py-3">
                        <h3 className="h6 mb-0">Totals by contribution type</h3>
                    </div>
                    <div className="table-responsive">
                        <table className="table-sm cp-table mb-0 table align-middle">
                            <thead>
                                <tr>
                                    <th>Contribution type</th>
                                    <th className="text-end">Count</th>
                                    <th className="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {groups.map((group) => {
                                    const isActive =
                                        activeGroup.type.id === group.type.id;

                                    return (
                                        <tr
                                            key={group.type.id}
                                            className={`cp-type-summary-row ${isActive ? 'is-active' : ''}`}
                                            onClick={() =>
                                                setActiveTypeId(group.type.id)
                                            }
                                            onKeyDown={(event) => {
                                                if (
                                                    event.key === 'Enter' ||
                                                    event.key === ' '
                                                ) {
                                                    event.preventDefault();
                                                    setActiveTypeId(
                                                        group.type.id,
                                                    );
                                                }
                                            }}
                                            tabIndex={0}
                                            role="button"
                                            aria-pressed={isActive}
                                        >
                                            <td className="fw-medium">
                                                {group.type.name}
                                            </td>
                                            <td className="text-end">
                                                {group.contributions_count}
                                            </td>
                                            <td className="text-end">
                                                {formatCurrency(
                                                    group.total_amount,
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                            <tfoot className="table-light">
                                <tr className="fw-semibold">
                                    <td>Grand total</td>
                                    <td className="text-end">{grandCount}</td>
                                    <td className="text-end text-primary">
                                        {formatCurrency(grandTotal)}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            )}

            <div className="card overflow-hidden border-0 shadow-sm">
                <div className="cp-type-tabs">
                    <div
                        className="cp-type-tabs__scroll"
                        role="tablist"
                        aria-label="Contribution types"
                    >
                        {groups.map((group) => {
                            const isActive =
                                activeGroup.type.id === group.type.id;

                            return (
                                <button
                                    key={group.type.id}
                                    type="button"
                                    role="tab"
                                    id={`contribution-type-tab-${group.type.id}`}
                                    aria-selected={isActive}
                                    aria-controls={`contribution-type-panel-${group.type.id}`}
                                    className={`cp-type-tabs__item ${isActive ? 'is-active' : ''}`}
                                    onClick={() =>
                                        setActiveTypeId(group.type.id)
                                    }
                                >
                                    <span className="cp-type-tabs__label">
                                        {group.type.name}
                                    </span>
                                    <span className="cp-type-tabs__meta">
                                        <span className="cp-type-tabs__count">
                                            {group.contributions_count}
                                        </span>
                                        <span className="cp-type-tabs__amount">
                                            {formatCurrency(group.total_amount)}
                                        </span>
                                    </span>
                                </button>
                            );
                        })}
                    </div>
                </div>

                <div
                    className="card-body p-0"
                    role="tabpanel"
                    id={`contribution-type-panel-${activeGroup.type.id}`}
                    aria-labelledby={`contribution-type-tab-${activeGroup.type.id}`}
                >
                    <DataTable
                        columns={columns}
                        data={activeGroup.contributions}
                        searchPlaceholder={`Search ${activeGroup.type.name.toLowerCase()} members...`}
                        emptyMessage="No contributions for this type."
                        footer={
                            <div className="d-flex justify-content-between align-items-center fw-semibold">
                                <span>
                                    {activeGroup.type.name} ·{' '}
                                    {activeGroup.contributions_count}{' '}
                                    {activeGroup.contributions_count === 1
                                        ? 'contribution'
                                        : 'contributions'}
                                </span>
                                <span className="text-primary">
                                    {formatCurrency(activeGroup.total_amount)}
                                </span>
                            </div>
                        }
                    />
                </div>
            </div>
        </div>
    );
}
