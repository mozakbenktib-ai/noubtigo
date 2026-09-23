@props(['timeline'])

<div class="activity-timeline">
    @foreach($timeline as $entry)
        @php
            // Determine dot class based on action + status
            $dotClass = $entry->action;
            if ($entry->action === 'status_changed' && isset($entry->changes['after']['status'])) {
                $dotClass = 'status_changed_' . $entry->changes['after']['status'];
            }
            if ($entry->action === 'ticket_on_hold') {
                $dotClass = 'status_changed_on_hold';
            }
            if ($entry->action === 'ticket_resumed') {
                $dotClass = 'status_changed_serving';
            }

            // Icon per action
            $icon = match($entry->action) {
                'created' => 'bi-plus-circle-fill',
                'updated' => 'bi-pencil-fill',
                'deleted' => 'bi-trash-fill',
                'status_changed' => match($entry->changes['after']['status'] ?? '') {
                    'called' => 'bi-megaphone-fill',
                    'serving' => 'bi-person-fill-gear',
                    'done' => 'bi-check-circle-fill',
                    'cancelled' => 'bi-x-circle-fill',
                    'no_show' => 'bi-exclamation-circle-fill',
                    'on_hold' => 'bi-pause-circle-fill',
                    default => 'bi-arrow-right-circle-fill',
                },
                'ticket_on_hold' => 'bi-pause-circle-fill',
                'ticket_resumed' => 'bi-play-circle-fill',
                default => 'bi-circle-fill',
            };

            $entryDescription = $entry->description;
            if ($entry->action === 'created' && $entry->model_type === 'Ticket') {
                $status = $entry->changes['after']['status'] ?? null;
                $entryDescription = __('ui.activity_ticket_created', [
                    'ticket' => $entry->model_label,
                    'status' => $status ? __('ui.status_' . $status) : '',
                ]);
            } elseif ($entry->action === 'status_changed') {
                $from = $entry->changes['before']['status'] ?? '';
                $to = $entry->changes['after']['status'] ?? '';
                $entryDescription = __('ui.activity_status_changed', [
                    'from' => $from ? __('ui.status_' . $from) : '',
                    'to' => $to ? __('ui.status_' . $to) : '',
                ]);
            }
        @endphp

        <div class="timeline-entry">
            <div class="timeline-entry-dot {{ $dotClass }}">
                <i class="bi {{ $icon }}"></i>
            </div>
            <div class="timeline-entry-content">
                <div class="timeline-entry-title">{{ $entryDescription }}</div>
                <div class="timeline-entry-meta">
                    <span><i class="bi bi-clock me-1"></i>{{ $entry->created_at->format('H:i:s') }}</span>
                    @if($entry->user)
                        <span><i class="bi bi-person me-1"></i>{{ $entry->user->full_name }}</span>
                    @endif
                    <span class="text-muted">{{ $entry->created_at->diffForHumans() }}</span>
                </div>

                @if($entry->changes && (isset($entry->changes['before']) || isset($entry->changes['after'])))
                    <div class="timeline-changes mt-2">
                        @php
                            $before = $entry->changes['before'] ?? [];
                            $after = $entry->changes['after'] ?? [];
                            $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
                        @endphp
                        
                        @foreach($keys as $key)
                            @if(str_ends_with($key, '_id')) @continue @endif
                            @if(($before[$key] ?? '') != ($after[$key] ?? ''))
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-light text-dark border small">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="text-danger text-decoration-line-through small">{{ is_array($before[$key] ?? '') ? json_encode($before[$key]) : ($before[$key] ?? '—') }}</span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="text-success fw-bold small">{{ is_array($after[$key] ?? '') ? json_encode($after[$key]) : ($after[$key] ?? '—') }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
