@extends('layouts.dashboard')

@section('title', __('ui.slot_settings') . ' | Noubtigo')
@section('header_title', __('ui.slot_settings') ?? 'Time Slot Settings')
@section('header_subtitle', __('ui.slot_settings_subtitle'))

@push('styles')
<style>
.slot-table th { font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; font-weight:600; }
.dow-badge {
    background: var(--primary-gradient); color:#fff;
    border-radius:8px; padding:3px 10px; font-size:.78rem; font-weight:600; white-space:nowrap;
}
.slot-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.slot-day-card {
    background:#fff; border-radius:16px; padding:18px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    transition:transform .18s, box-shadow .18s;
}
.slot-day-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.1); }
.cap-pill {
    background:#f0fdf4; color:#15803d; border-radius:20px;
    padding:2px 10px; font-size:.75rem; font-weight:600;
}
.ob-pill {
    background:#fffbeb; color:#b45309; border-radius:20px;
    padding:2px 10px; font-size:.75rem; font-weight:600;
}
.grace-pill {
    background:#eff6ff; color:#1d4ed8; border-radius:20px;
    padding:2px 10px; font-size:.75rem; font-weight:600;
}
.modal-content { border-radius:20px!important; border:none!important; box-shadow:0 20px 60px rgba(0,0,0,.15)!important; }
.modal-header { border-bottom:none!important; padding:24px 28px 8px!important; }
.modal-body { padding:20px 28px 28px!important; }

[data-bs-theme="dark"] .slot-day-card { background:#1e293b; }
</style>
@endpush

@section('content')

{{-- ── Toolbar ──────────────────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <a href="{{ route('appointments.index') }}" class="btn btn-light btn-sm me-2">
            <i class="bi bi-arrow-left me-1"></i>{{ __('ui.back') }}
        </a>
    </div>
    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addSlotModal">
        <i class="bi bi-plus-circle me-2"></i>{{ __('ui.add_slot') }}
    </button>
</div>

{{-- ── Filter by service ────────────────────────────────────────────────────── --}}
<div class="glass-card p-4 mb-4 fade-in">
    <div class="d-flex flex-wrap gap-3 align-items-center">
        <span class="fw-semibold">{{ __('ui.service') }}:</span>
        <button class="btn btn-sm btn-gradient" onclick="filterByService('')">{{ __('ui.all_services') }}</button>
        @foreach($services as $s)
            <button class="btn btn-sm btn-outline-secondary rounded-pill"
                    onclick="filterByService({{ $s->id }})">{{ $s->name }}</button>
        @endforeach
    </div>
</div>

{{-- ── Slot grid ────────────────────────────────────────────────────────────── --}}
@if($slots->isEmpty())
<div class="glass-card p-5 text-center fade-in">
    <i class="bi bi-clock-history fs-1 opacity-25 d-block mb-3"></i>
    <h5 class="fw-bold mb-2">{{ __('ui.no_slots_yet') }}</h5>
    <p class="text-secondary mb-4">{{ __('ui.add_slots_desc') }}</p>
    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addSlotModal">
        <i class="bi bi-plus-circle me-2"></i>{{ __('ui.add_first_slot') }}
    </button>
</div>
@else
<div class="slot-grid fade-in" id="slotGrid">
    @foreach($slots as $slot)
    <div class="slot-day-card" data-service="{{ $slot->service_id }}">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="fw-bold mb-1">{{ $slot->service?->name ?? '—' }}</div>
                <div class="small text-secondary mb-2"><i class="bi bi-door-open me-1"></i>{{ $slot->room?->name ?? 'Unassigned' }}</div>
                <span class="dow-badge">{{ __('ui.day_'.strtolower($slot->day_name)) }}</span>
            </div>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-light" onclick="openEdit({{ $slot->id }}, {{ $slot->toJson() }})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-light text-danger" onclick="deleteSlot({{ $slot->id }}, this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-clock text-secondary"></i>
            <span class="fw-semibold">{{ $slot->time_range }}</span>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if(!$slot->is_active)
                <span class="badge bg-secondary-subtle text-secondary">{{ __('ui.inactive') }}</span>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: Add Slot
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addSlotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-success"></i>{{ __('ui.new_time_slot') }}</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSlotForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('ui.service') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="service_id" required>
                            <option value="">{{ __('ui.choose_service') }}</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('ui.room') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_id" required>
                        <option value="">{{ __('ui.select_room') }}</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('ui.day_of_week') }} <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $day)
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="checkbox" name="days_of_week[]" value="{{ $i }}" id="day_{{ $i }}">
                                    <label class="form-check-label small" for="day_{{ $i }}">{{ __('ui.day_'.strtolower($day)) }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('ui.start_time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('ui.end_time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.slot_interval_minutes') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="interval_minutes" value="15" min="5" max="480" required>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="slotActive" checked>
                        <label class="form-check-label small fw-semibold" for="slotActive">{{ __('ui.active') }}</label>
                    </div>
                    <button type="submit" class="btn btn-gradient w-100 py-2 fw-semibold" id="addSlotBtn">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('ui.create_slot') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: Edit Slot
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editSlotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('ui.edit_time_slot') }}</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSlotForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editSlotId">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('ui.room') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="editRoom" name="room_id" required>
                        <option value="">{{ __('ui.select_room') }}</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('ui.day_of_week') }}</label>
                        <select class="form-select" id="editDow" name="day_of_week">
                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $day)
                                <option value="{{ $i }}">{{ __('ui.day_'.strtolower($day)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('ui.start_time') }}</label>
                            <input type="time" class="form-control" id="editStart" name="start_time">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('ui.end_time') }}</label>
                            <input type="time" class="form-control" id="editEnd" name="end_time">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.slot_interval_minutes') }}</label>
                            <input type="number" class="form-control" id="editInterval" name="interval_minutes" min="5" max="480">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="editActive" name="is_active" value="1">
                        <label class="form-check-label small fw-semibold" for="editActive">{{ __('ui.active') }}</label>
                    </div>
                    <button type="submit" class="btn btn-gradient w-100 py-2 fw-semibold">
                        <i class="bi bi-save me-2"></i>{{ __('ui.save_changes') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ─── Add Slot ─────────────────────────────────────────────────────────────────
document.getElementById('addSlotForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('addSlotBtn');
    btn.disabled = true;
    const fd = new FormData(this);

    fetch('{{ route("appointment-slots.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addSlotModal')).hide();
            showToast('{{ __("ui.slot_created") }}', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || '{{ __("ui.error_creating_slot") }}', 'danger');
        }
    })
    .catch(() => showToast('Network error.', 'danger'))
    .finally(() => { btn.disabled = false; });
});

// ─── Open Edit ────────────────────────────────────────────────────────────────
function openEdit(id, slot) {
    document.getElementById('editSlotId').value         = id;
    document.getElementById('editRoom').value            = slot.room_id || '';
    document.getElementById('editDow').value            = slot.day_of_week;
    document.getElementById('editStart').value          = slot.start_time?.substring(0,5);
    document.getElementById('editEnd').value            = slot.end_time?.substring(0,5);
    document.getElementById('editInterval').value       = slot.interval_minutes;
    document.getElementById('editActive').checked       = !!slot.is_active;

    new bootstrap.Modal(document.getElementById('editSlotModal')).show();
}

// ─── Edit Submit ──────────────────────────────────────────────────────────────
document.getElementById('editSlotForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('editSlotId').value;

    const payload = {
        day_of_week:        document.getElementById('editDow').value,
        room_id:            document.getElementById('editRoom').value,
        start_time:         document.getElementById('editStart').value,
        end_time:           document.getElementById('editEnd').value,
        interval_minutes:   document.getElementById('editInterval').value,
        is_active:          document.getElementById('editActive').checked ? 1 : 0,
    };

    fetch(`/appointment-slots/${id}`, {
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editSlotModal')).hide();
            showToast('{{ __("ui.slot_updated") }}', 'success');
            setTimeout(() => location.reload(), 800);
        } else showToast('{{ __("ui.error_updating") }}', 'danger');
    });
});

// ─── Delete Slot ──────────────────────────────────────────────────────────────
function deleteSlot(id, btn) {
    if (!confirm('{{ __("ui.confirm_delete_slot") }}')) return;
    btn.disabled = true;
    fetch(`/appointment-slots/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { showToast('{{ __("ui.slot_deleted") }}', 'warning'); setTimeout(() => location.reload(), 800); }
        else { btn.disabled = false; showToast('{{ __("ui.error_deleting") }}', 'danger'); }
    });
}

// ─── Filter by Service ────────────────────────────────────────────────────────
function filterByService(id) {
    document.querySelectorAll('#slotGrid .slot-day-card').forEach(card => {
        card.style.display = (!id || card.dataset.service == id) ? '' : 'none';
    });
}

// ─── Toast ────────────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer') || (() => {
        const el = document.createElement('div');
        el.id = 'toastContainer';
        el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px';
        document.body.appendChild(el);
        return el;
    })();
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} border-0 shadow-lg rounded-3 mb-0`;
    toast.innerHTML = `<i class="bi bi-${type==='success'?'check-circle':'exclamation-triangle'}-fill me-2"></i>${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3200);
}
</script>
@endpush
