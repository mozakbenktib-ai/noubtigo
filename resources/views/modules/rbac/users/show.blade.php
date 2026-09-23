@extends('layouts.dashboard')

@section('title', __('ui.staff_profile') . ' | ' . $user->full_name)
@section('header_title', $user->full_name)
@section('header_subtitle', __('ui.staff_profile_management_history'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">
<link rel="stylesheet" href="{{ asset('frontend/css/queue.css') }}">
<style>
    /* intl-tel-input styling overrides */
    .iti {
        width: 100% !important;
        display: block !important;
    }
    .iti__country-list {
        z-index: 1060 !important; /* ensure it is above bootstrap modals */
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #eee;
    }
    .user-card {
        background: white;
        border-radius: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .user-header {
        background: var(--primary-gradient);
        padding: 3rem 2rem;
        color: white;
        text-align: center;
        position: relative;
    }
    .user-avatar-large {
        width: 100px;
        height: 100px;
        background: white;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .role-badges {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .role-badge {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    .user-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
        background: #f8fafc;
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }

    .detail-card {
        background: white;
        border-radius: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        margin-bottom: 2rem;
    }
    .detail-card-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .detail-card-header .title-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .detail-card-header i {
        color: var(--primary-color);
        font-size: 1.25rem;
    }
    .detail-card-header h6 {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
    }
    .detail-card-body {
        padding: 2rem;
    }

    [data-bs-theme="dark"] .user-card,
    [data-bs-theme="dark"] .detail-card { background: #1e293b; border-color: rgba(255,255,255,0.05); }
    [data-bs-theme="dark"] .user-info-grid { background: #0f172a; }
    [data-bs-theme="dark"] .info-value,
    [data-bs-theme="dark"] .detail-card-header h6 { color: #f1f5f9; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        {{-- Left Column: User Profile --}}
        <div class="col-lg-4">
            <div class="user-card fade-in">
                <div class="user-header">
                    <div class="user-avatar-large">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name) }}&background=fff&color=22c55e&size=128" 
                             alt="{{ __('ui.avatar') }}" width="100%">
                    </div>
                    <h4 class="fw-bold mb-1">{{ $user->full_name }}</h4>
                    <p class="mb-0 text-white-50">{{ $user->email }}</p>
                    
                    <div class="role-badges">
                        @foreach($user->roles as $role)
                            <span class="role-badge">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="user-info-grid">
                    <div class="info-item">
                        <span class="info-label">{{ __('ui.member_since') }}</span>
                        <span class="info-value">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ __('ui.last_updated') }}</span>
                        <span class="info-value">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ __('ui.current_company') }}</span>
                        <span class="info-value">{{ $user->company->name ?? '—' }}</span>
                    </div>
                </div>

                <div class="p-4 bg-white border-top d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                        <i class="bi bi-pencil-square me-2"></i> {{ __('ui.edit_profile') }}
                    </button>
                    <button class="btn btn-success flex-grow-1 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#editRolesModal{{ $user->id }}">
                        <i class="bi bi-shield-lock me-2"></i> {{ __('ui.roles') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Column: Audit Timeline --}}
        <div class="col-lg-8">
            <div class="detail-card fade-in">
                <div class="detail-card-header">
                    <div class="title-wrap">
                        <i class="bi bi-shield-check"></i>
                        <h6>{{ __('ui.management_audit_timeline') }}</h6>
                    </div>
                </div>
                <div class="detail-card-body">
                    @if($timeline->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-clock-history d-block mb-3 display-4 text-muted opacity-25"></i>
                            <p class="text-muted">{{ __('ui.no_management_activity') }}</p>
                        </div>
                    @else
                        <x-activity-timeline :timeline="$timeline" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Re-use existing modals for Edit and Roles (we need to ensure they work on this page too or include them) --}}
@include('modules.rbac.users.partials.modals', ['users' => [$user], 'roles' => $roles])

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
@endpush
