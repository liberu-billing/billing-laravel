<?php

namespace App\Http\Controllers;

use App\Actions\CreateProjectFromTicket;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    public function index(): Factory|View
    {
        $user = auth()->user();

        $tickets = $user->hasRole('super_admin') || $user->hasRole('admin')
            ? Ticket::with('user')->latest()->paginate(10)
            : $user->tickets()->latest()->paginate(10);

        return view(
            'tickets.index',
            compact('tickets')
        );
    }

    public function create(): Factory|View
    {
        return view('tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'description' => [
                    'required',
                    'string',
                ],
                'priority' => [
                    'required',
                    'in:low,medium,high',
                ],
            ]
        );

        $ticket = Ticket::create(
            [
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
            ]
        );

        $admins = User::role(
            [
                'admin',
                'super_admin',
            ]
        )->get();
        Notification::send(
            $admins,
            new NewTicketNotification($ticket)
        );

        return redirect()->route(
            'tickets.show',
            $ticket
        )
            ->with(
                'success',
                'Ticket created successfully.'
            );
    }

    public function show(Ticket $ticket): Factory|View
    {
        $this->authorize(
            'view',
            $ticket
        );
        $ticket->load(
            [
                'responses.user',
                'user',
            ]
        );

        return view(
            'tickets.show',
            compact('ticket')
        );
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize(
            'update',
            $ticket
        );

        $validated = $request->validate(
            [
                'status' => [
                    'required',
                    'in:open,in_progress,closed',
                ],
            ]
        );

        $ticket->update($validated);

        return redirect()->back()->with(
            'success',
            'Ticket status updated.'
        );
    }

    public function createProject(
        Request $request,
        Ticket $ticket,
        CreateProjectFromTicket $action
    ): RedirectResponse {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customer = isset($validated['customer_id'])
            ? Customer::find($validated['customer_id'])
            : null;

        try {
            $project = $action($ticket, $customer);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['customer_id' => $e->getMessage()]);
        }

        return redirect()->back()->with(
            'success',
            "Project #{$project->id} created from ticket."
        );
    }
}
