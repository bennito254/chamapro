<?php

namespace App\Features\Fines\Controllers;

use App\Features\Fines\Models\Fine;
use App\Features\Fines\Models\FineType;
use App\Features\Fines\Requests\StoreFineRequest;
use App\Features\Fines\Requests\UpdateFineRequest;
use App\Features\Fines\Services\FineService;
use App\Features\Members\Models\Member;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Fine.
 */
class FineController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private FineService $fineService) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $fines = Fine::query()
            ->with(['member', 'fineType'])
            ->latest('date')
            ->paginate(15);

        return Inertia::render('portal/fines/index', [
            'fines' => $fines,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/fines/create', [
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
            'fineTypes' => FineType::where('status', 'active')->get(),
        ]);
    }

    /**
     * Store.
     */
    public function store(StoreFineRequest $request): RedirectResponse
    {
        $this->fineService->create([
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('portal.fines.index')
            ->with('success', 'Fine recorded successfully.');
    }

    /**
     * Show.
     */
    public function show(Fine $fine): Response
    {
        $fine->load(['member', 'fineType', 'recordedBy']);

        return Inertia::render('portal/fines/show', [
            'fine' => $fine,
        ]);
    }

    /**
     * Edit.
     */
    public function edit(Fine $fine): Response
    {
        return Inertia::render('portal/fines/edit', [
            'fine' => $fine,
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
            'fineTypes' => FineType::where('status', 'active')->get(),
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateFineRequest $request, Fine $fine): RedirectResponse
    {
        $fine->update($request->validated());

        return redirect()->route('portal.fines.show', $fine)
            ->with('success', 'Fine updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(Fine $fine): RedirectResponse
    {
        $fine->delete();

        return redirect()->route('portal.fines.index')
            ->with('success', 'Fine deleted successfully.');
    }

    /**
     * Pay.
     */
    public function pay(Fine $fine): RedirectResponse
    {
        $this->fineService->recordPayment($fine, request()->user()->id);

        return back()->with('success', 'Fine payment recorded successfully.');
    }
}
