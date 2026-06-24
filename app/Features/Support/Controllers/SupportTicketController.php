<?php

namespace App\Features\Support\Controllers;

use App\Features\Support\Models\SupportTicket;
use App\Features\Support\Requests\StoreSupportTicketRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Support Ticket.
 */
class SupportTicketController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $tickets = SupportTicket::query()
            ->with('user')
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/support-tickets/index', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/support-tickets/create');
    }

    /**
     * Store.
     */
    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        SupportTicket::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('portal.support-tickets.index')
            ->with('success', 'Support ticket submitted successfully.');
    }

    /**
     * Show.
     */
    public function show(SupportTicket $supportTicket): Response
    {
        $supportTicket->load(['user', 'notes']);

        return Inertia::render('portal/support-tickets/show', [
            'ticket' => $supportTicket,
        ]);
    }
}
