@extends('layouts.dashboard')

@section('title', __('ui.company_settings') . ' | Noubtigo')
@section('header_title', __('ui.company_settings'))
@section('header_subtitle', __('ui.manage_brand'))

@push('styles')
<style>
    .priority-rule-list { display: grid; gap: .75rem; }
    .priority-rule-card { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--bs-border-color); border-radius: 1rem; background: var(--bs-body-bg); transition: border-color .2s, box-shadow .2s, opacity .2s; }
    .priority-rule-card[draggable="true"] { cursor: grab; }
    .priority-rule-card.dragging { opacity: .45; }
    .priority-rule-card.drag-over { border-color: var(--bs-primary); box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .12); }
    .priority-rank { width: 2.25rem; height: 2.25rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 700; background: var(--bs-primary-bg-subtle); color: var(--bs-primary); }
    .priority-rule-actions { margin-left: auto; display: flex; gap: .35rem; }

    /* Modern Queue Mode Cards */
    .queue-mode-card {
        border: 2px solid var(--bs-border-color-translucent);
        background-color: var(--bs-body-bg);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .queue-mode-card:hover:not(.disabled) {
        border-color: rgba(var(--bs-primary-rgb), 0.4);
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -10px rgba(var(--bs-primary-rgb), 0.15);
    }
    .btn-check:checked + .queue-mode-card.simple {
        border-color: var(--bs-success);
        background-color: rgba(var(--bs-success-rgb), 0.02);
        box-shadow: 0 12px 24px -10px rgba(var(--bs-success-rgb), 0.25);
    }
    .btn-check:checked + .queue-mode-card.advanced {
        border-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.02);
        box-shadow: 0 12px 24px -10px rgba(var(--bs-primary-rgb), 0.25);
    }
    .btn-check:checked + .queue-mode-card .icon-container.simple {
        background: linear-gradient(135deg, var(--bs-success), #10b981) !important;
        box-shadow: 0 6px 15px rgba(var(--bs-success-rgb), 0.35);
    }
    .btn-check:checked + .queue-mode-card .icon-container.advanced {
        background: linear-gradient(135deg, var(--bs-primary), #3b82f6) !important;
        box-shadow: 0 6px 15px rgba(var(--bs-primary-rgb), 0.35);
    }
    .btn-check:checked + .queue-mode-card .icon-container i {
        color: white !important;
    }
    .queue-mode-card .icon-container {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-check:checked + .queue-mode-card .check-indicator {
        opacity: 1;
        transform: scale(1);
    }
    .check-indicator {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 26px;
        height: 26px;
        background: var(--bs-success);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .queue-mode-card.advanced .check-indicator {
        background: var(--bs-primary);
    }
</style>
@endpush

@section('content')
<div class="row fade-in">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">{{ __('ui.general_info') }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ __('ui.settings_updated') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.company_name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.email_address') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold d-block">{{ __('ui.phone_number') }}</label>
                            <input type="tel" id="company_phone" class="form-control w-100" value="{{ old('phone', $company->phone) }}">
                            <input type="hidden" name="phone" id="hidden_company_phone" value="{{ old('phone', $company->phone) }}">
                            <div id="company_phone_error" class="text-danger small mt-1 d-none">Invalid phone number</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.timezone') }}</label>
                            <div class="input-group">
                                <select name="timezone" id="timezoneSelect" class="form-select">
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ old('timezone', $company->timezone) == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-success" id="autoDetectTz" title="{{ __('ui.auto_detect') }}">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </button>
                            </div>
                            <div class="form-text small opacity-75">{{ __('ui.timezone_help') }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">{{ __('ui.address') }}</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $company->address) }}</textarea>
                    </div>

                    <hr class="my-5 opacity-10">

                    <h5 class="fw-bold mb-4">{{ __('ui.branding_appearance') }}</h5>
                    
                    <div class="row align-items-center mb-5">
                        <div class="col-auto">
                            <div class="logo-preview bg-light p-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                @if($company->logo)
                                    <img src="{{ $company->getLogoUrl() }}" alt="Logo" class="img-fluid rounded-2">
                                @else
                                    <i class="bi bi-image fs-1 text-secondary opacity-50"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label small fw-bold">{{ __('ui.company_logo') }}</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <div class="form-text mt-1">{{ __('ui.logo_recommendation') }}</div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.primary_color') }}</label>
                            <div class="input-group">
                                <input type="color" name="primary_color" class="form-control form-control-color border-0 p-1 w-25" value="{{ $company->getBrandingColors()['primary'] }}" title="Choose primary color">
                                <input type="text" class="form-control" id="primaryColorText" value="{{ $company->getBrandingColors()['primary'] }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.secondary_color') }}</label>
                            <div class="input-group">
                                <input type="color" name="secondary_color" class="form-control form-control-color border-0 p-1 w-25" value="{{ $company->getBrandingColors()['secondary'] }}" title="Choose secondary color">
                                <input type="text" class="form-control" id="secondaryColorText" value="{{ $company->getBrandingColors()['secondary'] }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold mb-3">{{ __('ui.gradient_preview') }}</label>
                        <div id="gradientPreview" class="rounded-4 p-5 d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="background: {{ $company->getBrandingColors()['gradient'] }};">
                            {{ __('ui.live_preview') }}
                        </div>
                    </div>

                    <hr class="my-5 opacity-10">

                    <h5 class="fw-bold mb-4"><i class="bi bi-toggles me-2 text-primary"></i>{{ __('ui.queue_mode') }}</h5>
                    <p class="text-secondary small mb-3">{{ __('ui.queue_mode_description') }}</p>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <input type="radio" class="btn-check" name="queue_mode" id="queueModeSimple" value="simple" {{ $company->getQueueMode() === 'simple' ? 'checked' : '' }}>
                            <label class="queue-mode-card simple w-100 py-4 px-4 rounded-4 text-start m-0 d-block h-100" for="queueModeSimple">
                                <div class="check-indicator"><i class="bi bi-check fs-5"></i></div>
                                <div class="d-flex flex-column align-items-center text-center gap-3 mt-2">
                                    <div class="icon-container simple bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                        <i class="bi bi-lightning-charge-fill text-success fs-2 m-0"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2 text-dark">{{ __('ui.simple_queue') }}</h5>
                                        <p class="text-secondary small mb-0 lh-base px-2">{{ __('ui.simple_queue_desc') }}</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6 mb-3">
                            @php
                                $canAdvanced = $company->hasFeature('queue.advanced');
                            @endphp
                            <input type="radio" class="btn-check" name="queue_mode" id="queueModeAdvanced" value="advanced" {{ $company->getQueueMode() === 'advanced' ? 'checked' : '' }} {{ !$canAdvanced ? 'disabled' : '' }}>
                            <label class="queue-mode-card advanced w-100 py-4 px-4 rounded-4 text-start m-0 d-block h-100 {{ !$canAdvanced ? 'disabled opacity-50' : '' }}" for="queueModeAdvanced">
                                <div class="check-indicator"><i class="bi bi-check fs-5"></i></div>
                                <div class="d-flex flex-column align-items-center text-center gap-3 mt-2">
                                    <div class="icon-container advanced bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                        <i class="bi bi-sliders2 text-primary fs-2 m-0"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-2 text-dark">
                                            {{ __('ui.advanced_queue') }}
                                            @if(!$canAdvanced)
                                                <span class="badge bg-warning text-dark align-text-top ms-1 shadow-sm" style="font-size: 0.6rem; padding: 0.35em 0.65em;">
                                                    <i class="bi bi-lock-fill"></i> {{ __('ui.upgrade') }}
                                                </span>
                                            @endif
                                        </h5>
                                        <p class="text-secondary small mb-0 lh-base px-2">{{ __('ui.advanced_queue_desc') }}</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="my-5 opacity-10">

                    <div id="advancedQueueRules" style="{{ $company->getQueueMode() === 'simple' ? 'display:none' : '' }}">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">{{ __('ui.queue_rules_config') }}</h5>
                            <p class="text-secondary small mb-0">{{ __('ui.queue_rules_description') }}</p>
                        </div>
                        <button type="button" class="btn btn-light btn-sm rounded-3" id="resetPriorityRules">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('ui.queue_rules_reset') }}
                        </button>
                    </div>

                    @php
                        $qRules = $company->getQueueRules();
                        $priorityRules = [
                            'vip' => ['label' => __('ui.queue_rule_vip'), 'description' => __('ui.queue_rule_vip_description'), 'icon' => 'star-fill', 'color' => 'danger'],
                            'on_time_appointment' => ['label' => __('ui.queue_rule_on_time'), 'description' => __('ui.queue_rule_on_time_description'), 'icon' => 'calendar-check', 'color' => 'success'],
                            'in_grace_appointment' => ['label' => __('ui.queue_rule_grace'), 'description' => __('ui.queue_rule_grace_description'), 'icon' => 'clock-history', 'color' => 'warning'],
                            'walk_in' => ['label' => __('ui.queue_rule_walk_in'), 'description' => __('ui.queue_rule_walk_in_description'), 'icon' => 'person-walking', 'color' => 'secondary'],
                        ];
                        $priorityRules = collect($priorityRules)->sortBy(fn ($rule, $key) => $qRules[$key]);
                    @endphp

                    <div class="priority-rule-list mb-2" id="priorityRuleList" aria-label="{{ __('ui.queue_rules_aria_label') }}">
                        @foreach($priorityRules as $key => $rule)
                            <div class="priority-rule-card" draggable="true" data-rule="{{ $key }}">
                                <span class="priority-rank" aria-hidden="true"></span>
                                <i class="bi bi-grip-vertical text-muted fs-5" aria-hidden="true"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-{{ $rule['color'] }}"><i class="bi bi-{{ $rule['icon'] }} me-2"></i>{{ $rule['label'] }}</div>
                                    <small class="text-secondary">{{ $rule['description'] }}</small>
                                </div>
                                <input type="hidden" name="queue_rules_{{ $key === 'on_time_appointment' ? 'on_time' : ($key === 'in_grace_appointment' ? 'grace' : $key) }}" value="{{ $qRules[$key] }}">
                                <div class="priority-rule-actions">
                                    <button type="button" class="btn btn-light btn-sm rule-move-up" title="{{ __('ui.queue_rules_move_up') }}" aria-label="{{ __('ui.queue_rules_move_up') }}"><i class="bi bi-chevron-up"></i></button>
                                    <button type="button" class="btn btn-light btn-sm rule-move-down" title="{{ __('ui.queue_rules_move_down') }}" aria-label="{{ __('ui.queue_rules_move_down') }}"><i class="bi bi-chevron-down"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-secondary small mb-4"><i class="bi bi-info-circle me-1"></i> {{ __('ui.queue_rules_help') }}</p>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-5">
                        <button type="button" class="btn btn-light px-4 rounded-3 fw-bold">{{ __('ui.cancel') }}</button>
                        <button type="submit" class="btn btn-success px-5 rounded-3 fw-bold shadow-sm">{{ __('ui.save_changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">

        <div class="card border-0 shadow-sm rounded-4 mb-4 border-success">
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold text-success mb-3"><i class="bi bi-qr-code-scan me-2"></i>{{ __('ui.tracking_hub_qr') ?? 'Tracking Hub QR' }}</h5>
                <p class="text-secondary small">{{ __('ui.print_qr_help') ?? 'Print this QR code and place it at your location for customers to scan.' }}</p>
                
                @php
                    $trackingUrl = route('queue.track.hub', $company->secure_public_token);
                @endphp
                
                <div class="bg-white p-3 d-inline-block rounded-3 border mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($trackingUrl) }}" alt="QR Code" class="img-fluid">
                </div>
                
                <div class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control bg-light" value="{{ $trackingUrl }}" id="trackingUrlInput" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('trackingUrlInput').value); alert('Copied!');">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('settings.company.print-qr') }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill fw-bold">
                        <i class="bi bi-printer-fill me-1"></i> {{ __('ui.print_poster') ?? 'Print Tracking Poster' }}
                    </a>
                    <a href="{{ $trackingUrl }}" target="_blank" class="btn btn-success btn-sm rounded-pill fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('ui.open_hub') ?? 'Open Tracking Hub' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
<script>
    const primaryInput = document.querySelector('input[name="primary_color"]');
    const secondaryInput = document.querySelector('input[name="secondary_color"]');
    const primaryText = document.getElementById('primaryColorText');
    const secondaryText = document.getElementById('secondaryColorText');
    const preview = document.getElementById('gradientPreview');

    function updatePreview() {
        primaryText.value = primaryInput.value.toUpperCase();
        secondaryText.value = secondaryInput.value.toUpperCase();
        preview.style.background = `linear-gradient(135deg, ${primaryInput.value}, ${secondaryInput.value})`;
    }

    primaryInput.addEventListener('input', updatePreview);
    secondaryInput.addEventListener('input', updatePreview);

    // The visual order is the saved priority order.
    const priorityRuleList = document.getElementById('priorityRuleList');
    const defaultPriorityOrder = ['vip', 'on_time_appointment', 'in_grace_appointment', 'walk_in'];

    function syncPriorityRules() {
        if (!priorityRuleList) return;

        [...priorityRuleList.children].forEach((card, index, cards) => {
            card.querySelector('.priority-rank').textContent = index + 1;
            card.querySelector('input[type="hidden"]').value = index + 1;
            card.querySelector('.rule-move-up').disabled = index === 0;
            card.querySelector('.rule-move-down').disabled = index === cards.length - 1;
        });
    }

    if (priorityRuleList) {
        priorityRuleList.addEventListener('click', event => {
            const card = event.target.closest('.priority-rule-card');
            if (!card) return;

            if (event.target.closest('.rule-move-up') && card.previousElementSibling) {
                priorityRuleList.insertBefore(card, card.previousElementSibling);
            }
            if (event.target.closest('.rule-move-down') && card.nextElementSibling) {
                priorityRuleList.insertBefore(card.nextElementSibling, card);
            }
            syncPriorityRules();
        });

        priorityRuleList.addEventListener('dragstart', event => {
            const card = event.target.closest('.priority-rule-card');
            if (!card) return;
            card.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
        });

        priorityRuleList.addEventListener('dragend', event => {
            event.target.closest('.priority-rule-card')?.classList.remove('dragging');
            priorityRuleList.querySelectorAll('.drag-over').forEach(card => card.classList.remove('drag-over'));
            syncPriorityRules();
        });

        priorityRuleList.addEventListener('dragover', event => {
            event.preventDefault();
            const target = event.target.closest('.priority-rule-card');
            const dragging = priorityRuleList.querySelector('.dragging');
            if (!target || !dragging || target === dragging) return;

            target.classList.add('drag-over');
            const bounds = target.getBoundingClientRect();
            priorityRuleList.insertBefore(dragging, event.clientY < bounds.top + bounds.height / 2 ? target : target.nextElementSibling);
        });

        priorityRuleList.addEventListener('dragleave', event => {
            event.target.closest('.priority-rule-card')?.classList.remove('drag-over');
        });

        document.getElementById('resetPriorityRules')?.addEventListener('click', () => {
            defaultPriorityOrder.forEach(key => {
                const card = priorityRuleList.querySelector(`[data-rule="${key}"]`);
                if (card) priorityRuleList.appendChild(card);
            });
            syncPriorityRules();
        });

        syncPriorityRules();
    }

    // Queue Mode Toggle — show/hide advanced queue rules
    document.querySelectorAll('input[name="queue_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const rulesDiv = document.getElementById('advancedQueueRules');
            if (this.value === 'simple') {
                rulesDiv.style.display = 'none';
            } else {
                rulesDiv.style.display = '';
            }
        });
    });

    // Timezone Auto-detection
    document.getElementById('autoDetectTz').addEventListener('click', function() {
        const detectedTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const select = document.getElementById('timezoneSelect');
        
        if (detectedTz) {
            // Check if timezone exists in select
            let found = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === detectedTz) {
                    select.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            
            if (found) {
                this.classList.remove('btn-outline-success');
                this.classList.add('btn-success');
                setTimeout(() => {
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-success');
                }, 1000);
            } else {
                alert('Detected timezone (' + detectedTz + ') not found in list. Reverting to UTC.');
                select.value = 'UTC';
            }
        }
    });

    // Auto-detect on load if no timezone is set (or if it's default UTC and we want to be helpful)
    @if(!$company->timezone || $company->timezone == 'UTC')
    window.addEventListener('load', () => {
        // Optional: you could auto-trigger the click here if you want it to be fully automatic on first load
        // But manual button is safer for settings.
    });
    @endif

    // Initialize intlTelInput on document load
    let itiCompany;
    const companyPhoneEl = document.getElementById('company_phone');
    const hiddenPhoneEl = document.getElementById('hidden_company_phone');

    document.addEventListener("DOMContentLoaded", function() {
        if (companyPhoneEl) {
            // Format phone to prepended '+' if it's purely numeric
            let phoneVal = companyPhoneEl.value.trim();
            if (phoneVal && !phoneVal.startsWith('+') && /^\d+$/.test(phoneVal)) {
                phoneVal = '+' + phoneVal;
                companyPhoneEl.value = phoneVal;
            }

            itiCompany = window.intlTelInput(companyPhoneEl, {
                initialCountry: "ma",
                preferredCountries: ["ma", "fr", "es"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js"
            });
        }

        // Add submit validator to form
        const form = companyPhoneEl.closest('form');
        form.addEventListener('submit', function(e) {
            const errorEl = document.getElementById('company_phone_error');
            errorEl.classList.add('d-none');
            companyPhoneEl.classList.remove('is-invalid');

            const val = companyPhoneEl.value.trim();
            if (val) {
                if (itiCompany && !itiCompany.isValidNumber()) {
                    e.preventDefault();
                    errorEl.textContent = 'Invalid phone number for the selected country.';
                    errorEl.classList.remove('d-none');
                    companyPhoneEl.classList.add('is-invalid');
                    return;
                }
                hiddenPhoneEl.value = itiCompany ? itiCompany.getNumber().replace(/\D/g, '') : val.replace(/\D/g, '');
            } else {
                hiddenPhoneEl.value = '';
            }
        });
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">
<style>
    .form-control-color {
        height: 45px;
        cursor: pointer;
    }
    .form-control:read-only {
        background-color: #f8fafc;
    }
    .iti {
        width: 100% !important;
        display: block !important;
    }
    .iti__country-list {
        z-index: 1060 !important;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #eee;
    }
    .iti input {
        padding-left: 52px !important;
    }
    [dir="rtl"] .iti input {
        padding-left: 12px !important;
        padding-right: 52px !important;
    }
</style>
@endpush
