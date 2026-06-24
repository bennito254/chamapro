<?php

namespace App\Features\Contributions\Controllers;

use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Requests\StoreContributionChannelRequest;
use App\Features\Contributions\Requests\UpdateContributionChannelRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Contribution Channel.
 */
class ContributionChannelController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $channels = ContributionChannel::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/contribution-channels/index', [
            'channels' => $channels,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/contribution-channels/create');
    }

    /**
     * Store.
     */
    public function store(StoreContributionChannelRequest $request): RedirectResponse
    {
        ContributionChannel::create($request->validated());

        return redirect()->route('portal.contribution-channels.index')
            ->with('success', 'Contribution channel created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(ContributionChannel $contributionChannel): Response
    {
        return Inertia::render('portal/contribution-channels/edit', [
            'channel' => $contributionChannel,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateContributionChannelRequest $request, ContributionChannel $contributionChannel): RedirectResponse
    {
        $contributionChannel->update($request->validated());

        return redirect()->route('portal.contribution-channels.index')
            ->with('success', 'Contribution channel updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(ContributionChannel $contributionChannel): RedirectResponse
    {
        $contributionChannel->delete();

        return redirect()->route('portal.contribution-channels.index')
            ->with('success', 'Contribution channel deleted successfully.');
    }
}
