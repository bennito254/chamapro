<?php

namespace App\Features\Shares\Controllers;

use App\Features\Members\Models\Member;
use App\Features\Shares\Models\SharePurchase;
use App\Features\Shares\Models\ShareSetting;
use App\Features\Shares\Requests\StoreSharePurchaseRequest;
use App\Features\Shares\Services\ShareService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Share.
 */
class ShareController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private ShareService $shareService) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $purchases = SharePurchase::query()
            ->with('member')
            ->latest('date')
            ->paginate(15);

        return Inertia::render('portal/shares/index', [
            'purchases' => $purchases,
            'settings' => ShareSetting::first(),
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
        ]);
    }

    /**
     * Store purchase.
     */
    public function storePurchase(StoreSharePurchaseRequest $request): RedirectResponse
    {
        $this->shareService->purchase([
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Share purchase recorded successfully.');
    }
}
