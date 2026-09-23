@extends('layouts.dashboard')

@section('title', 'Noubtigo | ' . __('ui.manage_rooms'))
@section('header_title', __('ui.rooms'))
@section('header_subtitle', __('ui.spaces_offered'))

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addRoomModal">
        <i class="bi bi-door-open-fill me-2"></i> {{ __('ui.add_room') }}
    </button>
</div>

<!-- Rooms Grid -->
<div class="row g-4 fade-in" id="roomsGrid">
    @foreach($rooms as $room)
    <div class="col-md-4">
        <div class="glass-card p-4 h-100 d-flex flex-column" style="background: white; border: 1px solid #eaeaea; border-radius: 1rem;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="bg-primary-subtle text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-door-open fs-4"></i>
                </div>
                <div class="dropdown">
                    <button class="btn btn-transparent border-0 shadow-none p-2" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical text-secondary fs-5"></i></button>
                    <ul class="dropdown-menu border-0 shadow">
                        <li>
                            <button class="dropdown-item w-100 text-start" onclick="openEditModal({{ json_encode($room) }})">
                                <i class="bi bi-pencil me-2"></i> {{ __('ui.edit') ?? 'Edit' }}
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item text-danger w-100 text-start" onclick="deleteRoom({{ $room->id }})">
                                <i class="bi bi-trash me-2"></i> {{ __('ui.delete') ?? 'Delete' }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <h5 class="fw-bold mb-2">{{ $room->name }}</h5>
            <p class="text-secondary small mb-4" style="min-height: 40px;">
                {{ Str::limit($room->description ?? 'No description provided.', 80) }}
            </p>
            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill small">
                    <i class="bi bi-door-open-fill me-1"></i> {{ $room->name }}
                </span>
                @if($room->is_active)
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small">{{ __('ui.active') }}</span>
                @else
                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill small">{{ __('ui.inactive') }}</span>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    @if($rooms->isEmpty())
    <div class="col-12 py-5 text-center bg-light rounded-3" style="border: 2px dashed #ccc;">
        <i class="bi bi-house-door text-muted display-4 d-block mb-3"></i>
        <h5 class="text-muted">{{ __('ui.no_rooms_yet') }}</h5>
        <button class="btn btn-gradient mt-3 px-4" data-bs-toggle="modal" data-bs-target="#addRoomModal">
             {{ __('ui.add_first_room') }}
        </button>
    </div>
    @endif
</div>

<!-- Modal: Add Room -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('ui.add_room') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form onsubmit="handleRoomStore(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.room_name') }}</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Consult A">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">{{ __('ui.description') ?? 'Description' }}</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveAdd" checked value="1">
                        <label class="form-check-label" for="isActiveAdd">{{ __('ui.room_is_open') }}</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold" id="btn-add-room">{{ __('ui.save_room') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Room -->
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('ui.edit_room') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form onsubmit="handleRoomUpdate(event)">
                    <input type="hidden" id="edit_room_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.room_name') }}</label>
                        <input type="text" class="form-control" id="edit_name" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">{{ __('ui.description') ?? 'Description' }}</label>
                        <textarea class="form-control" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">{{ __('ui.room_is_open') }}</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold" id="btn-update-room">{{ __('ui.update_room') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function handleRoomStore(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btn-add-room');
        btn.disabled = true;
        btn.innerHTML = 'Saving...';

        const payload = {
            name: form.name.value,
            description: form.description.value,
            is_active: form.is_active.checked ? 1 : 0
        };

        fetch('{{ route("rooms.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error occurred');
                btn.disabled = false;
                btn.innerHTML = 'Save Room';
            }
        });
    }

    function openEditModal(room) {
        document.getElementById('edit_room_id').value = room.id;
        document.getElementById('edit_name').value = room.name;
        document.getElementById('edit_description').value = room.description || '';
        document.getElementById('edit_is_active').checked = room.is_active;

        new bootstrap.Modal(document.getElementById('editRoomModal')).show();
    }

    function handleRoomUpdate(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-update-room');
        const id = document.getElementById('edit_room_id').value;
        
        btn.disabled = true;
        btn.innerHTML = 'Updating...';

        const payload = {
            name: document.getElementById('edit_name').value,
            description: document.getElementById('edit_description').value,
            is_active: document.getElementById('edit_is_active').checked ? 1 : 0
        };

        fetch(`/rooms/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error occurred');
                btn.disabled = false;
                btn.innerHTML = 'Update Room';
            }
        });
    }

    function deleteRoom(id) {
        if(!confirm('Delete this room?')) return;

        fetch(`/rooms/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            }
        });
    }
</script>
@endpush
