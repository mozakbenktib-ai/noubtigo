<?php

namespace App\Modules\Queue\Events;

use App\Modules\Queue\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    /**
     * Create a new event instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket->load(['customer', 'service', 'room']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('queue.company.' . $this->ticket->company_id),
        ];
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'id'            => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'status'        => $this->ticket->status,
                'room_id'       => $this->ticket->room_id,
                'room_name'     => $this->ticket->room?->name,
                'customer_name' => $this->ticket->customer?->full_name ?? 'Guest',
                'service_name'  => $this->ticket->service?->name,
                'is_vip'        => $this->ticket->is_vip,
                'called_at'     => $this->ticket->called_at?->toISOString(),
                'started_at'    => $this->ticket->started_at?->toISOString(),
                'finished_at'   => $this->ticket->finished_at?->toISOString(),
            ],
        ];
    }
}
