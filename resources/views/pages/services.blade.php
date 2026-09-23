@extends('layouts.dashboard')

@section('title', __('ui.manage_services') . ' | Noubtigo')
@section('header_title', __('ui.services'))
@section('header_subtitle', __('ui.services_offered'))

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        <i class="bi bi-plus-lg me-2"></i> {{ __('ui.add_service') }}
    </button>
</div>

<!-- Services Grid -->
<div class="row g-4 fade-in" id="servicesGrid">
    @foreach($services as $service)
    <div class="col-md-4">
        <div class="glass-card p-4 h-100 d-flex flex-column" style="background: white; border: 1px solid #eaeaea; border-radius: 1rem;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="bg-success-subtle text-success p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-4 fw-bold">{{ $service->prefix }}</span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-transparent border-0 shadow-none p-2" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical text-secondary fs-5"></i></button>
                    <ul class="dropdown-menu border-0 shadow">
                        <li>
                            <button class="dropdown-item w-100 text-start" onclick="openEditModal({{ json_encode($service) }})">
                                <i class="bi bi-pencil me-2"></i> {{ __('ui.edit') }}
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item text-danger w-100 text-start" onclick="deleteService({{ $service->id }})">
                                <i class="bi bi-trash me-2"></i> {{ __('ui.delete') }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <h5 class="fw-bold mb-2">{{ $service->name }}</h5>
            <p class="text-secondary small mb-4" style="min-height: 40px;">
                {{ Str::limit($service->description ?? __('ui.no_description_provided'), 80) }}
            </p>
            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill small">
                    <i class="bi bi-tag-fill me-1"></i> {{ $service->name }}
                </span>
            </div>
        </div>
    </div>
    @endforeach

    @if($services->isEmpty())
    <div class="col-12 py-5 text-center bg-light rounded-3" style="border: 2px dashed #ccc;">
        <i class="bi bi-inboxes text-muted display-4 d-block mb-3"></i>
        <h5 class="text-muted">{{ __('ui.no_services_configured') }}</h5>
        <button class="btn btn-gradient mt-3 px-4" data-bs-toggle="modal" data-bs-target="#addServiceModal">
             {{ __('ui.add_first_service') }}
        </button>
    </div>
    @endif
</div>

<!-- Modal: Add Service -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('ui.add_service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addServiceForm" onsubmit="handleServiceStore(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.service_name') }}</label>
                        <input type="text" class="form-control" name="name" required placeholder="{{ __('ui.service_name_example') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.prefix') }}</label>
                        <input type="text" class="form-control" name="prefix" maxlength="4" placeholder="{{ __('ui.prefix_example') }}">
                        <div class="form-text small">{{ __('ui.prefix_help') }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">{{ __('ui.description') }}</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveAdd" checked value="1">
                        <label class="form-check-label" for="isActiveAdd">{{ __('ui.service_is_active') }}</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold" id="btn-add-service">{{ __('ui.save_service') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Service -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('ui.edit_service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editServiceForm" onsubmit="handleServiceUpdate(event)">
                    <input type="hidden" id="edit_service_id" name="id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.service_name') }}</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.prefix') }}</label>
                        <input type="text" class="form-control" id="edit_prefix" name="prefix" maxlength="4">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">{{ __('ui.description') }}</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">{{ __('ui.service_is_active') }}</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold" id="btn-update-service">{{ __('ui.update_service') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showToast(message, type = 'danger') {
        const container = document.getElementById('toastContainer') || (() => {
            const element = document.createElement('div');
            element.id = 'toastContainer';
            element.className = 'position-fixed top-0 end-0 p-3';
            element.style.zIndex = '9999';
            document.body.appendChild(element);
            return element;
        })();

        const toast = document.createElement('div');
        toast.className = `alert alert-${type} border-0 shadow-lg rounded-3 mb-2`;
        toast.setAttribute('role', 'alert');
        const icon = document.createElement('i');
        icon.className = 'bi bi-exclamation-triangle-fill me-2';
        toast.append(icon, document.createTextNode(message));
        container.appendChild(toast);

        setTimeout(() => toast.remove(), 4000);
    }

    function handleServiceStore(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btn-add-service');
        btn.disabled = true;
        btn.innerHTML = '{{ __("ui.saving") }}';

        const payload = {
            name: form.name.value,
            prefix: form.prefix.value,
            description: form.description.value,
            is_active: form.is_active.checked ? 1 : 0
        };

        fetch('{{ route("services.store") }}', {
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
                showToast(data.message || '{{ __("ui.error_occurred") }}');
                btn.disabled = false;
                btn.innerHTML = '{{ __("ui.save_service") }}';
            }
        })
        .catch(err => {
            console.error(err);
            showToast('{{ __("ui.unable_to_save_service") }}');
            btn.disabled = false;
            btn.innerHTML = '{{ __("ui.save_service") }}';
        });
    }

    function openEditModal(service) {
        document.getElementById('edit_service_id').value = service.id;
        document.getElementById('edit_name').value = service.name;
        document.getElementById('edit_prefix').value = service.prefix || '';
        document.getElementById('edit_description').value = service.description || '';
        document.getElementById('edit_is_active').checked = service.is_active;

        new bootstrap.Modal(document.getElementById('editServiceModal')).show();
    }

    function handleServiceUpdate(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btn-update-service');
        const id = document.getElementById('edit_service_id').value;
        
        btn.disabled = true;
        btn.innerHTML = '{{ __("ui.updating") }}';

        const payload = {
            name: form.name.value,
            prefix: form.prefix.value,
            description: form.description.value,
            is_active: document.getElementById('edit_is_active').checked ? 1 : 0
        };

        fetch(`/services/${id}`, {
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
                showToast(data.message || '{{ __("ui.error_occurred") }}');
                btn.disabled = false;
                btn.innerHTML = '{{ __("ui.update_service") }}';
            }
        })
        .catch(err => {
            console.error(err);
            showToast('{{ __("ui.unable_to_update_service") }}');
            btn.disabled = false;
            btn.innerHTML = '{{ __("ui.update_service") }}';
        });
    }

    function deleteService(id) {
        if(!confirm('{{ __("ui.confirm_delete_service") }}')) {
            return;
        }

        fetch(`/services/${id}`, {
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
