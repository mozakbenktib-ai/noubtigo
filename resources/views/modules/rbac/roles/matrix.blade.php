@extends('layouts.dashboard')

@section('title', 'Permission Matrix | Noubtigo')
@section('header_title', 'Permissions Control')
@section('header_subtitle', 'Role-based access mapping for all modules')

@section('content')
<div class="card border-0 shadow-sm rounded-4 overflow-hidden fade-in">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Role-Permission Mapping</h5>
        <div class="d-flex gap-2">
            <span class="badge rounded-pill px-3 py-2" style="background: rgba(34, 197, 94, 0.1); color: var(--primary-color)">
                <i class="bi bi-info-circle me-1"></i> Permissions are grouped by module
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <form action="{{ route('rbac.roles.update-matrix') }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-start bg-white sticky-col border-0" style="width: 250px;">Permissions</th>
                            @foreach($roles as $role)
                             <th class="bg-white border-0">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="badge rounded-pill px-3 py-2 mb-1 shadow-sm" 
                                          style="background: {{ $role->slug == 'super-admin' ? 'var(--primary-gradient)' : ($role->is_global ? 'rgba(99, 102, 241, 0.1)' : 'rgba(34, 197, 94, 0.1)') }}; 
                                                 color: {{ $role->slug == 'super-admin' ? '#fff' : ($role->is_global ? '#4f46e5' : 'var(--primary-color)') }}; 
                                                 border: 1px solid {{ $role->slug == 'super-admin' ? 'transparent' : ($role->is_global ? '#4f46e5' : 'var(--primary-color)') }};">
                                        {{ $role->name }}
                                    </span>
                                    @if($role->is_global)
                                        <small class="text-indigo fw-bold" style="font-size: 0.6rem; text-transform: uppercase;">Global</small>
                                    @endif
                                </div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $module => $modulePermissions)
                        <tr class="bg-light">
                            <td colspan="{{ $roles->count() + 1 }}" class="text-start ps-4 py-2 fw-bold text-secondary text-uppercase small ls-1">
                                <i class="bi bi-folder2-open me-2"></i> {{ $module }} Module
                            </td>
                        </tr>
                        @foreach($modulePermissions as $permission)
                        <tr>
                            <td class="ps-4 text-start border-0 sticky-col bg-white">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-medium">{{ $permission->name }}</span>
                                        @if($permission->is_global)
                                            <span class="badge bg-indigo-subtle text-indigo border-indigo-subtle" style="font-size: 0.55rem; padding: 2px 5px;">GLOBAL</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $permission->description }}</small>
                                </div>
                            </td>
                            @foreach($roles as $role)
                            <td class="border-0">
                                <div class="form-check form-switch d-inline-block">
                                    @php
                                        // A role is disabled if it's the protected super-admin
                                        // OR if it's a Global Role and the user is NOT a system admin
                                        $isDisabled = ($role->slug == 'super-admin');
                                        
                                        if (!auth()->user()->is_system_admin && $role->is_global) {
                                            $isDisabled = true;
                                        }
                                    @endphp
                                    <input class="form-check-input permission-toggle" type="checkbox" 
                                           name="matrix[{{ $role->id }}][]" value="{{ $permission->id }}"
                                           {{ $isDisabled ? 'disabled' : '' }}
                                           {{ $role->slug == 'super-admin' ? 'checked' : ($role->permissions->contains($permission->id) ? 'checked' : '') }}>
                                </div>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top-0 d-flex justify-content-end gap-3 py-4 pe-5 pb-5">
                <button type="button" class="btn btn-light rounded-3 px-5 py-2 fw-semibold" onclick="location.reload()">Reset Changes</button>
                <button type="submit" class="btn text-white px-5 py-2 fw-semibold rounded-3 shadow-sm hover-up" 
                        style="background: var(--primary-gradient);">
                    <i class="bi bi-save2-fill me-2"></i> Save Matrix Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 1;
        box-shadow: 4px 0 8px rgba(0,0,0,0.02);
    }
    
    .ls-1 { letter-spacing: 1px; }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        cursor: pointer;
    }

    tr:hover td {
        background-color: rgba(34, 197, 94, 0.02) !important;
    }

    .hover-up:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
    }
    .text-indigo { color: #4338ca; }
    .bg-indigo-subtle { background-color: #e0e7ff; }
    .border-indigo-subtle { border: 1px solid #c7d2fe; }
</style>
@endpush
