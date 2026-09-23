<div class="history-table-wrapper fade-in">
    @if($tickets->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox d-block mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
            <p class="text-muted mb-0">{{ __('ui.no_tickets_found') }}</p>
        </div>
    @else
        <table class="history-table">
            <thead>
                <tr>
                    <th class="sortable text-nowrap cursor-pointer" data-sort="ticket_number" style="cursor: pointer;">
                        {{ __('ui.ticket_number') }}
                        <span class="sort-icon" id="sort-icon-ticket_number"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                    </th>
                    <th class="sortable text-nowrap cursor-pointer" data-sort="customer" style="cursor: pointer;">
                        {{ __('ui.customer') }}
                        <span class="sort-icon" id="sort-icon-customer"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                    </th>
                    <th class="sortable text-nowrap cursor-pointer" data-sort="service" style="cursor: pointer;">
                        {{ __('ui.service') }}
                        <span class="sort-icon" id="sort-icon-service"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                    </th>
                    <th class="sortable text-nowrap cursor-pointer" data-sort="status" style="cursor: pointer;">
                        {{ __('ui.status') }}
                        <span class="sort-icon" id="sort-icon-status"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                    </th>
                    <th class="sortable text-nowrap cursor-pointer" data-sort="created_at" style="cursor: pointer;">
                        {{ __('ui.date') }}
                        <span class="sort-icon" id="sort-icon-created_at"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                    </th>
                    <th class="text-nowrap">{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ticket-avatar" style="width:32px;height:32px;font-size:.75rem;">
                                    {{ current(explode('-', $ticket->ticket_number)) }}
                                </div>
                                <strong>{{ $ticket->ticket_number }}</strong>
                                @if($ticket->is_vip) <span class="vip-badge">{{ __('ui.vip') }}</span> @endif
                            </div>
                        </td>
                        <td>
                            <span>{{ $ticket->customer->full_name ?? __('ui.guest_customer') }}</span>
                        </td>
                        <td>
                            <span class="text-secondary">{{ $ticket->service?->name ?? '—' }}</span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($ticket->status) {
                                    'waiting' => 'waiting',
                                    'called' => 'called',
                                    'serving' => 'serving',
                                    'done' => 'done',
                                    'cancelled' => 'cancelled',
                                    'no_show' => 'no_show',
                                    'hold_cancelled' => 'cancelled',
                                    default => 'waiting',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
                                {{ __('ui.status_' . $ticket->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-secondary small">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" onclick="event.stopPropagation();">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ── Pagination ────────────────────────────────────────────────────── --}}
@if($tickets->hasPages())
    <div class="d-flex justify-content-center mt-4" id="pagination-wrapper">
        {{ $tickets->withQueryString()->links() }}
    </div>
@endif
