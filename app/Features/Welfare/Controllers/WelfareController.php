<?php

namespace App\Features\Welfare\Controllers;

use App\Features\Members\Models\Member;
use App\Features\Welfare\Models\WelfareContribution;
use App\Features\Welfare\Models\WelfareDisbursement;
use App\Features\Welfare\Requests\StoreWelfareContributionRequest;
use App\Features\Welfare\Requests\StoreWelfareDisbursementRequest;
use App\Features\Welfare\Services\WelfareService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Welfare.
 */
class WelfareController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private WelfareService $welfareService) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $contributions = WelfareContribution::query()
            ->with('member')
            ->latest('date')
            ->paginate(10, ['*'], 'contributions_page');

        $disbursements = WelfareDisbursement::query()
            ->with('member')
            ->latest('date')
            ->paginate(10, ['*'], 'disbursements_page');

        return Inertia::render('portal/welfare/index', [
            'contributions' => $contributions,
            'disbursements' => $disbursements,
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
        ]);
    }

    /**
     * Store contribution.
     */
    public function storeContribution(StoreWelfareContributionRequest $request): RedirectResponse
    {
        $this->welfareService->recordContribution([
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Welfare contribution recorded successfully.');
    }

    /**
     * Store disbursement.
     */
    public function storeDisbursement(StoreWelfareDisbursementRequest $request): RedirectResponse
    {
        $this->welfareService->recordDisbursement([
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Welfare disbursement recorded successfully.');
    }
}
