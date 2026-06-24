import { Head, Link } from '@inertiajs/react';
import ContributionsByTypeList, {
    type ContributionTypeGroup,
} from '@/components/shared/ContributionsByTypeList';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency, formatDate } from '@/lib/format';
import type { Meeting } from '@/types/models';

type Props = {
    date: string;
    meeting: Meeting | null;
    contributionGroups: ContributionTypeGroup[];
    summary: {
        contributions_count: number;
        total_amount: number;
        types_count: number;
    };
};

export default function Page({ date, meeting, contributionGroups, summary }: Props) {
    const title = meeting?.title ? `${meeting.title} — ${formatDate(date)}` : `Contributions — ${formatDate(date)}`;

    return (
        <>
            <Head title={title} />
            <PageHeader
                title={title}
                description={
                    meeting
                        ? `Contributions for this meeting, separated by type.`
                        : `Contributions for ${formatDate(date)}, separated by type.`
                }
                actions={
                    <Link href="/portal/contributions" className="btn btn-outline-secondary btn-sm">
                        <i className="bi bi-arrow-left me-1" />
                        Back to dates
                    </Link>
                }
            />

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-lg-3">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="text-muted small">Meeting Date</div>
                            <div className="fs-5 fw-semibold">{formatDate(date)}</div>
                        </div>
                    </div>
                </div>
                <div className="col-sm-6 col-lg-3">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="text-muted small">Contribution Types</div>
                            <div className="fs-5 fw-semibold">{summary.types_count}</div>
                        </div>
                    </div>
                </div>
                <div className="col-sm-6 col-lg-3">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="text-muted small">Contributions</div>
                            <div className="fs-5 fw-semibold">{summary.contributions_count}</div>
                        </div>
                    </div>
                </div>
                <div className="col-sm-6 col-lg-3">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <div className="text-muted small">Total Collected</div>
                            <div className="fs-5 fw-semibold">{formatCurrency(summary.total_amount)}</div>
                        </div>
                    </div>
                </div>
            </div>

            {meeting && (
                <div className="alert alert-light border mb-4">
                    <div className="fw-semibold">{meeting.title}</div>
                    <Link href={`/portal/meetings/${meeting.sqid}`} className="small">
                        View full meeting summary
                    </Link>
                </div>
            )}

            <ContributionsByTypeList
                groups={contributionGroups}
                emptyMessage="No contributions for this meeting date."
            />
        </>
    );
}
