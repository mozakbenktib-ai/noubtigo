@extends('layouts.dashboard')

@section('title', __('ui.displays') . ' | Noubtigo')
@section('header_title', __('ui.displays'))
@section('header_subtitle', __('ui.displays_subtitle', ['host' => request()->getHost()]))

@section('content')
<!-- Top Navigation Tabs: Displays vs Display Content -->
<div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
    <ul class="nav nav-pills gap-2">
        <li class="nav-item">
            <a class="nav-link active bg-primary text-white fw-bold px-4 py-2 rounded-pill shadow-sm" href="{{ route('displays.index') }}">
                <i class="bi bi-tv me-2"></i> {{ __('ui.displays') ?? 'Displays & Screens' }}
            </a>
        </li>
        @permission('display_content.view')
        <li class="nav-item">
            <a class="nav-link text-secondary fw-semibold px-4 py-2 rounded-pill" href="{{ route('displays.contents.index') }}">
                <i class="bi bi-file-earmark-richtext-fill me-2"></i> {{ __('ui.display_content') }}
            </a>
        </li>
        @endpermission
    </ul>

    @permission('display_content.create')
    <div>
        <a href="{{ route('displays.contents.create') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i> {{ __('ui.new_display_content') }}
        </a>
    </div>
    @endpermission
</div>

<div class="row g-4">
    <!-- Pair New Device Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-plus-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ __('ui.pair_new_display') }}</h5>
                        <p class="text-secondary small mb-0">{{ __('ui.pair_display_description') }}</p>
                    </div>
                </div>

                @if(isset($canCreateDisplay) && !$canCreateDisplay)
                    <div class="alert alert-warning border-0 rounded-3 small p-3 mb-4 d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>{{ __('ui.display_limit_reached') ?? 'Display Limit Reached' }}</strong>
                            <p class="mb-2 mt-1">{{ __('ui.display_limit_reached_desc') ?? "Your plan's display limit has been reached. Please upgrade your plan to register more displays." }}</p>
                            @if(Route::has('company.billing'))
                            <a href="{{ route('company.billing') }}" class="btn btn-sm btn-warning fw-bold rounded-pill text-dark text-decoration-none">
                                <i class="bi bi-arrow-up-circle-fill me-1"></i> {{ __('ui.upgrade_plan') ?? 'Upgrade Plan' }}
                            </a>
                            @endif
                        </div>
                    </div>
                @endif

                <form action="{{ route('displays.store') }}" method="POST">
                    @csrf
                    <fieldset {{ (isset($canCreateDisplay) && !$canCreateDisplay) ? 'disabled' : '' }}>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.room_name') }}</label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3" placeholder="{{ __('ui.display_name_example') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.assigned_room') }} ({{ __('ui.auto_detect') }})</label>
                        <select name="room_id" class="form-select form-select-lg rounded-3">
                            <option value="">{{ __('ui.any_room') }}</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.display_content') }}</label>
                        <select name="show_type" class="form-select form-select-lg rounded-3">
                            <option value="both">{{ __('ui.display_show_both') }}</option>
                            <option value="walk_in">{{ __('ui.display_show_walk_in') }}</option>
                            <option value="appointment">{{ __('ui.display_show_appointment') }}</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold">{{ __('ui.display_theme') ?? 'Theme' }}</label>
                            <select name="theme" class="form-select rounded-3">
                                <option value="dark">{{ __('ui.theme_dark') ?? 'Dark Mode' }}</option>
                                <option value="light">{{ __('ui.theme_light') ?? 'Light Mode' }}</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">{{ __('ui.display_language') ?? 'Language' }}</label>
                            <select name="language" class="form-select rounded-3">
                                <option value="">{{ __('ui.default_language') ?? 'Default' }}</option>
                                <option value="en">English</option>
                                <option value="ar">العربية (Arabic)</option>
                                <option value="fr">Français (French)</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm" {{ (isset($canCreateDisplay) && !$canCreateDisplay) ? 'disabled' : '' }}>
                        {{ __('ui.add_display') }}
                    </button>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Devices Table -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0">{{ __('ui.your_displays') }}</h5>
                    <span class="badge {{ (isset($canCreateDisplay) && !$canCreateDisplay) ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary' }} border rounded-pill px-3 py-2 small">
                        {{ count($devices) }} / {{ (isset($displayLimit) && $displayLimit !== null) ? ($displayLimit == -1 ? '∞' : $displayLimit) : '∞' }} {{ __('ui.screens') ?? 'Screens' }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.device') }}</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.assigned_to') }}</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.signage_content') }}</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.status') }}</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.shows') }}</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.pairing_code') }}</th>
                                <th class="py-3 text-secondary small fw-bold text-uppercase text-end pe-4">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($devices as $device)
                                <tr>
                                    <td class="ps-4 py-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-3">
                                                <i class="bi bi-display text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $device->name }}</div>
                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                    <span class="badge bg-light text-secondary border x-small">
                                                        <i class="bi {{ ($device->theme ?? 'dark') === 'light' ? 'bi-sun-fill text-warning' : 'bi-moon-stars-fill text-primary' }} me-1"></i>
                                                        {{ ucfirst($device->theme ?? 'dark') }}
                                                    </span>
                                                    @if(!empty($device->language))
                                                    <span class="badge bg-light text-secondary border x-small text-uppercase">
                                                        <i class="bi bi-translate me-1 text-info"></i>{{ $device->language }}
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="text-secondary x-small mt-1">ID: {{ substr($device->uid, 0, 8) }}...</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($device->room)
                                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                                                <i class="bi bi-door-open me-1"></i> {{ $device->room->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                                <i class="bi bi-globe me-1"></i> {{ __('ui.global') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $specific = $contentStats['devices'][$device->id] ?? 0;
                                            $global = $contentStats['global_count'] ?? 0;
                                            $total = $global + $specific;
                                        @endphp
                                        @permission('display_content.view')
                                        <a href="{{ route('displays.contents.index') }}" class="text-decoration-none">
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                                                <i class="bi bi-collection-play me-1"></i> {{ $total }} {{ __('ui.available') }}
                                            </span>
                                            <div class="text-muted x-small mt-1">{{ $global }} {{ __('ui.global') }} + {{ $specific }} {{ __('ui.specific') }}</div>
                                        </a>
                                        @else
                                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                            <i class="bi bi-collection-play me-1"></i> {{ $total }} {{ __('ui.available') }}
                                        </span>
                                        <div class="text-muted x-small mt-1">{{ $global }} {{ __('ui.global') }} + {{ $specific }} {{ __('ui.specific') }}</div>
                                        @endpermission
                                    </td>
                                    <td>
                                        @if($device->isPaired())
                                            <span class="text-success small fw-bold d-flex align-items-center gap-1">
                                                <i class="bi bi-check-circle-fill"></i> {{ __('ui.paired') }}
                                            </span>
                                            @if($device->hasActiveSession())
                                                <div class="mt-1">
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill x-small d-inline-flex align-items-center gap-1">
                                                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 0.4rem; height: 0.4rem;"></span>
                                                        {{ __('ui.display_session_active') ?? 'Live Screen Active' }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="mt-1">
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill x-small">
                                                        {{ __('ui.display_session_idle') ?? 'Idle / Offline' }}
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-warning small fw-bold d-flex align-items-center gap-1">
                                                <i class="bi bi-lock-fill"></i> {{ __('ui.locked') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->show_type === 'walk_in')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill small">{{ __('ui.source_walk_in') }}</span>
                                        @elseif($device->show_type === 'appointment')
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill small">{{ __('ui.source_appointment') }}</span>
                                        @else
                                            <span class="badge bg-dark bg-opacity-10 text-dark px-2 py-1 rounded-pill small">{{ __('ui.active') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$device->isPaired())
                                            <div class="bg-dark text-primary px-3 py-2 rounded-3 d-inline-block fw-bold fs-5 font-monospace border border-primary border-opacity-25" title="{{ __('ui.display_pin_help') }}">
                                                {{ $device->pairing_code }}
                                            </div>
                                        @else
                                            <span class="text-secondary small fst-italic">---</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            @if($device->current_session_id)
                                            <form action="{{ route('displays.reset_session', $device->id) }}" method="POST" onsubmit="return confirm('{{ __('ui.reset_session_confirm') ?? 'Release the active screen session for this display?' }}')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-circle" title="{{ __('ui.reset_display_session') ?? 'Reset / Release Active Session' }}">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 shadow-none text-primary fw-bold" 
                                                     data-bs-toggle="modal" data-bs-target="#editDisplayModal{{ $device->id }}">
                                                <i class="bi bi-pencil-square me-1"></i> {{ __('ui.edit') }}
                                            </button>
                                            <a href="{{ route('queue.display.setup', $device->uid) }}" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 shadow-none">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('ui.live_preview') }}
                                            </a>
                                            <form action="{{ route('displays.destroy', $device->id) }}" method="POST" onsubmit="return confirm('{{ __('ui.remove_display_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editDisplayModal{{ $device->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow rounded-4 text-start">
                                                    <div class="modal-header border-0 p-4 pb-0">
                                                        <h5 class="fw-bold mb-0">{{ __('ui.configure_display') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('displays.update', $device->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-body p-4">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-secondary">{{ __('ui.room_name') }}</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $device->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-secondary">{{ __('ui.assigned_room') }}</label>
                                                                <select name="room_id" class="form-select">
                                                                    <option value="">{{ __('ui.any_room') }}</option>
                                                                    @foreach($rooms as $room)
                                                                        <option value="{{ $room->id }}" {{ $device->room_id == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-secondary">{{ __('ui.display_content') }}</label>
                                                                <select name="show_type" class="form-select">
                                                                    <option value="both" {{ $device->show_type === 'both' ? 'selected' : '' }}>{{ __('ui.display_show_both') }}</option>
                                                                    <option value="walk_in" {{ $device->show_type === 'walk_in' ? 'selected' : '' }}>{{ __('ui.display_show_walk_in') }}</option>
                                                                    <option value="appointment" {{ $device->show_type === 'appointment' ? 'selected' : '' }}>{{ __('ui.display_show_appointment') }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="row g-2 mb-0">
                                                                <div class="col-6">
                                                                    <label class="form-label small fw-bold text-secondary">{{ __('ui.display_theme') ?? 'Theme' }}</label>
                                                                    <select name="theme" class="form-select">
                                                                        <option value="dark" {{ ($device->theme ?? 'dark') === 'dark' ? 'selected' : '' }}>{{ __('ui.theme_dark') ?? 'Dark Mode' }}</option>
                                                                        <option value="light" {{ ($device->theme ?? 'dark') === 'light' ? 'selected' : '' }}>{{ __('ui.theme_light') ?? 'Light Mode' }}</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label small fw-bold text-secondary">{{ __('ui.display_language') ?? 'Language' }}</label>
                                                                    <select name="language" class="form-select">
                                                                        <option value="" {{ empty($device->language) ? 'selected' : '' }}>{{ __('ui.default_language') ?? 'Default (System)' }}</option>
                                                                        <option value="en" {{ ($device->language ?? '') === 'en' ? 'selected' : '' }}>English</option>
                                                                        <option value="ar" {{ ($device->language ?? '') === 'ar' ? 'selected' : '' }}>العربية (Arabic)</option>
                                                                        <option value="fr" {{ ($device->language ?? '') === 'fr' ? 'selected' : '' }}>Français (French)</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 p-4 pt-0">
                                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">{{ __('ui.save_changes') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center">
                                        <div class="text-secondary mb-3">
                                            <i class="bi bi-display fs-1 opacity-25"></i>
                                        </div>
                                        <h6 class="text-secondary fw-bold">{{ __('ui.no_displays_registered') }}</h6>
                                        <p class="text-secondary small">{{ __('ui.add_first_display_description') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .letter-spacing-1 { letter-spacing: 2px; }
    .fw-mono { font-family: 'Courier New', Courier, monospace; }
</style>
@endpush
