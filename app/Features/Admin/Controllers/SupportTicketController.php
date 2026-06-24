<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Requests\AddSupportTicketNoteRequest;
use App\Features\Admin\Requests\UpdateSupportTicketRequest;
use App\Features\Auth\Models\SuperAdmin;
use App\Features\Support\Models\SupportTicket;
use App\Features\Support\Models\SupportTicketNote;
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
            ->withoutGlobalScopes()
            ->with(['group', 'user'])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/support-tickets/index', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Show.
     */
    public function show(SupportTicket $supportTicket): Response
    {
        $supportTicket->load(['group', 'user', 'notes']);

        return Inertia::render('admin/support-tickets/show', [
            'ticket' => $supportTicket,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateSupportTicketRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $supportTicket->update($request->validated());

        return back()->with('success', 'Ticket updated successfully.');
    }

    /**
     * Add note.
     */
    public function addNote(AddSupportTicketNoteRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        /** @var SuperAdmin $admin */
        $admin = $request->user('super_admin');

        SupportTicketNote::create([
            'support_ticket_id' => $supportTicket->id,
            'author_type' => SuperAdmin::class,
            'author_id' => $admin->id,
            'body' => $request->validated('body'),
            'is_internal' => $request->boolean('is_internal'),
        ]);

        return back()->with('success', 'Note added successfully.');
    }
}
