<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tickets.view') || $user->can('tickets.view-all');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->can('tickets.view-all')) {
            return true;
        }

        return $user->can('tickets.view')
            && ($ticket->customer_id === $user->id || $ticket->agent_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('tickets.create');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.update')
            && in_array($ticket->status, [TicketStatus::Open, TicketStatus::InProgress], true)
            && ($ticket->customer_id === $user->id || $ticket->agent_id === $user->id);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.assign')
            && in_array($ticket->status, [TicketStatus::Open, TicketStatus::InProgress], true);
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.close')
            && $ticket->status === TicketStatus::Resolved
            && ($ticket->agent_id === $user->id || $ticket->customer_id === $user->id);
    }
}
