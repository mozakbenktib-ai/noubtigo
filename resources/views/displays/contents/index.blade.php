@extends('layouts.dashboard')

@section('title', __('ui.display_content_mgmt') . ' | Noubtigo')
@section('header_title', __('ui.display_content_mgmt'))
@section('header_subtitle', __('ui.display_content_mgmt_subtitle'))

@section('content')
<div class="container-fluid px-0">

    <!-- Top Navigation Tabs: Displays vs Display Content -->
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
        <ul class="nav nav-pills gap-2">
            @permission('displays.view')
            <li class="nav-item">
                <a class="nav-link text-secondary fw-semibold px-4 py-2 rounded-pill" href="{{ route('displays.index') }}">
                    <i class="bi bi-tv me-2"></i> {{ __('ui.displays') ?? 'Displays & Screens' }}
                </a>
            </li>
            @endpermission
            <li class="nav-item">
                <a class="nav-link active bg-primary text-white fw-bold px-4 py-2 rounded-pill shadow-sm" href="{{ route('displays.contents.index') }}">
                    <i class="bi bi-file-earmark-richtext-fill me-2"></i> {{ __('ui.display_content') }}
                </a>
            </li>
        </ul>

        @permission('display_content.create')
        <div>
            <a href="{{ route('displays.contents.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i> {{ __('ui.new_display_content') }}
            </a>
        </div>
        @endpermission
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-collection-play-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase">{{ __('ui.total_contents') }}</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="bi bi-broadcast-pin fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase">{{ __('ui.active_on_screens') }}</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $activeCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="bi bi-globe2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase">{{ __('ui.global_broadcasts') }}</div>
                        <h3 class="fw-bold mb-0 text-info">{{ $globalCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                        <i class="bi bi-pin-map-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase">{{ __('ui.screen_targeted') }}</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $assignedCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Screen Availability Breakdown Bar -->
    @if($devices->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light bg-opacity-50">
            <div class="card-body p-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <span class="text-dark small fw-bold text-uppercase d-flex align-items-center gap-1">
                        <i class="bi bi-tv-fill text-primary"></i> {{ __('ui.screen_coverage') }}:
                    </span>
                    @foreach($devices as $device)
                        @php
                            $specific = $deviceStats['devices'][$device->id] ?? 0;
                            $global = $deviceStats['global_count'] ?? 0;
                            $totalAvailable = $global + $specific;
                        @endphp
                        <div class="bg-white border rounded-pill px-3 py-1 small shadow-sm d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark">{{ $device->name }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $totalAvailable }} {{ __('ui.available') }}</span>
                            <span class="text-muted x-small">({{ $global }} {{ __('ui.global') }} + {{ $specific }} {{ __('ui.specific') }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('displays.contents.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">{{ __('ui.all_content_types') }}</option>
                        <option value="information" {{ request('type') === 'information' ? 'selected' : '' }}>{{ __('ui.content_type_information') }}</option>
                        <option value="promotion" {{ request('type') === 'promotion' ? 'selected' : '' }}>{{ __('ui.content_type_promotion') }}</option>
                        <option value="qr_tracking" {{ request('type') === 'qr_tracking' ? 'selected' : '' }}>{{ __('ui.content_type_qr_tracking') }}</option>
                        <option value="announcement" {{ request('type') === 'announcement' ? 'selected' : '' }}>{{ __('ui.content_type_announcement') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="target_type" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">{{ __('ui.all_displays_targets') }}</option>
                        <option value="all" {{ request('target_type') === 'all' ? 'selected' : '' }}>{{ __('ui.all_displays_global') }}</option>
                        <option value="selected" {{ request('target_type') === 'selected' ? 'selected' : '' }}>{{ __('ui.selected_displays_only') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">{{ __('ui.all_statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('ui.active_now') }}</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>{{ __('ui.scheduled') }}</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('ui.expired') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('ui.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 w-50">{{ __('ui.filter') }}</button>
                    <a href="{{ route('displays.contents.index') }}" class="btn btn-sm btn-light rounded-pill px-3 w-50">{{ __('ui.reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Contents Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light bg-opacity-75">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase" style="width: 70px;">{{ __('ui.preview') }}</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.title_and_content') }}</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.type') }}</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.displays') }}</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.status') }}</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">{{ __('ui.schedule') }}</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">{{ __('ui.duration') }}</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">{{ __('ui.order') }}</th>
                        <th class="pe-4 py-3 text-secondary small fw-bold text-uppercase text-end">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contents as $item)
                        @php
                            $status = $item->status_details;
                            $types = \App\Modules\Displays\Models\DisplayContent::getTypes();
                            $typeInfo = $types[$item->type] ?? [
                                'name' => ucfirst($item->type),
                                'icon' => 'bi-file-earmark',
                                'badge' => 'bg-secondary bg-opacity-10 text-secondary'
                            ];
                        @endphp
                        <tr>
                            <!-- Preview Thumbnail -->
                            <td class="ps-4 py-3">
                                @if($item->image_url)
                                    <div class="rounded-3 overflow-hidden border shadow-sm" style="width: 54px; height: 54px; background: #0b1329;">
                                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-100 h-100 object-fit-cover">
                                    </div>
                                @elseif($item->type === 'qr_tracking')
                                    <div class="rounded-3 d-flex align-items-center justify-content-center border shadow-sm bg-primary bg-opacity-10 text-primary" style="width: 54px; height: 54px;">
                                        <i class="bi bi-qr-code-scan fs-4"></i>
                                    </div>
                                @elseif($item->type === 'announcement')
                                    <div class="rounded-3 d-flex align-items-center justify-content-center border shadow-sm bg-warning bg-opacity-10 text-warning" style="width: 54px; height: 54px;">
                                        <i class="bi bi-bell-fill fs-4"></i>
                                    </div>
                                @else
                                    <div class="rounded-3 d-flex align-items-center justify-content-center border shadow-sm bg-light text-secondary" style="width: 54px; height: 54px;">
                                        <i class="bi bi-info-circle-fill fs-4"></i>
                                    </div>
                                @endif
                            </td>

                            <!-- Title & Description -->
                            <td class="py-3">
                                <div class="fw-bold text-dark fs-6">{{ $item->title }}</div>
                                @if($item->description)
                                    <div class="text-secondary small text-truncate" style="max-width: 320px;">
                                        {{ $item->description }}
                                    </div>
                                @endif
                                @if($item->priority !== 'normal')
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill x-small mt-1">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ strtoupper($item->priority) }} {{ __('ui.priority_level') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Type -->
                            <td class="py-3">
                                <span class="badge {{ $typeInfo['badge'] }} px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi {{ $typeInfo['icon'] }} me-1"></i> {{ $typeInfo['name'] }}
                                </span>
                            </td>

                            <!-- Target Displays -->
                            <td class="py-3">
                                @if($item->target_type === 'all')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-globe me-1"></i> {{ __('ui.all_displays') }}
                                    </span>
                                @else
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 220px;">
                                        @forelse($item->devices as $dev)
                                            <span class="badge bg-secondary bg-opacity-10 text-dark px-2 py-1 rounded-pill small">
                                                <i class="bi bi-display me-1 text-primary"></i> {{ $dev->name }}
                                            </span>
                                        @empty
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill small">
                                                {{ __('ui.no_screens_assigned') }}
                                            </span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3">
                                <span class="badge {{ $status['badge'] }} px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi {{ $status['icon'] }} me-1"></i> {{ $status['label'] }}
                                </span>
                            </td>

                            <!-- Schedule -->
                            <td class="py-3">
                                @if(!$item->starts_at && !$item->ends_at)
                                    <span class="text-secondary small fst-italic">{{ __('ui.immediate_no_expiry') }}</span>
                                @else
                                    <div class="small text-dark">
                                        @if($item->starts_at)
                                            <div><i class="bi bi-play-circle text-success me-1"></i> {{ app(\App\Services\TimezoneService::class)->formatForDisplay($item->starts_at, 'M d, H:i') }}</div>
                                        @endif
                                        @if($item->ends_at)
                                            <div><i class="bi bi-stop-circle text-danger me-1"></i> {{ app(\App\Services\TimezoneService::class)->formatForDisplay($item->ends_at, 'M d, H:i') }}</div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Duration -->
                            <td class="py-3 text-center">
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-monospace fw-bold">
                                    <i class="bi bi-clock-history me-1 text-secondary"></i> {{ $item->duration }}s
                                </span>
                            </td>

                            <!-- Order -->
                            <td class="py-3 text-center">
                                <span class="badge bg-light text-secondary border rounded-circle p-2 font-monospace">
                                    {{ $item->sort_order }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="pe-4 py-3 text-end">
                                <div class="btn-group shadow-none" role="group">
                                    <!-- Preview Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-light rounded-circle me-1" 
                                            title="{{ __('ui.live_tv_preview') }}"
                                            onclick="openPreviewModal('{{ $item->id }}', '{{ addslashes($item->title) }}')">
                                        <i class="bi bi-eye text-primary"></i>
                                    </button>

                                    <!-- Quick Toggle Active -->
                                    @permission('display_content.edit')
                                    <form action="{{ route('displays.contents.toggle', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light rounded-circle me-1" 
                                                title="{{ $item->is_active ? __('ui.deactivate') : __('ui.activate') }}">
                                            <i class="bi {{ $item->is_active ? 'bi-toggle-on text-success fs-6' : 'bi-toggle-off text-muted fs-6' }}"></i>
                                        </button>
                                    </form>
                                    @endpermission

                                    <!-- Edit -->
                                    @permission('display_content.edit')
                                    <a href="{{ route('displays.contents.edit', $item->id) }}" class="btn btn-sm btn-light rounded-circle me-1" title="{{ __('ui.edit') }}">
                                        <i class="bi bi-pencil text-secondary"></i>
                                    </a>
                                    @endpermission

                                    <!-- Duplicate -->
                                    @permission('display_content.create')
                                    <form action="{{ route('displays.contents.duplicate', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light rounded-circle me-1" title="{{ __('ui.duplicate') }}">
                                            <i class="bi bi-copy text-secondary"></i>
                                        </button>
                                    </form>
                                    @endpermission

                                    <!-- Delete -->
                                    @permission('display_content.delete')
                                    <form action="{{ route('displays.contents.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('ui.delete_content_confirm') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light rounded-circle" title="{{ __('ui.delete') }}">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-secondary">
                                <div class="mb-3">
                                    <i class="bi bi-file-earmark-richtext display-4 text-muted opacity-50"></i>
                                </div>
                                <h5 class="fw-bold text-dark">{{ __('ui.no_display_content_yet') }}</h5>
                                <p class="text-secondary small mb-3">{{ __('ui.no_display_content_desc') }}</p>
                                @permission('display_content.create')
                                <a href="{{ route('displays.contents.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-plus-circle-fill me-1"></i> {{ __('ui.create_first_display_content') }}
                                </a>
                                @endpermission
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contents->hasPages())
            <div class="p-3 border-top">
                {{ $contents->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Live Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden" style="background: #080f1e; color: #f8fafc;">
            <div class="modal-header border-bottom border-white border-opacity-10 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 small">
                        <i class="bi bi-tv me-1"></i> {{ __('ui.tv_signage_simulation') }}
                    </span>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="previewModalLabel">{{ __('ui.preview_content') }}</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <!-- 16:9 Aspect Frame Preview -->
                <div class="signage-simulated-frame p-4 rounded-4 mx-auto" style="background: linear-gradient(145deg, #0b1329 0%, #050a17 100%); border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 20px 50px rgba(0,0,0,0.6); max-width: 680px; min-height: 380px;">
                    <div id="previewModalBody">
                        <div class="py-5 text-center text-secondary">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <div>{{ __('ui.loading_preview') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-white border-opacity-10 py-2 px-4 d-flex justify-content-between">
                <span class="text-secondary small">{{ __('ui.matches_display_zone', ['route' => '/queue/display']) }}</span>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.close') }}</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Signage Preview Card Specifics inside dashboard */
.signage-slide {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #ffffff;
    height: 100%;
}
.signage-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 12px;
}
.slide-heading {
    font-size: 1.5rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 8px;
    letter-spacing: -0.02em;
}
.slide-lead {
    font-size: 0.95rem;
    color: #94a3b8;
    max-width: 480px;
    line-height: 1.5;
    margin: 0 auto;
}
.qr-showcase-box {
    background: #ffffff;
    padding: 12px;
    border-radius: 16px;
    display: inline-block;
    margin: 16px 0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.qr-image-display {
    width: 160px;
    height: 160px;
    display: block;
    border-radius: 8px;
}
.qr-footer-hint {
    color: #0f172a;
    font-size: 0.75rem;
    font-weight: 700;
    margin-top: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.qr-perks-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
}
.qr-perk-pill {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    font-size: 0.75rem;
    padding: 4px 12px;
    border-radius: 9999px;
    font-weight: 600;
}
.promo-banner-wrapper, .info-media-wrapper {
    width: 100%;
    max-height: 180px;
    overflow: hidden;
    border-radius: 12px;
}
.promo-banner-img, .info-media-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}
.promo-action-pill {
    background: rgba(6, 182, 212, 0.15);
    color: #38bdf8;
    border: 1px solid rgba(6, 182, 212, 0.3);
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.announcement-box {
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.3);
}
</style>

<script>
function openPreviewModal(contentId, title) {
    const modalTitle = document.getElementById('previewModalLabel');
    const modalBody = document.getElementById('previewModalBody');
    if (modalTitle) modalTitle.textContent = title;
    
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="py-5 text-center text-secondary">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div>Loading slide preview...</div>
            </div>
        `;
    }

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();

    fetch(`{{ url('displays/contents') }}/${contentId}/preview`)
        .then(res => res.text())
        .then(html => {
            if (modalBody) modalBody.innerHTML = html;
        })
        .catch(err => {
            if (modalBody) {
                modalBody.innerHTML = `<div class="text-danger py-4">Failed to load preview: ${err.message}</div>`;
            }
        });
}
</script>
@endsection
