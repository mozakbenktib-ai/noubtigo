@php
    $type = $content->type;
    $duration = $content->duration ?? 10;
@endphp

<div class="signage-slide active preview-slide-card" data-duration="{{ $duration }}" data-type="{{ $type }}">
    @if($type === \App\Modules\Displays\Models\DisplayContent::TYPE_QR_TRACKING)
        <!-- QR / Tracking Type -->
        <div class="qr-slide-content">
            <span class="signage-badge-pill">
                <i class="bi bi-phone"></i> {{ __('ui.mobile_live_tracking', [], null) ?: 'Mobile Live Tracking' }}
            </span>

            <h2 class="slide-heading">{{ $content->title }}</h2>
            @if($content->description)
                <p class="slide-lead">{{ $content->description }}</p>
            @endif

            <div class="qr-showcase-box">
                <img src="{{ $content->qr_image_url }}" alt="Scan QR Code" class="qr-image-display" loading="lazy">
                <div class="qr-footer-hint">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>{{ __('ui.scan_with_camera', [], null) ?: 'Scan with phone camera' }}</span>
                </div>
            </div>

            <div class="qr-perks-row">
                <div class="qr-perk-pill">
                    <i class="bi bi-check-circle-fill"></i> {{ __('ui.zero_app_install') }}
                </div>
                <div class="qr-perk-pill">
                    <i class="bi bi-check-circle-fill"></i> {{ __('ui.live_turn_timer') }}
                </div>
                <div class="qr-perk-pill">
                    <i class="bi bi-check-circle-fill"></i> {{ __('ui.wait_in_comfort') }}
                </div>
            </div>
        </div>

    @elseif($type === \App\Modules\Displays\Models\DisplayContent::TYPE_PROMOTION)
        <!-- Promotion Type: High visual impact -->
        <div class="promo-slide-content">
            <span class="signage-badge-pill bg-emerald">
                <i class="bi bi-megaphone-fill"></i> {{ __('ui.special_feature') }}
            </span>

            <h2 class="slide-heading">{{ $content->title }}</h2>
            @if($content->description)
                <p class="slide-lead">{{ $content->description }}</p>
            @endif

            @if($content->image_url)
                <div class="promo-banner-wrapper my-3">
                    <img src="{{ $content->image_url }}" alt="{{ $content->title }}" class="promo-banner-img">
                </div>
            @endif

            @if($content->link_url)
                <div class="promo-action-pill mt-2">
                    <i class="bi bi-link-45deg"></i>
                    <span>{{ $content->link_url }}</span>
                </div>
            @endif
        </div>

    @elseif($type === \App\Modules\Displays\Models\DisplayContent::TYPE_ANNOUNCEMENT)
        <!-- Announcement Type: Notice / Alert -->
        <div class="announcement-slide-content">
            <span class="signage-badge-pill bg-warning-subtle text-warning-emphasis">
                <i class="bi bi-bell-fill"></i> {{ __('ui.announcement', [], null) ?: 'Announcement' }}
            </span>

            <h2 class="slide-heading text-warning-emphasis">{{ $content->title }}</h2>
            @if($content->description)
                <div class="announcement-box p-4 rounded-4 my-3">
                    <p class="slide-lead mb-0">{{ $content->description }}</p>
                </div>
            @endif

            @if($content->image_url)
                <div class="promo-banner-wrapper my-3">
                    <img src="{{ $content->image_url }}" alt="{{ $content->title }}" class="promo-banner-img">
                </div>
            @endif
        </div>

    @else
        <!-- Information Type (Default) -->
        <div class="info-slide-content">
            <span class="signage-badge-pill">
                <i class="bi bi-info-circle-fill"></i> {{ __('ui.information', [], null) ?: 'Information' }}
            </span>

            <h2 class="slide-heading">{{ $content->title }}</h2>
            @if($content->description)
                <p class="slide-lead">{{ $content->description }}</p>
            @endif

            @if($content->image_url)
                <div class="info-media-wrapper my-3">
                    <img src="{{ $content->image_url }}" alt="{{ $content->title }}" class="info-media-img">
                </div>
            @endif

            <div class="guideline-steps-list mt-3">
                <div class="guideline-item">
                    <div class="guideline-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6>{{ __('ui.noubtigo_smart_queue') }}</h6>
                        <p>{{ __('ui.smart_queue_tagline') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
