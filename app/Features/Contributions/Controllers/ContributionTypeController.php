<?php

namespace App\Features\Contributions\Controllers;

use App\Features\Contributions\Models\ContributionType;
use App\Features\Contributions\Requests\StoreContributionTypeRequest;
use App\Features\Contributions\Requests\UpdateContributionTypeRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Contribution Type.
 */
class ContributionTypeController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $types = ContributionType::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/contribution-types/index', [
            'types' => $types,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/contribution-types/create');
    }

    /**
     * Store.
     */
    public function store(StoreContributionTypeRequest $request): RedirectResponse
    {
        ContributionType::create($request->validated());

        return redirect()->route('portal.contribution-types.index')
            ->with('success', 'Contribution type created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(ContributionType $contributionType): Response
    {
        return Inertia::render('portal/contribution-types/edit', [
            'type' => $contributionType,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateContributionTypeRequest $request, ContributionType $contributionType): RedirectResponse
    {
        $contributionType->update($request->validated());

        return redirect()->route('portal.contribution-types.index')
            ->with('success', 'Contribution type updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(ContributionType $contributionType): RedirectResponse
    {
        $contributionType->delete();

        return redirect()->route('portal.contribution-types.index')
            ->with('success', 'Contribution type deleted successfully.');
    }
}
