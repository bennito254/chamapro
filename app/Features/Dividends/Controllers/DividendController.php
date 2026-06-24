<?php

namespace App\Features\Dividends\Controllers;

use App\Features\Dividends\Models\DividendRun;
use App\Features\Dividends\Requests\StoreDividendRequest;
use App\Features\Dividends\Services\DividendService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Dividend.
 */
class DividendController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private DividendService $dividendService) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $runs = DividendRun::query()
            ->with('allocations.member')
            ->latest('year')
            ->paginate(15);

        return Inertia::render('portal/dividends/index', [
            'runs' => $runs,
            'formula' => $this->dividendService->defaultFormula(),
        ]);
    }

    /**
     * Store.
     */
    public function store(StoreDividendRequest $request): RedirectResponse
    {
        $this->dividendService->run(
            $request->validated('year'),
            $request->validated('total_profit'),
            $request->user()->id,
        );

        return back()->with('success', 'Dividend run completed successfully.');
    }
}
