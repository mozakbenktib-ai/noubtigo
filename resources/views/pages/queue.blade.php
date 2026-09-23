@extends('layouts.dashboard')

@section('title', __('ui.real_time_queue') . ' | Noubtigo')
@section('header_title', __('ui.real_time_queue'))
@section('header_subtitle', __('ui.track_waiting_times'))

@section('content')
<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addTicketModal">
        <i class="bi bi-ticket-perforated me-2"></i> {{ __('ui.add_ticket') }}
    </button>
</div>

<!-- Queue Area -->
<div class="row g-4 fade-in">
    <!-- Active Queue Table -->
    <div class="col-lg-12">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">{{ __('ui.waiting_list') }}</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small">{{ __('ui.active') }}: 12</span>
                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill small">{{ __('ui.waiting') }}: 8</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-secondary small text-uppercase">
                        <tr>
                            <th># {{ __('ui.ticket') }}</th>
                            <th>{{ __('ui.customer') }}</th>
                            <th>{{ __('ui.service') }}</th>
                            <th>{{ __('ui.rooms') }}</th>
                            <th>{{ __('ui.status') }}</th>
                            <th>{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="fw-bold text-success">#A124</span></td>
                            <td>{{ __('ui.sample_customer_one') }}</td>
                            <td>{{ __('ui.sample_service_one') }}</td>
                            <td>{{ __('ui.sample_room_a') }}</td>
                            <td><span class="badge bg-success-subtle text-success">{{ __('ui.active') }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> {{ __('ui.done') }}</button>
                                    <button class="btn btn-light btn-sm"><i class="bi bi-three-dots"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="fw-bold text-success">#A125</span></td>
                            <td>{{ __('ui.sample_customer_two') }}</td>
                            <td>{{ __('ui.sample_service_two') }}</td>
                            <td>{{ __('ui.sample_room_b') }}</td>
                            <td><span class="badge bg-warning-subtle text-warning">{{ __('ui.waiting') }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-telephone me-1"></i> {{ __('ui.call') }}</button>
                                    <button class="btn btn-light btn-sm"><i class="bi bi-three-dots"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Ticket -->
<div class="modal fade" id="addTicketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0 p-4">
                <h5 class="fw-bold mb-0">{{ __('ui.issue_new_ticket') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Add Ticket Form Placeholder -->
                <p class="text-center text-secondary small">{{ __('ui.coming_soon') }}...</p>
            </div>
        </div>
    </div>
</div>
@endsection
