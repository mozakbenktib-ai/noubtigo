{{-- 
    Upgrade Prompt Component
    Usage: @include('components.upgrade-prompt', ['feature' => 'vip_management'])
--}}
@php
    $messages = [
        'vip_management'      => __('ui.upgrade_prompt_vip'),
        'drag_drop_reorder'   => __('ui.upgrade_prompt_reorder'),
        'advanced_analytics'  => __('ui.upgrade_prompt_analytics'),
        'appointments'        => __('ui.upgrade_prompt_appointments'),
        'customer_profiles'   => __('ui.upgrade_prompt_customers'),
        'priority_management' => __('ui.upgrade_prompt_priority'),
        'multi_room'          => __('ui.upgrade_prompt_rooms'),
    ];
    $msg = $messages[$feature] ?? __('ui.upgrade_prompt_generic');
@endphp

<div class="alert border-0 rounded-4 p-3 mb-3" style="background: linear-gradient(135deg, rgba(34,197,94,.08), rgba(6,182,212,.08)); border-left: 4px solid #22c55e !important; white-space: normal;">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-success bg-opacity-10 rounded-3 p-2 flex-shrink-0">
            <i class="bi bi-lock-fill text-success fs-5"></i>
        </div>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-1 small">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                {{ __('ui.upgrade_to_pro') }}
            </h6>
            <p class="text-secondary small mb-0">{{ $msg }}</p>
        </div>
        <a href="{{ route('settings.company') }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold flex-shrink-0">
            <i class="bi bi-arrow-up-circle me-1"></i>{{ __('ui.upgrade') }}
        </a>
    </div>
</div>
