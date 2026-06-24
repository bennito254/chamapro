<?php

namespace App\Features\Fines\Controllers;

use App\Features\Fines\Models\FineType;
use App\Features\Fines\Requests\StoreFineTypeRequest;
use App\Features\Fines\Requests\UpdateFineTypeRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Fine Type.
 */
class FineTypeController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $fineTypes = FineType::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/fine-types/index', [
            'fineTypes' => $fineTypes,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/fine-types/create');
    }

    /**
     * Store.
     */
    public function store(StoreFineTypeRequest $request): RedirectResponse
    {
        FineType::create($request->validated());

        return redirect()->route('portal.fine-types.index')
            ->with('success', 'Fine type created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(FineType $fineType): Response
    {
        return Inertia::render('portal/fine-types/edit', [
            'fineType' => $fineType,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateFineTypeRequest $request, FineType $fineType): RedirectResponse
    {
        $fineType->update($request->validated());

        return redirect()->route('portal.fine-types.index')
            ->with('success', 'Fine type updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(FineType $fineType): RedirectResponse
    {
        $fineType->delete();

        return redirect()->route('portal.fine-types.index')
            ->with('success', 'Fine type deleted successfully.');
    }
}
