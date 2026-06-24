<?php

namespace App\Features\Reports\Controllers;

use App\Features\Members\Models\Member;
use App\Features\Reports\Requests\ExportReportRequest;
use App\Features\Reports\Requests\ReportFilterRequest;
use App\Features\Reports\Services\ReportExportService;
use App\Features\Reports\Services\ReportPresenter;
use App\Features\Reports\Services\ReportService;
use App\Http\Controllers\Controller;
use App\Policies\ReportPolicy;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HTTP controller for portal reports.
 */
class ReportController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const REPORT_TYPES = [
        'contributions' => 'Contributions Report',
        'loans' => 'Active Loans Report',
        'closed_loans' => 'Closed Loans Report',
        'loan_aging' => 'Loan Aging Report',
        'loan_defaulters' => 'Loan Defaulters Report',
        'repayments' => 'Loan Repayments Report',
        'interest_earned' => 'Interest Earned Report',
        'fines' => 'Fines Report',
        'bank' => 'Bank Report',
        'cash' => 'Cash Report',
        'monthly' => 'Monthly Report',
        'annual' => 'Annual Report',
    ];

    /**
     * Create a new instance.
     */
    public function __construct(
        private ReportService $reportService,
        private ReportPresenter $reportPresenter,
        private ReportExportService $reportExportService,
    ) {}

    /**
     * List available report types.
     */
    public function index(): Response
    {
        abort_unless(app(ReportPolicy::class)->viewAny(auth()->user()), 403);

        return Inertia::render('portal/reports/index', [
            'reportTypes' => self::REPORT_TYPES,
        ]);
    }

    /**
     * Display a report with optional filters.
     */
    public function show(ReportFilterRequest $request, string $type): Response
    {
        $this->assertReportType($type);

        $raw = $this->resolveReport($type, $request);
        $data = $this->reportPresenter->present($type, $raw);

        return Inertia::render('portal/reports/show', [
            'type' => $type,
            'title' => self::REPORT_TYPES[$type],
            'data' => $data,
            'filters' => $request->validated(),
            'members' => $this->memberOptions(),
            'filterConfig' => $this->filterConfig($type),
        ]);
    }

    /**
     * Export a report in the requested format.
     */
    public function export(ExportReportRequest $request, string $type): StreamedResponse|HttpResponse
    {
        $this->assertReportType($type);

        $raw = $this->resolveReport($type, $request);
        $presented = $this->reportPresenter->present($type, $raw);
        $format = $request->validated('format', 'csv');

        return $this->reportExportService->export(
            $type,
            self::REPORT_TYPES[$type],
            $presented,
            $format,
        );
    }

    private function assertReportType(string $type): void
    {
        if (! array_key_exists($type, self::REPORT_TYPES)) {
            abort(404, 'Report type not found.');
        }
    }

    /**
     * @return list<array{id: int, label: string}>
     */
    private function memberOptions(): array
    {
        return Member::query()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'membership_number'])
            ->map(fn (Member $member): array => [
                'id' => $member->id,
                'label' => "{$member->full_name} ({$member->membership_number})",
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{dateRange: bool, period: bool, member: bool}
     */
    private function filterConfig(string $type): array
    {
        return [
            'dateRange' => in_array($type, [
                'contributions', 'loans', 'closed_loans', 'repayments',
                'interest_earned', 'fines', 'bank', 'cash',
            ], true),
            'period' => in_array($type, ['monthly', 'annual'], true),
            'member' => $type === 'contributions',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveReport(string $type, ReportFilterRequest|ExportReportRequest $request): array
    {
        $from = $request->date('from');
        $to = $request->date('to');

        return match ($type) {
            'contributions' => [
                'items' => $this->reportService->contributionsReport($request->only(['member_id', 'from', 'to'])),
            ],
            'loans' => ['items' => $this->reportService->activeLoansReport($from, $to)],
            'closed_loans' => ['items' => $this->reportService->closedLoansReport($from, $to)],
            'loan_aging' => ['items' => $this->reportService->loanAgingReport()],
            'loan_defaulters' => ['items' => $this->reportService->loanDefaultersReport()],
            'repayments' => $this->reportService->repaymentsReport($from, $to),
            'interest_earned' => $this->reportService->loanInterestEarnedReport($from, $to),
            'fines' => $this->reportService->finesReport($from, $to),
            'bank' => $this->reportService->bankReport($from, $to),
            'cash' => $this->reportService->cashReport($from, $to),
            'monthly' => $this->reportService->monthlyReport(
                (int) $request->input('year', now()->year),
                (int) $request->input('month', now()->month),
            ),
            'annual' => $this->reportService->annualReportForDisplay(
                (int) $request->input('year', now()->year),
            ),
            default => abort(404, 'Report type not found.'),
        };
    }
}
